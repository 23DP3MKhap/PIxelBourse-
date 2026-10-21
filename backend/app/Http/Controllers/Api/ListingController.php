<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Listing\StoreListingRequest;
use App\Http\Requests\Listing\UpdateListingRequest;
use App\Http\Resources\ListingResource;
use App\Models\CoinTransaction;
use App\Models\Image;
use App\Models\Listing;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ListingController extends Controller
{
    private const SORTS = ['newest', 'oldest', 'price_asc', 'price_desc', 'value_desc', 'clicks_desc'];

    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['coins', 'trade'])],
            'min_price' => ['nullable', 'integer', 'min:0'],
            'max_price' => ['nullable', 'integer', 'min:0'],
            'sort' => ['nullable', Rule::in(self::SORTS)],
        ]);

        $query = Listing::query()->active()->with(['image', 'seller']);

        $search = $request->string('search')->trim()->value();

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->whereHas('image', fn ($image) => $image->where('title', 'like', "%{$search}%"))
                    ->orWhereHas('seller', fn ($seller) => $seller->where('username', 'like', "%{$search}%"));
            });
        }

        match ($request->input('type')) {
            'coins' => $query->where('coin_purchase_available', true),
            'trade' => $query->where('trade_available', true),
            default => null,
        };

        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->integer('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->integer('max_price'));
        }

        match ($request->input('sort', 'newest')) {
            'oldest' => $query->oldest('id'),
            'price_asc' => $query->orderByRaw('price is null')->orderBy('price'),
            'price_desc' => $query->orderByRaw('price is null')->orderByDesc('price'),
            'value_desc' => $query->orderByDesc(Image::select('value')->whereColumn('images.id', 'listings.image_id')),
            'clicks_desc' => $query->orderByDesc(Image::select('clicks_count')->whereColumn('images.id', 'listings.image_id')),
            default => $query->latest('id'),
        };

        return ListingResource::collection($query->paginate(20)->withQueryString());
    }

    public function mine(#[CurrentUser] User $user): AnonymousResourceCollection
    {
        $listings = $user->sellingListings()->with(['image', 'seller'])->latest('id')->paginate(20);

        return ListingResource::collection($listings);
    }

    public function show(Listing $listing): ListingResource
    {
        return new ListingResource($listing->load(['image', 'seller']));
    }

    public function store(StoreListingRequest $request, #[CurrentUser] User $user): ListingResource
    {
        $image = Image::findOrFail($request->integer('image_id'));

        abort_unless($image->owner_id === $user->id, 403, 'You can only list your own images.');
        abort_if($image->listings()->active()->exists(), 409, 'This image is already listed.');

        $coins = $request->boolean('coin_purchase_available', true);

        $listing = Listing::create([
            'image_id' => $image->id,
            'seller_id' => $user->id,
            'price' => $coins ? $request->integer('price') : $request->input('price'),
            'coin_purchase_available' => $coins,
            'trade_available' => $request->boolean('trade_available'),
            'status' => Listing::STATUS_ACTIVE,
        ]);

        return new ListingResource($listing->load(['image', 'seller']));
    }

    public function update(UpdateListingRequest $request, Listing $listing, #[CurrentUser] User $user): ListingResource
    {
        $this->ensureSellerCanChange($listing, $user);

        $listing->update(['price' => $request->integer('price')]);

        return new ListingResource($listing->load(['image', 'seller']));
    }

    public function destroy(Listing $listing, #[CurrentUser] User $user): ListingResource
    {
        $this->ensureSellerCanChange($listing, $user);

        DB::transaction(function () use ($listing) {
            $listing->update([
                'status' => Listing::STATUS_CANCELLED,
                'closed_at' => now(),
            ]);

            $listing->trades()->pending()->update([
                'status' => Trade::STATUS_CANCELLED,
                'responded_at' => now(),
            ]);
        });

        return new ListingResource($listing->load(['image', 'seller']));
    }

    public function buy(Listing $listing, #[CurrentUser] User $user): ListingResource
    {
        $listing = DB::transaction(function () use ($listing, $user) {
            $listing = Listing::lockForUpdate()->findOrFail($listing->id);

            abort_unless($listing->isActive(), 409, 'This listing is no longer active.');
            abort_unless($listing->coin_purchase_available, 409, 'This listing cannot be bought for coins.');
            abort_if($listing->seller_id === $user->id, 403, 'You cannot buy your own listing.');

            $buyer = User::lockForUpdate()->findOrFail($user->id);
            $seller = User::lockForUpdate()->findOrFail($listing->seller_id);
            $image = Image::lockForUpdate()->findOrFail($listing->image_id);

            if ($buyer->coin_balance < $listing->price) {
                throw ValidationException::withMessages([
                    'coin_balance' => 'You do not have enough coins.',
                ]);
            }

            $fee = Listing::feeFor($listing->price);
            $earned = $listing->price - $fee;

            $buyer->decrement('coin_balance', $listing->price);
            $seller->increment('coin_balance', $earned);

            $image->update(['owner_id' => $buyer->id]);

            Trade::cancelPendingInvolving([$image->id]);

            $listing->update([
                'status' => Listing::STATUS_SOLD,
                'buyer_id' => $buyer->id,
                'closed_at' => now(),
            ]);

            CoinTransaction::create([
                'user_id' => $buyer->id,
                'listing_id' => $listing->id,
                'type' => CoinTransaction::TYPE_PURCHASE,
                'amount' => -$listing->price,
            ]);

            CoinTransaction::create([
                'user_id' => $seller->id,
                'listing_id' => $listing->id,
                'type' => CoinTransaction::TYPE_SALE,
                'amount' => $earned,
                'fee' => $fee,
            ]);

            return $listing;
        });

        return new ListingResource($listing->load(['image', 'seller']));
    }

    private function ensureSellerCanChange(Listing $listing, User $user): void
    {
        abort_unless($listing->seller_id === $user->id, 403, 'This is not your listing.');
        abort_unless($listing->isActive(), 409, 'This listing is no longer active.');
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Trade\StoreTradeRequest;
use App\Http\Resources\TradeResource;
use App\Models\Image;
use App\Models\Listing;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TradeController extends Controller
{
    private const RELATIONS = ['initiator', 'receiver', 'offeredImage', 'requestedImage'];

    public function index(Request $request, #[CurrentUser] User $user): AnonymousResourceCollection
    {
        $request->validate([
            'box' => ['nullable', Rule::in(['incoming', 'outgoing'])],
            'status' => ['nullable', Rule::in([Trade::STATUS_PENDING, Trade::STATUS_ACCEPTED, Trade::STATUS_DECLINED, Trade::STATUS_CANCELLED])],
        ]);

        $query = Trade::query()->with(self::RELATIONS)->latest('id');

        match ($request->input('box')) {
            'incoming' => $query->where('receiver_id', $user->id),
            'outgoing' => $query->where('initiator_id', $user->id),
            default => $query->involving($user),
        };

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return TradeResource::collection($query->paginate(20)->withQueryString());
    }

    public function show(Trade $trade, #[CurrentUser] User $user): TradeResource
    {
        abort_unless(in_array($user->id, [$trade->initiator_id, $trade->receiver_id], true), 403, 'This is not your trade.');

        return new TradeResource($trade->load(self::RELATIONS));
    }

    public function store(StoreTradeRequest $request, #[CurrentUser] User $user): TradeResource
    {
        $listing = Listing::findOrFail($request->integer('listing_id'));
        $offered = Image::findOrFail($request->integer('offered_image_id'));

        abort_unless($listing->isActive() && $listing->trade_available, 409, 'This listing is not open for trades.');
        abort_if($listing->seller_id === $user->id, 403, 'You cannot trade with your own listing.');
        abort_unless($offered->owner_id === $user->id, 403, 'You can only offer your own images.');
        abort_if($offered->listings()->active()->exists(), 409, 'Take your image off the market before offering it.');
        abort_if(
            $listing->trades()->pending()->where('initiator_id', $user->id)->exists(),
            409,
            'You already have a pending offer for this listing.',
        );

        $trade = Trade::create([
            'initiator_id' => $user->id,
            'receiver_id' => $listing->seller_id,
            'offered_image_id' => $offered->id,
            'requested_image_id' => $listing->image_id,
            'listing_id' => $listing->id,
            'status' => Trade::STATUS_PENDING,
        ]);

        return new TradeResource($trade->load(self::RELATIONS));
    }

    public function accept(Trade $trade, #[CurrentUser] User $user): TradeResource
    {
        $trade = DB::transaction(function () use ($trade, $user) {
            $trade = Trade::lockForUpdate()->findOrFail($trade->id);

            abort_unless($trade->receiver_id === $user->id, 403, 'Only the receiver can accept this trade.');
            abort_unless($trade->isPending(), 409, 'This trade is no longer pending.');

            $listing = $trade->listing_id ? Listing::lockForUpdate()->find($trade->listing_id) : null;
            $offered = Image::lockForUpdate()->findOrFail($trade->offered_image_id);
            $requested = Image::lockForUpdate()->findOrFail($trade->requested_image_id);

            abort_if($listing !== null && ! $listing->isActive(), 409, 'This listing is no longer active.');
            abort_unless(
                $offered->owner_id === $trade->initiator_id && $requested->owner_id === $trade->receiver_id,
                409,
                'One of the images has changed owner.',
            );
            abort_if($offered->listings()->active()->exists(), 409, 'The offered image is listed on the market.');

            $offered->update(['owner_id' => $trade->receiver_id]);
            $requested->update(['owner_id' => $trade->initiator_id]);

            $trade->update([
                'status' => Trade::STATUS_ACCEPTED,
                'responded_at' => now(),
            ]);

            $listing?->update([
                'status' => Listing::STATUS_TRADED,
                'buyer_id' => $trade->initiator_id,
                'closed_at' => now(),
            ]);

            Trade::cancelPendingInvolving([$offered->id, $requested->id]);

            return $trade;
        });

        return new TradeResource($trade->load(self::RELATIONS));
    }

    public function decline(Trade $trade, #[CurrentUser] User $user): TradeResource
    {
        abort_unless($trade->receiver_id === $user->id, 403, 'Only the receiver can decline this trade.');

        return $this->close($trade, Trade::STATUS_DECLINED);
    }

    public function cancel(Trade $trade, #[CurrentUser] User $user): TradeResource
    {
        abort_unless($trade->initiator_id === $user->id, 403, 'Only the sender can cancel this trade.');

        return $this->close($trade, Trade::STATUS_CANCELLED);
    }

    private function close(Trade $trade, string $status): TradeResource
    {
        abort_unless($trade->isPending(), 409, 'This trade is no longer pending.');

        $trade->update([
            'status' => $status,
            'responded_at' => now(),
        ]);

        return new TradeResource($trade->load(self::RELATIONS));
    }
}

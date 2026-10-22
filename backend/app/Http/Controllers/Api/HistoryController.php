<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Image;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class HistoryController extends Controller
{
    private const PER_PAGE = 20;

    public function __invoke(Request $request, #[CurrentUser] User $user): AnonymousResourceCollection
    {
        $request->validate([
            'type' => ['nullable', Rule::in(['bought', 'sold', 'traded', 'sold_to_site'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $type = $request->input('type');

        $entries = collect()
            ->merge($type === null || $type === 'bought' ? $this->coinEntries($user, CoinTransaction::TYPE_PURCHASE) : [])
            ->merge($type === null || $type === 'sold' ? $this->coinEntries($user, CoinTransaction::TYPE_SALE) : [])
            ->merge($type === null || $type === 'traded' ? $this->tradeEntries($user) : [])
            ->merge($type === null || $type === 'sold_to_site' ? $this->siteSaleEntries($user) : [])
            ->sortByDesc('date')
            ->values();

        $page = $request->integer('page', 1);

        $paginator = new LengthAwarePaginator(
            $entries->forPage($page, self::PER_PAGE)->values(),
            $entries->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );

        return JsonResource::collection($paginator);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function coinEntries(User $user, string $type): array
    {
        $bought = $type === CoinTransaction::TYPE_PURCHASE;

        return $user->coinTransactions()
            ->where('type', $type)
            ->with(['listing.image', 'listing.seller', 'listing.buyer'])
            ->get()
            ->map(fn (CoinTransaction $transaction) => [
                'type' => $bought ? 'bought' : 'sold',
                'date' => $transaction->created_at,
                'coins' => $transaction->amount,
                'fee' => $transaction->fee,
                'received_image' => $bought ? $this->image($transaction->listing?->image) : null,
                'given_image' => $bought ? null : $this->image($transaction->listing?->image),
                'other_user' => $this->user($bought ? $transaction->listing?->seller : $transaction->listing?->buyer),
                'listing_id' => $transaction->listing_id,
                'trade_id' => null,
            ])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function tradeEntries(User $user): array
    {
        return Trade::query()
            ->involving($user)
            ->where('status', Trade::STATUS_ACCEPTED)
            ->with(['initiator', 'receiver', 'offeredImage', 'requestedImage'])
            ->get()
            ->map(function (Trade $trade) use ($user) {
                $initiated = $trade->initiator_id === $user->id;

                return [
                    'type' => 'traded',
                    'date' => $trade->responded_at,
                    'coins' => null,
                    'fee' => null,
                    'received_image' => $this->image($initiated ? $trade->requestedImage : $trade->offeredImage),
                    'given_image' => $this->image($initiated ? $trade->offeredImage : $trade->requestedImage),
                    'other_user' => $this->user($initiated ? $trade->receiver : $trade->initiator),
                    'listing_id' => $trade->listing_id,
                    'trade_id' => $trade->id,
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function siteSaleEntries(User $user): array
    {
        return $user->coinTransactions()
            ->where('type', CoinTransaction::TYPE_SITE_SALE)
            ->get()
            ->map(fn (CoinTransaction $transaction) => [
                'type' => 'sold_to_site',
                'date' => $transaction->created_at,
                'coins' => $transaction->amount,
                'fee' => $transaction->fee,
                'received_image' => null,
                'given_image' => null,
                'other_user' => null,
                'listing_id' => null,
                'trade_id' => null,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function image(?Image $image): ?array
    {
        return $image === null ? null : [
            'id' => $image->id,
            'title' => $image->title,
            'url' => $image->file_path,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function user(?User $user): ?array
    {
        return $user === null ? null : [
            'id' => $user->id,
            'username' => $user->username,
        ];
    }
}

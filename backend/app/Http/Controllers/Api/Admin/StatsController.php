<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Image;
use App\Models\Listing;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class StatsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $sales = CoinTransaction::query()->where('type', CoinTransaction::TYPE_SALE);
        $siteSales = CoinTransaction::query()->where('type', CoinTransaction::TYPE_SITE_SALE);

        return response()->json([
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', UserRole::Admin)->count(),
                'new_last_7_days' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'images' => [
                'total' => Image::count(),
                'listed' => Listing::active()->count(),
                'total_clicks' => (int) Image::sum('clicks_count'),
                'total_value' => (int) Image::sum('value'),
            ],
            'listings' => $this->countByStatus(Listing::query(), [
                Listing::STATUS_ACTIVE, Listing::STATUS_SOLD, Listing::STATUS_TRADED, Listing::STATUS_CANCELLED,
            ]),
            'trades' => $this->countByStatus(Trade::query(), [
                Trade::STATUS_PENDING, Trade::STATUS_ACCEPTED, Trade::STATUS_DECLINED, Trade::STATUS_CANCELLED,
            ]),
            'coins' => [
                'in_circulation' => (int) User::sum('coin_balance'),
                'sales_count' => (clone $sales)->count(),
                'turnover' => (int) -CoinTransaction::where('type', CoinTransaction::TYPE_PURCHASE)->sum('amount'),
                'site_sales_count' => (clone $siteSales)->count(),
                'paid_by_site' => (int) (clone $siteSales)->sum('amount'),
                'fees' => (int) CoinTransaction::whereIn('type', [CoinTransaction::TYPE_SALE, CoinTransaction::TYPE_SITE_SALE])->sum('fee'),
            ],
        ]);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  array<int, string>  $statuses
     * @return array<string, int>
     */
    private function countByStatus(Builder $query, array $statuses): array
    {
        $counts = $query->toBase()
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $result = ['total' => 0];

        foreach ($statuses as $status) {
            $result[$status] = (int) ($counts[$status] ?? 0);
            $result['total'] += $result[$status];
        }

        return $result;
    }
}

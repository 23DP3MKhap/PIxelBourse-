<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoinTransaction;
use App\Models\Image;
use App\Models\Listing;
use App\Models\User;
use App\Services\CloudinaryFiles;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ImageSaleController extends Controller
{
    public function __invoke(Image $image, #[CurrentUser] User $user, CloudinaryFiles $files): JsonResponse
    {
        $result = DB::transaction(function () use ($image, $user) {
            $image = Image::lockForUpdate()->findOrFail($image->id);

            abort_unless($image->owner_id === $user->id, 403, 'You can only sell your own images.');
            abort_if($image->listings()->active()->exists(), 409, 'Take the image off the market before selling it to the website.');

            $fee = Listing::feeFor($image->value);
            $earned = $image->value - $fee;

            $seller = User::lockForUpdate()->findOrFail($user->id);
            $seller->increment('coin_balance', $earned);

            CoinTransaction::create([
                'user_id' => $seller->id,
                'type' => CoinTransaction::TYPE_SITE_SALE,
                'amount' => $earned,
                'fee' => $fee,
            ]);

            $image->deleteWithMarketHistory();

            return [
                'coins_received' => $earned,
                'fee' => $fee,
                'coin_balance' => $seller->coin_balance,
            ];
        });

        $files->destroy($image->file_path);

        return response()->json($result);
    }
}

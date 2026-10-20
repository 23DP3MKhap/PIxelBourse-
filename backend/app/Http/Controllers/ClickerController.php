<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ClickerController extends Controller
{

    // POST /api/clicker/sync  { "image_id": 5, "count": 143 }
    public function sync(Request $request)
    {
        $request->validate([
            'image_id' => ['required', 'integer'],
            'count'    => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        $image = Image::where('id', $request->image_id)
            ->where('owner_id', $user->id)
            ->first();

        if (!$image) {
            return response()->json(['message' => 'no image'], 404);
        }

        $lastKey = "clicker_last_sync:{$user->id}:{$image->id}";
        $last = Cache::get($lastKey);
        $seconds = $last ? now()->timestamp - $last : 600;
        $seconds = max(2, min($seconds, 3600));
        $maxClicks = $seconds * 15;

        Cache::put($lastKey, now()->timestamp, now()->addDay());

        if ($request->count > $maxClicks) {
            return response()->json([
                'message'      => 'Suspicious',
                'clicks_count' => $image->clicks_count,
                'value'        => $image->value,
            ], 422);
        }

        $image->increment('clicks_count', $request->count);
        $image->increment('value', $request->count);

        return response()->json([
            'clicks_count' => $image->clicks_count,
            'value'        => $image->value,
        ]);
    }
}
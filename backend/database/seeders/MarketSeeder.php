<?php

namespace Database\Seeders;

use App\Models\Image;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Seeder;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $artist = User::firstWhere('email', 'artist@example.com')
            ?? User::factory()->create(['username' => 'pixelartist', 'email' => 'artist@example.com']);

        $collector = User::firstWhere('email', 'collector@example.com')
            ?? User::factory()->create(['username' => 'collector', 'email' => 'collector@example.com']);

        if ($artist->uploadedImages()->exists()) {
            return;
        }

        User::where('email', 'test@example.com')->update(['coin_balance' => 5000]);

        $listings = [
            [$artist, 'Neon Cat', 1520, 1200, true, false],
            [$artist, 'Pixel Sunset', 340, 450, true, true],
            [$artist, 'Retro Car', 89, 150, true, false],
            [$artist, 'Space Whale', 2750, null, false, true],
            [$collector, 'Golden Frog', 610, 800, true, true],
            [$collector, 'Tiny Castle', 45, 60, true, false],
            [$collector, 'Glitch Skull', 1200, 2500, true, false],
        ];

        foreach ($listings as $i => [$seller, $title, $clicks, $price, $coins, $trade]) {
            $image = Image::create([
                'uploader_id' => $seller->id,
                'owner_id' => $seller->id,
                'title' => $title,
                'file_path' => 'https://picsum.photos/seed/pixelboris'.($i + 1).'/600/600',
                'clicks_count' => $clicks,
                'value' => $clicks,
            ]);

            Listing::create([
                'image_id' => $image->id,
                'seller_id' => $seller->id,
                'price' => $price,
                'coin_purchase_available' => $coins,
                'trade_available' => $trade,
                'status' => Listing::STATUS_ACTIVE,
            ]);
        }
    }
}

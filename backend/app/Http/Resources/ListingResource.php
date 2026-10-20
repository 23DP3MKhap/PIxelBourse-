<?php

namespace App\Http\Resources;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Listing
 */
class ListingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'coin_purchase_available' => $this->coin_purchase_available,
            'trade_available' => $this->trade_available,
            'status' => $this->status,
            'image' => [
                'id' => $this->image->id,
                'title' => $this->image->title,
                'url' => $this->image->file_path,
                'click_count' => $this->image->click_count,
                'value' => $this->image->value,
            ],
            'seller' => [
                'id' => $this->seller->id,
                'username' => $this->seller->username,
            ],
            'buyer_id' => $this->buyer_id,
            'created_at' => $this->created_at,
            'closed_at' => $this->closed_at,
        ];
    }
}

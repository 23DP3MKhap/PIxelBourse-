<?php

namespace App\Http\Resources;

use App\Models\Image;
use App\Models\Trade;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Trade
 */
class TradeResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'listing_id' => $this->listing_id,
            'initiator' => $this->user($this->initiator),
            'receiver' => $this->user($this->receiver),
            'offered_image' => $this->image($this->offeredImage),
            'requested_image' => $this->image($this->requestedImage),
            'created_at' => $this->created_at,
            'responded_at' => $this->responded_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function user(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function image(Image $image): array
    {
        return [
            'id' => $image->id,
            'title' => $image->title,
            'url' => $image->file_path,
            'clicks_count' => $image->clicks_count,
            'value' => $image->value,
        ];
    }
}

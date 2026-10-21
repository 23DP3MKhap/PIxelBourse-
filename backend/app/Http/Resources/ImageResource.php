<?php

namespace App\Http\Resources;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Image
 */
class ImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->file_path,
            'clicks_count' => $this->clicks_count,
            'value' => $this->value,
            'owner' => [
                'id' => $this->owner->id,
                'username' => $this->owner->username,
            ],
            'listed' => $this->whenCounted('active_listings', fn (int $count) => $count > 0),
            'created_at' => $this->created_at,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Image extends Model
{
    protected $fillable = [
        'uploader_id',
        'owner_id',
        'title',
        'file_path',
        'click_count',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'click_count' => 'integer',
            'value' => 'integer',
        ];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    public function offeredInTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'offered_image_id');
    }

    public function requestedInTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'requested_image_id');
    }
}
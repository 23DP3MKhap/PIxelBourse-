<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Image extends Model
{
    public function deleteWithMarketHistory(): void
    {
        DB::transaction(function () {
            Trade::query()
                ->where('offered_image_id', $this->id)
                ->orWhere('requested_image_id', $this->id)
                ->delete();

            Listing::query()->where('image_id', $this->id)->delete();

            $this->delete();
        });
    }

    protected $fillable = [
        'uploader_id',
        'owner_id',
        'title',
        'file_path',
        'clicks_count',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'clicks_count' => 'integer',
            'value' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function listings(): HasMany
    {
        return $this->hasMany(Listing::class);
    }

    /**
     * @return HasMany<Trade, $this>
     */
    public function offeredInTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'offered_image_id');
    }

    /**
     * @return HasMany<Trade, $this>
     */
    public function requestedInTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'requested_image_id');
    }
}

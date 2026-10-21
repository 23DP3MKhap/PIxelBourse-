<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_SOLD = 'sold';

    public const STATUS_TRADED = 'traded';

    public const STATUS_CANCELLED = 'cancelled';

    public const FEE_PERCENT = 25;

    public const MAX_PRICE = 1_000_000_000;

    protected $fillable = [
        'image_id',
        'seller_id',
        'buyer_id',
        'price',
        'coin_purchase_available',
        'trade_available',
        'status',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'coin_purchase_available' => 'boolean',
            'trade_available' => 'boolean',
            'closed_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public static function feeFor(int $price): int
    {
        return intdiv($price * self::FEE_PERCENT, 100);
    }

    /**
     * @param  Builder<Listing>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * @return BelongsTo<Image, $this>
     */
    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * @return HasMany<Trade, $this>
     */
    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }
}

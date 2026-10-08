<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SOLD = 'sold';
    public const STATUS_TRADED = 'traded';
    public const STATUS_CANCELLED = 'cancelled';

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

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function trades(): HasMany
    {
        return $this->hasMany(Trade::class);
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }
}
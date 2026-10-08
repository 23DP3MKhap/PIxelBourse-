<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'coin_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'coin_balance' => 'integer',
        ];
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'owner_id');
    }

    public function uploadedImages(): HasMany
    {
        return $this->hasMany(Image::class, 'uploader_id');
    }

    public function sellingListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    public function purchasedListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'buyer_id');
    }

    public function initiatedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'initiator_id');
    }

    public function receivedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }
}
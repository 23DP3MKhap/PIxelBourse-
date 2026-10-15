<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $username
 * @property string $email
 * @property string $password
 * @property int $coin_balance
 * @property UserRole $role
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    // coin_balance and role are deliberately not fillable: they only change through game logic or an admin.
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Mirrors the column defaults so a freshly created model has them without a refresh.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'coin_balance' => 0,
        'role' => 'user',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'coin_balance' => 'integer',
            'role' => UserRole::class,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    /**
     * Images, listings, trades and coin transactions reference users without cascading deletes,
     * so a user who has any of them cannot be removed from the database.
     */
    public function hasGameHistory(): bool
    {
        return $this->images()->exists()
            || $this->uploadedImages()->exists()
            || $this->sellingListings()->exists()
            || $this->initiatedTrades()->exists()
            || $this->receivedTrades()->exists()
            || $this->coinTransactions()->exists();
    }

    /**
     * @return HasMany<Image, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'owner_id');
    }

    /**
     * @return HasMany<Image, $this>
     */
    public function uploadedImages(): HasMany
    {
        return $this->hasMany(Image::class, 'uploader_id');
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function sellingListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    /**
     * @return HasMany<Listing, $this>
     */
    public function purchasedListings(): HasMany
    {
        return $this->hasMany(Listing::class, 'buyer_id');
    }

    /**
     * @return HasMany<Trade, $this>
     */
    public function initiatedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'initiator_id');
    }

    /**
     * @return HasMany<Trade, $this>
     */
    public function receivedTrades(): HasMany
    {
        return $this->hasMany(Trade::class, 'receiver_id');
    }

    /**
     * @return HasMany<CoinTransaction, $this>
     */
    public function coinTransactions(): HasMany
    {
        return $this->hasMany(CoinTransaction::class);
    }
}

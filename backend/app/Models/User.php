<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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
#[Fillable(['username', 'email', 'password'])]
#[Hidden(['password'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Mirrors the column defaults so a freshly created model has them without a refresh.
     * coin_balance and role are deliberately not fillable: they only change through game logic or an admin.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'coin_balance' => 0,
        'role' => 'user',
    ];

    /**
     * Get the attributes that should be cast.
     *
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
        return DB::table('images')->where('uploader_id', $this->id)->orWhere('owner_id', $this->id)->exists()
            || DB::table('listings')->where('seller_id', $this->id)->exists()
            || DB::table('trades')->where('initiator_id', $this->id)->orWhere('receiver_id', $this->id)->exists()
            || DB::table('coin_transactions')->where('user_id', $this->id)->exists();
    }
}

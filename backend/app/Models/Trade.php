<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trade extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'initiator_id',
        'receiver_id',
        'offered_image_id',
        'requested_image_id',
        'listing_id',
        'status',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
        ];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * @param  array<int, int>  $imageIds
     */
    public static function cancelPendingInvolving(array $imageIds): void
    {
        static::query()
            ->pending()
            ->where(fn ($query) => $query->whereIn('offered_image_id', $imageIds)->orWhereIn('requested_image_id', $imageIds))
            ->update(['status' => self::STATUS_CANCELLED, 'responded_at' => now()]);
    }

    /**
     * @param  Builder<Trade>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', self::STATUS_PENDING);
    }

    /**
     * @param  Builder<Trade>  $query
     */
    public function scopeInvolving(Builder $query, User $user): void
    {
        $query->where(fn ($query) => $query->where('initiator_id', $user->id)->orWhere('receiver_id', $user->id));
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * @return BelongsTo<Image, $this>
     */
    public function offeredImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'offered_image_id');
    }

    /**
     * @return BelongsTo<Image, $this>
     */
    public function requestedImage(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'requested_image_id');
    }

    /**
     * @return BelongsTo<Listing, $this>
     */
    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }
}

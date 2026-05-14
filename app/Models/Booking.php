<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'user_id', 'name', 'phone',
        'starts_at', 'duration_minutes', 'status', 'notes',
    ];

    protected $casts = [
        'starts_at'        => 'datetime',
        'duration_minutes' => 'integer',
    ];

    public const STATUSES = [
        'pending'   => 'بانتظار التأكيد',
        'confirmed' => 'مؤكد',
        'cancelled' => 'مُلغى',
        'completed' => 'تم',
        'no_show'   => 'لم يحضر',
    ];

    public const STATUS_TONES = [
        'pending'   => 'honey',
        'confirmed' => 'mint',
        'cancelled' => 'blush',
        'completed' => 'mint',
        'no_show'   => 'ink-400',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Bookings that block a slot (everything except cancelled). */
    public function scopeBlocking(Builder $q): Builder
    {
        return $q->whereNotIn('status', ['cancelled', 'no_show']);
    }

    /** Bookings from now onwards. */
    public function scopeUpcoming(Builder $q): Builder
    {
        return $q->where('starts_at', '>=', now('Africa/Cairo'));
    }

    /** Returns "د. أحمد · ٠١٠..." style label masked for owner list. */
    public function maskedPhone(): string
    {
        $p = preg_replace('/\D/', '', $this->phone);
        if (strlen($p) < 6) return $this->phone;
        return substr($p, 0, 3) . '****' . substr($p, -3);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function endsAt()
    {
        return $this->starts_at?->copy()->addMinutes($this->duration_minutes);
    }
}

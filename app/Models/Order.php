<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'business_id', 'user_id', 'customer_name', 'customer_phone', 'customer_address',
    'area_id', 'delivery_fee',
    'notes', 'subtotal', 'currency', 'status', 'wa_send_status', 'wa_sent_at',
])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'subtotal'     => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'wa_sent_at'   => 'datetime',
        ];
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /** Items + delivery. Use this when displaying the final total. */
    public function grandTotal(): float
    {
        return (float) $this->subtotal + (float) ($this->delivery_fee ?? 0);
    }

    public const STATUSES = [
        'pending'          => 'بانتظار التأكيد',
        'confirmed'        => 'مؤكد',
        'preparing'        => 'بيتجهز',
        'out_for_delivery' => 'في الطريق',
        'completed'        => 'تم',
        'cancelled'        => 'مُلغى',
    ];

    public const STATUS_TONES = [
        'pending'          => 'honey',
        'confirmed'        => 'mint',
        'preparing'        => 'coral',
        'out_for_delivery' => 'coral',
        'completed'        => 'mint',
        'cancelled'        => 'blush',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereNotIn('status', ['cancelled', 'completed']);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function maskedPhone(): string
    {
        $p = preg_replace('/\D/', '', $this->customer_phone);
        if (strlen($p) < 6) return $this->customer_phone;
        return substr($p, 0, 3) . '****' . substr($p, -3);
    }
}

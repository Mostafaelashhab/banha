<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'amount_egp', 'points_cost', 'method', 'payout_handle',
    'status', 'admin_id', 'admin_note', 'payout_reference',
    'requested_at', 'processed_at', 'paid_at', 'meta',
])]
class Withdrawal extends Model
{
    public const METHODS = [
        'instapay' => 'InstaPay',
        'vcash'    => 'فودافون كاش',
    ];

    public const STATUSES = [
        'pending'   => ['label' => 'بانتظار المراجعة', 'tone' => 'honey'],
        'approved'  => ['label' => 'اتعمد · بنحوّل',    'tone' => 'mint'],
        'paid'      => ['label' => 'اتدفع ✓',           'tone' => 'mint'],
        'rejected'  => ['label' => 'مرفوض',              'tone' => 'blush'],
        'cancelled' => ['label' => 'الغيته انت',         'tone' => 'ink'],
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
            'paid_at'      => 'datetime',
            'meta'         => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function statusMeta(): array
    {
        return self::STATUSES[$this->status] ?? self::STATUSES['pending'];
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}

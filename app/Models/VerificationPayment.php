<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id', 'user_id', 'method', 'amount', 'months',
        'transaction_id', 'proof_url', 'note', 'status',
        'reviewed_by_admin', 'reviewed_at', 'reject_reason',
    ];

    protected $casts = [
        'amount'      => 'integer',
        'months'      => 'integer',
        'reviewed_at' => 'datetime',
    ];

    public const METHODS = [
        'instapay'      => 'InstaPay',
        'vodafone_cash' => 'فودافون كاش',
        'cash'          => 'نقدي',
    ];

    public const STATUSES = [
        'pending'  => 'بانتظار المراجعة',
        'approved' => 'تم التفعيل',
        'rejected' => 'مرفوض',
    ];

    public function business(): BelongsTo { return $this->belongsTo(Business::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', 'pending');
    }

    public function methodLabel(): string { return self::METHODS[$this->method] ?? $this->method; }
    public function statusLabel(): string { return self::STATUSES[$this->status] ?? $this->status; }
}

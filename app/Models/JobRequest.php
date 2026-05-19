<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'phone', 'sub_type', 'zone_id',
        'address', 'description', 'budget_min', 'budget_max',
        'urgency', 'status', 'matched_business_id',
        'responses_count', 'views_count', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'budget_min' => 'integer',
        'budget_max' => 'integer',
        'responses_count' => 'integer',
        'views_count' => 'integer',
    ];

    public const URGENCIES = [
        'asap'      => '⚡ في خلال ساعة',
        'today'     => 'النهارده',
        'this_week' => 'خلال الأسبوع',
        'flexible'  => 'مفيش استعجال',
    ];

    public const STATUSES = [
        'open'      => 'مفتوح',
        'matched'   => 'تواصل صنايعي',
        'completed' => 'تم',
        'cancelled' => 'مُلغى',
        'expired'   => 'منتهي',
    ];

    public function zone(): BelongsTo            { return $this->belongsTo(Zone::class); }
    public function user(): BelongsTo            { return $this->belongsTo(User::class); }
    public function matchedBusiness(): BelongsTo { return $this->belongsTo(Business::class, 'matched_business_id'); }
    public function responses(): HasMany         { return $this->hasMany(JobResponse::class); }

    /** Jobs that craftsmen can still respond to. */
    public function scopeOpen(Builder $q): Builder
    {
        return $q->where('status', 'open')
                 ->where(function ($x) {
                     $x->whereNull('expires_at')->orWhere('expires_at', '>', now('Africa/Cairo'));
                 });
    }

    public function scopeForTrade(Builder $q, string $subType): Builder
    {
        return $q->where('sub_type', $subType);
    }

    public function scopeForZone(Builder $q, int $zoneId): Builder
    {
        return $q->where('zone_id', $zoneId);
    }

    public function urgencyLabel(): string
    {
        return self::URGENCIES[$this->urgency] ?? $this->urgency;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function tradeMeta(): array
    {
        return Business::SUB_TYPES[$this->sub_type] ?? ['label' => $this->sub_type, 'emoji' => '🔧', 'icon' => 'wrench'];
    }

    /** Masked phone for the public job-feed (full number visible only after a response). */
    public function maskedPhone(): string
    {
        $p = preg_replace('/\D/', '', (string) $this->phone);
        if (strlen($p) < 6) return $this->phone;
        return substr($p, 0, 3) . '****' . substr($p, -3);
    }

    public function budgetLabel(): ?string
    {
        if (! $this->budget_min && ! $this->budget_max) return null;
        if ($this->budget_min && $this->budget_max) {
            return number_format($this->budget_min) . ' - ' . number_format($this->budget_max) . ' ج';
        }
        return number_format($this->budget_min ?? $this->budget_max) . ' ج';
    }
}

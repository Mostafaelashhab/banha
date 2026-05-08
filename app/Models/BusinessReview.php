<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'business_id', 'user_id', 'author_name', 'author_phone',
    'rating', 'body', 'source', 'external_id', 'reviewed_at',
])]
class BusinessReview extends Model
{
    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'rating'      => 'integer',
        ];
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function maskedPhone(): string
    {
        $p = $this->author_phone ?? '';
        if (strlen($p) < 6) return 'مستخدم';
        return substr($p, 0, 4).'****'.substr($p, -3);
    }
}

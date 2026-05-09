<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'image_url', 'caption', 'expires_at', 'created_at'])]
class Story extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo  { return $this->belongsTo(User::class); }

    public function scopeAlive(Builder $q): Builder
    {
        return $q->where('expires_at', '>', now());
    }
}

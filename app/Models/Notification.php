<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'title', 'body', 'url', 'read_at', 'created_at'])]
class Notification extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at', 'read_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'read_at'    => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }
}

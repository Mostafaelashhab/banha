<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['thread_id', 'user_id', 'body', 'image_url', 'created_at'])]
class ChatMessage extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
    }

    public function thread(): BelongsTo  { return $this->belongsTo(ChatThread::class, 'thread_id'); }
    public function user(): BelongsTo    { return $this->belongsTo(User::class); }
}

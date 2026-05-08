<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'target_type', 'target_id'])]
class Bookmark extends Model
{
    public $timestamps = false;
    protected $dates = ['created_at'];

    public static function exists_(int $userId, string $type, int $id): bool
    {
        return self::where('user_id', $userId)
            ->where('target_type', $type)
            ->where('target_id', $id)
            ->exists();
    }
}

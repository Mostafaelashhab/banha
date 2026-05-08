<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['phone', 'purpose', 'code_hash', 'attempts', 'expires_at', 'verified_at', 'ip'])]
class OtpCode extends Model
{
    protected function casts(): array
    {
        return [
            'expires_at'  => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['business_id', 'url', 'caption', 'sort'])]
class BusinessPhoto extends Model
{
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}

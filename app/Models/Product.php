<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'category', 'unit', 'emoji', 'is_active', 'sort'])]
class Product extends Model
{
    public const CATEGORIES = [
        'vegetables' => 'خضار',
        'fruits'     => 'فاكهة',
        'meat'       => 'لحوم ودواجن',
        'dairy'      => 'ألبان وأجبان',
        'pantry'     => 'بقالة',
        'fuel'       => 'وقود وغاز',
        'bread'      => 'مخبوزات',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }
}

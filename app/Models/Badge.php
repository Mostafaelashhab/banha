<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['slug', 'name', 'description', 'emoji', 'color', 'tier', 'criteria_kind', 'criteria_value', 'is_secret', 'sort'])]
class Badge extends Model
{
    public const TIERS = [
        'common'    => 'عادي',
        'rare'      => 'نادر',
        'legendary' => 'أسطوري',
    ];

    protected function casts(): array
    {
        return [
            'is_secret' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_badges')
            ->withPivot('earned_at');
    }
}

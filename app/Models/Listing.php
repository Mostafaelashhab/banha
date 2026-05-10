<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id', 'zone_id', 'lat', 'lng', 'kind', 'category', 'title', 'description',
    'price', 'currency', 'negotiable', 'photo_url',
    'contact_phone', 'contact_whatsapp', 'status', 'expires_at',
    'featured_until', 'phone_clicks', 'whatsapp_clicks',
])]
class Listing extends Model
{
    public const KINDS = [
        'sale'  => ['label' => 'بيع',     'tone' => 'coral',  'icon' => 'tag'],
        'buy'   => ['label' => 'مطلوب',   'tone' => 'mint',   'icon' => 'cart'],
        'lost'  => ['label' => 'مفقودات', 'tone' => 'blush',  'icon' => 'flag'],
        'found' => ['label' => 'لقطات',   'tone' => 'honey',  'icon' => 'check'],
    ];

    public const CATEGORIES = [
        'electronics' => ['label' => 'إلكترونيات',     'icon' => 'tv'],
        'mobile'      => ['label' => 'موبايلات',       'icon' => 'phone'],
        'furniture'   => ['label' => 'أثاث',           'icon' => 'sofa'],
        'clothing'    => ['label' => 'ملابس',           'icon' => 'shirt'],
        'vehicles'    => ['label' => 'عربيات',          'icon' => 'car'],
        'home'        => ['label' => 'أدوات منزلية',   'icon' => 'home'],
        'baby'        => ['label' => 'مستلزمات أطفال', 'icon' => 'baby'],
        'books'       => ['label' => 'كتب',             'icon' => 'book'],
        'sports'      => ['label' => 'رياضة',           'icon' => 'dumbbell'],
        'pets'        => ['label' => 'حيوانات أليفة',  'icon' => 'paw'],
        'jobs'        => ['label' => 'وظائف وخدمات',   'icon' => 'briefcase'],
        'real_estate' => ['label' => 'عقارات',          'icon' => 'home'],
        'other'       => ['label' => 'حاجة تانية',     'icon' => 'bag'],
    ];

    protected function casts(): array
    {
        return [
            'negotiable'     => 'boolean',
            'price'          => 'integer',
            'expires_at'     => 'datetime',
            'featured_until' => 'datetime',
        ];
    }

    public function isFeatured(): bool
    {
        return $this->featured_until && $this->featured_until->isFuture();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function kindMeta(): array
    {
        return self::KINDS[$this->kind] ?? self::KINDS['sale'];
    }

    public function categoryMeta(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    public function priceLabel(): string
    {
        if (in_array($this->kind, ['lost', 'found'], true)) return '';
        if (! $this->price) return 'بسعر مفاوض';
        return number_format($this->price).' ج';
    }
}

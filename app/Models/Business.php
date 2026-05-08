<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name', 'category', 'sub_type', 'zone_id', 'owner_user_id',
    'description', 'phone', 'whatsapp', 'address', 'lat', 'lng',
    'hours', 'is_24h', 'is_verified', 'is_active',
    'rating_avg', 'ratings_count', 'emoji', 'photo_url',
])]
class Business extends Model
{
    public const CATEGORIES = [
        'craftsmen' => ['label' => 'صنايعية',          'emoji' => '🔧', 'color' => '#FFB85C'],
        'food'      => ['label' => 'مطاعم وكافيهات',  'emoji' => '🍽️', 'color' => '#FF7A4D'],
        'medical'   => ['label' => 'دكاترة وصيدليات', 'emoji' => '🩺', 'color' => '#1FA857'],
        'shops'     => ['label' => 'محلات',            'emoji' => '🛒', 'color' => '#7C3AED'],
        'services'  => ['label' => 'خدمات تانية',     'emoji' => '🛎️', 'color' => '#0EA5E9'],
    ];

    public const SUB_TYPES = [
        // craftsmen
        'plumber'         => ['label' => 'سباك',              'category' => 'craftsmen', 'emoji' => '🔧'],
        'electrician'     => ['label' => 'كهربائي',           'category' => 'craftsmen', 'emoji' => '💡'],
        'carpenter'       => ['label' => 'نجار',              'category' => 'craftsmen', 'emoji' => '🔨'],
        'painter'         => ['label' => 'نقاش',              'category' => 'craftsmen', 'emoji' => '🎨'],
        'ac_tech'         => ['label' => 'فني تكييف',         'category' => 'craftsmen', 'emoji' => '❄️'],
        'appliance_tech'  => ['label' => 'فني أجهزة كهربائية','category' => 'craftsmen', 'emoji' => '⚙️'],
        'gas_tech'        => ['label' => 'فني غاز',           'category' => 'craftsmen', 'emoji' => '🔥'],
        'aluminum'        => ['label' => 'ألوميتال',          'category' => 'craftsmen', 'emoji' => '🪟'],

        // food
        'restaurant'      => ['label' => 'مطعم',              'category' => 'food',      'emoji' => '🍽️'],
        'cafe'            => ['label' => 'كافيه',             'category' => 'food',      'emoji' => '☕'],
        'fast_food'       => ['label' => 'فطار وفاست فود',   'category' => 'food',      'emoji' => '🥙'],
        'sweets'          => ['label' => 'حلواني',            'category' => 'food',      'emoji' => '🍰'],
        'bakery'          => ['label' => 'مخبز',              'category' => 'food',      'emoji' => '🍞'],
        'juice'           => ['label' => 'عصاير',             'category' => 'food',      'emoji' => '🥤'],

        // medical
        'doctor'          => ['label' => 'طبيب',              'category' => 'medical',   'emoji' => '🩺'],
        'pediatrician'    => ['label' => 'طبيب أطفال',        'category' => 'medical',   'emoji' => '👶'],
        'dentist'         => ['label' => 'طبيب أسنان',        'category' => 'medical',   'emoji' => '🦷'],
        'pharmacy'        => ['label' => 'صيدلية',            'category' => 'medical',   'emoji' => '💊'],
        'lab'             => ['label' => 'معمل تحاليل',       'category' => 'medical',   'emoji' => '🧪'],
        'physio'          => ['label' => 'علاج طبيعي',        'category' => 'medical',   'emoji' => '💪'],

        // shops
        'grocery'         => ['label' => 'بقالة',             'category' => 'shops',     'emoji' => '🥫'],
        'supermarket'     => ['label' => 'سوبر ماركت',        'category' => 'shops',     'emoji' => '🛒'],
        'butcher'         => ['label' => 'جزار',              'category' => 'shops',     'emoji' => '🥩'],
        'fish_shop'       => ['label' => 'سمكري',             'category' => 'shops',     'emoji' => '🐟'],
        'fruit_veg'       => ['label' => 'خضار وفاكهة',      'category' => 'shops',     'emoji' => '🥬'],
        'clothing'        => ['label' => 'ملابس',             'category' => 'shops',     'emoji' => '👕'],

        // services
        'laundry'         => ['label' => 'مغسلة',             'category' => 'services',  'emoji' => '🧺'],
        'tailor'          => ['label' => 'خياط',              'category' => 'services',  'emoji' => '✂️'],
        'barber'          => ['label' => 'حلاق',              'category' => 'services',  'emoji' => '💈'],
        'salon'           => ['label' => 'كوافير',            'category' => 'services',  'emoji' => '💇'],
        'photographer'    => ['label' => 'مصور',              'category' => 'services',  'emoji' => '📷'],
        'tutor'           => ['label' => 'سنتر دروس',         'category' => 'services',  'emoji' => '📚'],
        'tuktuk'          => ['label' => 'توك توك',           'category' => 'services',  'emoji' => '🛺'],
    ];

    protected function casts(): array
    {
        return [
            'is_24h'       => 'boolean',
            'is_verified'  => 'boolean',
            'is_active'    => 'boolean',
            'lat'          => 'decimal:7',
            'lng'          => 'decimal:7',
            'rating_avg'   => 'decimal:1',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function categoryMeta(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['services'];
    }

    public function subTypeMeta(): array
    {
        return self::SUB_TYPES[$this->sub_type] ?? ['label' => $this->sub_type, 'category' => $this->category, 'emoji' => '📍'];
    }
}

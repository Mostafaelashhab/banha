<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'category', 'sub_type', 'custom_sub_type', 'zone_id', 'owner_user_id',
    'description', 'phone', 'whatsapp', 'address', 'lat', 'lng',
    'hours', 'is_24h', 'is_verified', 'promoted_until', 'is_active',
    'rating_avg', 'ratings_count', 'views_count', 'phone_clicks', 'whatsapp_clicks',
    'emoji', 'photo_url', 'has_menu', 'menu_currency', 'external_id',
])]
class Business extends Model
{
    public const CATEGORIES = [
        'food'        => ['label' => 'مطاعم وكافيهات',   'emoji' => '🍽️', 'icon' => 'utensils',    'color' => '#FF7A4D'],
        'medical'     => ['label' => 'دكاترة وصيدليات',  'emoji' => '🩺', 'icon' => 'stethoscope', 'color' => '#1FA857'],
        'shops'       => ['label' => 'محلات',             'emoji' => '🛒', 'icon' => 'cart',        'color' => '#7C3AED'],
        'craftsmen'   => ['label' => 'صنايعية',           'emoji' => '🔧', 'icon' => 'wrench',      'color' => '#FFB85C'],
        'services'    => ['label' => 'خدمات تانية',      'emoji' => '🛎️', 'icon' => 'briefcase',   'color' => '#0EA5E9'],
        'government'  => ['label' => 'مصالح حكومية',     'emoji' => '🏛',  'icon' => 'briefcase',   'color' => '#4B5563'],
        'education'   => ['label' => 'تعليم',             'emoji' => '🎓', 'icon' => 'graduation',  'color' => '#2563EB'],
        'transport'   => ['label' => 'مواصلات',           'emoji' => '🚕', 'icon' => 'car',         'color' => '#DC2626'],
        'religious'   => ['label' => 'مساجد وكنائس',      'emoji' => '🕌', 'icon' => 'check',       'color' => '#059669'],
        'banks'       => ['label' => 'بنوك وATM',         'emoji' => '💳', 'icon' => 'cart',        'color' => '#0891B2'],
        'tourist'     => ['label' => 'أماكن وحدائق',      'emoji' => '🌳', 'icon' => 'leaf',        'color' => '#10B981'],
        'emergency'   => ['label' => 'طوارئ',             'emoji' => '🚨', 'icon' => 'bolt',        'color' => '#B91C1C'],
    ];

    public const SUB_TYPES = [
        // ── craftsmen ─────────────────────────────────────────
        'plumber'         => ['label' => 'سباك',              'category' => 'craftsmen', 'emoji' => '🔧', 'icon' => 'wrench'],
        'electrician'     => ['label' => 'كهربائي',           'category' => 'craftsmen', 'emoji' => '💡', 'icon' => 'bolt'],
        'carpenter'       => ['label' => 'نجار',              'category' => 'craftsmen', 'emoji' => '🔨', 'icon' => 'hammer'],
        'painter'         => ['label' => 'نقاش',              'category' => 'craftsmen', 'emoji' => '🎨', 'icon' => 'brush'],
        'ac_tech'         => ['label' => 'فني تكييف',         'category' => 'craftsmen', 'emoji' => '❄️', 'icon' => 'snowflake'],
        'appliance_tech'  => ['label' => 'فني أجهزة كهربائية','category' => 'craftsmen', 'emoji' => '⚙️', 'icon' => 'gear'],
        'gas_tech'        => ['label' => 'فني غاز',           'category' => 'craftsmen', 'emoji' => '🔥', 'icon' => 'flame'],
        'aluminum'        => ['label' => 'ألوميتال',          'category' => 'craftsmen', 'emoji' => '🪟', 'icon' => 'square'],
        'builder'         => ['label' => 'بنّاء / محارة',     'category' => 'craftsmen', 'emoji' => '🧱', 'icon' => 'brick'],
        'tile_setter'     => ['label' => 'سيراميك ورخام',     'category' => 'craftsmen', 'emoji' => '🟦', 'icon' => 'grid'],
        'blacksmith'      => ['label' => 'حداد',              'category' => 'craftsmen', 'emoji' => '⚒️', 'icon' => 'anvil'],
        'welder'          => ['label' => 'لحام',              'category' => 'craftsmen', 'emoji' => '🔥', 'icon' => 'flame'],
        'mechanic_car'    => ['label' => 'ميكانيكي عربيات',   'category' => 'craftsmen', 'emoji' => '🚗', 'icon' => 'car'],
        'mechanic_bike'   => ['label' => 'ميكانيكي موتوسيكل', 'category' => 'craftsmen', 'emoji' => '🏍️', 'icon' => 'bike'],
        'glazier'         => ['label' => 'محل زجاج',          'category' => 'craftsmen', 'emoji' => '🪞', 'icon' => 'square'],
        'locksmith'       => ['label' => 'مفاتيح وأقفال',     'category' => 'craftsmen', 'emoji' => '🔑', 'icon' => 'key'],
        'gypsum'          => ['label' => 'جبس بورد',          'category' => 'craftsmen', 'emoji' => '🧱', 'icon' => 'layers'],
        'moving'          => ['label' => 'نقل عفش',           'category' => 'craftsmen', 'emoji' => '📦', 'icon' => 'truck'],
        'finishing'       => ['label' => 'تشطيبات',           'category' => 'craftsmen', 'emoji' => '🏗️', 'icon' => 'tools'],
        'satellite_tech'  => ['label' => 'فني دش',            'category' => 'craftsmen', 'emoji' => '📡', 'icon' => 'wifi'],
        'pest_control'    => ['label' => 'مكافحة حشرات',      'category' => 'craftsmen', 'emoji' => '🪳', 'icon' => 'bug'],
        'craftsmen_other' => ['label' => 'صنايعي تاني',       'category' => 'craftsmen', 'emoji' => '🧰', 'icon' => 'tools'],

        // ── food ──────────────────────────────────────────────
        'restaurant'      => ['label' => 'مطعم',              'category' => 'food',      'emoji' => '🍽️', 'icon' => 'utensils'],
        'cafe'            => ['label' => 'كافيه',             'category' => 'food',      'emoji' => '☕', 'icon' => 'coffee'],
        'fast_food'       => ['label' => 'فطار وفاست فود',   'category' => 'food',      'emoji' => '🥙', 'icon' => 'utensils'],
        'sweets'          => ['label' => 'حلواني',            'category' => 'food',      'emoji' => '🍰', 'icon' => 'cake'],
        'bakery'          => ['label' => 'مخبز',              'category' => 'food',      'emoji' => '🍞', 'icon' => 'bread'],
        'juice'           => ['label' => 'عصاير',             'category' => 'food',      'emoji' => '🥤', 'icon' => 'cup'],
        'food_other'      => ['label' => 'أكل تاني',          'category' => 'food',      'emoji' => '🍴', 'icon' => 'utensils'],

        // ── medical ───────────────────────────────────────────
        'doctor'          => ['label' => 'طبيب باطنة',        'category' => 'medical',   'emoji' => '🩺', 'icon' => 'stethoscope'],
        'pediatrician'    => ['label' => 'طبيب أطفال',        'category' => 'medical',   'emoji' => '👶', 'icon' => 'baby'],
        'dentist'         => ['label' => 'طبيب أسنان',        'category' => 'medical',   'emoji' => '🦷', 'icon' => 'tooth'],
        'gynecologist'    => ['label' => 'طبيب نسا',          'category' => 'medical',   'emoji' => '👩‍⚕️', 'icon' => 'stethoscope'],
        'orthopedic'      => ['label' => 'طبيب عظام',         'category' => 'medical',   'emoji' => '🦴', 'icon' => 'stethoscope'],
        'ent'             => ['label' => 'أنف وأذن',          'category' => 'medical',   'emoji' => '👂', 'icon' => 'stethoscope'],
        'dermatology'     => ['label' => 'طبيب جلدية',        'category' => 'medical',   'emoji' => '🧴', 'icon' => 'stethoscope'],
        'pharmacy'        => ['label' => 'صيدلية',            'category' => 'medical',   'emoji' => '💊', 'icon' => 'pill'],
        'lab'             => ['label' => 'معمل تحاليل',       'category' => 'medical',   'emoji' => '🧪', 'icon' => 'flask'],
        'physio'          => ['label' => 'علاج طبيعي',        'category' => 'medical',   'emoji' => '💪', 'icon' => 'heart'],
        'vet'             => ['label' => 'طبيب بيطري',        'category' => 'medical',   'emoji' => '🐾', 'icon' => 'paw'],
        'nurse'           => ['label' => 'تمريض منزلي',       'category' => 'medical',   'emoji' => '💉', 'icon' => 'heart'],
        'medical_other'   => ['label' => 'تخصص تاني',         'category' => 'medical',   'emoji' => '🏥', 'icon' => 'stethoscope'],

        // ── shops ─────────────────────────────────────────────
        'grocery'         => ['label' => 'بقالة',             'category' => 'shops',     'emoji' => '🥫', 'icon' => 'cart'],
        'supermarket'     => ['label' => 'سوبر ماركت',        'category' => 'shops',     'emoji' => '🛒', 'icon' => 'cart'],
        'butcher'         => ['label' => 'جزار',              'category' => 'shops',     'emoji' => '🥩', 'icon' => 'meat'],
        'fish_shop'       => ['label' => 'سمكري',             'category' => 'shops',     'emoji' => '🐟', 'icon' => 'fish'],
        'fruit_veg'       => ['label' => 'خضار وفاكهة',      'category' => 'shops',     'emoji' => '🥬', 'icon' => 'leaf'],
        'clothing'        => ['label' => 'ملابس',             'category' => 'shops',     'emoji' => '👕', 'icon' => 'shirt'],
        'bookshop'        => ['label' => 'مكتبة',             'category' => 'shops',     'emoji' => '📚', 'icon' => 'book'],
        'mobile_shop'     => ['label' => 'محل موبايلات',      'category' => 'shops',     'emoji' => '📱', 'icon' => 'phone'],
        'electronics'     => ['label' => 'إلكترونيات',         'category' => 'shops',     'emoji' => '💻', 'icon' => 'tv'],
        'gas_station'     => ['label' => 'محطة بنزين',        'category' => 'shops',     'emoji' => '⛽', 'icon' => 'fuel'],
        'hardware'        => ['label' => 'محل أدوات',         'category' => 'shops',     'emoji' => '🛠️', 'icon' => 'tools'],
        'gold_shop'       => ['label' => 'محل دهب',           'category' => 'shops',     'emoji' => '💍', 'icon' => 'gem'],
        'toys'            => ['label' => 'محل لعب',           'category' => 'shops',     'emoji' => '🧸', 'icon' => 'gift'],
        'furniture'       => ['label' => 'محل أثاث',          'category' => 'shops',     'emoji' => '🛋️', 'icon' => 'sofa'],
        'baby_shop'       => ['label' => 'محل أطفال',         'category' => 'shops',     'emoji' => '🍼', 'icon' => 'baby'],
        'shops_other'     => ['label' => 'محل تاني',          'category' => 'shops',     'emoji' => '🏬', 'icon' => 'cart'],

        // ── services ──────────────────────────────────────────
        'laundry'         => ['label' => 'مغسلة',             'category' => 'services',  'emoji' => '🧺', 'icon' => 'shirt'],
        'tailor'          => ['label' => 'خياط',              'category' => 'services',  'emoji' => '✂️', 'icon' => 'scissors'],
        'barber'          => ['label' => 'حلاق',              'category' => 'services',  'emoji' => '💈', 'icon' => 'scissors'],
        'salon'           => ['label' => 'كوافير',            'category' => 'services',  'emoji' => '💇', 'icon' => 'scissors'],
        'photographer'    => ['label' => 'مصور',              'category' => 'services',  'emoji' => '📷', 'icon' => 'camera'],
        'tutor'           => ['label' => 'سنتر دروس',         'category' => 'services',  'emoji' => '📚', 'icon' => 'graduation'],
        'school'          => ['label' => 'مدرسة / حضانة',     'category' => 'services',  'emoji' => '🏫', 'icon' => 'graduation'],
        'gym'             => ['label' => 'جيم / نادي رياضي',  'category' => 'services',  'emoji' => '🏋️', 'icon' => 'dumbbell'],
        'tuktuk'          => ['label' => 'توك توك',           'category' => 'services',  'emoji' => '🛺', 'icon' => 'car'],
        'delivery'        => ['label' => 'دليفري',            'category' => 'services',  'emoji' => '🛵', 'icon' => 'bike'],
        'driver'          => ['label' => 'سواق',              'category' => 'services',  'emoji' => '🚖', 'icon' => 'car'],
        'house_cleaning'  => ['label' => 'تنظيف منازل',       'category' => 'services',  'emoji' => '🧽', 'icon' => 'spray'],
        'car_wash'        => ['label' => 'مغسلة عربيات',      'category' => 'services',  'emoji' => '🚿', 'icon' => 'car'],
        'car_rental'      => ['label' => 'تأجير عربيات',      'category' => 'services',  'emoji' => '🚗', 'icon' => 'car'],
        'printing'        => ['label' => 'مطبعة',             'category' => 'services',  'emoji' => '🖨️', 'icon' => 'printer'],
        'services_other'  => ['label' => 'خدمة تانية',        'category' => 'services',  'emoji' => '💼', 'icon' => 'briefcase'],

        // ── government ─────────────────────────────────────
        'gov_traffic'     => ['label' => 'المرور',             'category' => 'government', 'emoji' => '🚦', 'icon' => 'car'],
        'gov_registry'    => ['label' => 'الشهر العقاري',     'category' => 'government', 'emoji' => '📜', 'icon' => 'briefcase'],
        'gov_court'       => ['label' => 'المحكمة',            'category' => 'government', 'emoji' => '⚖️', 'icon' => 'briefcase'],
        'gov_post'        => ['label' => 'مكتب البريد',        'category' => 'government', 'emoji' => '📮', 'icon' => 'briefcase'],
        'gov_electricity' => ['label' => 'شركة الكهرباء',     'category' => 'government', 'emoji' => '⚡', 'icon' => 'bolt'],
        'gov_gas'         => ['label' => 'شركة الغاز',         'category' => 'government', 'emoji' => '🔥', 'icon' => 'flame'],
        'gov_water'       => ['label' => 'شركة المياه',        'category' => 'government', 'emoji' => '💧', 'icon' => 'briefcase'],
        'gov_tax'         => ['label' => 'الضرائب',            'category' => 'government', 'emoji' => '💰', 'icon' => 'briefcase'],
        'gov_other'       => ['label' => 'مصلحة تانية',        'category' => 'government', 'emoji' => '🏛',  'icon' => 'briefcase'],

        // ── education ──────────────────────────────────────
        'edu_nursery'     => ['label' => 'حضانة',              'category' => 'education',  'emoji' => '🧸', 'icon' => 'baby'],
        'edu_school_prim' => ['label' => 'مدرسة ابتدائي',      'category' => 'education',  'emoji' => '🏫', 'icon' => 'graduation'],
        'edu_school_prep' => ['label' => 'مدرسة إعدادي',       'category' => 'education',  'emoji' => '🏫', 'icon' => 'graduation'],
        'edu_school_sec'  => ['label' => 'مدرسة ثانوي',        'category' => 'education',  'emoji' => '🎒', 'icon' => 'graduation'],
        'edu_university'  => ['label' => 'جامعة / كلية',       'category' => 'education',  'emoji' => '🎓', 'icon' => 'graduation'],
        'edu_center'      => ['label' => 'سنتر دروس',          'category' => 'education',  'emoji' => '📚', 'icon' => 'book'],
        'edu_lang'        => ['label' => 'لغات',               'category' => 'education',  'emoji' => '🗣',  'icon' => 'graduation'],
        'edu_other'       => ['label' => 'تعليم تاني',         'category' => 'education',  'emoji' => '✏️', 'icon' => 'graduation'],

        // ── transport ──────────────────────────────────────
        'trn_microbus'    => ['label' => 'موقف ميكروباص',      'category' => 'transport',  'emoji' => '🚐', 'icon' => 'car'],
        'trn_taxi'        => ['label' => 'موقف تاكسي',         'category' => 'transport',  'emoji' => '🚖', 'icon' => 'car'],
        'trn_bus'         => ['label' => 'موقف أوتوبيس',       'category' => 'transport',  'emoji' => '🚌', 'icon' => 'car'],
        'trn_railway'     => ['label' => 'محطة قطر',           'category' => 'transport',  'emoji' => '🚉', 'icon' => 'car'],
        'trn_uber'        => ['label' => 'تاكسي تطبيق',         'category' => 'transport',  'emoji' => '🚗', 'icon' => 'car'],
        'trn_other'       => ['label' => 'مواصلات تانية',      'category' => 'transport',  'emoji' => '🛣',  'icon' => 'car'],

        // ── religious ──────────────────────────────────────
        'rel_mosque'      => ['label' => 'جامع',               'category' => 'religious',  'emoji' => '🕌', 'icon' => 'check'],
        'rel_church'      => ['label' => 'كنيسة',              'category' => 'religious',  'emoji' => '⛪', 'icon' => 'check'],
        'rel_zawia'       => ['label' => 'زاوية',              'category' => 'religious',  'emoji' => '🕋', 'icon' => 'check'],

        // ── banks ──────────────────────────────────────────
        'bank_branch'     => ['label' => 'فرع بنك',            'category' => 'banks',      'emoji' => '🏦', 'icon' => 'cart'],
        'bank_atm'        => ['label' => 'ATM ماكينة سحب',     'category' => 'banks',      'emoji' => '🏧', 'icon' => 'cart'],
        'bank_exchange'   => ['label' => 'صرافة',              'category' => 'banks',      'emoji' => '💵', 'icon' => 'cart'],

        // ── tourist / parks ────────────────────────────────
        'tour_park'       => ['label' => 'حديقة عامة',         'category' => 'tourist',    'emoji' => '🌳', 'icon' => 'leaf'],
        'tour_corniche'   => ['label' => 'كورنيش',             'category' => 'tourist',    'emoji' => '🌊', 'icon' => 'leaf'],
        'tour_club'       => ['label' => 'نادي',               'category' => 'tourist',    'emoji' => '⚽', 'icon' => 'dumbbell'],
        'tour_monument'   => ['label' => 'معلم تاريخي',        'category' => 'tourist',    'emoji' => '🏛',  'icon' => 'check'],
        'tour_cinema'     => ['label' => 'سينما',              'category' => 'tourist',    'emoji' => '🎬', 'icon' => 'tv'],
        'tour_other'      => ['label' => 'مكان تاني',          'category' => 'tourist',    'emoji' => '📍', 'icon' => 'map-pin'],

        // ── emergency ──────────────────────────────────────
        'emr_police'      => ['label' => 'قسم شرطة',           'category' => 'emergency',  'emoji' => '🚓', 'icon' => 'bolt'],
        'emr_ambulance'   => ['label' => 'إسعاف',              'category' => 'emergency',  'emoji' => '🚑', 'icon' => 'bolt'],
        'emr_fire'        => ['label' => 'مطافي',              'category' => 'emergency',  'emoji' => '🚒', 'icon' => 'flame'],
        'emr_hospital'    => ['label' => 'مستشفى طوارئ',       'category' => 'emergency',  'emoji' => '🏥', 'icon' => 'stethoscope'],
        'emr_civil'       => ['label' => 'حماية مدنية',        'category' => 'emergency',  'emoji' => '🛡',  'icon' => 'bolt'],
    ];

    protected function casts(): array
    {
        return [
            'is_24h'         => 'boolean',
            'is_verified'    => 'boolean',
            'is_active'      => 'boolean',
            'has_menu'       => 'boolean',
            'promoted_until' => 'datetime',
            'lat'            => 'decimal:7',
            'lng'            => 'decimal:7',
            'rating_avg'     => 'decimal:1',
        ];
    }

    public function isPromoted(): bool
    {
        return $this->promoted_until && $this->promoted_until->isFuture();
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class)->latest('reviewed_at');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BusinessPhoto::class)->orderBy('sort');
    }

    public function menuCategories(): HasMany
    {
        return $this->hasMany(MenuCategory::class)->orderBy('sort')->orderBy('id');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort')->orderBy('id');
    }

    public function categoryMeta(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['services'];
    }

    public function subTypeMeta(): array
    {
        return self::SUB_TYPES[$this->sub_type] ?? ['label' => $this->sub_type, 'category' => $this->category, 'emoji' => '📍', 'icon' => 'briefcase'];
    }

    public function displayType(): string
    {
        if ($this->custom_sub_type) return $this->custom_sub_type;
        return $this->subTypeMeta()['label'];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'category', 'sub_type', 'custom_sub_type', 'zone_id', 'owner_user_id',
    'description', 'phone', 'whatsapp', 'hotline', 'address', 'lat', 'lng',
    'hours', 'hours_schedule', 'is_24h', 'is_verified', 'promoted_until', 'is_active',
    'rating_avg', 'ratings_count', 'views_count', 'phone_clicks', 'whatsapp_clicks',
    'emoji', 'photo_url', 'has_menu', 'menu_currency', 'external_id', 'extra',
    'booking_enabled', 'booking_slot_minutes', 'booking_lead_hours', 'booking_capacity',
    'delivery_fees', 'delivery_min_order',
])]
class Business extends Model
{
    public const CATEGORIES = [
        'food'        => ['label' => 'مطاعم وكافيهات',   'emoji' => '🍽️', 'icon' => 'utensils',    'color' => '#FF7A4D'],
        'hotels'      => ['label' => 'فنادق ومنتجعات',    'emoji' => '🏨', 'icon' => 'briefcase',   'color' => '#9333EA'],
        'medical'     => ['label' => 'دكاترة وصيدليات',  'emoji' => '🩺', 'icon' => 'stethoscope', 'color' => '#1FA857'],
        'shops'       => ['label' => 'محلات',             'emoji' => '🛒', 'icon' => 'cart',        'color' => '#7C3AED'],
        'craftsmen'   => ['label' => 'صنايعية',           'emoji' => '🔧', 'icon' => 'wrench',      'color' => '#FFB85C'],
        'services'    => ['label' => 'خدمات تانية',      'emoji' => '🛎️', 'icon' => 'briefcase',   'color' => '#0EA5E9'],
        'companies'   => ['label' => 'شركات ومصانع',      'emoji' => '🏢', 'icon' => 'briefcase',   'color' => '#475569'],
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

        // ── hotels ─────────────────────────────────────────
        'hotel_3star'     => ['label' => 'فندق ٣ نجوم',         'category' => 'hotels',     'emoji' => '🏨', 'icon' => 'briefcase'],
        'hotel_4star'     => ['label' => 'فندق ٤ نجوم',         'category' => 'hotels',     'emoji' => '🏨', 'icon' => 'briefcase'],
        'hotel_5star'     => ['label' => 'فندق ٥ نجوم',         'category' => 'hotels',     'emoji' => '🏨', 'icon' => 'briefcase'],
        'hotel_resort'    => ['label' => 'منتجع / قرية سياحية',  'category' => 'hotels',     'emoji' => '🌴', 'icon' => 'leaf'],
        'hotel_apart'     => ['label' => 'شقق فندقية',           'category' => 'hotels',     'emoji' => '🛏', 'icon' => 'briefcase'],
        'hotel_guesthouse'=> ['label' => 'بيت ضيافة / نُزُل',     'category' => 'hotels',     'emoji' => '🛌', 'icon' => 'briefcase'],
        'hotel_other'     => ['label' => 'مكان إقامة تاني',     'category' => 'hotels',     'emoji' => '🏨', 'icon' => 'briefcase'],

        // ── companies ──────────────────────────────────────
        'comp_factory'    => ['label' => 'مصنع',                'category' => 'companies',  'emoji' => '🏭', 'icon' => 'briefcase'],
        'comp_office'     => ['label' => 'مكتب شركة',           'category' => 'companies',  'emoji' => '🏢', 'icon' => 'briefcase'],
        'comp_construction'=> ['label' => 'مقاولات وإنشاءات',   'category' => 'companies',  'emoji' => '🏗', 'icon' => 'tools'],
        'comp_logistics'  => ['label' => 'شحن ولوجستيات',       'category' => 'companies',  'emoji' => '🚚', 'icon' => 'truck'],
        'comp_realestate' => ['label' => 'تطوير عقاري',         'category' => 'companies',  'emoji' => '🏢', 'icon' => 'briefcase'],
        'comp_legal'      => ['label' => 'مكتب محاماة',          'category' => 'companies',  'emoji' => '⚖️', 'icon' => 'briefcase'],
        'comp_accounting' => ['label' => 'مكتب محاسبة',          'category' => 'companies',  'emoji' => '📊', 'icon' => 'briefcase'],
        'comp_marketing'  => ['label' => 'دعاية وتسويق',         'category' => 'companies',  'emoji' => '📣', 'icon' => 'briefcase'],
        'comp_tech'       => ['label' => 'شركة تكنولوجيا',       'category' => 'companies',  'emoji' => '💻', 'icon' => 'briefcase'],
        'comp_other'      => ['label' => 'شركة تانية',           'category' => 'companies',  'emoji' => '🏢', 'icon' => 'briefcase'],
    ];

    /**
     * Per-sub_type extra fields. The form renders only fields whose
     * `applies_to` matches the picked sub_type (or its category prefix).
     *
     * Each field: ['label', 'type' (text|number|select|checkbox), 'options'?, 'placeholder'?, 'help'?, 'applies_to' => array of sub_types or category keys].
     */
    public const EXTRA_FIELDS = [
        // ── Hotels ─────────────────────────────────────
        'stars' => [
            'label' => 'تصنيف النجوم', 'type' => 'select',
            'options' => ['1' => '⭐', '2' => '⭐⭐', '3' => '⭐⭐⭐', '4' => '⭐⭐⭐⭐', '5' => '⭐⭐⭐⭐⭐'],
            'applies_to' => ['hotels'],
        ],
        'rooms' => [
            'label' => 'عدد الغرف', 'type' => 'number',
            'placeholder' => 'مثلاً: 50',
            'applies_to' => ['hotels'],
        ],
        'has_pool' => [
            'label' => 'حمام سباحة', 'type' => 'checkbox',
            'applies_to' => ['hotels'],
        ],
        'has_breakfast' => [
            'label' => 'إفطار شامل', 'type' => 'checkbox',
            'applies_to' => ['hotels'],
        ],
        'has_wifi' => [
            'label' => 'واي فاي مجاني', 'type' => 'checkbox',
            'applies_to' => ['hotels', 'food'],
        ],

        // ── Food ────────────────────────────────────────
        'cuisine' => [
            'label' => 'نوع المطبخ', 'type' => 'select',
            'options' => [
                'مصري' => 'مصري', 'شرقي' => 'شرقي', 'إيطالي' => 'إيطالي', 'أمريكي' => 'أمريكي',
                'صيني وآسيوي' => 'صيني وآسيوي', 'لبناني وشامي' => 'لبناني وشامي',
                'بحري وأسماك' => 'بحري وأسماك', 'حلويات ومخبوزات' => 'حلويات ومخبوزات',
                'فاست فود' => 'فاست فود', 'متنوع' => 'متنوع',
            ],
            'applies_to' => ['food'],
        ],
        'has_delivery' => [
            'label' => 'بيوصّل دليفري', 'type' => 'checkbox',
            'applies_to' => ['food'],
        ],
        'family_section' => [
            'label' => 'قسم عائلات', 'type' => 'checkbox',
            'applies_to' => ['food'],
        ],
        'avg_price' => [
            'label' => 'متوسط السعر للشخص', 'type' => 'select',
            'options' => [
                '50_below'  => 'أقل من ٥٠ ج',
                '50_100'    => '٥٠–١٠٠ ج',
                '100_200'   => '١٠٠–٢٠٠ ج',
                '200_500'   => '٢٠٠–٥٠٠ ج',
                '500_above' => 'فوق ٥٠٠ ج',
            ],
            'applies_to' => ['food'],
        ],

        // ── Medical: doctors / clinics ─────────────────
        // Pharmacies, labs, and vets get DIFFERENT fields below.
        'specialty' => [
            'label' => 'التخصص الدقيق', 'type' => 'text',
            'placeholder' => 'مثلاً: جلدية تجميلية، أسنان أطفال…',
            'applies_to' => ['doctor', 'pediatrician', 'dentist', 'gynecologist', 'orthopedic', 'ent', 'dermatology', 'physio', 'nurse', 'medical_other'],
        ],
        'clinic_days' => [
            'label' => 'أيام الكشف', 'type' => 'text',
            'placeholder' => 'مثلاً: السبت والثلاثاء',
            'applies_to' => ['doctor', 'pediatrician', 'dentist', 'gynecologist', 'orthopedic', 'ent', 'dermatology', 'physio', 'medical_other'],
        ],
        'has_appointment' => [
            'label' => 'بالحجز المسبق', 'type' => 'checkbox',
            'applies_to' => ['doctor', 'pediatrician', 'dentist', 'gynecologist', 'orthopedic', 'ent', 'dermatology', 'physio', 'medical_other'],
        ],

        // ── Pharmacy ───────────────────────────────────
        'pharmacy_delivery' => [
            'label' => 'بيوصّل دواء للبيت', 'type' => 'checkbox',
            'applies_to' => ['pharmacy'],
        ],
        'pharmacy_has_lab' => [
            'label' => 'بيقيس ضغط/سكر', 'type' => 'checkbox',
            'applies_to' => ['pharmacy'],
        ],

        // ── Lab ────────────────────────────────────────
        'lab_home_collection' => [
            'label' => 'بيجي يسحب عينة من البيت', 'type' => 'checkbox',
            'applies_to' => ['lab'],
        ],
        'lab_results_whatsapp' => [
            'label' => 'النتايج على واتساب', 'type' => 'checkbox',
            'applies_to' => ['lab'],
        ],

        // ── Vet ────────────────────────────────────────
        'vet_animals' => [
            'label' => 'بيعالج إيه؟', 'type' => 'select',
            'options' => [
                'pets'      => 'حيوانات أليفة (قطط، كلاب)',
                'livestock' => 'مواشي وحيوانات مزرعة',
                'birds'     => 'طيور',
                'all'       => 'الكل',
            ],
            'applies_to' => ['vet'],
        ],
        'vet_home_visits' => [
            'label' => 'بيجي للبيت/المزرعة', 'type' => 'checkbox',
            'applies_to' => ['vet'],
        ],

        // ── Education ──────────────────────────────────
        'school_type' => [
            'label' => 'نوع المدرسة/المؤسسة', 'type' => 'select',
            'options' => [
                'حكومية'      => 'حكومية',
                'خاصة'        => 'خاصة',
                'لغات'        => 'لغات',
                'تجريبية'     => 'تجريبية',
                'دولية'       => 'دولية',
                'أزهرية'      => 'أزهرية',
            ],
            'applies_to' => ['education'],
        ],
        'languages' => [
            'label' => 'اللغات المتاحة', 'type' => 'text',
            'placeholder' => 'مثلاً: عربي، إنجليزي، فرنساوي',
            'applies_to' => ['education'],
        ],

        // ── Companies ──────────────────────────────────
        'industry' => [
            'label' => 'المجال', 'type' => 'text',
            'placeholder' => 'مثلاً: تصنيع غذائي، عقارات، استشارات…',
            'applies_to' => ['companies'],
        ],
        'employees_range' => [
            'label' => 'عدد الموظفين', 'type' => 'select',
            'options' => [
                'under_10'  => 'أقل من ١٠',
                '10_50'     => '١٠–٥٠',
                '50_200'    => '٥٠–٢٠٠',
                '200_1000'  => '٢٠٠–١٠٠٠',
                'over_1000' => 'أكتر من ١٠٠٠',
            ],
            'applies_to' => ['companies'],
        ],
        'year_founded' => [
            'label' => 'سنة التأسيس', 'type' => 'number',
            'placeholder' => 'مثلاً: 2010',
            'applies_to' => ['companies'],
        ],
        'website' => [
            'label' => 'الموقع الإلكتروني', 'type' => 'text',
            'placeholder' => 'https://example.com',
            'applies_to' => ['hotels', 'companies', 'education'],
        ],

        // ── Craftsmen (all sub-types) ─────────────────
        'experience_years' => [
            'label' => 'سنين الخبرة', 'type' => 'number',
            'placeholder' => 'مثلاً: 10',
            'applies_to' => ['craftsmen'],
        ],
        'visit_price' => [
            'label' => 'سعر الكشف/المعاينة', 'type' => 'text',
            'placeholder' => 'مثلاً: ٥٠ ج · أو مجاني',
            'applies_to' => ['craftsmen'],
        ],
        'emergency_call' => [
            'label' => 'بيرد على الطوارئ', 'type' => 'checkbox',
            'applies_to' => ['craftsmen'],
        ],
        'service_area' => [
            'label' => 'بيشتغل في إيه من المناطق', 'type' => 'text',
            'placeholder' => 'مثلاً: بنها وقها وكفر شكر',
            'applies_to' => ['craftsmen', 'driver', 'tuktuk', 'delivery', 'house_cleaning', 'moving'],
        ],

        // ── Shops ──────────────────────────────────────
        'shop_delivery' => [
            'label' => 'بيوصّل', 'type' => 'checkbox',
            'applies_to' => ['grocery', 'supermarket', 'butcher', 'fish_shop', 'fruit_veg', 'baby_shop', 'toys', 'bookshop', 'mobile_shop', 'electronics', 'furniture'],
        ],
        'accepts_cards' => [
            'label' => 'بيقبل فيزا/InstaPay', 'type' => 'checkbox',
            'applies_to' => ['shops'],
        ],
        'has_warranty' => [
            'label' => 'بيدّي ضمان على المنتجات', 'type' => 'checkbox',
            'applies_to' => ['mobile_shop', 'electronics', 'hardware', 'furniture', 'gold_shop'],
        ],
        'fuel_types' => [
            'label' => 'أنواع البنزين', 'type' => 'text',
            'placeholder' => 'مثلاً: ٨٠ + ٩٢ + سولار',
            'applies_to' => ['gas_station'],
        ],

        // ── Services ───────────────────────────────────
        'salon_gender' => [
            'label' => 'القسم متاح لـ', 'type' => 'select',
            'options' => ['men' => 'رجالي', 'women' => 'حريمي', 'unisex' => 'مختلط'],
            'applies_to' => ['barber', 'salon'],
        ],
        'salon_home_visits' => [
            'label' => 'بيجي للبيت', 'type' => 'checkbox',
            'applies_to' => ['barber', 'salon', 'house_cleaning'],
        ],
        'gym_gender' => [
            'label' => 'الجيم لـ', 'type' => 'select',
            'options' => ['men' => 'رجالي', 'women' => 'حريمي', 'mixed' => 'مختلط'],
            'applies_to' => ['gym'],
        ],
        'monthly_price' => [
            'label' => 'الاشتراك الشهري', 'type' => 'text',
            'placeholder' => 'مثلاً: ٣٠٠ ج',
            'applies_to' => ['gym'],
        ],
        'has_classes' => [
            'label' => 'فيه كلاسات (يوجا، كروس فيت، إلخ)', 'type' => 'checkbox',
            'applies_to' => ['gym'],
        ],
        'tutor_subjects' => [
            'label' => 'المواد', 'type' => 'text',
            'placeholder' => 'مثلاً: رياضيات، فيزياء، إنجليزي',
            'applies_to' => ['tutor', 'school'],
        ],
        'photographer_styles' => [
            'label' => 'بيصوّر إيه', 'type' => 'text',
            'placeholder' => 'مثلاً: أفراح، خطوبات، منتجات، بورتريه',
            'applies_to' => ['photographer'],
        ],

        // ── Religious ──────────────────────────────────
        'has_madrasah' => [
            'label' => 'فيه مدرسة قرآن/كنسية', 'type' => 'checkbox',
            'applies_to' => ['rel_mosque', 'rel_church'],
        ],
        'friday_imam' => [
            'label' => 'إمام الجمعة', 'type' => 'text',
            'placeholder' => 'مثلاً: الشيخ محمد عبدالعزيز',
            'applies_to' => ['rel_mosque'],
        ],
        'rel_capacity' => [
            'label' => 'سعة المصلين/الحضور', 'type' => 'number',
            'placeholder' => 'مثلاً: 500',
            'applies_to' => ['religious'],
        ],

        // ── Banks ──────────────────────────────────────
        'has_atm' => [
            'label' => 'فيه ATM', 'type' => 'checkbox',
            'applies_to' => ['bank_branch'],
        ],
        'atm_24h' => [
            'label' => 'الـATM شغال ٢٤ ساعة', 'type' => 'checkbox',
            'applies_to' => ['bank_atm', 'bank_branch'],
        ],
        'currencies_traded' => [
            'label' => 'العملات اللي بتتعامل فيها', 'type' => 'text',
            'placeholder' => 'مثلاً: USD، EUR، GBP، SAR',
            'applies_to' => ['bank_exchange'],
        ],

        // ── Tourist / parks / leisure ─────────────────
        'free_entry' => [
            'label' => 'دخول مجاني', 'type' => 'checkbox',
            'applies_to' => ['tour_park', 'tour_corniche', 'tour_monument'],
        ],
        'kids_play' => [
            'label' => 'فيه ألعاب أطفال', 'type' => 'checkbox',
            'applies_to' => ['tour_park', 'tour_corniche', 'tour_club'],
        ],
        'entrance_fee' => [
            'label' => 'سعر الدخول', 'type' => 'text',
            'placeholder' => 'مثلاً: ١٠ ج للبالغين',
            'applies_to' => ['tour_park', 'tour_monument', 'tour_cinema', 'tour_club'],
        ],
        'membership_fee' => [
            'label' => 'الاشتراك السنوي', 'type' => 'text',
            'placeholder' => 'مثلاً: ٢٠٠٠ ج',
            'applies_to' => ['tour_club'],
        ],

        // ── Government ─────────────────────────────────
        'gov_appointment' => [
            'label' => 'محتاج موعد مسبق', 'type' => 'checkbox',
            'applies_to' => ['government'],
        ],
        'gov_required_papers' => [
            'label' => 'الأوراق المطلوبة', 'type' => 'text',
            'placeholder' => 'مثلاً: بطاقة + إيصال مرافق',
            'applies_to' => ['government'],
        ],

        // ── Transport ──────────────────────────────────
        'transport_routes' => [
            'label' => 'الخطوط/الوجهات', 'type' => 'text',
            'placeholder' => 'مثلاً: بنها → القاهرة، طوخ، قها',
            'applies_to' => ['trn_microbus', 'trn_bus', 'trn_taxi'],
        ],
        'transport_fare' => [
            'label' => 'متوسط الأجرة', 'type' => 'text',
            'placeholder' => 'مثلاً: ٥-١٠ ج',
            'applies_to' => ['trn_microbus', 'trn_taxi', 'trn_uber', 'tuktuk'],
        ],

        // ── Emergency ──────────────────────────────────
        'response_area' => [
            'label' => 'بيغطّي إيه من مناطق', 'type' => 'text',
            'placeholder' => 'مثلاً: بنها كاملة + قها + طوخ',
            'applies_to' => ['emergency'],
        ],
    ];

    /**
     * Per-category labels for the "menu/services" feature so the UI adapts
     * (a restaurant has أقسام/أصناف، a hotel has باقات/غرف، a doctor has كشوفات).
     * Falls back to "الخدمات/الصنف" if category isn't in the map.
     */
    public static function menuLabels(string $category): array
    {
        $map = [
            'food'       => ['title' => 'المنيو',           'cta_show' => 'شوف المنيو والأسعار', 'category_label' => 'القسم',       'item_label' => 'الصنف',     'price_label' => 'السعر',           'cat_examples' => 'بيتزا، مشروبات، حلو…',                 'item_placeholder' => 'مثلاً: مارجريتا'],
            'hotels'     => ['title' => 'الغرف والباقات',   'cta_show' => 'شوف الغرف والأسعار',   'category_label' => 'الفئة',       'item_label' => 'الغرفة/الباقة', 'price_label' => 'السعر/الليلة',     'cat_examples' => 'غرف عادية، أجنحة، شاليهات…',          'item_placeholder' => 'مثلاً: غرفة دبل'],
            'medical'    => ['title' => 'الخدمات والكشوفات','cta_show' => 'شوف الخدمات والأسعار', 'category_label' => 'القسم',       'item_label' => 'الخدمة',     'price_label' => 'السعر',           'cat_examples' => 'كشوفات، متابعة، أشعة، تحاليل…',         'item_placeholder' => 'مثلاً: كشف عام'],
            'shops'      => ['title' => 'المنتجات',         'cta_show' => 'شوف المنتجات والأسعار', 'category_label' => 'القسم',       'item_label' => 'المنتج',     'price_label' => 'السعر',           'cat_examples' => 'موبايلات، إكسسوارات، أجهزة…',           'item_placeholder' => 'مثلاً: iPhone 15'],
            'craftsmen'  => ['title' => 'الخدمات والأسعار', 'cta_show' => 'شوف الخدمات والأسعار',  'category_label' => 'النوع',       'item_label' => 'الخدمة',     'price_label' => 'السعر',           'cat_examples' => 'تركيبات، صيانة، طوارئ…',                'item_placeholder' => 'مثلاً: تركيب سخان'],
            'services'   => ['title' => 'الخدمات والباقات', 'cta_show' => 'شوف الخدمات والأسعار',  'category_label' => 'القسم',       'item_label' => 'الخدمة',     'price_label' => 'السعر',           'cat_examples' => 'كلاسات، باقات شهرية، خدمات إضافية…',  'item_placeholder' => 'مثلاً: قص شعر'],
            'companies'  => ['title' => 'الخدمات والمنتجات','cta_show' => 'شوف الخدمات والأسعار',  'category_label' => 'القسم',       'item_label' => 'الخدمة',     'price_label' => 'السعر',           'cat_examples' => 'استشارة، تنفيذ، تطوير…',                'item_placeholder' => 'مثلاً: استشارة قانونية'],
            'education'  => ['title' => 'الكورسات والمراحل','cta_show' => 'شوف الكورسات والأسعار', 'category_label' => 'المرحلة',     'item_label' => 'الكورس',     'price_label' => 'المصاريف',         'cat_examples' => 'KG، ابتدائي، إعدادي، ثانوي…',           'item_placeholder' => 'مثلاً: KG2'],
            'tourist'    => ['title' => 'التذاكر والباقات', 'cta_show' => 'شوف التذاكر والأسعار',  'category_label' => 'الفئة',       'item_label' => 'التذكرة',     'price_label' => 'السعر',           'cat_examples' => 'تذاكر بالغين، أطفال، اشتراكات…',       'item_placeholder' => 'مثلاً: تذكرة بالغ'],
            'banks'      => ['title' => 'الخدمات والرسوم',  'cta_show' => 'شوف الخدمات',            'category_label' => 'النوع',       'item_label' => 'الخدمة',     'price_label' => 'الرسوم',           'cat_examples' => 'تحويلات، حسابات، قروض…',                'item_placeholder' => 'مثلاً: تحويل دولي'],
            'transport'  => ['title' => 'الخطوط والأجرة',   'cta_show' => 'شوف الخطوط والأجرة',    'category_label' => 'الجهة',       'item_label' => 'الخط',       'price_label' => 'الأجرة',           'cat_examples' => 'القاهرة، طوخ، قها…',                    'item_placeholder' => 'مثلاً: بنها → القاهرة'],
            'government' => ['title' => 'الخدمات',          'cta_show' => 'شوف الخدمات',            'category_label' => 'القسم',       'item_label' => 'الخدمة',     'price_label' => 'الرسوم',           'cat_examples' => 'تجديد، استخراج، شكاوى…',               'item_placeholder' => 'مثلاً: تجديد رخصة'],
            'religious'  => ['title' => 'الأنشطة والدروس',  'cta_show' => 'شوف الأنشطة',           'category_label' => 'النوع',       'item_label' => 'النشاط',     'price_label' => 'المساهمة',         'cat_examples' => 'دروس، حلقات، أنشطة…',                  'item_placeholder' => 'مثلاً: درس مغرب'],
            'emergency'  => ['title' => 'الخدمات',          'cta_show' => 'شوف الخدمات',            'category_label' => 'النوع',       'item_label' => 'الخدمة',     'price_label' => '',                  'cat_examples' => 'إسعاف، إخلاء، حماية…',                  'item_placeholder' => 'مثلاً: إسعاف منزلي'],
        ];
        return $map[$category] ?? $map['services'];
    }

    /** Return the extra-field definitions that apply to a given sub_type. */
    public static function extraFieldsFor(string $subType): array
    {
        $st = self::SUB_TYPES[$subType] ?? null;
        $cat = $st['category'] ?? null;
        $applicable = [];
        foreach (self::EXTRA_FIELDS as $key => $def) {
            $rules = $def['applies_to'] ?? [];
            // Match by category key (e.g., 'food', 'hotels') or exact sub_type
            if (in_array($cat, $rules, true) || in_array($subType, $rules, true)) {
                $applicable[$key] = $def;
            }
        }
        return $applicable;
    }

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
            'extra'          => 'array',
            'hours_schedule' => 'array',
            'booking_enabled'      => 'boolean',
            'booking_slot_minutes' => 'integer',
            'booking_lead_hours'   => 'integer',
            'booking_capacity'     => 'integer',
            'delivery_fees'        => 'array',
            'delivery_min_order'   => 'integer',
        ];
    }

    /**
     * Resolve the delivery fee (EGP) for a given area id, or null if this
     * area isn't serviced by the business. Returns 0 explicitly when the
     * business set it to "free delivery here".
     */
    public function deliveryFeeFor(int $areaId): ?float
    {
        $map = (array) ($this->delivery_fees ?? []);
        if (! array_key_exists((string) $areaId, $map)) return null;
        return (float) $map[(string) $areaId];
    }

    /** Does this business deliver anywhere at all? */
    public function offersDelivery(): bool
    {
        return is_array($this->delivery_fees) && count($this->delivery_fees) > 0;
    }

    /** Day codes used by hours_schedule. Saturday-first to match Egyptian week. */
    public const WEEKDAYS = [
        'sat' => 'السبت',
        'sun' => 'الأحد',
        'mon' => 'الإتنين',
        'tue' => 'الثلاثاء',
        'wed' => 'الأربعاء',
        'thu' => 'الخميس',
        'fri' => 'الجمعة',
    ];

    private const DOW_TO_KEY = [
        6 => 'sat',  0 => 'sun',  1 => 'mon',
        2 => 'tue',  3 => 'wed',  4 => 'thu', 5 => 'fri',
    ];

    /**
     * Check whether the business is currently open based on hours_schedule.
     * Returns null if no schedule is set (caller should fall back to is_24h or freeform `hours`).
     */
    public function isOpenNow(?\DateTimeInterface $now = null): ?bool
    {
        if ($this->is_24h) return true;
        $schedule = $this->hours_schedule;
        if (! is_array($schedule) || empty($schedule)) return null;

        $now ??= now('Africa/Cairo');
        $todayKey     = self::DOW_TO_KEY[(int) $now->format('w')];
        $yesterdayKey = self::DOW_TO_KEY[(int) ((int) $now->format('w') + 6) % 7];

        // Today's shift
        if ($shift = $this->parseShift($schedule[$todayKey] ?? null)) {
            [$startMin, $endMin, $overnight] = $shift;
            $nowMin = ((int) $now->format('G')) * 60 + (int) $now->format('i');
            if (! $overnight) {
                if ($nowMin >= $startMin && $nowMin <= $endMin) return true;
            } else {
                // Shift wraps past midnight (e.g., 20:00-03:00) — open if past start tonight
                if ($nowMin >= $startMin) return true;
            }
        }
        // Yesterday's overnight tail (e.g., yesterday 20:00-03:00, now is 02:30 today)
        if ($shift = $this->parseShift($schedule[$yesterdayKey] ?? null)) {
            [$startMin, $endMin, $overnight] = $shift;
            if ($overnight) {
                $nowMin = ((int) $now->format('G')) * 60 + (int) $now->format('i');
                if ($nowMin <= $endMin) return true;
            }
        }
        return false;
    }

    /** Returns "مفتوح · 9ص-11م" / "مغلق · يفتح 9ص" / null when no schedule. */
    public function openStatusLabel(?\DateTimeInterface $now = null): ?string
    {
        if ($this->is_24h) return 'مفتوح · ٢٤ ساعة';
        $open = $this->isOpenNow($now);
        if ($open === null) return null;

        $now ??= now('Africa/Cairo');
        $todayKey = self::DOW_TO_KEY[(int) $now->format('w')];
        $shift = $this->parseShift(($this->hours_schedule ?? [])[$todayKey] ?? null);

        if ($open && $shift) {
            return 'مفتوح · ' . $this->prettyTime($shift[0]) . '-' . $this->prettyTime($shift[1]);
        }
        // Find next opening day
        $next = $this->nextOpeningTime($now);
        if (! $next) return 'مغلق';
        return 'مغلق · يفتح ' . $next;
    }

    /** Find the next opening label, e.g. "9ص" or "السبت 9ص". */
    private function nextOpeningTime(\DateTimeInterface $from): ?string
    {
        $schedule = $this->hours_schedule ?? [];
        $dow = (int) $from->format('w');
        for ($i = 0; $i < 7; $i++) {
            $key = self::DOW_TO_KEY[($dow + $i) % 7];
            $shift = $this->parseShift($schedule[$key] ?? null);
            if (! $shift) continue;

            // Today: only count if start is still in the future
            if ($i === 0) {
                $nowMin = ((int) $from->format('G')) * 60 + (int) $from->format('i');
                if ($shift[0] <= $nowMin) continue;
                return $this->prettyTime($shift[0]);
            }
            return self::WEEKDAYS[$key] . ' ' . $this->prettyTime($shift[0]);
        }
        return null;
    }

    /** "09:00-23:30" → [540, 1410, false]. Returns null if invalid/empty. */
    private function parseShift(?string $raw): ?array
    {
        if (! $raw || ! preg_match('/^(\d{1,2}):(\d{2})\s*-\s*(\d{1,2}):(\d{2})$/', trim($raw), $m)) return null;
        $startMin = ((int) $m[1]) * 60 + (int) $m[2];
        $endMin   = ((int) $m[3]) * 60 + (int) $m[4];
        $overnight = $endMin < $startMin;
        return [$startMin, $overnight ? $endMin + 24 * 60 : $endMin, $overnight];
    }

    /** 540 → "9ص"; 1410 → "11:30م". */
    private function prettyTime(int $totalMin): string
    {
        $h = (int) ($totalMin / 60) % 24;
        $m = $totalMin % 60;
        $suffix = $h < 12 ? 'ص' : 'م';
        $h12 = $h % 12 ?: 12;
        return $m === 0 ? "{$h12}{$suffix}" : sprintf('%d:%02d%s', $h12, $m, $suffix);
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('starts_at');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->latest();
    }

    /**
     * Compute bookable slots for a given date, gated by hours_schedule + existing bookings.
     *
     * @return array<array{
     *   starts_at: \Carbon\Carbon,
     *   label: string,
     *   capacity: int,
     *   taken: int,
     *   available: int,
     *   bookable: bool,
     *   reason: ?string,
     * }>
     */
    public function availableSlots(\Carbon\Carbon $date): array
    {
        if (! $this->booking_enabled) return [];

        $tz = 'Africa/Cairo';
        $day = $date->copy()->setTimezone($tz)->startOfDay();
        $dayKey = ['sun','mon','tue','wed','thu','fri','sat'][(int) $day->format('w')];

        // Closed today?
        $shift = $this->parseShift(($this->hours_schedule ?? [])[$dayKey] ?? null);
        if (! $shift && ! $this->is_24h) return [];

        // 24/7 = 06:00 → 22:00 (we don't book through the night even when "open")
        [$startMin, $endMin, $overnight] = $shift ?? [6 * 60, 22 * 60, false];
        if ($overnight) $endMin += 24 * 60;

        $slotMin = max(5, (int) ($this->booking_slot_minutes ?: 30));
        $capacity = max(1, (int) ($this->booking_capacity ?: 1));
        $leadHours = max(0, (int) ($this->booking_lead_hours ?? 2));
        $minBookableAt = now($tz)->addHours($leadHours);

        // Pre-load taken counts in [day, day+1) bucketed by start time
        $bookedCounts = $this->bookings()
            ->blocking()
            ->whereBetween('starts_at', [$day->copy(), $day->copy()->endOfDay()])
            ->get()
            ->groupBy(fn ($b) => $b->starts_at->setTimezone($tz)->format('H:i'))
            ->map->count();

        $slots = [];
        for ($m = $startMin; $m + $slotMin <= $endMin; $m += $slotMin) {
            $slotAt = $day->copy()->addMinutes($m);
            $key = $slotAt->format('H:i');
            $taken = (int) ($bookedCounts[$key] ?? 0);
            $available = max(0, $capacity - $taken);
            $reason = null;
            $bookable = true;
            if ($slotAt->lt($minBookableAt)) { $bookable = false; $reason = 'past'; }
            elseif ($available <= 0) { $bookable = false; $reason = 'full'; }

            $slots[] = [
                'starts_at' => $slotAt,
                'label'     => $this->prettyTime($m % (24 * 60)),
                'capacity'  => $capacity,
                'taken'     => $taken,
                'available' => $available,
                'bookable'  => $bookable,
                'reason'    => $reason,
            ];
        }
        return $slots;
    }

    public function categoryMeta(): array
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['services'];
    }

    /** Categories where "اطلب أوردر" makes sense (cart → WhatsApp via WAAPI). */
    public const ORDER_CATEGORIES = ['food', 'shops'];

    /** Cart/ordering is offered for businesses in ORDER_CATEGORIES that also have a WhatsApp number. */
    public function supportsOrdering(): bool
    {
        return in_array($this->category, self::ORDER_CATEGORIES, true) && ! empty($this->whatsapp);
    }

    /** Booking is offered for non-food categories (food uses ordering instead). */
    public function bookingApplicable(): bool
    {
        return $this->category !== 'food';
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

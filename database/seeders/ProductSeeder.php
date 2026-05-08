<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // [name, category, unit, emoji]
            ['طماطم',           'vegetables', 'كيلو',   '🍅'],
            ['بطاطس',           'vegetables', 'كيلو',   '🥔'],
            ['بصل',             'vegetables', 'كيلو',   '🧅'],
            ['خيار',            'vegetables', 'كيلو',   '🥒'],
            ['فلفل أخضر',       'vegetables', 'كيلو',   '🌶️'],
            ['كوسة',            'vegetables', 'كيلو',   '🥒'],
            ['باذنجان',         'vegetables', 'كيلو',   '🍆'],

            ['تفاح',            'fruits',     'كيلو',   '🍎'],
            ['موز',             'fruits',     'كيلو',   '🍌'],
            ['برتقال',          'fruits',     'كيلو',   '🍊'],
            ['عنب',             'fruits',     'كيلو',   '🍇'],
            ['مانجو',           'fruits',     'كيلو',   '🥭'],

            ['فراخ',            'meat',       'كيلو',   '🐔'],
            ['لحمة بقري',       'meat',       'كيلو',   '🥩'],
            ['سمك بلطي',        'meat',       'كيلو',   '🐟'],
            ['بيض',             'meat',       'طبق',    '🥚'],

            ['لبن',             'dairy',      'لتر',    '🥛'],
            ['جبنة بيضا',       'dairy',      'كيلو',   '🧀'],
            ['جبنة قريش',       'dairy',      'كيلو',   '🧀'],
            ['زبادي',           'dairy',      'علبة',   '🥛'],

            ['زيت',             'pantry',     'لتر',    '🫒'],
            ['أرز',             'pantry',     'كيلو',   '🍚'],
            ['مكرونة',          'pantry',     'علبة',   '🍝'],
            ['سكر',             'pantry',     'كيلو',   '🍬'],
            ['شاي',             'pantry',     '500ج',   '🍵'],

            ['خبز سياحي',       'bread',      'رغيف',   '🍞'],
            ['عيش بلدي',        'bread',      'رغيف',   '🥖'],

            ['بنزين 80',        'fuel',       'لتر',    '⛽'],
            ['بنزين 92',        'fuel',       'لتر',    '⛽'],
            ['بنزين 95',        'fuel',       'لتر',    '⛽'],
            ['سولار',           'fuel',       'لتر',    '⛽'],
            ['أنبوبة بوتاجاز',  'fuel',       'أنبوبة', '🔥'],
        ];

        foreach ($products as $i => [$name, $cat, $unit, $emoji]) {
            $slug = Str::slug($name, '-', null) ?: 'p-' . ($i + 1);
            // Arabic names won't slugify well — fall back to incremental
            if ($slug === '') {
                $slug = 'p-' . ($i + 1);
            }
            // Ensure uniqueness
            $slug = $slug . '-' . ($i + 1);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'      => $name,
                    'category'  => $cat,
                    'unit'      => $unit,
                    'emoji'     => $emoji,
                    'is_active' => true,
                    'sort'      => $i,
                ]
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        // [name, slug, governorate, lat, lng, sort]
        $zones = [
            ['بنها',           'banha',           'القليوبية', 30.4595, 31.1840, 1],
            ['طوخ',            'toukh',           'القليوبية', 30.3553, 31.2026, 2],
            ['القليوب',        'qalyub',          'القليوبية', 30.1796, 31.2123, 3],
            ['شبرا الخيمة',    'shubra-elkheima', 'القليوبية', 30.1286, 31.2444, 4],
            ['قها',            'qaha',            'القليوبية', 30.2873, 31.2050, 5],
            ['كفر شكر',        'kafr-shoukr',     'القليوبية', 30.5566, 31.2569, 6],
            ['الخانكة',        'el-khanka',       'القليوبية', 30.2123, 31.3635, 7],
            ['العبور',         'el-obour',        'القليوبية', 30.2218, 31.4638, 8],
            ['قليوبية أخرى',  'qalyubia-other',  'القليوبية', 30.3000, 31.2000, 9],
        ];

        foreach ($zones as [$name, $slug, $gov, $lat, $lng, $sort]) {
            Zone::updateOrCreate(
                ['slug' => $slug],
                [
                    'name'        => $name,
                    'governorate' => $gov,
                    'lat'         => $lat,
                    'lng'         => $lng,
                    'sort'        => $sort,
                    'is_active'   => true,
                ]
            );
        }
    }
}

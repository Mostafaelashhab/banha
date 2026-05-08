<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ZoneSeeder::class,
            ProductSeeder::class,
            BadgeSeeder::class,
            BusinessSeeder::class,
        ]);

        if (app()->environment(['local', 'staging']) || env('SEED_DEMO', false)) {
            $this->call(DemoSeeder::class);
        }
    }
}

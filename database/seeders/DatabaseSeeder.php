<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Always-needed reference data (zones, products, badges)
        $this->call([
            ZoneSeeder::class,
            ProductSeeder::class,
            BadgeSeeder::class,
        ]);

        // Demo content — gated behind SEED_DEMO env flag (off by default)
        if (env('SEED_DEMO', false)) {
            $this->call([
                BusinessSeeder::class,
                DemoSeeder::class,
            ]);
        }
    }
}

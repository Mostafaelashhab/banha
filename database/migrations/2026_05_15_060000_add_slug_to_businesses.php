<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            // Brand-friendly URL slug: /biz/{slug}. This is the primary
            // SEO surface (matches city-app.org's strategy of /armada-style
            // URLs that rank for brand-name queries like "ارمادا بنها").
            $t->string('slug', 120)->nullable()->after('name');
            $t->unique('slug');
        });

        // Backfill: generate a slug for every existing active business.
        // We loop in PHP because PDO doesn't have a built-in transliterator,
        // and we need to dedupe collisions (two businesses called "كافيه نوار"
        // become `kafyh-nwar` and `kafyh-nwar-2`).
        $seen = [];
        DB::table('businesses')
            ->whereNull('slug')
            ->orderBy('id')
            ->select(['id', 'name', 'sub_type'])
            ->cursor()
            ->each(function ($row) use (&$seen) {
                $base = Str::slug($row->name, '-', 'en');
                if ($base === '') {
                    $base = 'biz-' . $row->id;
                }
                $slug = $base;
                $n = 2;
                while (isset($seen[$slug]) || DB::table('businesses')->where('slug', $slug)->exists()) {
                    $slug = $base . '-' . $n++;
                }
                $seen[$slug] = true;
                DB::table('businesses')->where('id', $row->id)->update(['slug' => $slug]);
            });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $t) {
            $t->dropUnique(['slug']);
            $t->dropColumn('slug');
        });
    }
};

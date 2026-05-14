<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The cityapp import used 01222725481 as a placeholder contact across 104
 * businesses. Swap it for the new central support line. Idempotent — re-running
 * does nothing because the WHERE matches zero rows after the first run.
 */
return new class extends Migration {
    private const OLD = '01222725481';
    private const NEW = '01550047838';

    public function up(): void
    {
        $affected = DB::table('businesses')
            ->where('phone', self::OLD)
            ->update(['phone' => self::NEW]);

        // Also swap any whatsapp / hotline copies of the same number if they
        // ever drift in. Cheap because the columns are indexed.
        $affected += DB::table('businesses')
            ->where('whatsapp', self::OLD)
            ->update(['whatsapp' => self::NEW]);

        $affected += DB::table('businesses')
            ->where('hotline', self::OLD)
            ->update(['hotline' => self::NEW]);

        if (function_exists('logger')) {
            logger()->info('[migration] swapped cityapp placeholder phone', [
                'old' => self::OLD, 'new' => self::NEW, 'rows' => $affected,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('businesses')->where('phone',    self::NEW)->update(['phone'    => self::OLD]);
        DB::table('businesses')->where('whatsapp', self::NEW)->update(['whatsapp' => self::OLD]);
        DB::table('businesses')->where('hotline',  self::NEW)->update(['hotline'  => self::OLD]);
    }
};

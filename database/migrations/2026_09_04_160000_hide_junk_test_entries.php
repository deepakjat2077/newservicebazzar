<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hides a few obvious test/placeholder rows (a listing "Testinh", subcategories
 * "9" and "j") by setting status = 0 rather than deleting them, so it's fully
 * reversible. Real short names like "CA" (Chartered Accountant) are left alone.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('listings')->whereIn('id', [11085])->update(['status' => 0]);
        DB::table('subcategories')->whereIn('id', [1999, 2611])->update(['status' => 0]);
    }

    public function down(): void
    {
        DB::table('listings')->whereIn('id', [11085])->update(['status' => 1]);
        DB::table('subcategories')->whereIn('id', [1999, 2611])->update(['status' => 1]);
    }
};

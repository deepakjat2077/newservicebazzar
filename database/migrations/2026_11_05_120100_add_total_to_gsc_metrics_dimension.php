<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Allow a per-day site "total" row in gsc_metrics (pulled with no dimension),
 * so the headline Clicks/Impressions/CTR match Search Console exactly instead
 * of only summing the stored top queries.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE gsc_metrics MODIFY dimension ENUM('query','page','total') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE gsc_metrics MODIFY dimension ENUM('query','page') NOT NULL");
    }
};

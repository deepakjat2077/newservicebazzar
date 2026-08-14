<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * payment_success conversions fire from server-to-server IPN webhooks
 * (the payment gateway calling us, not the customer's browser), so there
 * is no visitor_id cookie to read on that request — visitor_id must be
 * nullable to record those conversions at all (resolved instead via the
 * paying user's most recent known Visitor row, on a best-effort basis).
 *
 * Uses raw SQL rather than Schema::table(...)->change() since
 * doctrine/dbal (required for fluent column modification) isn't
 * installed in this project.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('analytics_events') || !Schema::hasColumn('analytics_events', 'visitor_id')) {
            return;
        }

        DB::statement('ALTER TABLE `analytics_events` MODIFY `visitor_id` CHAR(36) NULL');
    }

    public function down(): void
    {
        if (!Schema::hasTable('analytics_events') || !Schema::hasColumn('analytics_events', 'visitor_id')) {
            return;
        }

        DB::statement('ALTER TABLE `analytics_events` MODIFY `visitor_id` CHAR(36) NOT NULL');
    }
};

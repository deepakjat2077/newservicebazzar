<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Serviceable Area / Pincodes" — the real (only) part of this that's
 * buildable without a suburb/pincode-boundary system: whether a quote
 * request from a *different* city than this listing's own should ever be
 * allowed to reach it. See SiteController::requestQuoteStore(), which
 * checks this when a city's own listings can't fill the 5-business fan-out.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'receive_leads_other_cities')) {
                $table->boolean('receive_leads_other_cities')->default(true)->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'receive_leads_other_cities')) {
                $table->dropColumn('receive_leads_other_cities');
            }
        });
    }
};

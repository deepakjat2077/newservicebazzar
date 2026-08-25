<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * listings.website already existed (one URL, updated via updatesection()).
 * "+ Add Another Website" needs somewhere to put the rest — same array
 * pattern as extra_emails, not a new related table for what's just a list
 * of strings.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'extra_websites')) {
                $table->json('extra_websites')->nullable()->after('website');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'extra_websites')) {
                $table->dropColumn('extra_websites');
            }
        });
    }
};

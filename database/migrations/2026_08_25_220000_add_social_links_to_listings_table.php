<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One JSON column, not five — {facebook:[...], x:[...], linkedin:[...],
 * youtube:[...], instagram:[...]}, each a plain array of URLs so "+ Add
 * Another ... Link" per platform is just appending to that platform's array,
 * same shape as extra_emails/extra_websites elsewhere on this model.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'social_links')) {
                $table->json('social_links')->nullable()->after('extra_websites');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'social_links')) {
                $table->dropColumn('social_links');
            }
        });
    }
};

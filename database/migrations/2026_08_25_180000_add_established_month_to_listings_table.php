<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Year of Establishment" already existed as a plain year; this adds the
 * month half so the page can genuinely show "Month" + "Year" the way it's
 * asked for, rather than a Month dropdown that doesn't save anywhere.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'established_month')) {
                $table->unsignedTinyInteger('established_month')->nullable()->after('established_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'established_month')) {
                $table->dropColumn('established_month');
            }
        });
    }
};

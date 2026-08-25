<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Real "Response Time" needs a real timestamp to measure from — is_read
 * alone only says whether it happened, not when, so an average response
 * time couldn't be computed from it. This is set the moment is_read first
 * flips to true (see ListingController::leadRead()), never backdated or
 * guessed for enquiries that predate this column.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiries', 'responded_at')) {
                $table->dateTime('responded_at')->nullable()->after('is_read');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (Schema::hasColumn('enquiries', 'responded_at')) {
                $table->dropColumn('responded_at');
            }
        });
    }
};

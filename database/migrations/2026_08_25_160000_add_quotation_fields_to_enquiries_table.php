<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A "Quotation Request" is a real, separate thing from a plain enquiry: one
 * buyer submission gets fanned out to several matching businesses at once
 * (see SiteController::requestQuoteStore()), and it carries what the buyer
 * actually wants quoted rather than nothing. `type` is how a row tells the
 * two apart; everything else in the enquiries table — read/star/hide/note/
 * reminder — already works the same for both, so nothing there needed to
 * change.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiries', 'type')) {
                $table->string('type', 20)->default('enquiry')->after('id');
            }
            if (!Schema::hasColumn('enquiries', 'message')) {
                $table->text('message')->nullable()->after('email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            foreach (['type', 'message'] as $column) {
                if (Schema::hasColumn('enquiries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

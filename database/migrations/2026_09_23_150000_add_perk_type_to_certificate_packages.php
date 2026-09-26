<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            // What the buyer actually gets: none | banner | certificate |
            // badge_verified | badge_trusted | top_position
            $table->string('perk_type', 30)->default('none')->after('needs_ad_details');
        });

        Schema::table('listing_sales_packages', function (Blueprint $table) {
            // Generated asset for this purchase (the certificate image).
            $table->string('asset')->nullable()->after('days');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_packages', fn (Blueprint $t) => $t->dropColumn('perk_type'));
        Schema::table('listing_sales_packages', fn (Blueprint $t) => $t->dropColumn('asset'));
    }
};

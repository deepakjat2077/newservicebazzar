<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_ad_banners', function (Blueprint $table) {
            // The category the ad is shown under — the advertiser's own.
            $table->unsignedBigInteger('category_id')->nullable()->index()->after('area');
        });
    }

    public function down(): void
    {
        Schema::table('listing_ad_banners', fn (Blueprint $t) => $t->dropColumn('category_id'));
    }
};

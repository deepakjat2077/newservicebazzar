<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_ad_banners', function (Blueprint $table) {
            // Same desktop/mobile pair the admin's category banners use.
            $table->string('image_mobile')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('listing_ad_banners', fn (Blueprint $t) => $t->dropColumn('image_mobile'));
    }
};

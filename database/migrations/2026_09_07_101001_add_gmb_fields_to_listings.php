<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('gmb_place_id')->nullable()->index();
            $table->json('gmb_photo_urls')->nullable();
            $table->timestamp('gmb_last_sync')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['gmb_place_id', 'gmb_photo_urls', 'gmb_last_sync']);
        });
    }
};

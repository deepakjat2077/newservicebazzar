<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('listing_daily_stats')) {
            return;
        }

        Schema::create('listing_daily_stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->index();
            $table->date('date')->index();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('unique_views')->default(0);
            $table->unsignedInteger('whatsapp_clicks')->default(0);
            $table->unsignedInteger('phone_clicks')->default(0);
            $table->unsignedInteger('website_clicks')->default(0);
            $table->unsignedInteger('direction_clicks')->default(0);
            $table->unsignedInteger('gallery_clicks')->default(0);
            $table->unsignedInteger('review_clicks')->default(0);
            $table->unsignedInteger('share_clicks')->default(0);
            $table->unsignedInteger('favourite_clicks')->default(0);
            $table->float('avg_time_seconds')->nullable();
            $table->timestamps();

            $table->unique(['listing_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_daily_stats');
    }
};

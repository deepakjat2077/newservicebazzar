<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_position_slots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_package_id')->index();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('listing_sales_package_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('city', 100);
            $table->string('pincode', 10)->nullable();
            $table->string('area', 150)->nullable();
            // "Top 1", "Top 2"… — one business per position per area per category.
            $table->string('tier', 60);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
            $table->index(['city', 'pincode', 'tier']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_position_slots');
    }
};

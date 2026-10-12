<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Businesses a sales rep has edited but did not create. They are listed under
 * New Business too, without taking the business over from whoever owns it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_listing_touches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_user_id');
            $table->unsignedBigInteger('listing_id');
            $table->timestamps();

            $table->unique(['sales_user_id', 'listing_id']);
            $table->index('listing_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_listing_touches');
    }
};

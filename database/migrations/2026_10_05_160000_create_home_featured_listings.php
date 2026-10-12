<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Listings the admin picks by hand for the home page "Featured Listings"
 * section — no payment involved, in the admin's own order.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_featured_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id')->unique();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_featured_listings');
    }
};

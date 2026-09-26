<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The "Get Listed / Grow Your Business" screen shows a few hand-picked example
 * listings. This is the admin's pick-list for them: each row points at a real
 * listing, with a sort order and an on/off flag, so the app can render exactly
 * the businesses an admin chose (and in the order they chose) instead of a
 * hardcoded sample.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showcase_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->unique('listing_id');
            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showcase_listings');
    }
};

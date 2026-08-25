<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A favorite is a specific business (e.g. "Raj Homeopathy Clinic, Jhotwara"),
 * not just a category label — the Favorites step's category grid is only the
 * entry point that filters the real, existing listings down to the ones
 * worth showing a "Add to Favorite" button for.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_favorite_listings')) {
            return;
        }

        Schema::create('user_favorite_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('listing_id')->constrained()->cascadeOnDelete();
            // Which grid card the user came in through — kept only so the
            // Favorites step can group "your favorites" by category later.
            $table->string('category_key', 40)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'listing_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorite_listings');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Favorites step is a fixed grid of everyday-need categories (Doctor,
 * Hospital, Grocery Store, ...) rather than the site's full listing category
 * tree — it is not "which of my business categories do I like", it is "what
 * kind of nearby help do I want quick access to". So this stores a plain key
 * from a fixed list (UserController::FAVORITE_CATEGORIES) instead of a
 * foreign key into categories.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_favorite_categories')) {
            return;
        }

        Schema::create('user_favorite_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category_key', 40);
            $table->timestamps();

            $table->unique(['user_id', 'category_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_favorite_categories');
    }
};

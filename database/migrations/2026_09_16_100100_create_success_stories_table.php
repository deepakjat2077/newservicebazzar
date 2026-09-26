<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client "success stories" / testimonials shown on the Get Listed screen —
 * admin-managed content: who the client is, their business, an optional photo,
 * a star rating and the story itself, ordered and toggleable like banners.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('success_stories', function (Blueprint $table) {
            $table->id();
            $table->string('client_name');
            $table->string('business_name')->nullable();
            $table->string('image')->nullable();
            $table->decimal('rating', 2, 1)->nullable();
            $table->text('story');
            $table->integer('sort_order')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->index(['status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('success_stories');
    }
};

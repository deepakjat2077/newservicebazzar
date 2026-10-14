<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Pixel size of every listing image (main photo + gallery), read once and kept,
// so the admin "small images" Excel does not have to open ~44k files per click.
// Upload names are unique and never reused, so a row stays valid for its file.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listing_image_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('file')->unique();              // path under public/
            $table->unsignedInteger('width')->nullable();  // null = file missing or unreadable
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listing_image_sizes');
    }
};

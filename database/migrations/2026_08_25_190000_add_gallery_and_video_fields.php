<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extra_images', function (Blueprint $table) {
            if (!Schema::hasColumn('extra_images', 'is_cover')) {
                // Its own flag rather than reusing listings.thumb: thumb is
                // read across the rest of the site (search cards, etc.) and
                // lives in a different upload folder (listing/thumb vs
                // listing/extra) — conflating the two would mean either
                // duplicating the file or quietly changing what "thumb"
                // means everywhere else. This only affects the gallery view.
                $table->boolean('is_cover')->default(false)->after('image');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (!Schema::hasColumn('listings', 'youtube_video_url')) {
                $table->string('youtube_video_url')->nullable()->after('thumb');
            }
        });
    }

    public function down(): void
    {
        Schema::table('extra_images', function (Blueprint $table) {
            if (Schema::hasColumn('extra_images', 'is_cover')) {
                $table->dropColumn('is_cover');
            }
        });

        Schema::table('listings', function (Blueprint $table) {
            if (Schema::hasColumn('listings', 'youtube_video_url')) {
                $table->dropColumn('youtube_video_url');
            }
        });
    }
};

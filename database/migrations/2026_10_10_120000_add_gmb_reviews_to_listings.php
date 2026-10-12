<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// gmb_place_id already exists (2026_09_07_101001), so only the review columns are added here.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('gmb_rating', 3, 2)->nullable()->after('gmb_last_sync');
            $table->unsignedInteger('gmb_review_count')->default(0)->after('gmb_rating');
            $table->json('gmb_reviews_json')->nullable()->after('gmb_review_count');
            $table->timestamp('gmb_reviews_synced_at')->nullable()->after('gmb_reviews_json');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['gmb_rating', 'gmb_review_count', 'gmb_reviews_json', 'gmb_reviews_synced_at']);
        });
    }
};

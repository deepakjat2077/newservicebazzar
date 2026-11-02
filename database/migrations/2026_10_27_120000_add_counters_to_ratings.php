<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Rating's like/comment counters. The app API (likeReview, commentReview,
// review lists) and the Rating model already used these columns, but they were
// never created, so every like or comment from the app answered 500 after the
// like/comment row itself had been saved. Filled from the existing rows.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            if (!Schema::hasColumn('ratings', 'likes_count')) {
                $table->unsignedInteger('likes_count')->default(0)->after('is_verified');
            }
            if (!Schema::hasColumn('ratings', 'comments_count')) {
                $table->unsignedInteger('comments_count')->default(0)->after('likes_count');
            }
        });

        DB::statement('UPDATE ratings r SET
            likes_count = (SELECT COUNT(*) FROM rating_likes l WHERE l.rating_id = r.id),
            comments_count = (SELECT COUNT(*) FROM rating_comments c WHERE c.rating_id = r.id)');
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropColumn(['likes_count', 'comments_count']);
        });
    }
};

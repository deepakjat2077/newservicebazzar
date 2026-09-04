<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Removes 13 orphaned ratings whose listing or user no longer exists — ghost
 * reviews that can never be displayed correctly (no listing page to show them
 * on, or no author). Also clears any likes/comments hanging off those ratings.
 * One-time data cleanup; safe to run once.
 */
return new class extends Migration
{
    public function up(): void
    {
        $ids = DB::table('ratings as r')
            ->leftJoin('listings as l', 'l.id', '=', 'r.listing_id')
            ->leftJoin('users as u', 'u.id', '=', 'r.user_id')
            ->where(function ($q) {
                $q->whereNull('l.id')->orWhereNull('u.id');
            })
            ->pluck('r.id')->toArray();

        if (empty($ids)) {
            return;
        }

        DB::transaction(function () use ($ids) {
            DB::table('rating_likes')->whereIn('rating_id', $ids)->delete();
            DB::table('rating_comments')->whereIn('rating_id', $ids)->delete();
            DB::table('ratings')->whereIn('id', $ids)->delete();
        });
    }

    public function down(): void
    {
        // Deleted orphans cannot be restored — their parents are already gone.
    }
};

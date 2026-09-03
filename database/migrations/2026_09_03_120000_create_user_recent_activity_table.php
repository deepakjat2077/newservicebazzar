<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The profile page's "Recent Search" rail had nothing behind it: the header's
 * recent searches live in the visitor's localStorage and never reach the
 * server, so the page fell back to showing the four newest listings — which
 * made every account display the same businesses as if they had all searched
 * for the same things.
 *
 * This is where a signed-in person's own activity is recorded: the businesses
 * they opened, and the terms they searched for.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_recent_activity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            // 'listing' — opened a business page. 'search' — typed a query.
            $table->string('type', 10);

            $table->unsignedBigInteger('listing_id')->nullable();
            $table->string('term', 150)->nullable();

            // MySQL treats NULLs in a unique index as distinct, so listing_id
            // and term cannot dedupe on their own. Hashing whatever identifies
            // the row gives one stable column to be unique on, which lets a
            // repeat visit bump the timestamp instead of adding a duplicate.
            $table->char('key_hash', 40);

            $table->timestamps();

            $table->unique(['user_id', 'key_hash']);
            $table->index(['user_id', 'type', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_recent_activity');
    }
};

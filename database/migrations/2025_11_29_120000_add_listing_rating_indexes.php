<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('listings', 'listings_status_index', function (Blueprint $table) {
            $table->index('status', 'listings_status_index');
        });

        $this->addIndexIfMissing('listings', 'listings_category_id_index', function (Blueprint $table) {
            $table->index('category_id', 'listings_category_id_index');
        });

        $this->addIndexIfMissing('listings', 'listings_location_id_index', function (Blueprint $table) {
            $table->index('location_id', 'listings_location_id_index');
        });

        $this->addIndexIfMissing('listings', 'listings_user_id_index', function (Blueprint $table) {
            $table->index('user_id', 'listings_user_id_index');
        });

        $this->addIndexIfMissing('listings', 'listings_slug_index', function (Blueprint $table) {
            $table->index('slug', 'listings_slug_index');
        });

        $this->addIndexIfMissing('ratings', 'ratings_listing_id_index', function (Blueprint $table) {
            $table->index('listing_id', 'ratings_listing_id_index');
        });

        $this->addIndexIfMissing('ratings', 'ratings_user_id_index', function (Blueprint $table) {
            $table->index('user_id', 'ratings_user_id_index');
        });

        $this->addIndexIfMissing('ratings', 'ratings_listing_user_index', function (Blueprint $table) {
            $table->index(['listing_id', 'user_id'], 'ratings_listing_user_index');
        });
    }

    public function down(): void
    {
        $this->dropIndexIfExists('listings', 'listings_status_index');
        $this->dropIndexIfExists('listings', 'listings_category_id_index');
        $this->dropIndexIfExists('listings', 'listings_location_id_index');
        $this->dropIndexIfExists('listings', 'listings_user_id_index');
        $this->dropIndexIfExists('listings', 'listings_slug_index');

        $this->dropIndexIfExists('ratings', 'ratings_listing_id_index');
        $this->dropIndexIfExists('ratings', 'ratings_user_id_index');
        $this->dropIndexIfExists('ratings', 'ratings_listing_user_index');
    }

    private function addIndexIfMissing(string $table, string $indexName, callable $callback): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }
        Schema::table($table, $callback);
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->indexExists($table, $indexName)) {
            return;
        }
        Schema::table($table, function (Blueprint $table) use ($indexName) {
            $table->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return !empty($result);
    }
};

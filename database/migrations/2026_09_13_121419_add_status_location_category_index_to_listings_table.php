<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * listings had an index on status alone and one on category_id alone,
     * but every city/category listing page filters on status + location_id
     * (+ category_id) together. With no composite index, MySQL used the
     * status-only index, pulled every approved listing (~4,300 rows), and
     * filtered location_id in memory row by row.
     *
     * That was survivable at low concurrency. During a scraper swarm hitting
     * ?filter=/?rating= variants across every city x category combination
     * (2026-09-13), dozens of these scans ran at once, each taking 1-20s,
     * which piled up 90 simultaneous MySQL threads and exhausted every
     * PHP-FPM worker on both app servers -> site-wide 502/504s.
     *
     * This index took the same count query from a ~4,300-row scan to an
     * index range read returning exactly the matching rows (899 in the
     * case measured live) in 13ms.
     */
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->index(['status', 'location_id', 'category_id'], 'listings_status_location_category_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropIndex('listings_status_location_category_index');
        });
    }
};

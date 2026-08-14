<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes the analytics dashboard's date filter actually reach an index.
 *
 * Every query in AnalyticsController::buildStats() bounds itself by a date,
 * but neither table had an index those bounds could use:
 *
 *   - analytics_events has (event_category, event_type, created_at), and the
 *     dashboard's queries filter on event_type without event_category. The
 *     leftmost column is missing, so the composite is unusable — EXPLAIN
 *     falls back to the plain event_type index and reads every listing_view
 *     or search_performed row ever recorded before applying the date.
 *
 *   - page_views has (session_uuid, opened_at), and "top pages" groups by
 *     page_name across a date range with no session_uuid. Same problem, on
 *     the fastest-growing table in the schema.
 *
 * The practical effect was that the dashboard's cost tracked the table's
 * total size rather than the range the admin picked, so "today" and "last 30
 * days" did roughly the same work and both got slower every week.
 *
 * Both new indexes lead with the filtered column and carry the grouped one,
 * so the range narrows first and the GROUP BY runs over far fewer rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_events') && !$this->hasIndex('analytics_events', 'ae_type_created')) {
            Schema::table('analytics_events', function ($table) {
                // Equality on event_type, range on created_at — the shape
                // topCategories/topLocations/topKeywords/funnel all use.
                $table->index(['event_type', 'created_at'], 'ae_type_created');
            });
        }

        if (Schema::hasTable('page_views') && !$this->hasIndex('page_views', 'pv_opened_page')) {
            Schema::table('page_views', function ($table) {
                // page_name rides along so topPages never leaves the index.
                $table->index(['opened_at', 'page_name'], 'pv_opened_page');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('analytics_events') && $this->hasIndex('analytics_events', 'ae_type_created')) {
            Schema::table('analytics_events', function ($table) {
                $table->dropIndex('ae_type_created');
            });
        }

        if (Schema::hasTable('page_views') && $this->hasIndex('page_views', 'pv_opened_page')) {
            Schema::table('page_views', function ($table) {
                $table->dropIndex('pv_opened_page');
            });
        }
    }

    /**
     * Guarded so a re-run on a database that already has them is a no-op
     * rather than a failed deploy — the same defensive shape the tracking
     * tables' own migrations use with Schema::hasTable().
     */
    private function hasIndex(string $table, string $index): bool
    {
        return !empty(DB::select(
            'SHOW INDEX FROM `' . $table . '` WHERE Key_name = ?',
            [$index]
        ));
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analytics_events')) {
            return;
        }

        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->index();
            $table->uuid('session_uuid')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();

            // Plain strings rather than enums so new event types never
            // require a schema change — this table is the extensibility
            // backbone for the whole tracking system.
            $table->string('event_category')->index(); // click | search | conversion | view
            $table->string('event_type')->index();

            $table->unsignedBigInteger('listing_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable()->index();

            // First-class (indexed) rather than buried in `meta`, since
            // "top keywords" / "no-result searches" are core, frequent
            // GROUP BY / WHERE targets for the dashboard.
            $table->string('keyword')->nullable()->index();
            $table->unsignedInteger('results_count')->nullable();

            $table->text('url')->nullable();
            $table->json('meta')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['event_category', 'event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_events');
    }
};

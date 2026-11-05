<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily Google Search Console performance, pulled by `seo:gsc-pull`.
 * One row per (date, dimension, value) — dimension is 'query' or 'page'.
 * Clicks/impressions summed and position averaged (weighted by impressions)
 * over any window the admin Search Console page reports on.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gsc_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->enum('dimension', ['query', 'page']);
            $table->string('value', 500);
            $table->char('value_hash', 40);          // sha1(value) — keeps the unique key short
            $table->unsignedInteger('clicks')->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->decimal('position', 6, 2)->default(0);

            $table->unique(['date', 'dimension', 'value_hash'], 'gsc_metrics_day_dim_val');
            $table->index(['dimension', 'date'], 'gsc_metrics_dim_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gsc_metrics');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// What Google's URL Inspection API last said about a sent URL
// (seo:google-index-check): indexed or not, and when Google last crawled it.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('google_index_submissions', function (Blueprint $table) {
            $table->timestamp('inspected_at')->nullable()->after('error');
            $table->string('index_verdict', 20)->nullable()->after('inspected_at');   // PASS | NEUTRAL | FAIL | ...
            $table->string('coverage_state', 150)->nullable()->after('index_verdict'); // e.g. "Submitted and indexed"
            $table->timestamp('last_crawl_at')->nullable()->after('coverage_state');

            $table->index('inspected_at');
            $table->index('index_verdict');
        });
    }

    public function down(): void
    {
        Schema::table('google_index_submissions', function (Blueprint $table) {
            $table->dropIndex(['inspected_at']);
            $table->dropIndex(['index_verdict']);
            $table->dropColumn(['inspected_at', 'index_verdict', 'coverage_state', 'last_crawl_at']);
        });
    }
};

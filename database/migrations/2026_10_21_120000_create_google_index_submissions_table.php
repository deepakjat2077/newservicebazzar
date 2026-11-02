<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One row per URL sent to the Google Indexing API (seo:google-index): when it
// was last sent, for which version of the page, and what Google answered.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_index_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('url', 500)->unique();
            $table->string('source', 20);                    // listing | blog
            $table->unsignedBigInteger('source_id')->nullable();
            $table->timestamp('content_updated_at')->nullable(); // page version that was sent
            $table->timestamp('notified_at')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('error', 500)->nullable();
            $table->timestamps();

            $table->index('notified_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_index_submissions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// "People also search for" Q&As on a city x category page (seo:pasf-faq):
// the related searches found for "<category> in <city>", and a short answer
// to each written from that city's own listings. Shown only once an admin
// approves it.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_page_faqs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('location_id');
            $table->json('keywords')->nullable();        // related searches, as found
            $table->json('faqs')->nullable();            // [{keyword, question, answer}]
            $table->json('links')->nullable();           // listing name => URL, for linking names in answers
            $table->string('keyword_source', 20)->nullable(); // serpapi | autocomplete
            $table->string('status', 10)->default('draft');   // draft | approved | rejected
            $table->string('error', 500)->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();

            $table->unique(['category_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_page_faqs');
    }
};

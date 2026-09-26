<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_links', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('campaign', 100);
            $table->string('source', 50);
            $table->string('medium', 50);
            $table->string('landing_url', 1000);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->unique(['campaign', 'source', 'medium']);
            $table->index('campaign');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_links');
    }
};

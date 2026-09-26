<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('freelisting_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('freelisting_id')->index();
            $table->string('path');
            $table->string('type', 10)->default('photo'); // photo | video
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('freelisting_photos');
    }
};

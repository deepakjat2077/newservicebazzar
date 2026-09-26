<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            // One benefit per line — rendered as the green-tick "Benefits You get" list.
            $table->text('benefits')->nullable();
            // [{"years":1,"price":6000}, {"years":2,"price":9000}, ...]
            $table->json('plans')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificate_packages');
    }
};

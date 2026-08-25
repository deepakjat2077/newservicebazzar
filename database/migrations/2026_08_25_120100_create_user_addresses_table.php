<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_addresses')) {
            return;
        }

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('landmark')->nullable();
            $table->string('pincode', 10)->nullable();
            $table->string('city')->nullable();
            $table->string('landline_std', 10)->nullable();
            $table->string('landline_number', 20)->nullable();
            // Home / Office / Other — matches the "Tag" dropdown on the
            // Addresses step.
            $table->string('tag', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};

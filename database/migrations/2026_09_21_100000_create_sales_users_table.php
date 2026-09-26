<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('empcode')->unique();
            $table->string('email')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('city')->default('Jaipur');
            $table->string('designation')->nullable();
            $table->string('password');
            $table->tinyInteger('status')->default(1);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_users');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_position_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('certificate_package_id')->index();
            $table->string('city', 100);
            // Blank area = the price for the whole city.
            $table->string('area', 150)->default('');
            $table->unsignedTinyInteger('position');
            $table->decimal('price', 12, 2);
            $table->timestamps();
            $table->unique(['certificate_package_id', 'city', 'area', 'position'], 'pos_price_unique');
        });

        Schema::table('certificate_packages', function (Blueprint $table) {
            // 'plan' = one price list; 'position' = price per city/area per position.
            $table->string('pricing_type', 20)->default('plan')->after('plans');
            $table->unsignedTinyInteger('positions')->default(3)->after('pricing_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_position_prices');
        Schema::table('certificate_packages', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'positions']);
        });
    }
};

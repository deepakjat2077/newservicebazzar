<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('token', 40)->unique();
            $table->unsignedBigInteger('listing_id')->index();
            $table->unsignedBigInteger('sales_user_id')->nullable()->index();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('gst', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            // 0 = awaiting payment, 1 = paid, 2 = cancelled
            $table->unsignedTinyInteger('status')->default(0)->index();
            $table->string('gateway', 30)->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_order_id')->index();
            $table->unsignedBigInteger('certificate_package_id')->index();
            $table->string('name');
            $table->string('plan', 190)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->unsignedSmallInteger('days')->default(365);
            $table->boolean('is_free')->default(false);
            // city/area/positions for a top-position campaign
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};

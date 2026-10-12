<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Discounts the admin sets up and the sales team picks from in the cart. The order keeps its own
 * copy of the name and amount, so editing or deleting a discount never changes a past sale.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('type', 10);                       // percent | flat
            $table->decimal('value', 10, 2);
            $table->text('package_ids')->nullable();          // JSON list; empty = every package
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('sales_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('discount_id')->nullable()->after('gst');
            $table->string('discount_name', 120)->nullable()->after('discount_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_name');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['discount_id', 'discount_name', 'discount_amount']);
        });

        Schema::dropIfExists('sales_discounts');
    }
};

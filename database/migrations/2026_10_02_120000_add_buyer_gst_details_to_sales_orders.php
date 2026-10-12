<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The GSTN & PAN screen the rep fills in before a payment link is made.
 * buyer_gstin already existed and is what the tax invoice prints.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->string('buyer_pan', 20)->nullable()->after('buyer_gstin');
            $table->string('buyer_gst_address', 255)->nullable()->after('buyer_pan');
            $table->string('buyer_gst_pincode', 10)->nullable()->after('buyer_gst_address');
            $table->string('buyer_gst_state', 100)->nullable()->after('buyer_gst_pincode');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['buyer_pan', 'buyer_gst_address', 'buyer_gst_pincode', 'buyer_gst_state']);
        });
    }
};

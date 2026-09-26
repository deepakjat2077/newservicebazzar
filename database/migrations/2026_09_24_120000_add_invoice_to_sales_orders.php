<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            // Allocated once, when the payment is credited — an invoice number
            // must never change after it has gone out.
            $table->string('invoice_no', 32)->nullable()->unique()->after('paid_at');
            $table->timestamp('invoice_sent_at')->nullable()->after('invoice_no');
            $table->string('invoice_email', 191)->nullable()->after('invoice_sent_at');
            // What the buyer's GSTIN was on the day — a later change to their
            // KYC must not rewrite an invoice already issued.
            $table->string('buyer_gstin', 20)->nullable()->after('invoice_email');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['invoice_no', 'invoice_sent_at', 'invoice_email', 'buyer_gstin']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When the rep pressed "Deal Close". The deal then shows under Deal Closed
 * with its payment still unconfirmed until the money actually arrives.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->timestamp('closed_at')->nullable()->after('paid_at');
            $table->unsignedBigInteger('closed_by')->nullable()->after('closed_at');
        });
    }

    public function down(): void
    {
        Schema::table('sales_orders', function (Blueprint $table) {
            $table->dropColumn(['closed_at', 'closed_by']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Company details printed on every tax invoice, managed from
            // Setting > Invoice rather than a config file on disk.
            $table->json('invoice')->nullable()->after('gupshup');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('invoice');
        });
    }
};

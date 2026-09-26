<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            // One chip per line, e.g. "Official Business Verified Certificate".
            $table->text('highlights')->nullable()->after('benefits');
        });
    }

    public function down(): void
    {
        Schema::table('certificate_packages', function (Blueprint $table) {
            $table->dropColumn('highlights');
        });
    }
};

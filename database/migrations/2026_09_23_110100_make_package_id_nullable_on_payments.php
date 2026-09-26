<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // A sales-package payment has no legacy package_id.
        DB::statement('ALTER TABLE payments MODIFY package_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments MODIFY package_id BIGINT UNSIGNED NOT NULL');
    }
};

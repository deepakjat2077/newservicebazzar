<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Sales Genio entries have no web `user_id` — they carry sales_user_id
        // instead. Make user_id nullable so those rows can be created.
        DB::statement('ALTER TABLE freelistings MODIFY user_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE freelistings MODIFY user_id BIGINT UNSIGNED NOT NULL');
    }
};

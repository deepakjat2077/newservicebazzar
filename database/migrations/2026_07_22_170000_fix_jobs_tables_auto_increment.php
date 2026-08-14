<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `jobs`, `failed_jobs`, and `migrations` all exist in this environment's
 * database (installed outside this repo's migration history, likely via
 * the bundled installer's SQL import) but their `id` column was never
 * marked AUTO_INCREMENT — every queue dispatch fails with "Field 'id'
 * doesn't have a default value", and every migration run has needed a
 * manual bookkeeping-row workaround for the same reason on `migrations`
 * itself. `migrations.id` additionally has no PRIMARY KEY at all (MySQL
 * requires an indexed column for AUTO_INCREMENT), so that one needs a key
 * added first. Fixed here properly since the whole tracking system
 * depends on a working queue.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['jobs', 'failed_jobs', 'migrations'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $column = DB::selectOne("SHOW COLUMNS FROM `{$table}` WHERE Field = 'id'");

            if (!$column || stripos($column->Extra, 'auto_increment') !== false) {
                continue;
            }

            $hasPrimaryKey = !empty(DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"));

            $modify = "MODIFY `id` {$column->Type} NOT NULL AUTO_INCREMENT";
            $addKey = $hasPrimaryKey ? '' : ', ADD PRIMARY KEY (`id`)';

            DB::statement("ALTER TABLE `{$table}` {$modify}{$addKey}");
        }
    }

    public function down(): void
    {
        // Intentionally a no-op: reverting AUTO_INCREMENT would restore the
        // broken state this migration exists to fix.
    }
};

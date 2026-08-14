<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Same defect the jobs tables had (see the 2026_07_22 fix), but across the
 * rest of the schema: 25 tables carry a PRIMARY KEY `id` with no
 * AUTO_INCREMENT, so every plain insert dies with
 *
 *     SQLSTATE[HY000]: General error: 1364 Field 'id' doesn't have a default value
 *
 * It surfaces one table at a time — adding an agent, a banner, a category,
 * a location — whenever code inserts without naming an id. The tables were
 * installed outside this repo's migration history (bundled installer SQL
 * import), which is where the AUTO_INCREMENT got lost.
 *
 * Rather than list table names, this walks information_schema so any table
 * with the defect is repaired, and each counter is seeded past the highest
 * id already in use so existing rows are never at risk.
 */
return new class extends Migration
{
    public function up(): void
    {
        $database = DB::getDatabaseName();

        $broken = DB::select(
            "SELECT c.TABLE_NAME AS table_name, c.COLUMN_TYPE AS column_type
             FROM information_schema.COLUMNS c
             JOIN information_schema.TABLES t
               ON t.TABLE_SCHEMA = c.TABLE_SCHEMA AND t.TABLE_NAME = c.TABLE_NAME
             WHERE c.TABLE_SCHEMA = ?
               AND t.TABLE_TYPE = 'BASE TABLE'
               AND c.COLUMN_NAME = 'id'
               AND c.EXTRA NOT LIKE '%auto_increment%'",
            [$database]
        );

        foreach ($broken as $row) {
            $table = $row->table_name;

            // AUTO_INCREMENT requires the column to be indexed.
            $hasPrimaryKey = !empty(DB::select("SHOW KEYS FROM `{$table}` WHERE Key_name = 'PRIMARY'"));

            $addKey = $hasPrimaryKey ? '' : ', ADD PRIMARY KEY (`id`)';

            /*
             * Other tables' foreign keys point at these primary keys. The
             * column type is unchanged (only the AUTO_INCREMENT flag is
             * added), but MySQL can still refuse the ALTER while checks are
             * on, so they are suspended for the statement.
             */
            DB::statement('SET FOREIGN_KEY_CHECKS = 0');

            try {
                DB::statement(
                    "ALTER TABLE `{$table}` MODIFY `id` {$row->column_type} NOT NULL AUTO_INCREMENT{$addKey}"
                );

                // Start the counter after the highest id in use.
                $next = (int) DB::table($table)->max('id') + 1;

                DB::statement("ALTER TABLE `{$table}` AUTO_INCREMENT = {$next}");
            } finally {
                DB::statement('SET FOREIGN_KEY_CHECKS = 1');
            }
        }
    }

    public function down(): void
    {
        // Intentionally a no-op: reverting AUTO_INCREMENT would restore the
        // broken state this migration exists to fix.
    }
};

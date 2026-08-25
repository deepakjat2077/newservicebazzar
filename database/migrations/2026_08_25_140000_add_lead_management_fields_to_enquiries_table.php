<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The per-listing Leads screen (JustDial-style: Hidden / Starred / Notes /
 * Hot Enquiries tabs, "Add Note", "Add Reminder") needs somewhere real to
 * keep that state — this is that somewhere, on the enquiry row it's about.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            if (!Schema::hasColumn('enquiries', 'is_starred')) {
                $table->boolean('is_starred')->default(false)->after('is_read');
            }
            if (!Schema::hasColumn('enquiries', 'is_hidden')) {
                $table->boolean('is_hidden')->default(false)->after('is_starred');
            }
            if (!Schema::hasColumn('enquiries', 'note')) {
                $table->text('note')->nullable()->after('is_hidden');
            }
            if (!Schema::hasColumn('enquiries', 'reminder_at')) {
                $table->dateTime('reminder_at')->nullable()->after('note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            foreach (['is_starred', 'is_hidden', 'note', 'reminder_at'] as $column) {
                if (Schema::hasColumn('enquiries', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

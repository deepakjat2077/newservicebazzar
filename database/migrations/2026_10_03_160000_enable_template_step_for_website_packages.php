<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/** The other two packages that deliver a website get the same "choose a design" step. */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('certificate_packages')
            ->whereIn('name', ['Your Own Website', 'Website + Facebook + Instagram Combo'])
            ->update(['needs_template' => true]);
    }

    public function down(): void
    {
        DB::table('certificate_packages')
            ->whereIn('name', ['Your Own Website', 'Website + Facebook + Instagram Combo'])
            ->update(['needs_template' => false]);
    }
};

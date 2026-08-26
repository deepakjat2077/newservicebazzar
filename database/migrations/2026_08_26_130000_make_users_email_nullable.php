<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A WhatsApp OTP login has no email to record, and inventing one
 * ("<number>@whatsapp.servicebazzar.com") put a fake address in front of the
 * account holder on their own profile page — with a verified tick next to it,
 * for a mailbox that does not exist. The column holds NULL instead, and the
 * profile offers an "add your email" field.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 191)->nullable()->change();
        });

        // Clear the placeholders already handed out. The mobile_verified
        // migration that runs before this one has already read them, so the
        // accounts keep the identity their OTP proved.
        DB::table('users')
            ->where('email', 'like', '%@whatsapp.servicebazzar.com')
            ->update(['email' => null]);
    }

    public function down(): void
    {
        // Rows left without an address would break a NOT NULL column, so give
        // them back a placeholder before restoring the constraint.
        DB::table('users')
            ->whereNull('email')
            ->update(['email' => DB::raw("CONCAT(COALESCE(NULLIF(mobile, ''), id), '@whatsapp.servicebazzar.com')")]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('email', 191)->nullable(false)->change();
        });
    }
};

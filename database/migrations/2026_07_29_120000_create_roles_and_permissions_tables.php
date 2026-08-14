<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();

            /*
             * Locked roles are the ones the app itself depends on
             * (super_admin). They can be viewed but not renamed or deleted,
             * so nobody can lock themselves out of the panel.
             */
            $table->boolean('is_locked')->default(false);

            $table->timestamps();
        });

        Schema::create('role_permission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();

            // Permission keys live in config/permissions.php, not in a table —
            // they are code, not data, and must not drift from the routes.
            $table->string('permission');

            $table->timestamps();

            $table->unique(['role_id', 'permission']);
        });

        Schema::table('admins', function (Blueprint $table) {
            $table->foreignId('role_id')
                ->nullable()
                ->after('role')
                ->constrained('roles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('role_permission');
        Schema::dropIfExists('roles');
    }
};

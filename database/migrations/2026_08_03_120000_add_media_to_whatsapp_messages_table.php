<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Images, documents and audio sent by customers used to be flattened to
     * the text "[image]", so there was nothing to open. These columns keep
     * the provider URL, the content type, and our own downloaded copy —
     * Gupshup's media links expire, so a local copy is the only way the
     * picture is still there tomorrow.
     */
    public function up(): void
    {
        if (!Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('whatsapp_messages', 'media_url')) {
                $table->text('media_url')->nullable()->after('body');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'media_type')) {
                $table->string('media_type', 100)->nullable()->after('media_url');
            }

            if (!Schema::hasColumn('whatsapp_messages', 'media_path')) {
                $table->string('media_path', 255)->nullable()->after('media_type');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::table('whatsapp_messages', function (Blueprint $table) {
            foreach (['media_url', 'media_type', 'media_path'] as $column) {
                if (Schema::hasColumn('whatsapp_messages', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

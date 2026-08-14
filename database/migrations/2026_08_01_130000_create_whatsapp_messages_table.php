<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Single log for both directions of WhatsApp traffic.
     *
     * Outbound rows are written when the admin panel submits a template to
     * Gupshup; the webhook later finds them again by `message_id` and moves
     * `status` along (submitted -> sent -> delivered -> read, or failed).
     * Inbound rows are replies from users, which the panel shows and badges
     * as unread until an admin opens them.
     */
    public function up(): void
    {
        if (Schema::hasTable('whatsapp_messages')) {
            return;
        }

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            $table->enum('direction', ['outbound', 'inbound'])->default('outbound');

            $table->unsignedBigInteger('listing_id')->nullable();
            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->string('phone', 20);
            $table->string('contact_name', 191)->nullable();

            $table->string('template_id', 191)->nullable();
            $table->text('body')->nullable();

            // Gupshup's own id for the message, the join key the webhook uses.
            $table->string('message_id', 191)->nullable();

            $table->string('status', 30)->default('queued');
            $table->text('error')->nullable();

            $table->longText('payload')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index('direction');
            $table->index('listing_id');
            $table->index('enquiry_id');
            $table->index('message_id');
            $table->index('status');
            $table->index('phone');
            $table->index(['direction', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};

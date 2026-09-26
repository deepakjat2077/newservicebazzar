<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listing_dispositions', function (Blueprint $table) {
            // When the rep has to act on this — follow-ups, call backs and
            // appointments all carry a due date and time.
            $table->timestamp('due_at')->nullable()->after('call_back')->index();
        });
    }

    public function down(): void
    {
        Schema::table('listing_dispositions', fn (Blueprint $t) => $t->dropColumn('due_at'));
    }
};

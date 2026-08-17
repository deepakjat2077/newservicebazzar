<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One enquiry per person per thing enquired about.
 *
 * The same visitor was submitting the same enquiry over and over — the same
 * listing four times, or "Physiotherapists in Jaipur" four times — and every
 * one of them landed in the admin's unread list as a fresh lead.
 *
 * The rule is enforced here rather than only in the controller because two
 * submits a moment apart both read "nothing found" before either writes. A
 * unique index is the only thing that settles that race.
 *
 * It cannot be a plain composite index over (phone, listing_id, category_id,
 * location_id): three of those are nullable, and MySQL treats every NULL as
 * distinct — so the case in the report, an enquiry with no listing and no
 * category, would slip through the index every single time. Instead each row
 * carries a single string built from the same values, with the empties spelled
 * out, and that string is what has to be unique. Enquiry::dedupeKeyFor()
 * builds it and is the one place the rule lives.
 *
 * Existing rows: the oldest of each duplicate group keeps a key, the later
 * copies are left NULL. They stay visible as history — nothing is deleted —
 * and NULLs repeat freely under a unique index, so the backfill cannot fail on
 * data that is already duplicated.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('dedupe_key', 64)->nullable()->after('is_read');
            $table->unique('dedupe_key', 'enquiries_dedupe_key_unique');
        });

        $this->backfill();
    }

    /**
     * Stamp the first enquiry of each group, leaving its later copies NULL.
     *
     * Done in PHP through the model so the key is built by exactly the same
     * code that will build it for new submissions — a second, SQL-shaped
     * version of the phone normalising would drift from it.
     */
    protected function backfill(): void
    {
        $seen = [];

        DB::table('enquiries')
            ->select('id', 'phone', 'listing_id', 'category_id', 'location_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use (&$seen) {
                foreach ($rows as $row) {
                    $key = \App\Models\Enquiry::dedupeKeyFor(
                        $row->phone,
                        $row->listing_id,
                        $row->category_id,
                        $row->location_id
                    );

                    if ($key === null || isset($seen[$key])) {
                        continue;
                    }

                    $seen[$key] = true;

                    DB::table('enquiries')->where('id', $row->id)->update([
                        'dedupe_key' => $key,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->dropUnique('enquiries_dedupe_key_unique');
            $table->dropColumn('dedupe_key');
        });
    }
};

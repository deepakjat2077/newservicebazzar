<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A signed-in person's own trail: the businesses they opened and the terms
 * they searched for. Only what the profile page's "Recent Search" rail needs,
 * and only for signed-in people — guests are not recorded here at all.
 */
class UserRecentActivity extends Model
{
    protected $table = 'user_recent_activity';

    protected $fillable = ['user_id', 'type', 'listing_id', 'term', 'key_hash'];

    public const TYPE_LISTING = 'listing';
    public const TYPE_SEARCH  = 'search';

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Records one visit or one search, without ever letting a failure here
     * break the page the visitor actually asked for — this is a side note on
     * someone else's request, not the request itself.
     *
     * A repeat of the same thing updates the existing row's timestamp rather
     * than adding a duplicate, so the rail shows four distinct things.
     */
    public static function remember(string $type, ?int $listingId = null, ?string $term = null): void
    {
        try {
            $userId = auth()->id();

            if (!$userId) {
                return;
            }

            $term = $term === null ? null : mb_substr(trim($term), 0, 150);

            if ($type === self::TYPE_SEARCH && ($term === null || $term === '')) {
                return;
            }

            if ($type === self::TYPE_LISTING && !$listingId) {
                return;
            }

            static::updateOrCreate(
                [
                    'user_id'  => $userId,
                    'key_hash' => sha1($type.'|'.$listingId.'|'.mb_strtolower((string) $term)),
                ],
                [
                    'type'       => $type,
                    'listing_id' => $listingId,
                    'term'       => $term,
                ]
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}

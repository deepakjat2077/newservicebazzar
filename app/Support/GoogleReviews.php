<?php

namespace App\Support;

use App\Models\Listing;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Google Places API (New) client for the listing reviews block.
 *
 * Uses services.google.places_api_key (GOOGLE_PLACES_API_KEY), the same key
 * as App\Support\GooglePlaces. Writes go through saveQuietly() so a background
 * sync never fires Listing model events (slug regeneration, cache hooks).
 */
class GoogleReviews
{
    private const SEARCH_URL  = 'https://places.googleapis.com/v1/places:searchText';
    private const DETAILS_URL = 'https://places.googleapis.com/v1/places/';
    private const TIMEOUT     = 15;
    private const MAX_REVIEWS = 5;

    /** Set on PERMISSION_DENIED / RESOURCE_EXHAUSTED so commands stop early. */
    public static bool $stopRequested = false;

    public static function apiKey(): ?string
    {
        return config('services.google.places_api_key') ?: null;
    }

    /**
     * Returns the Google place id for a listing, or null when nothing close enough is found.
     *
     * A name match alone is not enough (tested: "Dr. Kavita Nagar" matched "Dr Kavita Sharma"
     * across town), so the result must also agree on location: area in the address, same
     * pincode, or within 2 km of our coordinates. With no location evidence the name must be
     * a near-exact match.
     */
    public static function findPlaceId(Listing $listing): ?string
    {
        $key = self::apiKey();
        $name = trim((string) $listing->name);
        if (!$key || $name === '') {
            return null;
        }

        $query = implode(' ', array_filter([$name, $listing->area, $listing->city]));

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders([
                'X-Goog-Api-Key'   => $key,
                'X-Goog-FieldMask' => 'places.id,places.displayName,places.formattedAddress,places.location',
            ])
            ->post(self::SEARCH_URL, [
                'textQuery'      => $query,
                'maxResultCount' => 1,
                'regionCode'     => 'IN',
                'languageCode'   => 'en',
            ]);

        if (!$response->successful()) {
            self::logFailure('GMB text search failed', $response->json() ?? []);
            return null;
        }

        $place = $response->json('places.0');
        if (empty($place['id'])) {
            return null;
        }

        $nameScore = self::similarity($name, $place['displayName']['text'] ?? '');

        if ($nameScore < 70) {
            return null;
        }

        if (self::locationAgrees($listing, $place)) {
            return $place['id'];
        }

        return $nameScore >= 90 && !self::hasLocationData($listing) ? $place['id'] : null;
    }

    private static function hasLocationData(Listing $listing): bool
    {
        return !empty($listing->area) || !empty($listing->pincode)
            || (!empty($listing->latitude) && !empty($listing->longitude));
    }

    private static function locationAgrees(Listing $listing, array $place): bool
    {
        $address = strtolower($place['formattedAddress'] ?? '');

        $area = strtolower(trim((string) $listing->area));
        if ($area !== '' && $address !== '' && str_contains($address, $area)) {
            return true;
        }

        $pin = preg_replace('/\D/', '', (string) $listing->pincode);
        if (strlen($pin) === 6 && str_contains($address, $pin)) {
            return true;
        }

        $lat = (float) $listing->latitude;
        $lng = (float) $listing->longitude;
        $pLat = $place['location']['latitude'] ?? null;
        $pLng = $place['location']['longitude'] ?? null;

        if ($lat && $lng && $pLat !== null && $pLng !== null) {
            return self::distanceKm($lat, $lng, (float) $pLat, (float) $pLng) <= 2.0;
        }

        return false;
    }

    private static function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * Fetches rating, total count and the top reviews for a place id.
     */
    public static function fetchSummary(string $placeId): ?array
    {
        $key = self::apiKey();
        if (!$key) {
            return null;
        }

        $response = Http::timeout(self::TIMEOUT)
            ->withHeaders([
                'X-Goog-Api-Key'   => $key,
                'X-Goog-FieldMask' => 'id,rating,userRatingCount,reviews',
            ])
            ->get(self::DETAILS_URL . rawurlencode($placeId), ['languageCode' => 'en']);

        if (!$response->successful()) {
            self::logFailure('GMB place details failed', $response->json() ?? []);
            return null;
        }

        $data = $response->json();

        $reviews = collect($data['reviews'] ?? [])
            ->take(self::MAX_REVIEWS)
            ->map(fn (array $r) => [
                'author'     => $r['authorAttribution']['displayName'] ?? 'Google user',
                'author_url' => $r['authorAttribution']['uri'] ?? null,
                'rating'     => (int) ($r['rating'] ?? 0),
                'text'       => $r['text']['text'] ?? ($r['originalText']['text'] ?? ''),
                'when'       => $r['relativePublishTimeDescription'] ?? null,
            ])
            ->filter(fn (array $r) => $r['text'] !== '')
            ->values()
            ->all();

        return [
            'rating'  => isset($data['rating']) ? round((float) $data['rating'], 2) : null,
            'count'   => (int) ($data['userRatingCount'] ?? 0),
            'reviews' => $reviews,
        ];
    }

    /**
     * Full refresh for one listing: find the place id if missing, then pull the summary.
     * Returns true when the listing's review data was updated.
     */
    public static function syncListing(Listing $listing): bool
    {
        if (empty($listing->gmb_place_id)) {
            $placeId = self::findPlaceId($listing);

            if (!$placeId) {
                $listing->forceFill(['gmb_reviews_synced_at' => now()])->saveQuietly();
                return false;
            }

            $listing->forceFill(['gmb_place_id' => $placeId])->saveQuietly();
        }

        $summary = self::fetchSummary($listing->gmb_place_id);
        if (!$summary) {
            return false;
        }

        $listing->forceFill([
            'gmb_rating'            => $summary['rating'],
            'gmb_review_count'      => $summary['count'],
            // Listing has no casts, so the JSON is encoded here and decoded in the partial.
            'gmb_reviews_json'      => json_encode($summary['reviews'], JSON_UNESCAPED_UNICODE),
            'gmb_reviews_synced_at' => now(),
        ])->saveQuietly();

        return true;
    }

    private static function similarity(string $a, string $b): float
    {
        $a = preg_replace('/[^a-z0-9]/', '', strtolower($a));
        $b = preg_replace('/[^a-z0-9]/', '', strtolower($b));

        if ($a === '' || $b === '') {
            return 0;
        }

        similar_text($a, $b, $percent);

        return $percent;
    }

    private static function logFailure(string $message, array $body): void
    {
        $status = $body['error']['status'] ?? null;

        if (in_array($status, ['PERMISSION_DENIED', 'RESOURCE_EXHAUSTED'], true)) {
            self::$stopRequested = true;
        }

        // The API key is never logged; only Google's status and message.
        Log::warning($message, [
            'status' => $status,
            'error'  => $body['error']['message'] ?? null,
        ]);
    }
}

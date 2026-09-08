<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class GooglePlaces
{
    private $apiKey;
    private const API_URL = 'https://maps.googleapis.com/maps/api';

    public function __construct()
    {
        $this->apiKey = config('services.google.places_api_key');

        if (!$this->apiKey) {
            throw new Exception('Google Places API Key not configured');
        }
    }

    /**
     * Search for a business on Google Places
     * Returns place_id if found, null otherwise
     */
    public function searchPlace($businessName, $location = null)
    {
        try {
            $query = $businessName;
            if ($location) {
                $query .= ' ' . $location;
            }

            $response = Http::get(self::API_URL . '/place/findplacefromtext/json', [
                'input' => $query,
                'inputtype' => 'textquery',
                'fields' => 'place_id,formatted_address',
                'key' => $this->apiKey,
            ]);

            $data = $response->json();

            // Google returns a `status` field. Anything other than OK/ZERO_RESULTS
            // (e.g. REQUEST_DENIED for a wrong key or a disabled API) is logged
            // with its error_message so the real cause is visible.
            $status = $data['status'] ?? 'UNKNOWN';

            if ($status === 'OK' && !empty($data['candidates'])) {
                return $data['candidates'][0]['place_id'] ?? null;
            }

            if ($status !== 'ZERO_RESULTS') {
                Log::error('GMB Search failed', [
                    'query'         => $query,
                    'status'        => $status,
                    'error_message' => $data['error_message'] ?? null,
                ]);
            }

            return null;
        } catch (Exception $e) {
            Log::error('GMB Search Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get photos for a business using place_id
     * Returns array of up to 10 photo URLs
     */
    public function getPhotos($placeId, $limit = 10)
    {
        try {
            $response = Http::get(self::API_URL . '/place/details/json', [
                'place_id' => $placeId,
                'fields' => 'photos',
                'key' => $this->apiKey,
            ]);

            $data = $response->json();
            $status = $data['status'] ?? 'UNKNOWN';

            if ($status !== 'OK' || !isset($data['result']['photos'])) {
                if ($status !== 'OK') {
                    Log::error('GMB Photo Fetch failed', [
                        'place_id'      => $placeId,
                        'status'        => $status,
                        'error_message' => $data['error_message'] ?? null,
                    ]);
                }
                return [];
            }

            $photos = [];
            $photoArray = array_slice($data['result']['photos'], 0, $limit);

            foreach ($photoArray as $photo) {
                $photoRef = $photo['photo_reference'] ?? null;
                if ($photoRef) {
                    $photoUrl = $this->getPhotoUrl($photoRef, $photo['width'] ?? 800);
                    if ($photoUrl) {
                        $photos[] = $photoUrl;
                    }
                }
            }

            return $photos;
        } catch (Exception $e) {
            Log::error('GMB Photo Fetch Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Build the Place Photo endpoint URL from a photo reference.
     * This endpoint 302-redirects to the actual image; the HTTP client
     * downloading it follows the redirect automatically.
     */
    private function getPhotoUrl($photoReference, $maxWidth = 800)
    {
        return self::API_URL . '/place/photo?' . http_build_query([
            'maxwidth'       => $maxWidth,
            'photoreference' => $photoReference,
            'key'            => $this->apiKey,
        ]);
    }
}

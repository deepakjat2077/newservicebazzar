<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserApiToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging sender (FCM HTTP v1 API).
 *
 * Credentials come from the admin panel (Setting > Push Notification) as a
 * Firebase service-account JSON — AppServiceProvider decodes it into
 * services.fcm.*. This mints a short-lived OAuth access token from that service
 * account (JWT, signed RS256) and posts to the v1 endpoint, one message per
 * device the user is signed in on (user_api_tokens.fcm_token).
 *
 * A no-op whenever push is disabled or unconfigured, so the in-app notification
 * list keeps working with no Firebase setup — push simply layers on top once
 * the admin pastes the service account.
 */
class FcmService
{
    protected const OAUTH_URL = 'https://oauth2.googleapis.com/token';
    protected const SCOPE     = 'https://www.googleapis.com/auth/firebase.messaging';

    public function isConfigured(): bool
    {
        return (bool) config('services.fcm.enabled')
            && filled(config('services.fcm.project_id'))
            && is_array(config('services.fcm.service_account'))
            && filled(config('services.fcm.service_account.private_key') ?? null);
    }

    /** Every FCM token registered across this user's signed-in devices. */
    public function tokensFor(User $user): array
    {
        return UserApiToken::where('user_id', $user->id)
            ->whereNotNull('fcm_token')->where('fcm_token', '!=', '')
            ->pluck('fcm_token')->unique()->values()->all();
    }

    /**
     * Send a push to all of a user's devices.
     *
     * @param array $data optional key/value payload the app reads on tap. FCM
     *                    v1 requires every data value to be a string.
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): void
    {
        if (!$this->isConfigured()) {
            return;
        }

        $tokens = $this->tokensFor($user);

        if (empty($tokens)) {
            return;
        }

        $accessToken = $this->accessToken();
        if (!$accessToken) {
            return;
        }

        $url = 'https://fcm.googleapis.com/v1/projects/' . config('services.fcm.project_id') . '/messages:send';
        $stringData = array_map(fn ($v) => (string) $v, array_filter($data, fn ($v) => $v !== null && $v !== ''));

        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)->timeout(15)->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => $stringData ?: (object) [],
                        'android'      => ['priority' => 'high'],
                    ],
                ]);

                if (!$response->successful()) {
                    Log::warning('FCM v1 push failed', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                // A push failure must never break the action that triggered it.
                Log::warning('FCM v1 push threw', ['message' => $e->getMessage()]);
            }
        }
    }

    /**
     * A cached OAuth access token for the service account. Google issues these
     * for an hour; cache a little short of that so it never goes out stale.
     */
    protected function accessToken(): ?string
    {
        $sa = config('services.fcm.service_account');
        $cacheKey = 'fcm_access_token:' . md5($sa['client_email'] ?? '');

        if ($cached = Cache::get($cacheKey)) {
            return $cached;
        }

        try {
            $now = time();
            $jwt = $this->signJwt([
                'iss'   => $sa['client_email'],
                'scope' => self::SCOPE,
                'aud'   => self::OAUTH_URL,
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $sa['private_key']);

            if (!$jwt) {
                return null;
            }

            $response = Http::asForm()->timeout(15)->post(self::OAUTH_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                Log::warning('FCM OAuth token failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $token = $response->json('access_token');

            // Only a real token is cached — a failure must be retryable now.
            if ($token) {
                Cache::put($cacheKey, $token, now()->addMinutes(55));
            }

            return $token;
        } catch (\Throwable $e) {
            Log::warning('FCM OAuth token threw', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Build and RS256-sign a JWT with pure PHP (openssl) — no JWT library
     * needed, so this works regardless of what is in vendor/.
     */
    protected function signJwt(array $claims, string $privateKey): ?string
    {
        $encode = fn ($data) => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');

        $header  = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $encode(json_encode($claims));
        $signingInput = $header . '.' . $payload;

        $signature = '';
        if (!openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
            Log::warning('FCM JWT signing failed — check the service-account private key.');
            return null;
        }

        return $signingInput . '.' . $encode($signature);
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Exotel Bridge Call Service
 * Integrates Exotel's Number Masking (Bridge Call) API
 * Masks the actual phone numbers between customers and vendors
 */
class ExotelService
{
    private $accountSid;
    private $apiKey;
    private $apiToken;
    private $apiEndpoint;
    private $virtualNumber;
    private $recording;

    public function __construct()
    {
        if (!config('exotel.enabled')) {
            throw new Exception('Exotel service is not enabled');
        }

        $this->accountSid = config('exotel.account_sid');
        $this->apiKey = config('exotel.api_key');
        $this->apiToken = config('exotel.api_token');
        $this->apiEndpoint = config('exotel.api_endpoint');
        $this->virtualNumber = config('exotel.virtual_number');
        $this->recording = config('exotel.recording');

        // Exotel authenticates with API Key (username) + API Token (password),
        // with the Account SID living in the URL path — all three are required.
        if (!$this->accountSid || !$this->apiKey || !$this->apiToken) {
            throw new Exception('Exotel credentials are not configured');
        }
    }

    /**
     * Initiate a bridge call between customer and vendor
     *
     * @param string $from_number Customer's phone number (format: 919876543210)
     * @param string $to_number Vendor's phone number (format: 919876543210)
     * @param array $options Additional options (listing_id, etc)
     *
     * @return array Response from Exotel API
     * @throws Exception
     */
    public function initiateCall(string $from_number, string $to_number, array $options = []): array
    {
        try {
            // Validate phone numbers
            if (!$this->isValidPhoneNumber($from_number)) {
                throw new Exception("Invalid customer phone number: {$from_number}");
            }

            if (!$this->isValidPhoneNumber($to_number)) {
                throw new Exception("Invalid vendor phone number: {$to_number}");
            }

            // Exotel Connect API. The ".json" suffix asks for a JSON body back
            // (the default is XML). Account SID lives in the path; auth is the
            // API Key + API Token pair, not the SID.
            $url = "{$this->apiEndpoint}/v1/Accounts/{$this->accountSid}/Calls/connect.json";

            // Connect flow: Exotel first rings `From` (the customer), and once
            // they pick up, dials `To` (the vendor) and bridges the two. Both
            // sides only ever see `CallerId` — the ExoPhone virtual number — so
            // neither real number is exposed.
            $payload = [
                'From' => $from_number,
                'To' => $to_number,
                'CallerId' => $this->virtualNumber,
                'Record' => $this->recording ? 'true' : 'false',
                'CallType' => 'trans',
                'StatusCallback' => $options['callback_url'] ?? null,
            ];

            // Remove null / empty values
            $payload = array_filter($payload, function ($value) {
                return $value !== null && $value !== '';
            });

            Log::info('Exotel Bridge Call Initiated', [
                'from' => $from_number,
                'to' => $to_number,
                'url' => $url,
            ]);

            // Basic Auth = API Key (username) + API Token (password).
            $response = Http::withBasicAuth($this->apiKey, $this->apiToken)
                ->asForm()
                ->post($url, $payload);

            $body = $response->json();

            Log::info('Exotel API Response', [
                'status' => $response->status(),
                'body' => $body,
            ]);

            // A real Exotel success is a 2xx whose JSON body carries a Call SID.
            // A 200 with a null/HTML body means we hit the wrong host (e.g. the
            // marketing site) — treat that as a failure, never a silent "sent".
            $callSid = data_get($body, 'Call.Sid');

            if ($response->failed() || !is_array($body) || empty($callSid)) {
                $reason = data_get($body, 'RestException.Message')
                    ?? ($response->status() . ' ' . \Illuminate\Support\Str::limit($response->body(), 200));

                throw new Exception("Exotel API did not accept the call: {$reason}");
            }

            return [
                'success' => true,
                'status' => $response->status(),
                'data' => $body,
                'call_sid' => $callSid,
            ];

        } catch (Exception $e) {
            Log::error('Exotel Bridge Call Failed', [
                'message' => $e->getMessage(),
                'from' => $from_number ?? null,
                'to' => $to_number ?? null,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Hangup an active call
     *
     * @param string $callSid The SID of the call to hangup
     *
     * @return array Response from Exotel API
     */
    public function hangupCall(string $callSid): array
    {
        try {
            $url = "{$this->apiEndpoint}/v1/Accounts/{$this->accountSid}/Calls/{$callSid}.json";

            $response = Http::withBasicAuth($this->apiKey, $this->apiToken)
                ->asForm()
                ->post($url, [
                    'Status' => 'completed',
                ]);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];

        } catch (Exception $e) {
            Log::error('Exotel Hangup Failed', [
                'message' => $e->getMessage(),
                'call_sid' => $callSid,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get call details/recordings
     *
     * @param string $callSid The SID of the call
     *
     * @return array Call details
     */
    public function getCallDetails(string $callSid): array
    {
        try {
            $url = "{$this->apiEndpoint}/v1/Accounts/{$this->accountSid}/Calls/{$callSid}.json";

            $response = Http::withBasicAuth($this->apiKey, $this->apiToken)
                ->get($url);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
            ];

        } catch (Exception $e) {
            Log::error('Exotel Get Call Details Failed', [
                'message' => $e->getMessage(),
                'call_sid' => $callSid,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate Indian phone number format
     *
     * @param string $phoneNumber
     *
     * @return bool
     */
    private function isValidPhoneNumber(string $phoneNumber): bool
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Must be 10 or 12 digits (with or without country code)
        if (strlen($cleaned) === 10) {
            // Add country code if missing
            return true;
        }

        if (strlen($cleaned) === 12 && substr($cleaned, 0, 2) === '91') {
            // Valid Indian number with country code
            return true;
        }

        return false;
    }

    /**
     * Format phone number to E.164 format (required by Exotel)
     *
     * @param string $phoneNumber
     *
     * @return string Formatted number (919876543210)
     */
    public static function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If 10 digits, add country code
        if (strlen($cleaned) === 10) {
            return '91' . $cleaned;
        }

        // If already has country code, return as is
        return $cleaned;
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Web Push via VAPID (RFC 8292) + aes128gcm payload encryption (RFC 8291).
 *
 * Zero external libraries — uses PHP 8's built-in openssl and hash_hkdf.
 * Keys are stored once in storage/app/private/vapid-keys.json.
 */
class VapidService
{
    private const KEYS_PATH  = 'app/private/vapid-keys.json';
    private const SUBJECT    = 'mailto:admin@servicebazzar.com';
    private const RS         = 4096;   // aes128gcm record size

    // ──────────────────────────────────────────────────────────────────────────
    // Key management
    // ──────────────────────────────────────────────────────────────────────────

    public function keysExist(): bool
    {
        return file_exists(storage_path(self::KEYS_PATH));
    }

    /** Generate and persist a VAPID P-256 key pair. Call once on setup. */
    public function generateKeys(): array
    {
        $key = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);

        openssl_pkey_export($key, $privatePem);
        $details   = openssl_pkey_get_details($key);
        $pubKeyDer = $details['key'];

        // The browser expects the public key as an uncompressed P-256 point
        // (0x04 || x || y, 65 bytes), base64url-encoded.
        $rawPub = $this->derPubToRaw($pubKeyDer);
        $pubB64 = $this->b64u($rawPub);

        $data = ['private_pem' => $privatePem, 'public_b64url' => $pubB64];

        @mkdir(dirname(storage_path(self::KEYS_PATH)), 0750, true);
        file_put_contents(storage_path(self::KEYS_PATH), json_encode($data));

        return $data;
    }

    public function loadKeys(): ?array
    {
        $path = storage_path(self::KEYS_PATH);
        if (!file_exists($path)) return null;
        return json_decode(file_get_contents($path), true) ?: null;
    }

    /** Public key in base64url for the JS subscribe call. */
    public function publicKey(): ?string
    {
        return $this->loadKeys()['public_b64url'] ?? null;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Send a push notification
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * @param array  $subscription  ['endpoint'=>..., 'p256dh'=>..., 'auth'=>...]
     * @param array  $payload       ['title'=>..., 'body'=>..., 'url'=>...]
     */
    public function send(array $subscription, array $payload): bool
    {
        $keys = $this->loadKeys();
        if (!$keys) {
            Log::warning('VapidService: no VAPID keys found');
            return false;
        }

        try {
            $json      = json_encode($payload);
            $encrypted = $this->encrypt($json, $subscription['p256dh'], $subscription['auth']);
            $jwt       = $this->buildJwt($subscription['endpoint'], $keys['private_pem']);

            $response = Http::withHeaders([
                'Content-Type'     => 'application/octet-stream',
                'Content-Encoding' => 'aes128gcm',
                'TTL'              => '86400',
                'Urgency'          => 'normal',
                'Authorization'    => 'vapid t=' . $jwt . ',k=' . $keys['public_b64url'],
            ])->withBody($encrypted, 'application/octet-stream')
              ->timeout(15)
              ->post($subscription['endpoint']);

            if ($response->status() >= 400 && $response->status() !== 410) {
                Log::warning('VapidService: push failed', ['status' => $response->status(), 'body' => substr($response->body(), 0, 200)]);
            }

            // 410 Gone = subscription expired; caller should delete it
            return $response->status() < 400 || $response->status() === 410;
        } catch (\Throwable $e) {
            Log::warning('VapidService: push exception', ['message' => $e->getMessage()]);
            return false;
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // RFC 8291 payload encryption (aes128gcm)
    // ──────────────────────────────────────────────────────────────────────────

    private function encrypt(string $plaintext, string $p256dhB64, string $authB64): string
    {
        // Decode receiver keys
        $receiverPubRaw = $this->b64uDecode($p256dhB64);    // 65-byte uncompressed P-256
        $authSecret     = $this->b64uDecode($authB64);       // 16-byte auth secret

        // Generate ephemeral sender key pair
        $senderKey     = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $senderDetails = openssl_pkey_get_details($senderKey);
        $senderPubRaw  = $this->derPubToRaw($senderDetails['key']);  // 65 bytes

        // Import receiver public key from raw uncompressed point
        $receiverPubPem = $this->rawPubToPem($receiverPubRaw);
        $receiverPubKey = openssl_pkey_get_public($receiverPubPem);

        // ECDH shared secret
        $dh = openssl_pkey_derive($receiverPubKey, $senderKey, 32);
        if ($dh === false) {
            throw new \RuntimeException('ECDH key derivation failed');
        }

        // RFC 8291 §3.1 — derive IKM from ECDH result using auth secret
        // Each step is HKDF-Extract = HMAC(key=salt, data=ikm) or
        // HKDF-Expand (single block) = HMAC(key=PRK, data=info || 0x01)
        $prkKey  = hash_hmac('sha256', $dh, $authSecret, true);            // HKDF-Extract
        $keyInfo = "WebPush: info\x00" . $receiverPubRaw . $senderPubRaw;
        $ikm     = hash_hmac('sha256', $keyInfo . "\x01", $prkKey, true);  // HKDF-Expand T(1), 32 bytes

        // RFC 8188 §2.1 — per-record salt and content key derivation
        $salt    = random_bytes(16);
        $prk     = hash_hmac('sha256', $ikm,  $salt, true);                            // HKDF-Extract
        $cek     = substr(hash_hmac('sha256', "Content-Encoding: aes128gcm\x00\x01", $prk, true), 0, 16);
        $nonce   = substr(hash_hmac('sha256', "Content-Encoding: nonce\x00\x01",     $prk, true), 0, 12);

        // Encrypt plaintext + 0x02 delimiter (last record) with AES-128-GCM
        $tag        = '';
        $ciphertext = openssl_encrypt($plaintext . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag, '', 16);

        if ($ciphertext === false) {
            throw new \RuntimeException('AES-128-GCM encryption failed');
        }

        // aes128gcm header: salt (16) + rs (4 BE) + idlen (1) + keyid (65)
        $header = $salt
            . pack('N', self::RS)     // record size (4 bytes big-endian)
            . pack('C', 65)           // keyid length = 65 (uncompressed P-256)
            . $senderPubRaw;          // sender's ephemeral public key

        return $header . $ciphertext . $tag;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // VAPID JWT (ES256 / ECDSA-P256-SHA256)
    // ──────────────────────────────────────────────────────────────────────────

    private function buildJwt(string $endpoint, string $privatePem): string
    {
        $origin = parse_url($endpoint, PHP_URL_SCHEME) . '://' . parse_url($endpoint, PHP_URL_HOST);

        $header  = $this->b64u(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $claims  = $this->b64u(json_encode(['aud' => $origin, 'exp' => time() + 43200, 'sub' => self::SUBJECT]));
        $input   = $header . '.' . $claims;

        $privKey = openssl_pkey_get_private($privatePem);
        openssl_sign($input, $derSig, $privKey, OPENSSL_ALGO_SHA256);

        return $input . '.' . $this->b64u($this->derSigToRaw($derSig));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // DER / raw key conversion helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** Extract uncompressed P-256 point (65 bytes) from SubjectPublicKeyInfo DER. */
    private function derPubToRaw(string $derPem): string
    {
        // Strip PEM envelope
        $der = base64_decode(preg_replace('/-----[^-]+-----|\\s/', '', $derPem));

        // The uncompressed P-256 point is the last 65 bytes of the DER
        // SubjectPublicKeyInfo structure (04 || X || Y).
        $raw = substr($der, -65);
        if ($raw[0] !== "\x04") {
            throw new \RuntimeException('Unexpected public key format — expected uncompressed point');
        }
        return $raw;
    }

    /** Wrap an uncompressed P-256 point back into a PEM for openssl_pkey_get_public(). */
    private function rawPubToPem(string $raw): string
    {
        // SubjectPublicKeyInfo header for P-256 (EC, prime256v1)
        $header = "\x30\x59\x30\x13\x06\x07\x2a\x86\x48\xce\x3d\x02\x01\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07\x03\x42\x00";
        $der    = $header . $raw;
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    /**
     * Convert ECDSA DER signature to raw R||S (64 bytes) required by JWT.
     * DER SEQUENCE: 30 [len] 02 [rlen] [r...] 02 [slen] [s...]
     * Handles both 1-byte and 2-byte DER length fields.
     */
    private function derSigToRaw(string $der): string
    {
        $pos = 0;
        if (ord($der[$pos++]) !== 0x30) throw new \RuntimeException('Bad DER sig: no SEQUENCE');
        // Skip length (1 or 2 bytes)
        $lenByte = ord($der[$pos++]);
        if ($lenByte > 0x80) $pos += $lenByte - 0x80;

        $extractInt = function () use (&$pos, $der): string {
            if (ord($der[$pos++]) !== 0x02) throw new \RuntimeException('Bad DER sig: no INTEGER');
            $len = ord($der[$pos++]);
            $val = substr($der, $pos, $len);
            $pos += $len;
            // Strip leading 0x00 padding byte
            if (strlen($val) > 32 && $val[0] === "\x00") $val = substr($val, 1);
            return str_pad($val, 32, "\x00", STR_PAD_LEFT);
        };

        $r = $extractInt();
        $s = $extractInt();
        return $r . $s;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // base64url helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function b64u(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function b64uDecode(string $data): string
    {
        $pad  = strlen($data) % 4;
        $data = strtr($data, '-_', '+/');
        if ($pad === 2) $data .= '==';
        elseif ($pad === 3) $data .= '=';
        return base64_decode($data);
    }
}

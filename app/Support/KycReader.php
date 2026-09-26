<?php

namespace App\Support;

use App\Models\Listing;
use App\Models\ListingKycDocument;
use App\Services\AiContentGenerator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads the number off an uploaded PAN card / GST certificate with the vision
 * model the app already uses, then checks it without the AI: format, GSTIN
 * check digit, PAN inside the GSTIN, and the name against the business.
 *
 * Only PAN and GST: an Aadhaar image must not be sent to a third party.
 * Never throws — a failed read leaves the document as it was.
 */
class KycReader
{
    public const TYPES = ['pan', 'gst'];

    private const PAN_ENTITY = [
        'P' => 'Individual', 'C' => 'Company', 'H' => 'HUF', 'F' => 'Firm / LLP',
        'A' => 'Association of Persons', 'T' => 'Trust', 'B' => 'Body of Individuals',
        'L' => 'Local Authority', 'J' => 'Artificial Juridical Person', 'G' => 'Government',
    ];

    /** Read, check, store the result, and fill doc_number when it is safe to. */
    public static function run(ListingKycDocument $doc): array
    {
        if (!in_array($doc->doc_type, self::TYPES, true)) {
            return ['ok' => false, 'message' => 'Only PAN and GST documents are read automatically.'];
        }

        $read = self::read($doc);

        if (!$read['ok']) {
            $doc->forceFill(['ai_check' => ['ok' => false, 'message' => $read['message']], 'ai_checked_at' => now()])->save();

            return $read;
        }

        $check = self::check($doc, $read['data']);

        $filled = false;
        // Filled on its own only when nothing looked off; otherwise an admin
        // decides (a wrong PAN on an invoice is worse than none).
        if ($check['valid'] && $check['number'] && !$check['issues'] && empty($doc->doc_number)) {
            // Every file of this type carries the number (the invoice reads any of them).
            ListingKycDocument::where('listing_id', $doc->listing_id)->where('doc_type', $doc->doc_type)
                ->where(fn ($q) => $q->whereNull('doc_number')->orWhere('doc_number', ''))
                ->update(['doc_number' => $check['number']]);
            $doc->doc_number = $check['number'];
            $filled = true;
        }

        $result = $check + ['ok' => true, 'filled' => $filled];
        $doc->forceFill(['ai_check' => $result, 'ai_checked_at' => now()])->save();

        return $result;
    }

    /** @return array{ok: bool, message?: string, data?: array} */
    public static function read(ListingKycDocument $doc): array
    {
        $ai = new AiContentGenerator();
        if (!$ai->isConfigured()) {
            return ['ok' => false, 'message' => 'AI key is not configured.'];
        }

        $file = public_path($doc->path);
        if (!$doc->path || !is_file($file)) {
            return ['ok' => false, 'message' => 'Document file not found.'];
        }

        $mime = mime_content_type($file) ?: 'application/octet-stream';
        $data = base64_encode(file_get_contents($file));

        $part = $mime === 'application/pdf'
            ? ['type' => 'file', 'file' => ['filename' => basename($file), 'file_data' => 'data:application/pdf;base64,' . $data]]
            : ['type' => 'image_url', 'image_url' => ['url' => "data:{$mime};base64,{$data}", 'detail' => 'high']];

        $prompt = 'This should be an Indian ' . ($doc->doc_type === 'pan' ? 'PAN card' : 'GST registration certificate')
            . '. Read it and reply with JSON: {"document_type": "pan_card" | "gst_certificate" | "other", '
            . '"number": the PAN (10 characters) or GSTIN (15 characters) exactly as printed, uppercase, no spaces, or null if unreadable, '
            . '"name": the holder / legal name as printed or null, "trade_name": trade name if printed or null, '
            . '"readable": true or false}. Do not guess characters you cannot read; use null instead.';

        try {
            $attempt = HttpRetry::send(function () use ($ai, $prompt, $part) {
                return Http::withHeaders(['Authorization' => 'Bearer ' . $ai->apiKey()])
                    ->acceptJson()->connectTimeout(10)->timeout(45)
                    ->post(AiContentGenerator::ENDPOINT, [
                        'model' => AiContentGenerator::MODEL,
                        'response_format' => ['type' => 'json_object'],
                        'messages' => [
                            ['role' => 'system', 'content' => 'You extract fields from Indian identity/tax documents. Reply with one JSON object only.'],
                            ['role' => 'user', 'content' => [['type' => 'text', 'text' => $prompt], $part]],
                        ],
                    ]);
            }, 2, 800, 'openai.kyc', 60);

            $response = $attempt['response'];
            if (!$response || !$response->successful()) {
                Log::warning('KYC read failed', ['doc' => $doc->id, 'status' => optional($response)->status(), 'body' => mb_substr((string) optional($response)->body(), 0, 300)]);

                return ['ok' => false, 'message' => 'AI could not read the document right now.'];
            }

            $json = json_decode((string) $response->json('choices.0.message.content'), true);

            return is_array($json)
                ? ['ok' => true, 'data' => $json]
                : ['ok' => false, 'message' => 'AI reply was not readable.'];
        } catch (\Throwable $e) {
            Log::warning('KYC read exception', ['doc' => $doc->id, 'error' => $e->getMessage()]);

            return ['ok' => false, 'message' => 'AI could not read the document right now.'];
        }
    }

    /** Everything decidable in code, from what the AI read. */
    public static function check(ListingKycDocument $doc, array $read): array
    {
        $number = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($read['number'] ?? '')));
        $number = $number !== '' ? $number : null;
        $issues = [];
        $notes = [];

        $expected = $doc->doc_type === 'pan' ? 'pan_card' : 'gst_certificate';
        if (($read['document_type'] ?? null) && $read['document_type'] !== $expected) {
            $issues[] = 'This does not look like a ' . ($doc->doc_type === 'pan' ? 'PAN card' : 'GST certificate') . '.';
        }

        if (!$number) {
            $issues[] = 'The number could not be read clearly.';
        }

        $valid = false;
        if ($number && $doc->doc_type === 'pan') {
            $valid = self::validPan($number);
            $valid ? $notes[] = 'PAN type: ' . (self::PAN_ENTITY[$number[3]] ?? 'Unknown')
                   : $issues[] = 'Not a valid PAN format (should be like AAAAA0000A).';
        }
        if ($number && $doc->doc_type === 'gst') {
            $valid = self::validGstin($number);
            if (!$valid) {
                $issues[] = 'Not a valid GSTIN (format or check digit is wrong).';
            } else {
                $notes[] = 'State code ' . substr($number, 0, 2);
            }
        }

        // The PAN inside a GSTIN (characters 3-12) must be the business's PAN.
        $other = ListingKycDocument::where('listing_id', $doc->listing_id)
            ->where('doc_type', $doc->doc_type === 'pan' ? 'gst' : 'pan')
            ->whereNotNull('doc_number')->where('doc_number', '!=', '')->value('doc_number');
        if ($valid && $other) {
            $pan = $doc->doc_type === 'pan' ? $number : substr($number, 2, 10);
            $otherPan = $doc->doc_type === 'pan' ? substr($other, 2, 10) : $other;
            $pan === $otherPan
                ? $notes[] = 'PAN matches the GSTIN.'
                : $issues[] = 'PAN and GSTIN belong to different PANs.';
        }

        if ($valid) {
            $elsewhere = ListingKycDocument::where('doc_type', $doc->doc_type)
                ->where('doc_number', $number)->where('listing_id', '!=', $doc->listing_id)
                ->distinct()->count('listing_id');
            if ($elsewhere > 0) {
                $issues[] = 'The same ' . strtoupper($doc->doc_type) . ' is on ' . $elsewhere . ' other business' . ($elsewhere > 1 ? 'es' : '') . '.';
            }
        }

        $listing = Listing::find($doc->listing_id);
        $nameMatch = self::nameMatch([$read['name'] ?? null, $read['trade_name'] ?? null], [$listing->name ?? null, $listing->contact_person ?? null]);
        if ($nameMatch === false) {
            $issues[] = 'Name on the document does not match the business or contact person.';
        } elseif ($nameMatch === true) {
            $notes[] = 'Name matches.';
        }

        return [
            'number' => $number,
            'name' => $read['name'] ?? null,
            'trade_name' => $read['trade_name'] ?? null,
            'valid' => $valid && !in_array('This does not look like a ' . ($doc->doc_type === 'pan' ? 'PAN card' : 'GST certificate') . '.', $issues, true),
            'issues' => $issues,
            'notes' => $notes,
        ];
    }

    public static function validPan(string $pan): bool
    {
        return (bool) preg_match('/^[A-Z]{3}[PCHFATBLJG][A-Z][0-9]{4}[A-Z]$/', $pan);
    }

    /** Format plus the GSTIN's own mod-36 check digit. */
    public static function validGstin(string $gstin): bool
    {
        if (!preg_match('/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][1-9A-Z]Z[0-9A-Z]$/', $gstin)) {
            return false;
        }

        $state = (int) substr($gstin, 0, 2);
        if ($state < 1 || $state > 38) {
            return false;
        }

        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $v = strpos($chars, $gstin[$i]) * (($i % 2) + 1);
            $sum += intdiv($v, 36) + ($v % 36);
        }

        return $chars[(36 - ($sum % 36)) % 36] === $gstin[14];
    }

    /** true / false, or null when there is nothing to compare. */
    private static function nameMatch(array $docNames, array $knownNames): ?bool
    {
        $norm = fn ($s) => trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9 ]/', ' ',
            str_replace(['m/s', 'pvt', 'private', 'limited', 'ltd', 'llp', '&', ' and '], ' ', mb_strtolower((string) $s)))));
        $docNames = array_filter(array_map($norm, $docNames));
        $knownNames = array_filter(array_map($norm, $knownNames));

        if (!$docNames || !$knownNames) {
            return null;
        }

        foreach ($docNames as $a) {
            foreach ($knownNames as $b) {
                similar_text($a, $b, $pct);
                $wa = array_filter(explode(' ', $a), fn ($w) => strlen($w) > 2);
                $wb = array_filter(explode(' ', $b), fn ($w) => strlen($w) > 2);
                if ($pct >= 70 || ($wa && count(array_intersect($wa, $wb)) >= min(2, count($wa)))) {
                    return true;
                }
            }
        }

        return false;
    }
}

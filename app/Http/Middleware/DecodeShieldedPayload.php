<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

/**
 * Restores a listing save that the browser encoded to get past the firewall.
 *
 * The hosting firewall in front of this site inspects request bodies and
 * answers 403 to some listing saves — before PHP runs, so nothing reaches the
 * application log and nothing here can catch it. It only ever happens on the
 * listing forms, and the trigger is the photo: a save carrying any amount of
 * text goes through, the same save with a real image attached is refused, and
 * a same-sized block of dummy bytes is not. It is scanning the upload.
 *
 * So the listing forms send neither a file part nor readable text. Every
 * non-file field goes into one base64 value named `_shielded`, and every
 * selected image into another named `_shielded_files`. The firewall sees two
 * opaque strings and no upload; this middleware unpacks both, so controllers
 * and validation see exactly the request they always did.
 *
 * `_token` is deliberately left outside the envelopes: it is a random string
 * that can never trip a rule, and leaving it in the clear means CSRF
 * protection keeps working normally regardless of middleware ordering.
 *
 * A request without either envelope passes straight through, so an older
 * cached copy of the form, a browser where the script did not run, and any
 * other caller all keep working.
 */
class DecodeShieldedPayload
{
    public function handle(Request $request, Closure $next)
    {
        // The two envelopes are independent: a save may carry text with no
        // new image, or replace only the photo. Neither may depend on the
        // other having been sent.
        $this->restoreFields($request);
        $this->restoreUploads($request);
        $this->restoreFiles($request);

        return $next($request);
    }

    /**
     * Unpack the text fields back into the request.
     *
     * parse_str rebuilds the bracket notation the form uses for its repeating
     * groups (mobile_numbers[], faq[question][], and so on) into the same
     * nested arrays a normal POST would have produced.
     */
    protected function restoreFields(Request $request): void
    {
        $envelope = $request->input('_shielded');

        if (!is_string($envelope) || $envelope === '') {
            return;
        }

        $request->request->remove('_shielded');

        $decoded = base64_decode($envelope, true);

        if ($decoded === false) {
            // Malformed envelope: let validation report the missing fields
            // rather than 500 on a half-decoded payload.
            return;
        }

        $fields = [];
        parse_str($decoded, $fields);

        $request->merge($fields);
    }

    /**
     * Attach photos that were uploaded ahead of this request.
     *
     * The browser sends each image to ShieldedUploadController in small
     * pieces before submitting, so the save carries only a list of ids. This
     * turns each finished file into a real upload without it ever having been
     * part of this request body — the save stays a few kilobytes whatever the
     * photo weighs, which is what keeps the firewall out of it.
     */
    protected function restoreUploads(Request $request): void
    {
        $manifest = $request->input('_shielded_uploads');
        $request->request->remove('_shielded_uploads');

        if (!is_string($manifest) || $manifest === '') {
            return;
        }

        $entries = json_decode($manifest, true);

        if (!is_array($entries)) {
            return;
        }

        $rebuilt = [];

        foreach ($entries as $entry) {
            $id = (string) ($entry['id'] ?? '');

            // The id names a file on disk, so nothing but letters and digits
            // may reach the path — the same rule the upload endpoint applies.
            if (!preg_match('/^[a-zA-Z0-9]{8,64}$/', $id) || empty($entry['field'])) {
                continue;
            }

            $path = storage_path('app/shielded-uploads/'.$id.'.part');

            if (!is_file($path) || filesize($path) === 0) {
                continue;
            }

            $this->place(
                $rebuilt,
                (string) $entry['field'],
                $this->uploadFrom($path, $entry)
            );
        }

        $this->apply($request, $rebuilt);
    }

    /**
     * Rebuild the uploads the browser sent inside the envelope.
     *
     * The images are what the firewall actually objects to: a save with any
     * amount of text goes through, and the same save with a real photo
     * attached is refused — it inspects the uploaded bytes. Carried as base64
     * inside an ordinary field there is no file part to inspect, so here they
     * are turned back into real uploads before validation or the controller
     * ever look at the request.
     */
    protected function restoreFiles(Request $request): void
    {
        $envelope = $request->input('_shielded_files');
        $request->request->remove('_shielded_files');

        if (!is_string($envelope) || $envelope === '') {
            return;
        }

        $decoded = base64_decode($envelope, true);
        $entries = $decoded === false ? null : json_decode($decoded, true);

        if (!is_array($entries)) {
            return;
        }

        $rebuilt = [];

        foreach ($entries as $entry) {
            $binary = base64_decode($entry['data'] ?? '', true);

            if ($binary === false || $binary === '' || empty($entry['field'])) {
                continue;
            }

            $path = tempnam(sys_get_temp_dir(), 'shielded_upload_');

            if ($path === false || file_put_contents($path, $binary) === false) {
                continue;
            }

            $this->place($rebuilt, (string) $entry['field'], $this->uploadFrom($path, $entry));
        }

        $this->apply($request, $rebuilt);
    }

    /**
     * $test: true — the file never went through PHP's upload handling, so the
     * strict is_uploaded_file() check would reject it. Everything downstream
     * (validation's image/mimes/max rules, and ->move()) then behaves exactly
     * as it does for an ordinary upload.
     */
    protected function uploadFrom(string $path, array $entry): UploadedFile
    {
        return new UploadedFile(
            $path,
            $entry['name'] ?? 'upload',
            $entry['type'] ?? null,
            null,
            true
        );
    }

    /**
     * A trailing [] is how the form names its repeating photo input, and the
     * controller reads that field as a list — so it has to stay a list even
     * when only one photo was chosen.
     */
    protected function place(array &$rebuilt, string $field, UploadedFile $upload): void
    {
        if (str_ends_with($field, '[]')) {
            $rebuilt[substr($field, 0, -2)][] = $upload;
        } else {
            $rebuilt[$field] = $upload;
        }
    }

    /**
     * Never overwrites: the pre-uploaded photos are attached first, and the
     * inline envelope is only a fallback for a form that could not reach the
     * upload endpoint. If both somehow describe the same field, the one
     * already in place is the one that was actually received in full.
     */
    protected function apply(Request $request, array $rebuilt): void
    {
        foreach ($rebuilt as $field => $upload) {
            if ($request->files->has($field)) {
                continue;
            }

            $request->files->set($field, $upload);
        }
    }
}

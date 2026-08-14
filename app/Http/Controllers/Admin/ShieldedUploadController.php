<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Receives a listing photo in pieces, ahead of the save that will use it.
 *
 * The hosting firewall answers 403 to a listing save carrying a photo, before
 * PHP runs — nothing reaches the log and nothing in the application can catch
 * it. Encoding the photo into the form got past the file inspection, but left
 * the save with a multi-megabyte body, which is its own liability: one opaque
 * blob that large is exactly the shape of request a firewall is most willing
 * to reject, and if anything truncates it the whole form arrives unparsed.
 *
 * So the photo never travels with the save at all. The browser sends it here
 * first as a series of small ordinary text fields, this appends them to one
 * file, and the save then carries nothing but that file's id — a few bytes.
 * The request that used to be refused is now smaller than a search query.
 */
class ShieldedUploadController extends Controller
{
    /**
     * Refuse anything that is not plausibly one listing photo. The pieces are
     * accepted one at a time, so this is the only place a total can be
     * enforced before the disk fills.
     */
    private const MAX_BYTES = 25 * 1024 * 1024;

    private const KEEP_PARTS_HOURS = 6;

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'upload_id' => ['required', 'string', 'regex:/^[a-zA-Z0-9]{8,64}$/'],
            'chunk'     => ['required', 'string'],
            'final'     => ['nullable'],
        ]);

        $path = $this->pathFor($request->input('upload_id'));

        // base64 strict: a piece that did not survive the trip is dropped
        // rather than written as junk into the middle of the image.
        $binary = base64_decode((string) $request->input('chunk'), true);

        if ($binary === false) {
            return response()->json(['ok' => false, 'error' => 'corrupt'], 422);
        }

        if ((is_file($path) ? filesize($path) : 0) + strlen($binary) > self::MAX_BYTES) {
            @unlink($path);

            return response()->json(['ok' => false, 'error' => 'too_large'], 422);
        }

        // FILE_APPEND with LOCK_EX: the browser sends the pieces in order but
        // does not wait for each reply, so two can be written at once.
        if (file_put_contents($path, $binary, FILE_APPEND | LOCK_EX) === false) {
            return response()->json(['ok' => false, 'error' => 'write_failed'], 500);
        }

        if ($request->boolean('final')) {
            $this->pruneStaleParts();
        }

        return response()->json(['ok' => true, 'bytes' => filesize($path)]);
    }

    /**
     * One file per upload id, inside the app's own storage — never a path the
     * browser can influence beyond the id, which validation has already
     * restricted to letters and digits.
     */
    private function pathFor(string $uploadId): string
    {
        $dir = storage_path('app/shielded-uploads');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir.'/'.$uploadId.'.part';
    }

    /**
     * A save that is abandoned between the upload and the submit leaves its
     * pieces behind, and nothing else would ever remove them. Cheap enough to
     * run whenever an upload completes, which is the only moment the
     * directory is known to be in use.
     */
    private function pruneStaleParts(): void
    {
        $cutoff = time() - (self::KEEP_PARTS_HOURS * 3600);

        foreach (glob(storage_path('app/shielded-uploads').'/*.part') ?: [] as $file) {
            if (filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}

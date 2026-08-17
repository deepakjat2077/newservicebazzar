<?php

namespace App\Support\Forms;

use Illuminate\Http\Request;

/**
 * Tells a submitting browser apart from a script POSTing at the endpoint.
 *
 * Two signals, both cheap, and — importantly — both safe to sit on a page that
 * CachePublicPages has stored and is serving to everybody. Nothing here is
 * generated per request, so a cached copy of the form stays as valid as a
 * freshly rendered one.
 *
 *  - A honeypot field. It is in the markup and empty, but positioned off the
 *    page and hidden from assistive technology, so no visitor can type into
 *    it. A script filling in "every input it finds" fills it too.
 *
 *  - An interaction flag the page's own JavaScript sets the first time the
 *    visitor touches the form. A script that POSTs straight at the URL never
 *    loads that script and never sets it.
 *
 * Deliberately not a wall. A determined bot can read the page, skip the
 * honeypot and post the flag — this stops the volume traffic that sprays
 * every form it can find, which is what these forms actually get. Real
 * protection against a targeted bot is a challenge (Turnstile/reCAPTCHA);
 * this is what works without asking the visitor to do anything.
 *
 * Fails open on the interaction flag, on purpose: a visitor whose JavaScript
 * did not run is a real lead that must still get through. The honeypot has no
 * such excuse — a filled honeypot cannot be a person.
 */
class FormGuard
{
    /**
     * Named like something a form might plausibly ask for, because a script
     * choosing which fields to fill often skips ones called "honeypot".
     */
    public const TRAP_FIELD = 'company_website';

    /** Set to "1" by the page's JS on the first real interaction. */
    public const TOUCH_FIELD = '_hp_touched';

    /**
     * Is this submission a bot?
     *
     * @param  string|null  $reason  Filled with what gave it away, for logging.
     */
    public static function looksAutomated(Request $request, ?string &$reason = null): bool
    {
        if (trim((string) $request->input(self::TRAP_FIELD, '')) !== '') {
            $reason = 'honeypot filled';

            return true;
        }

        /*
         * A submission that carries the flag but says the form was touched and
         * sent in under a second is a script replaying a captured request.
         * Missing flag is not judged — see the note above about failing open.
         */
        $touched = $request->input(self::TOUCH_FIELD);

        if ($touched !== null && $touched !== '' && !in_array((string) $touched, ['1'], true)) {
            $reason = 'interaction flag malformed';

            return true;
        }

        return false;
    }

    /**
     * The fields a controller must ignore when it reads the request.
     */
    public static function fields(): array
    {
        return [self::TRAP_FIELD, self::TOUCH_FIELD];
    }
}

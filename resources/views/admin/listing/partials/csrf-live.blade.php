{{--
    Always-current CSRF token for the listing forms' AJAX helpers.

    The listing forms sit open for a long time, and session-keepalive refreshes
    the session behind them — when the token rotates it writes the new value
    into the form's hidden _token and nowhere else. Any AJAX that had baked
    `{{ csrf_token() }}` into its payload at render time then posts the old
    token and comes back 419, which the admin sees as "User create nahi hua" or
    "Sub category add nahi hui" on a form that was working minutes earlier.

    So the helpers read the token from the form instead of from the rendered
    page. Falls back to the meta tag, and then to the render-time value, so it
    still returns something on a page where neither is present.

    $tokenForm = selector of the form whose hidden _token is kept fresh.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    var selector = "{{ $tokenForm ?? '#listingCreateForm' }}";
    var rendered = "{{ csrf_token() }}";
    var pingUrl  = "{{ route('admin.session.ping') }}";

    window.listingCsrfToken = function () {
        var fromForm = $(selector).find('input[name="_token"]').val();

        if (fromForm) {
            return fromForm;
        }

        return $('meta[name="csrf-token"]').attr('content') || rendered;
    };

    /**
     * Pull a current token from the server, and say what actually happened.
     *
     * Callers used to treat "the ping did not come back" as "the login is
     * gone", which is the one reading the response does not support. A ping
     * is refused for reasons that have nothing to do with the session — the
     * CDN in front of this site answers 403 with its own challenge page to
     * requests it does not recognise, and a dropped connection answers with
     * nothing at all. Neither says the admin is logged out, but both used to
     * put "Your login has expired" on screen and throw the save away.
     *
     * So this reports one of three states and lets the caller decide:
     *
     *   'fresh'   - a new token is in the form; the session is definitely live
     *   'expired' - the server positively said so; the save cannot succeed
     *   'blocked' - something refused the ping without proving anything
     *
     * Only the redirect to the login page and a 401/419 are treated as proof:
     * the `admin` middleware sends a session-less request to /admin, fetch
     * follows that, and `redirected` is what distinguishes the login page
     * arriving here from any other non-JSON body.
     */
    window.refreshListingToken = async function () {
        var response;

        try {
            response = await fetch(pingUrl, {
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (error) {
            return 'blocked';
        }

        if (response.status === 401 || response.status === 419) {
            return 'expired';
        }

        // 403 from the CDN, 5xx from the origin: refused, but not evidence.
        if (!response.ok) {
            return 'blocked';
        }

        var payload;

        try {
            payload = await response.json();
        } catch (error) {
            return response.redirected ? 'expired' : 'blocked';
        }

        if (!payload || !payload.ok || !payload.token) {
            return 'blocked';
        }

        $(selector).find('input[name="_token"]').val(payload.token);
        $('meta[name="csrf-token"]').attr('content', payload.token);

        return 'fresh';
    };
})(jQuery);
</script>
@endpush

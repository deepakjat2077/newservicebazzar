{{--
    Session keep-alive for the long listing forms.

    Filling one of these takes longer than session.lifetime (120 min) more
    often than you'd think, and typing sends no requests — so the session was
    garbage-collected while the page sat open. The save then arrived with no
    session, failed the CSRF check, and the agent lost everything they had
    typed. storage/logs/admin-errors-* records that failure, repeatedly, on
    POST admin/listing/store with "admin_id": null.

    Two parts: ping often enough that the session never goes idle long enough
    to be collected, and — if the session is gone anyway (browser reopened,
    logged out elsewhere) — say so instead of letting them hit Save and lose
    the lot.

    The request itself, and writing the refreshed token back into the form,
    both belong to refreshListingToken in the csrf-live partial — which every
    page including this one also includes, against the same form.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    // Comfortably inside the window, and short enough to survive one or two
    // failed pings on a flaky connection.
    var everyMs = {{ max(60, (int) (config('session.lifetime') * 60 / 4)) }} * 1000;
    var warned  = false;

    function sessionLost() {
        if (warned) {
            return;
        }

        warned = true;

        Swal.fire({
            title: 'Your login has expired',
            html: 'Nothing here is lost yet. Open <b>' + window.location.origin +
                  '/admin</b> in a <b>new tab</b>, log in again, then come back ' +
                  'to this tab and press Save.',
            icon: 'warning',
            confirmButtonText: 'Got it'
        });
    }

    /*
     * refreshListingToken (csrf-live) does the request and reports whether the
     * session is actually gone or the ping was merely refused. This used to
     * call sessionLost() on a status of 0 or 200 as well, which meant a
     * dropped connection or the CDN's own challenge page raised "Your login
     * has expired" over a session that was still perfectly good — and, on the
     * add form, cancelled the save with it.
     */
    function ping() {
        window.refreshListingToken().then(function (state) {
            if (state === 'expired') {
                sessionLost();

                return;
            }

            // 'blocked' proves nothing, so it must not clear the warning
            // either — only a ping that really came back does that.
            if (state === 'fresh') {
                warned = false;
            }
        });
    }

    setInterval(ping, everyMs);

    // Coming back to a tab that sat in the background overnight is exactly
    // when the session is most likely already gone — check right away.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            ping();
        }
    });
})(jQuery);
</script>
@endpush

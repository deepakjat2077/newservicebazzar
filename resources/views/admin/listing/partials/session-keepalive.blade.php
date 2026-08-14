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

    $keepAliveForm = selector of the form whose _token should stay fresh.
--}}
@push('js')
<script>
(function ($) {
    "use strict";

    var url      = "{{ route('admin.session.ping') }}";
    var $form    = $("{{ $keepAliveForm ?? '#listingCreateForm' }}");
    // Comfortably inside the window, and short enough to survive one or two
    // failed pings on a flaky connection.
    var everyMs  = {{ max(60, (int) (config('session.lifetime') * 60 / 4)) }} * 1000;
    var warned   = false;

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

    function ping() {
        $.ajax({ url: url, method: 'GET', dataType: 'json' })
            .done(function (res) {
                if (!res || !res.ok) {
                    sessionLost();
                    return;
                }

                warned = false;

                // The token can rotate (session regenerate); keep the form's
                // hidden field in step so Save always posts a current one.
                if (res.token && $form.length) {
                    $form.find('input[name="_token"]').val(res.token);
                }
            })
            .fail(function (xhr) {
                // 302 to the login page comes back as a parse error or a 0
                // status; either way the session is no longer usable.
                if (xhr.status === 401 || xhr.status === 419 || xhr.status === 0 ||
                    xhr.status === 200) {
                    sessionLost();
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

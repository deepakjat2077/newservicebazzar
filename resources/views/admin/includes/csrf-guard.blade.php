{{--
    Keeps every admin page posting a CSRF token the session actually holds.

    The panel was throwing "CSRF token mismatch" across unrelated screens —
    WhatsApp send, chat reply, the Gupshup settings form, listing save, even
    the login form — and storage/logs/admin-errors-* recorded them as 419s
    that mostly carried a real "admin_id". That combination is the whole
    story: the admin was still authenticated, so this was never a logout.

    What actually happens is:

      1. A page renders and bakes `{{ csrf_token() }}` into its AJAX payload.
         Seven admin views do this; the token is frozen at render time.
      2. The session behind that page ends — it goes idle past
         session.lifetime, or Redis drops it.
      3. The next request arrives, so Laravel starts a *new* session with a
         *new* token, and the "remember me" cookie (admin logins always set
         one) silently signs the admin back in. Hence the real admin_id.
      4. The baked token is now from a session that no longer exists, so the
         CSRF check fails and the work is lost.

    Refreshing the page appeared to fix it because that re-rendered a token
    from the current session; logging out and back in did not, because a
    fresh login rotates the token again and every already-open tab is still
    holding the old one.

    So rather than chase the baked token through seven views, the token is
    made live for the whole panel:

      - the session is pinged often enough that it does not go idle out from
        under an open page in the first place,
      - the refreshed token is written back to the meta tag and to every
        hidden _token on the page,
      - and every jQuery AJAX call is rewritten on its way out to carry the
        current token, which is what fixes the views that baked one in
        without having to touch them.

    That last part has to rewrite the payload, not just add the header:
    Laravel reads `_token` from the body first and only falls back to
    X-CSRF-TOKEN when it is absent, so a stale `_token` in the data would
    win over a correct header.
--}}
<script>
(function ($) {
    "use strict";

    if (!$) {
        return;
    }

    var pingUrl = "{{ route('admin.session.ping') }}";

    // A quarter of the session window, so a page survives losing a ping or
    // two, and never less than a minute.
    var everyMs = {{ max(60, (int) (config('session.lifetime') * 60 / 4)) }} * 1000;

    function currentToken() {
        return $('meta[name="csrf-token"]').attr('content') || '';
    }

    function applyToken(token) {
        if (!token) {
            return;
        }

        $('meta[name="csrf-token"]').attr('content', token);
        $('input[name="_token"]').val(token);
    }

    /*
     * Only a redirect to the login page or a 401/419 proves the session is
     * gone. A 403 is the CDN in front of this site answering with its own
     * challenge, and a dead connection answers with nothing — neither says
     * anything about the session, so neither may raise a false alarm or stop
     * anyone's work.
     */
    function ping() {
        $.ajax({
            url: pingUrl,
            method: 'GET',
            dataType: 'json',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            if (res && res.ok && res.token) {
                applyToken(res.token);
            }
        });
    }

    /*
     * Every jQuery AJAX request leaves with the token this page currently
     * holds, in the header and — for the views that baked one into their
     * payload at render time — in the body too.
     */
    $.ajaxPrefilter(function (options, originalOptions, xhr) {
        var token = currentToken();

        if (!token || /^(GET|HEAD|OPTIONS)$/i.test(options.type || 'GET')) {
            return;
        }

        xhr.setRequestHeader('X-CSRF-TOKEN', token);

        // FormData carries files and cannot be rebuilt here, so the field is
        // set on the object itself.
        if (options.data instanceof FormData) {
            if (options.data.has('_token')) {
                options.data.set('_token', token);
            }

            return;
        }

        if ($.isPlainObject(options.data) && '_token' in options.data) {
            options.data._token = token;

            return;
        }

        // Already serialised to "a=1&_token=stale&b=2" by the caller.
        if (typeof options.data === 'string' && options.data.indexOf('_token=') !== -1) {
            options.data = options.data.replace(
                /(^|&)_token=[^&]*/,
                '$1_token=' + encodeURIComponent(token)
            );
        }
    });

    setInterval(ping, everyMs);

    // A tab that sat in the background is where an idled-out session is most
    // likely waiting, so check the moment it comes back rather than waiting
    // for the next interval.
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) {
            ping();
        }
    });
})(window.jQuery);
</script>

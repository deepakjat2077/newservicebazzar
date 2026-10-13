<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * Adds a small script to every SB Sales page that asks the browser for the
 * rep's location (the browser shows its own permission prompt) and posts it
 * to sales_genio.location. Once right after login, then at most every
 * 10 minutes while the panel is open. Added here rather than in ~30 views.
 */
class SalesGeoBeacon
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $user = Auth::guard('sales')->user();
        if (!$user
            || !$request->isMethod('GET')
            || !$response instanceof Response
            || $response->getStatusCode() !== 200
            || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        $pos = strripos($content, '</body>');
        if ($pos === false) {
            return $response;
        }

        $cfg = json_encode([
            'url'   => route('sales_genio.location'),
            'token' => csrf_token(),
            'uid'   => $user->id,
            'login' => !$request->session()->get('sales_geo_logged', false),
        ], JSON_UNESCAPED_SLASHES);

        $script = <<<HTML
<script>
(function (c) {
    var KEY = 'sbgeo:' + c.uid, EVERY = 10 * 60 * 1000;
    function send(d) {
        try { localStorage.setItem(KEY, String(Date.now())); } catch (e) {}
        try {
            fetch(c.url, { method: 'POST', credentials: 'same-origin', keepalive: true,
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': c.token },
                body: JSON.stringify(d) });
        } catch (e) {}
    }
    function run(force) {
        var last = 0;
        try { last = +localStorage.getItem(KEY) || 0; } catch (e) {}
        if (!force && Date.now() - last < EVERY) return;
        if (!navigator.geolocation) { send({ status: 'unavailable' }); return; }
        navigator.geolocation.getCurrentPosition(function (p) {
            send({ status: 'ok', lat: p.coords.latitude, lng: p.coords.longitude, accuracy: Math.round(p.coords.accuracy) });
        }, function (e) {
            send({ status: e.code === 1 ? 'denied' : (e.code === 3 ? 'timeout' : 'unavailable') });
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
    }
    run(c.login);
    setInterval(function () { run(false); }, EVERY);
})($cfg);
</script>
HTML;

        $response->setContent(substr($content, 0, $pos) . $script . substr($content, $pos));

        return $response;
    }
}

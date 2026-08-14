<?php

use App\Http\Middleware\AdminApiPermission;
use App\Http\Middleware\AdminPermission;
use App\Http\Middleware\Authenticate;
use App\Http\Middleware\AuthenticateAdminApi;
use App\Http\Middleware\CachePublicPages;
use App\Http\Middleware\DecodeShieldedPayload;
use App\Http\Middleware\EnsureVisitorId;
use App\Http\Middleware\LogSlowRequests;
use App\Http\Middleware\NoStoreAdminPages;
use App\Http\Middleware\RedirectIfAdmin;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\RedirectIfNotAdmin;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\Verification;
use App\Models\UrlRedirect;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ValidateSignature;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        // Serves the external WhatsApp panel. routes/api.php existed before
        // this but was never loaded — nothing in it was live.
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule) {
        // Rolls up the previous day's raw analytics events into
        // listing_daily_stats so the admin analytics dashboard's "top
        // listings" queries stay fast as the event log grows. Requires
        // the server's cron to call `schedule:run` every minute (standard
        // Laravel deployment requirement, not configured by this app
        // itself).
        $schedule->command('analytics:rollup-listings')->dailyAt('00:30');

        // Hourly rather than daily: the size cap only helps if it is checked
        // while a runaway file is still growing, not the morning after.
        $schedule->command('logs:prune')->hourly();
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'auth' => Authenticate::class,
            'auth.basic' => AuthenticateWithBasicAuth::class,
            'cache.headers' => SetCacheHeaders::class,
            'can' => Authorize::class,
            'guest' => RedirectIfAuthenticated::class,
            'password.confirm' => RequirePassword::class,
            'signed' => ValidateSignature::class,
            'throttle' => ThrottleRequests::class,

            'admin' => RedirectIfNotAdmin::class,
            'admin.guest' => RedirectIfAdmin::class,
            'permission' => AdminPermission::class,
            'shielded' => DecodeShieldedPayload::class,
            'verified' => Verification::class,

            // Bearer-token equivalents of `admin` / `permission`, for the
            // external WhatsApp panel. They answer 401/403 in JSON instead of
            // redirecting to the HTML login page.
            'admin.api' => AuthenticateAdminApi::class,
            'admin.api.can' => AdminApiPermission::class,
        ]);

        // CORS is not registered here on purpose: Laravel already runs
        // HandleCors in the global stack, and adding it again would emit
        // Access-Control-Allow-Origin twice, which browsers reject outright.
        // The panel's origins are configured in config/cors.php via
        // WHATSAPP_PANEL_ORIGINS.

        // Reject requests with a Host header that doesn't match the
        // configured APP_URL (and its subdomains) — previously the app had
        // a custom TrustHosts middleware defined but never actually wired
        // into the stack, so Host-header validation was inactive.
        //
        // Loopback hosts are trusted too: the middleware only skips itself
        // when APP_ENV=local, and local copies of this app run with the
        // production .env, so `php artisan serve` on 127.0.0.1 would
        // otherwise 400 on every request. These patterns are regexes matched
        // against the port-less host, and they can never match a real
        // public request.
        $middleware->trustHosts(at: [
            '^localhost$',
            '^127\.0\.0\.1$',
            '^\[::1\]$',
        ], subdomains: true);

        $middleware->web(append: [
            SecurityHeaders::class,
            EnsureVisitorId::class,
            // Appended, so it runs after StartSession — it needs a session to
            // read csrf_token() from, both to mask the token on the way in and
            // to put the visitor's own token back on the way out.
            CachePublicPages::class,
        ]);

        // Laravel sorts the stack by its priority list, and ThrottleRequests
        // sits in that list while CachePublicPages does not — so the throttle
        // ran first and counted requests it never had to do any work for.
        // Serving a cached page costs nothing, so it must not spend anyone's
        // rate limit: put the cache ahead of the throttle explicitly, leaving
        // the limit to guard only the uncached renders it was added for.
        $middleware->prependToPriorityList(
            ThrottleRequests::class,
            CachePublicPages::class
        );

        // Nothing here ever told a cache to leave the admin panel alone, so a
        // browser could reopen a listing form from its own stored copy —
        // stale data, stale CSRF token, and shipped fixes that never appeared
        // until someone hard-refreshed. The middleware itself limits the
        // headers to admin/*, so registering it globally is safe.
        $middleware->append(NoStoreAdminPages::class);

        // Prepended so the timer wraps every other middleware — a request
        // held up inside the stack itself would otherwise be measured as
        // fast. Writes only past its threshold, so a healthy request costs
        // one microtime() call and nothing else.
        $middleware->prependToGroup('web', LogSlowRequests::class);

        // Must stay plain text: the client-side tracking script reads this
        // cookie directly via document.cookie to attach the visitor_id to
        // every analytics beacon (see EnsureVisitorId middleware). The
        // app's own App\Http\Middleware\EncryptCookies subclass is legacy,
        // pre-bootstrap-style scaffolding that is never actually wired into
        // this middleware stack — this is the framework-native way to
        // exclude a cookie from encryption in Laravel 11+.
        $middleware->encryptCookies(except: [
            'visitor_id',
        ]);

        // Gupshup POSTs delivery receipts and user replies to this endpoint
        // and has no CSRF token to send, so it must be exempt.
        //
        // The route's own ->withoutMiddleware([App\Http\Middleware\
        // VerifyCsrfToken::class]) never worked: like EncryptCookies above,
        // that class is legacy scaffolding that is not in this stack — the
        // active middleware is the framework's ValidateCsrfToken, so its
        // $except list was never read and every webhook POST 419'd. This is
        // the framework-native way to exempt a path in Laravel 11+.
        //
        // The endpoint is not left unguarded: GupshupWebhookController
        // verifies the shared secret from the `token` query parameter.
        $middleware->validateCsrfTokens(except: [
            'gupshup/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        /*
         * MySQL refusing the connection is an outage, not a bug in the
         * request — 2002/2003 cannot connect, 2006/2013 lost it mid-flight,
         * 1040/1203 the connection allowance is spent.
         *
         * Walks the previous-exception chain because the PDOException that
         * carries errorInfo is usually wrapped by the time it reaches here.
         */
        $databaseUnreachable = function (\Throwable $exception): bool {
            $codes = [1040, 1203, 2002, 2003, 2006, 2013];

            for ($current = $exception; $current !== null; $current = $current->getPrevious()) {
                if (!$current instanceof \PDOException) {
                    continue;
                }

                if (in_array((int) ($current->errorInfo[1] ?? 0), $codes, true)) {
                    return true;
                }
            }

            return false;
        };

        /*
         * Answer 503, not 500. It tells a visitor the site is coming back and
         * tells Google to keep the pages indexed rather than treat them as
         * broken; Retry-After says how long to wait.
         */
        $exceptions->render(function (\Throwable $exception, $request) use ($databaseUnreachable) {
            if (!$databaseUnreachable($exception)) {
                return null;
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Service abhi busy hai. Thodi der baad dobara try karein.',
                ], 503)->header('Retry-After', '120');
            }

            return response()->view('errors.503', [], 503)->header('Retry-After', '120');
        });

        /*
         * One line per failure instead of a 68-frame trace.
         *
         * On 2026-08-07 this exact exception was recorded 64,540 times and the
         * traces alone came to 1.9 GB in storage/logs — enough noise to hide
         * every other error in the file. Returning false stops the default
         * handler from writing its own copy.
         */
        $exceptions->report(function (\Throwable $exception) use ($databaseUnreachable) {
            if (!$databaseUnreachable($exception)) {
                return;
            }

            Log::warning('Database unreachable', [
                'error' => Str::limit($exception->getMessage(), 200),
                'uri'   => request()->path(),
            ]);

            return false;
        });

        // SEO: legacy-URL forwarding. Listing/Location slugs regenerate
        // whenever their name is edited (see the `updating` hooks on those
        // models), which would otherwise silently 404 a previously
        // indexed/linked URL. ModelNotFoundException is already normalized
        // to NotFoundHttpException by prepareException() before render
        // callbacks run, so this catches both real 404 routes and
        // firstOrFail() lookups in one place.
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if (!$request->isMethod('GET')) {
                return null;
            }

            $redirect = UrlRedirect::where('old_path', $request->path())->first();

            if ($redirect) {
                return redirect('/' . ltrim($redirect->new_path, '/'), 301);
            }

            return null;
        });

        /*
         * A 403/419/404 inside the admin panel renders an error page and
         * leaves nothing behind — the main log only records exceptions the
         * app throws itself, so "an agent got a 403 adding a listing" is
         * unanswerable after the fact. Record which URL, which admin and
         * which status, on its own daily file so it stays readable.
         *
         * Returns null: this only observes, Laravel still renders the
         * response it was going to.
         */
        $exceptions->render(function (HttpExceptionInterface $e, $request) {
            if (!$request->is('admin', 'admin/*')) {
                return null;
            }

            $status = $e->getStatusCode();

            if ($status < 400) {
                return null;
            }

            Log::build([
                'driver' => 'daily',
                'path'   => storage_path('logs/admin-errors.log'),
                'days'   => (int) config('logging.channels.daily.days', 1),
            ])->warning('ADMIN HTTP ERROR', [
                'status'   => $status,
                'method'   => $request->method(),
                'uri'      => $request->path(),
                'admin_id' => optional(auth()->guard('admin')->user())->id,
                'ip'       => $request->ip(),
                'ajax'     => $request->ajax(),
                'referer'  => $request->headers->get('referer'),
                'message'  => $e->getMessage(),

                /*
                 * A 419 on a listing save is ambiguous: the token can be
                 * genuinely stale, or the body can have arrived truncated so
                 * PHP parsed no fields at all — identical message, opposite
                 * cause. The sizes and the field names tell them apart.
                 */
                'sent'     => $status === 419 ? [
                    'declared_length' => $request->headers->get('content-length'),
                    'fields'          => array_keys($request->request->all()),
                    'files'           => array_keys($request->allFiles()),
                    'token_length'    => strlen((string) $request->input('_token')),
                ] : null,
            ]);

            return null;
        });
    })->create();

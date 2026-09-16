<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Serves already-rendered HTML to logged-out visitors.
 *
 * The load on this site is not people — it is crawlers walking a URL space of
 * roughly 60 cities x 5,535 subcategories, plus the area-level combinations
 * on top. Every one of those hits used to cost a full render: ~27 queries and
 * around 2.8 seconds on /pune/plumber, during which one of the 100 PHP workers
 * and one MySQL connection were held. Once the workers are all busy the
 * account reaches its process ceiling, new work is refused with
 * `SQLSTATE[HY000] [2002] Operation not permitted`, and nginx answers 504
 * because nothing upstream ever replies.
 *
 * A cached page costs no worker time worth measuring and touches no database
 * at all, which is the whole point: it breaks that chain at the start.
 *
 * Deliberately narrow, because a page cache is the easiest place to leak one
 * visitor's view to another:
 *
 *   - guests only, so a logged-in header is never stored or served
 *   - GET only, and only a plain 200 HTML response
 *   - no query string, so ?sort= and ?filter= variants cannot multiply the
 *     stored copies (robots.txt disallows those separately)
 *   - the CSRF token is swapped for a placeholder before storing and put back
 *     per request on the way out, so a cached form never carries somebody
 *     else's token
 */
class CachePublicPages
{
    /** How long a rendered page stays usable. */
    protected const TTL_MINUTES = 30;

    /**
     * How long past its 30 minutes a page is still worth serving while one
     * request rebuilds it. Nothing invalidates this cache when a listing
     * changes, so TTL_MINUTES stays where it is — a new listing must not wait
     * hours to appear. This only covers the moment of expiry.
     */
    protected const STALE_MINUTES = 60;

    /**
     * How many uncached public pages may render at the same time.
     *
     * A per-IP rate limit does not bound this: the crawlers walking the
     * city x subcategory space arrive from large address pools, so each IP
     * stays under its own limit while together they still start hundreds of
     * renders a minute. Every one of those holds a PHP worker and a MySQL
     * connection for its whole ~27-query render, and once the account's
     * ceiling is reached the server stops accepting work at all — which is
     * where an admin saving a listing gets a bare 403 from LiteSpeed with
     * nothing in the application log.
     *
     * Capping the renders themselves is what keeps that from happening.
     * Cache hits, admin traffic and logged-in users never reach this, so the
     * only thing it can shed is the surplus crawl, and it sheds it for the
     * price of one lock attempt instead of a full render.
     */
    protected const MAX_CONCURRENT_RENDERS = 6;

    /**
     * How many uncached (query-string) renders may run at once. Kept small and
     * separate: this is the traffic shape that emptied the worker pool twice,
     * and nothing past this limit is worth a worker.
     */
    protected const MAX_CONCURRENT_UNCACHED_RENDERS = 4;

    /** Longest a slot may stay held if a request dies without releasing it. */
    protected const RENDER_SLOT_TTL_SECONDS = 30;

    /** How long to wait for a slot to free up before rendering unslotted. */
    protected const SLOT_WAIT_MS = 2000;

    /** How often to re-check for a free slot while waiting. */
    protected const SLOT_WAIT_STEP_MS = 100;

    /** Stands in for the real token while the HTML sits in the cache. */
    protected const TOKEN_PLACEHOLDER = '__SB_CSRF__';

    /**
     * Path prefixes that must always render fresh.
     *
     * Everything behind a login is already excluded by the guest check; these
     * are the paths where a logged-out response still must not be reused —
     * forms that set up a session, and endpoints that answer per visitor.
     */
    protected const NEVER_CACHE = [
        'admin', 'admin/*',
        'user', 'user/*',
        'dashboard', 'dashboard/*',
        'login', 'register', 'logout',
        'password/*', 'my-profile', 'messages',
        'free-listing', 'listing-form',
        'cart', 'checkout', 'payment/*',
        'track/*', 'ajax/*', 'api/*',
        'geocode-address', 'search-locations',
        'set-location-session', 'set-category-session',
        'sitemap.xml', 'sitemap-*', 'up',
        // The load balancer's health check. It must reach PHP to be worth
        // anything, but it must not take a render slot to get there.
        'health',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!$this->isPublicPage($request)) {
            return $next($request);
        }

        // A query string keeps a page out of the cache, so every one of these
        // is a full render — and that is the shape the scraper swarms use
        // (?filter=, ?rating=, ?sort=). Left unguarded they take a worker each
        // until the pool is gone and the whole site 504s, which is what the
        // 2026-09-13 and 2026-09-16 outages were. They get the same six render
        // slots as everyone else, but no waiting and no unslotted fallback: if
        // all six are busy this is refused outright. Refusing costs nothing,
        // and it holds whatever parameter name the next swarm invents.
        if ($request->query() !== []) {
            // Their own pool, not the shared one: the six main slots are busy
            // almost continuously on a live site, so drawing from those would
            // refuse real visitors clicking a filter. Four concurrent renders
            // is far more than human filter traffic ever asks for and far less
            // than it takes to empty the worker pool.
            $slot = $this->acquireRenderSlot(
                'page-query-render-slot:',
                self::MAX_CONCURRENT_UNCACHED_RENDERS
            );

            if (!$slot) {
                return response('Too Many Requests', 429)
                    ->header('Retry-After', '30');
            }

            try {
                return $next($request);
            } finally {
                $slot->release();
            }
        }

        $key = 'page:' . sha1($request->getHost() . '|' . $request->path());

        $cached = $this->store()->get($key);
        $stale = null;

        if ($cached !== null) {
            // Entries written before this became an array are still plain HTML.
            $html = is_array($cached) ? ($cached['html'] ?? '') : $cached;
            $freshUntil = is_array($cached) ? ($cached['fresh_until'] ?? 0) : PHP_INT_MAX;

            if ($freshUntil > time()) {
                return response($this->withCurrentToken($html))
                    ->header('Content-Type', 'text/html; charset=UTF-8')
                    ->header('X-Page-Cache', 'HIT');
            }

            // Past its freshness but still held: usable while someone rebuilds it.
            $stale = $html;
        }

        $slot = $this->acquireRenderSlot();

        // A page going stale used to send every request that arrived for it into
        // its own render at the same moment. One request rebuilds it; the rest
        // get the copy we already have, which is at most STALE_MINUTES old.
        if (!$slot && $stale !== null) {
            return response($this->withCurrentToken($stale))
                ->header('Content-Type', 'text/html; charset=UTF-8')
                ->header('X-Page-Cache', 'STALE');
        }

        if (!$slot) {
            // All slots were busy for an instant, but a slot only ever holds
            // for a single render (or up to RENDER_SLOT_TTL_SECONDS if one
            // dies mid-flight). Rather than turn any visitor away with a 503,
            // wait briefly for one to free up.
            $slot = $this->waitForRenderSlot();
        }

        if (!$slot) {
            // Still nothing free after waiting — render anyway rather than
            // show an error page. This request is uncounted (no slot to
            // release), so it isn't cached either, but a slow page beats a
            // visible error for a real visitor.
            $response = $next($request);
            $response->headers->set('X-Page-Cache', 'MISS-UNSLOTTED');

            return $response;
        }

        try {
            $response = $next($request);

            if ($this->isStorable($response)) {
                $this->store()->put(
                    $key,
                    [
                        'html' => $this->withPlaceholderToken($response->getContent()),
                        'fresh_until' => now()->addMinutes(self::TTL_MINUTES)->getTimestamp(),
                    ],
                    now()->addMinutes(self::TTL_MINUTES + self::STALE_MINUTES)
                );
            }
        } finally {
            // finally, so a render that throws still hands its slot back
            // rather than starving the next request until the TTL expires.
            optional($slot)->release();
        }

        $response->headers->set('X-Page-Cache', 'MISS');

        return $response;
    }

    /**
     * Keep public HTML page cache off the default app cache store.
     *
     * On production the default cache may be "database"; that makes a page
     * cache hit open MySQL before it can answer. The public page cache should
     * be cheap even while MySQL is under pressure, so it defaults to "file"
     * and can be moved to redis/memcached with PUBLIC_PAGE_CACHE_STORE.
     */
    protected function store()
    {
        return Cache::store(config('cache.public_pages.store', 'file'));
    }

    /**
     * Take one of the fixed render slots, or null when all are busy.
     *
     * Each slot is a separate short-lived lock rather than a counter: a
     * counter incremented by a request that then dies never comes back down
     * and would wedge the site shut, whereas a lock simply expires.
     */
    protected function acquireRenderSlot(
        string $prefix = 'page-render-slot:',
        int $count = self::MAX_CONCURRENT_RENDERS
    ) {
        for ($slot = 1; $slot <= $count; $slot++) {
            $lock = $this->store()->lock($prefix . $slot, self::RENDER_SLOT_TTL_SECONDS);

            if ($lock->get()) {
                return $lock;
            }
        }

        return null;
    }

    /**
     * Poll for a free render slot for a short while instead of failing
     * immediately. A slot only holds for the duration of one render (a
     * fraction of a second once MySQL isn't saturated), so most callers that
     * lose the first race pick one up within a step or two.
     */
    protected function waitForRenderSlot()
    {
        $waited = 0;

        while ($waited < self::SLOT_WAIT_MS) {
            usleep(self::SLOT_WAIT_STEP_MS * 1000);
            $waited += self::SLOT_WAIT_STEP_MS;

            if ($slot = $this->acquireRenderSlot()) {
                return $slot;
            }
        }

        return null;
    }

    /**
     * A guest-facing HTML page — cacheable or not.
     *
     * Kept apart from shouldConsider() because the query-string variants fail
     * only that one extra test, and they are precisely the renders that need
     * guarding.
     */
    protected function isPublicPage(Request $request): bool
    {
        return $request->isMethod('GET')
            && !$request->ajax()
            && !$request->expectsJson()
            && !auth()->check()
            && !auth()->guard('admin')->check()
            && !$request->is(self::NEVER_CACHE);
    }

    /** Is this a request whose response could be shared with anyone else? */
    protected function shouldConsider(Request $request): bool
    {
        return $this->isPublicPage($request) && $request->query() === [];
    }

    /** Only a plain, complete HTML page is worth keeping. */
    protected function isStorable($response): bool
    {
        return $response instanceof \Illuminate\Http\Response
            && $response->getStatusCode() === 200
            && str_contains((string) $response->headers->get('Content-Type'), 'text/html')
            && $response->getContent() !== ''
            // A redirect or a streamed download has no body to reuse, and an
            // error page must never be served to the next visitor as if it
            // were the real thing.
            && !$response->isRedirection();
    }

    protected function withPlaceholderToken(string $html): string
    {
        $token = csrf_token();

        return $token === '' ? $html : str_replace($token, self::TOKEN_PLACEHOLDER, $html);
    }

    protected function withCurrentToken(string $html): string
    {
        return str_replace(self::TOKEN_PLACEHOLDER, csrf_token(), $html);
    }
}

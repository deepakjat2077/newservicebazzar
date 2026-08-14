/**
 * ServiceBazzar — User Activity Tracking & Analytics
 *
 * Vanilla JS (no jQuery dependency), matching the existing click-tracking
 * convention already used in listings/details.blade.php and
 * partials/ajax-listings.blade.php (fetch() + keepalive, not $.ajax).
 *
 * Loaded sitewide from layouts/site.blade.php and layouts/app.blade.php.
 * Every beacon is fire-and-forget: failures are swallowed so tracking can
 * never break or slow down the actual page.
 */
(function () {
    'use strict';

    if (typeof document === 'undefined') {
        return;
    }

    // ---------------------------------------------------------------
    // Identity helpers
    // ---------------------------------------------------------------

    function readCookie(name) {
        var match = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
        return match ? decodeURIComponent(match.pop()) : null;
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function uuid() {
        if (window.crypto && window.crypto.randomUUID) {
            return window.crypto.randomUUID();
        }
        // Fallback for older browsers without crypto.randomUUID.
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
            var r = (Math.random() * 16) | 0;
            var v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    }

    // session_uuid lives in sessionStorage so it naturally resets per
    // browser tab/session — giving correct session-boundary semantics for
    // free, with no server-side session-expiry logic needed.
    function sessionUuid() {
        try {
            var existing = window.sessionStorage.getItem('sb_session_uuid');
            if (existing) {
                return existing;
            }
            var fresh = uuid();
            window.sessionStorage.setItem('sb_session_uuid', fresh);
            return fresh;
        } catch (e) {
            // Private-browsing/storage-disabled fallback: a per-load id is
            // still better than nothing, just won't persist across pages.
            return uuid();
        }
    }

    var VISITOR_ID = readCookie('visitor_id');
    var SESSION_UUID = sessionUuid();
    var CLIENT_PAGE_VIEW_ID = uuid();
    var TRACK_BASE = '/track';

    // ---------------------------------------------------------------
    // Transport: sendBeacon primary, fetch+keepalive fallback.
    // CSRF token always travels in the body (`_token`), never a header —
    // navigator.sendBeacon cannot set custom headers, and Laravel's
    // VerifyCsrfToken already reads `_token` from request input.
    // ---------------------------------------------------------------

    function send(path, payload) {
        if (!VISITOR_ID) {
            return; // no cookie yet (e.g. middleware not applied) — skip silently
        }

        payload._token = csrfToken();

        var url = TRACK_BASE + path;
        var json = JSON.stringify(payload);

        try {
            if (navigator.sendBeacon) {
                var blob = new Blob([json], { type: 'application/json' });
                var ok = navigator.sendBeacon(url, blob);
                if (ok) {
                    return;
                }
            }
        } catch (e) {
            // fall through to fetch
        }

        try {
            fetch(url, {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body: json
            }).catch(function () {});
        } catch (e) {
            // swallow — tracking must never throw into the page
        }
    }

    // ---------------------------------------------------------------
    // Page view: open / close
    // ---------------------------------------------------------------

    function parseUtm() {
        var params = new URLSearchParams(window.location.search);
        return {
            utm_source: params.get('utm_source') || null,
            utm_medium: params.get('utm_medium') || null,
            utm_campaign: params.get('utm_campaign') || null,
            utm_term: params.get('utm_term') || null,
            utm_content: params.get('utm_content') || null
        };
    }

    function pageName() {
        return document.body.getAttribute('data-page-name') || document.title || null;
    }

    var pageOpenedAt = Date.now();
    var closeSent = false;

    function sendPageViewOpen() {
        var utm = parseUtm();
        var listingId = document.body.getAttribute('data-listing-id');

        send('/page-view/open', {
            client_page_view_id: CLIENT_PAGE_VIEW_ID,
            session_uuid: SESSION_UUID,
            url: window.location.href,
            page_name: pageName(),
            listing_id: listingId ? parseInt(listingId, 10) : null,
            referrer_url: document.referrer || null,
            utm_source: utm.utm_source,
            utm_medium: utm.utm_medium,
            utm_campaign: utm.utm_campaign,
            utm_term: utm.utm_term,
            utm_content: utm.utm_content,
            screen_resolution: (window.screen ? window.screen.width + 'x' + window.screen.height : null)
        });

        // A listing detail page is also logged as a first-class analytics
        // event (distinct from the generic page_views row above) so
        // per-listing rollups (views/unique views/avg time) don't have to
        // parse page_views.url at query time.
        if (listingId) {
            send('/event', {
                event_category: 'view',
                event_type: 'listing_view',
                session_uuid: SESSION_UUID,
                listing_id: parseInt(listingId, 10),
                category_id: toIntOrNull(document.body.getAttribute('data-category-id')),
                location_id: toIntOrNull(document.body.getAttribute('data-location-id')),
                url: window.location.href
            });
        }

        // Search-results page: fired once per results page load with the
        // actual results_count — only the results page knows this, so
        // search tracking happens here rather than on the search form's
        // submit (which would only know the query, not the outcome).
        var resultsCountAttr = document.body.getAttribute('data-search-results-count');
        if (resultsCountAttr !== null) {
            send('/event', {
                event_category: 'search',
                event_type: 'search_performed',
                session_uuid: SESSION_UUID,
                category_id: toIntOrNull(document.body.getAttribute('data-search-category-id')),
                location_id: toIntOrNull(document.body.getAttribute('data-search-location-id')),
                keyword: document.body.getAttribute('data-search-keyword') || null,
                results_count: toIntOrNull(resultsCountAttr),
                url: window.location.href,
                meta: { filters: window.location.search }
            });
        }
    }

    function sendPageViewClose(isExit) {
        if (closeSent) {
            return;
        }
        closeSent = true;

        var spent = Math.round((Date.now() - pageOpenedAt) / 1000);

        send('/page-view/close', {
            client_page_view_id: CLIENT_PAGE_VIEW_ID,
            session_uuid: SESSION_UUID,
            time_spent_seconds: spent,
            is_exit: !!isExit,
            url: window.location.href,
            page_name: pageName()
        });
    }

    function toIntOrNull(v) {
        if (v === null || v === undefined || v === '') {
            return null;
        }
        var n = parseInt(v, 10);
        return isNaN(n) ? null : n;
    }

    // `pagehide` is the reliable "the user is actually leaving" signal
    // (works for tab close, navigation, and back/forward-cache eviction);
    // `visibilitychange` covers the common case of switching tabs/apps
    // without technically unloading the page. Both call the same guarded
    // sender so only the first one fires.
    window.addEventListener('pagehide', function () {
        sendPageViewClose(true);
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            sendPageViewClose(false);
        }
    });

    // ---------------------------------------------------------------
    // Generic click/interaction tracking.
    //
    // Layered ADDITIVELY on top of existing markup — a new `data-track`
    // attribute, not a replacement for the existing `track-click` /
    // `list-track-click` classes that already feed the separate
    // ListingClick system. Both fire independently on the same click with
    // zero conflict.
    //
    // Usage: data-track="event_type" [data-track-category="click|search|conversion|view"]
    //        [data-track-listing="id"] [data-track-category-id="id"]
    //        [data-track-location-id="id"] [data-track-meta='{"json":"here"}']
    // ---------------------------------------------------------------

    function handleTrackedClick(el) {
        var eventType = el.getAttribute('data-track');
        if (!eventType) {
            return;
        }

        var meta = null;
        var metaAttr = el.getAttribute('data-track-meta');
        if (metaAttr) {
            try {
                meta = JSON.parse(metaAttr);
            } catch (e) {
                meta = null;
            }
        }

        send('/event', {
            event_category: el.getAttribute('data-track-category') || 'click',
            event_type: eventType,
            session_uuid: SESSION_UUID,
            listing_id: toIntOrNull(el.getAttribute('data-track-listing') || el.getAttribute('data-listing-id')),
            category_id: toIntOrNull(el.getAttribute('data-track-category-id') || el.getAttribute('data-category-id')),
            subcategory_id: toIntOrNull(el.getAttribute('data-track-subcategory-id')),
            location_id: toIntOrNull(el.getAttribute('data-track-location-id') || el.getAttribute('data-location-id')),
            url: window.location.href,
            meta: meta
        });
    }

    // Delegated so it works for content added after load (AJAX-rendered
    // listing cards, dynamically injected pagination, etc.) without
    // needing to re-bind listeners.
    document.addEventListener('click', function (e) {
        var trackEl = e.target.closest('[data-track]');
        if (trackEl) {
            handleTrackedClick(trackEl);
            return;
        }

        // Pagination — Laravel's Bootstrap paginator output.
        var pageLink = e.target.closest('.pagination .page-link');
        if (pageLink) {
            send('/event', {
                event_category: 'click',
                event_type: 'pagination_click',
                session_uuid: SESSION_UUID,
                url: window.location.href,
                meta: { target: pageLink.getAttribute('href') }
            });
            return;
        }

        // Filter pills / sort controls on the listing results page.
        var filterPill = e.target.closest('.filter-pill');
        if (filterPill) {
            send('/event', {
                event_category: 'click',
                event_type: 'filter_click',
                session_uuid: SESSION_UUID,
                url: window.location.href,
                meta: { label: (filterPill.textContent || '').trim().slice(0, 100) }
            });
        }

        // Category banner / ad carousel clicks.
        var bannerLink = e.target.closest('#categoryBannerCarousel a, .desktop-banner-img, .mobile-banner-img');
        if (bannerLink) {
            var anchor = bannerLink.closest('a');
            send('/event', {
                event_category: 'click',
                event_type: 'banner_click',
                session_uuid: SESSION_UUID,
                url: window.location.href,
                meta: { target: anchor ? anchor.getAttribute('href') : null }
            });
        }
    });

    // Search form submissions (the click on the submit button / Enter key).
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (form && (form.id === 'headerSearchForm' || form.classList.contains('yt-search-form'))) {
            send('/event', {
                event_category: 'click',
                event_type: 'search_button',
                session_uuid: SESSION_UUID,
                url: window.location.href
            });
        }
    });

    // ---------------------------------------------------------------
    // Boot
    // ---------------------------------------------------------------

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', sendPageViewOpen);
    } else {
        sendPageViewOpen();
    }
})();

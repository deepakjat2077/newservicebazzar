<?php

namespace App\Providers;

use App\Models\Listing;
use App\Models\Location;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\GuestMessage;
use App\Models\SocialSite;
use App\Models\Enquiry;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if ($this->app->environment('production')) {
            config(['app.debug' => false]);
        }
    }

    /**
     * Stop a `/public` entry point from spreading through every generated URL.
     *
     * route() and url() build on the *incoming request's* base path, not
     * APP_URL — that only applies on the console. So one hit on
     * /public/admin/listing/add gives the whole page links, form actions and
     * assets carrying the same prefix, and the next click keeps it. The root
     * .htaccess is supposed to 301 that prefix away; when the document root
     * points straight at public/ it never gets the chance.
     *
     * Deliberately narrow: it re-roots at the request's own host, and only
     * once the prefix is actually present. Forcing APP_URL unconditionally
     * would point every link on a local copy at the live domain, since local
     * copies here run with the production .env.
     */
    protected function stripPublicPrefixFromUrls(): void
    {
        if ($this->app->runningInConsole()) {
            return;
        }

        $request = $this->app->make('request');
        $base = $request->getBaseUrl();

        if ($base === '/public' || str_starts_with($base, '/public/')) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }
    }

    public function boot(): void
    {
        $this->stripPublicPrefixFromUrls();

        $this->shareAdBanners();

        try {

            /*
             * Cached because these two ran on literally every request —
             * including every crawler hit — and Laravel holds the MySQL
             * connection for the whole request, so under load they were part
             * of what exhausted max_user_connections. Both models forget the
             * entry in their own booted() hooks, so an admin edit still shows
             * up immediately.
             */
            $setting = Cache::remember(Setting::CACHE_KEY, now()->addHours(6), function () {
                return Setting::first();
            });

            if ($setting) {
                $viewShare['setting'] = $setting;
                $viewShare['themeView'] = 'themes.' . $setting->theme . '.';
                $viewShare['themeAsset'] = 'assets/' . $setting->theme . '/';
                $viewShare['socialSites'] = Cache::remember(
                    SocialSite::CACHE_KEY,
                    now()->addHours(6),
                    function () {
                        return SocialSite::latest()->get();
                    }
                );

                view()->share($viewShare);

                /*
                 * SMTP credentials live on the settings row (Setting > Email),
                 * not in .env.
                 *
                 * This used to read $setting->email_config — a property that
                 * does not exist on the model, so it was always null and the
                 * block never ran. With no MAIL_MAILER in .env either, the
                 * mailer stayed on the 'log' driver and nothing the site tried
                 * to send ever left the server. The column is 'mail', and its
                 * keys are host/port/encryption/username/password/email_from.
                 */
                if ($setting->mail) {
                    $email = $setting->mail;

                    config([
                        'mail.default' => 'smtp',
                        'mail.mailers.smtp.transport' => 'smtp',
                        'mail.mailers.smtp.host' => $email->host ?? 'smtp.gmail.com',
                        'mail.mailers.smtp.port' => $email->port ?? 587,
                        'mail.mailers.smtp.encryption' => strtolower($email->encryption ?? $email->enc ?? 'tls'),
                        'mail.mailers.smtp.username' => $email->username ?? null,
                        'mail.mailers.smtp.password' => $email->password ?? null,
                        'mail.from.address' => $email->email_from ?? ($email->username ?? null),
                        'mail.from.name' => $setting->site_name ?: config('app.name'),
                    ]);
                }

                /*
                 * Company details for the tax invoice, set in Setting >
                 * Invoice. Only non-empty values override, so a field left
                 * blank falls back to whatever config/invoice.php holds.
                 */
                if ($setting->invoice) {
                    $inv = (array) $setting->invoice;

                    foreach (['legal_name', 'trade_name', 'address', 'city', 'state', 'state_code',
                              'pincode', 'gstin', 'cin', 'pan', 'phone', 'email', 'website'] as $key) {
                        if (filled($inv[$key] ?? null)) {
                            config(['invoice.company.' . $key => $inv[$key]]);
                        }
                    }

                    foreach (['name' => 'bank_name', 'account' => 'bank_account',
                              'ifsc' => 'bank_ifsc', 'branch' => 'bank_branch'] as $key => $field) {
                        config(['invoice.bank.' . $key => $inv[$field] ?? null]);
                    }

                    if (filled($inv['sac'] ?? null)) {
                        config(['invoice.sac' => $inv['sac']]);
                    }

                    if (filled($inv['gst_rate'] ?? null)) {
                        config(['invoice.gst_rate' => (float) $inv['gst_rate']]);
                    }

                    if (!empty($inv['terms'])) {
                        config(['invoice.terms' => (array) $inv['terms']]);
                    }
                }

                /*
                 * Gupshup WhatsApp credentials are managed from the admin
                 * panel (Setting > WhatsApp / Gupshup) instead of .env, so
                 * push whatever is stored on the settings row into the
                 * services.gupshup.* config the controllers already read.
                 *
                 * Only non-empty values override, so a field left blank in
                 * the admin form falls back to the .env value rather than
                 * silently breaking the sends.
                 */
                if ($setting->gupshup) {
                    $gupshup = (array) $setting->gupshup;

                    $keys = [
                        'api_key', 'app_name', 'source',
                        'template_id', 'otp_template_id', 'enquiry_template_id',
                        'payment_template_id',
                        'webhook_secret',
                    ];

                    foreach ($keys as $key) {
                        $value = $gupshup[$key] ?? null;

                        if (filled($value)) {
                            config(['services.gupshup.' . $key => trim((string) $value)]);
                        }
                    }
                }

                /*
                 * Google / Facebook "continue with" login credentials —
                 * managed from the admin panel (Setting > Social Login)
                 * instead of .env, same pattern as Gupshup above. Socialite
                 * reads services.google.* / services.facebook.*, so this is
                 * the only place those need to be filled in from the DB.
                 */
                if ($setting->social_login) {
                    $social = (array) $setting->social_login;

                    foreach (['google', 'facebook'] as $provider) {
                        $enabled = $social[$provider . '_enabled'] ?? false;
                        $clientId = $social[$provider . '_client_id'] ?? null;
                        $clientSecret = $social[$provider . '_client_secret'] ?? null;

                        if ($enabled && filled($clientId) && filled($clientSecret)) {
                            config([
                                'services.' . $provider . '.client_id' => trim((string) $clientId),
                                'services.' . $provider . '.client_secret' => trim((string) $clientSecret),
                                'services.' . $provider . '.redirect' => url('/user/social-login/' . $provider . '/callback'),
                            ]);
                        }
                    }
                }

                /*
                 * Firebase (FCM) push credentials — managed from the admin
                 * panel (Setting > Push Notification) instead of .env, same
                 * pattern as gupshup / social login above. FcmService reads
                 * services.fcm.* to send push via the FCM HTTP v1 API.
                 */
                if ($setting->push_notification) {
                    $push = (array) $setting->push_notification;

                    if (!empty($push['enabled']) && !empty($push['service_account'])) {
                        config([
                            'services.fcm.enabled'         => true,
                            'services.fcm.project_id'      => $push['project_id'] ?? null,
                            'services.fcm.service_account' => (array) $push['service_account'],
                        ]);
                    }
                }
            }

            Str::macro('strToKey', function ($str) {
                $str = str_replace(' ', '_', $str);
                $str = str_replace('-', '_', $str);
                return strtolower($str);
            });

            Str::macro('keyToStr', function ($str) {
                $str = str_replace('_', ' ', $str);
                return ucfirst($str);
            });

            view()->composer([
                'admin.includes.sidebar',
                'admin.includes.topbar',
            ], function ($view) {

                $pendingListings = Listing::where('status', 0)->count();

                $pendingDeposits = Payment::where('status', 2)->count();

                $unreadGuestChats = GuestMessage::where('sender_type', 'guest')
                    ->whereNull('read_at')
                    ->count();

                $pendingEnquiries = Enquiry::where('is_read', 0)->count();
                  $pendingUpdateListings = Listing::where(function ($q) {
        $q->where('name_approval_status', 'pending')
          ->orWhere('address_approval_status', 'pending')
          ->orWhere('website_approval_status', 'pending');
    })->count();

                // Drives the red badge on the WhatsApp menu — replies nobody
                // has opened yet.
                $unreadWhatsappReplies = \App\Models\WhatsappMessage::inbound()
                    ->unread()
                    ->count();

                $view->with('unreadWhatsappReplies', $unreadWhatsappReplies);
                $view->with('pendingListings', $pendingListings);
                $view->with('pendingDeposits', $pendingDeposits);
                $view->with('unreadGuestChats', $unreadGuestChats);
                $view->with('pendingEnquiries', $pendingEnquiries);

                $view->with('pendingUpdateListings', $pendingUpdateListings);
            });

            // Single source of truth for the footer's "Popular Categories"
            // and "Popular Cities" sections (previously each page — home,
            // listing details — computed/rendered its own separate copy).
            // The footer is @include'd on every page via layouts/site.blade.php,
            // so binding the composer to it means every page gets the same
            // data without every controller having to set it individually.
            // getPopularCategories() is the exact SiteController@index() query
            // (same location detection, same category/subcategory filtering) —
            // reused here instead of a separate query so results stay
            // identical across the footer, ajaxPopularCategories, and index().
            view()->composer(
                'themes.' . ($setting->theme ?? 'servicebazzar') . '.includes.footer',
                function ($view) {
                    $existing = $view->getData();
                    $siteController = app(\App\Http\Controllers\SiteController::class);

                    $popularCategories = $existing['popularCategories']
                        ?? $siteController->getPopularCategories();

                    // Cached — this composer runs on every page render.
                    $locations = $existing['locations'] ?? activeLocations();

                    $view->with('popularCategories', $popularCategories);
                    // The partial builds /{city}/{subcategory} links. It used
                    // to re-derive the city from the session itself, in a
                    // different priority order than the query above, so the
                    // tabs could be ranked for one city while their links
                    // pointed at another. Hand it the same location the
                    // ranking used.
                    $view->with('popularCategoriesLocation', $existing['popularCategoriesLocation']
                        ?? $siteController->resolvePopularCategoriesLocation());
                    $view->with('locations', $locations);
                }
            );

        } catch (\Exception $e) {
            /*
             * A database blip used to end here silently, and then every view
             * that reads $setting / $themeView / $socialSites threw
             * "Undefined variable" instead. One unreachable MySQL produced
             * 64,540 stack traces and a 1.9 GB log file in a single day on
             * 2026-08-07, because each failure was re-raised by every view
             * rather than handled once here.
             *
             * Sharing empty defaults will not make the site usable — the
             * content is in that same database — but it keeps the failure to
             * the one error that actually happened.
             */
            view()->share([
                'setting'     => null,
                'themeView'   => 'themes.servicebazzar.',
                'themeAsset'  => 'assets/servicebazzar/',
                'socialSites' => collect(),
            ]);
        }

        /*
         * Permission helpers for the admin panel.
         *
         * These sit outside the try/catch above on purpose — that block
         * swallows exceptions, and a skipped Blade directive would blow up
         * every admin view that uses it.
         *
         * A plain Gate would not work here: Gate resolves the user from the
         * default ('web') guard, while the panel authenticates on the
         * 'admin' guard, so @can would always see a null/User instead of an
         * Admin. Hence a dedicated directive.
         */
        Blade::if('adminCan', function (string $permission) {
            $admin = auth()->guard('admin')->user();

            return $admin ? $admin->hasPermission($permission) : false;
        });

        Blade::if('adminCanAny', function (...$permissions) {
            $admin = auth()->guard('admin')->user();

            if (!$admin) {
                return false;
            }

            foreach (Arr::flatten($permissions) as $permission) {
                if ($admin->hasPermission($permission)) {
                    return true;
                }
            }

            return false;
        });

        Paginator::useBootstrap();
    }

    /**
     * Rotational banners for the search-result pages: whichever live banners
     * were bought for the city (and area) the visitor is looking at.
     */
    private function shareAdBanners(): void
    {
        \Illuminate\Support\Facades\View::composer(
            ['themes.*.listings.all', 'themes.*.listings.cat_loc'],
            function ($view) {
                $data = $view->getData();

                $place = $data['locationText'] ?? $data['selected_location'] ?? '';
                $parts = array_values(array_filter(array_map('trim', explode(',', (string) $place))));

                if (!$parts) {
                    $view->with('adBannerSlides', collect());
                    return;
                }

                $city = end($parts);
                $area = count($parts) > 1 ? $parts[0] : null;

                // Only ads from the category being browsed — a hotel's banner
                // has no business on an IT Consultants page.
                $categoryId = optional($data['selectedCategory'] ?? null)->id;

                if (!$categoryId) {
                    $view->with('adBannerSlides', collect());
                    return;
                }

                $slides = \App\Models\ListingAdBanner::query()
                    ->where('category_id', $categoryId)
                    ->where('status', \App\Models\ListingAdBanner::LIVE)
                    ->whereNotNull('image')
                    ->where('starts_at', '<=', now())
                    ->where('expires_at', '>', now())
                    ->where('city', $city)
                    ->when($area, fn ($q) => $q->where(fn ($w) => $w->where('area', $area)->orWhere('area', '')->orWhereNull('area')))
                    ->with('listing')
                    ->inRandomOrder()
                    ->limit(5)
                    ->get();

                $view->with('adBannerSlides', $slides);
            }
        );
    }
}

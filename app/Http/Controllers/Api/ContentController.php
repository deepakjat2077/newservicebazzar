<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Service;
use App\Models\Setting;
use App\Models\ShowcaseListing;
use App\Models\SuccessStory;
use App\Models\Theme;
use Illuminate\Http\Request;

/**
 * Read-only content for the mobile app: the News feed (which shows the site's
 * blog posts — the user confirmed "News me blogs aayenge") and the list of
 * premium plans (Package rows).
 *
 * Blogs live in the CMS `themes` table (name = 'blog', is_multiple = 1) with
 * their fields under a JSON `content` object, exactly as the website reads them
 * in SiteController@blogs / @blogDetails. The news/blog routes are fully public
 * (no token) — the content is identical for every caller.
 */
class ContentController extends Controller
{
    protected function themeName(): string
    {
        return optional(Setting::first())->theme ?? 'servicebazzar';
    }

    /** News feed = blog posts, newest first, paginated. */
    public function news(Request $request)
    {
        $posts = Theme::where('theme_name', $this->themeName())
            ->where('name', 'blog')->where('is_multiple', 1)
            ->orderByDesc('id')->paginate(12);

        return response()->json([
            'success' => true,
            'news'    => collect($posts->items())->map(fn ($b) => $this->blogCard($b))->values(),
            'meta'    => [
                'current_page' => $posts->currentPage(),
                'last_page'    => $posts->lastPage(),
                'total'        => $posts->total(),
            ],
        ]);
    }

    /** A single news / blog post by its slug. */
    public function newsDetail(string $slug)
    {
        $post = Theme::where('theme_name', $this->themeName())
            ->where('name', 'blog')->where('is_multiple', 1)
            ->get()
            ->first(fn ($item) => isset($item->content->slug) && $item->content->slug === $slug);

        abort_unless($post, 404);

        $post->view = ($post->view ?? 0) + 1;
        $post->save();

        $recent = Theme::where('theme_name', $this->themeName())
            ->where('name', 'blog')->where('is_multiple', 1)
            ->where('id', '!=', $post->id)
            ->orderByDesc('id')->limit(5)->get();

        return response()->json([
            'success' => true,
            'post'    => array_merge($this->blogCard($post), [
                'content'          => optional($post->content)->content,
                'meta_title'       => optional($post->content)->meta_title,
                'meta_description' => optional($post->content)->meta_description,
                'views'            => (int) ($post->view ?? 0),
            ]),
            'recent'  => $recent->map(fn ($b) => $this->blogCard($b))->values(),
        ]);
    }

    /**
     * The "Get Listed / Grow Your Business" screen: the admin-picked showcase
     * listings and the client success stories, both active and in their chosen
     * order. Managed from Admin > Get Listed Showcase and Admin > Success
     * Stories (ShowcaseController / SuccessStoryController).
     */
    public function getListed()
    {
        $featured = ShowcaseListing::with('listing.category', 'listing.location')
            ->where('status', 1)
            ->orderBy('sort_order')->orderByDesc('id')
            ->get()
            // Drop rows whose listing was deleted or is no longer active.
            ->filter(fn ($s) => $s->listing && (int) $s->listing->status === 1)
            ->map(fn ($s) => $this->showcaseCard($s->listing))
            ->values();

        $stories = SuccessStory::where('status', 1)
            ->orderBy('sort_order')->orderByDesc('id')->get()
            ->map(fn ($s) => [
                'id'            => $s->id,
                'client_name'   => $s->client_name,
                'business_name' => $s->business_name,
                'rating'        => $s->rating !== null ? (float) $s->rating : null,
                'story'         => $s->story,
                'image'         => $s->image ? asset('assets/images/success-stories/' . $s->image) : null,
            ])->values();

        return response()->json([
            'success'           => true,
            'featured_listings' => $featured,
            'success_stories'   => $stories,
        ]);
    }

    /** Compact business card for the Get Listed showcase (no favourite state). */
    protected function showcaseCard($l): array
    {
        return [
            'id'            => $l->id,
            'name'          => $l->name,
            'slug'          => $l->slug,
            'category'      => optional($l->category)->name,
            'category_slug' => optional($l->category)->slug,
            'area'          => $l->area ?: optional($l->location)->name,
            'city'          => $l->city,
            'thumb'         => $l->thumb ? loadImage('assets/images/listing/thumb/' . $l->thumb) : loadImage('', false),
            'avg_rating'    => round((float) $l->review, 1),
            'verified'      => (bool) $l->verified,
            'trust'         => (bool) $l->trust,
        ];
    }

    /** Premium / advertise plans — the Package rows, as-is. */
    public function plans()
    {
        return response()->json([
            'success' => true,
            'plans'   => Package::all()->map(fn ($p) => $p->toArray())->values(),
        ]);
    }

    /**
     * The homepage banner carousel, exactly as the admin's Banner Section
     * builds it (Theme row name = 'banner', is_multiple = 0).
     *
     * The images are stored as a JSON array under content->image for desktop
     * and content->mobile_banner for phones. Each entry is either
     * {"image": "...", "link": "..."} or, in older rows, a bare filename with
     * no link — both shapes are normalised here so the app only ever sees the
     * first one. Absolute URLs are returned, WebP where a twin exists.
     */
    public function banners()
    {
        $banner = Theme::where('theme_name', $this->themeName())
            ->where('name', 'banner')
            ->where('is_multiple', 0)
            ->first();

        $content = optional($banner)->content;

        return response()->json([
            'success' => true,
            'banner'  => [
                'title'     => optional($content)->title,
                'sub_title' => optional($content)->sub_title,
                'desktop'   => $this->bannerSlides(optional($content)->image),
                'mobile'    => $this->bannerSlides(optional($content)->mobile_banner),
            ],
        ]);
    }

    /** Normalises one of the two JSON banner columns into {image, link} rows. */
    protected function bannerSlides($raw): array
    {
        if (empty($raw)) {
            return [];
        }

        $rows = json_decode($raw, true);

        if (!is_array($rows)) {
            return [];
        }

        $slides = [];

        foreach ($rows as $row) {
            $file = is_array($row) ? ($row['image'] ?? '') : $row;
            $link = is_array($row) ? ($row['link'] ?? '') : '';

            if ($file === '' || $file === null) {
                continue;
            }

            $path = 'assets/servicebazzar/images/banner/'.$file;

            $slides[] = [
                'image' => loadImageWebp($path),
                'link'  => $link ?: null,
            ];
        }

        return $slides;
    }

    /*
    |--------------------------------------------------------------------------
    | Services — the pages behind the website's SERVICES menu
    |--------------------------------------------------------------------------
    */

    /** Every live service, in menu order, as summary cards. */
    public function services()
    {
        $services = Service::where('status', 1)->orderBy('id')->get();

        return response()->json([
            'success'  => true,
            'services' => $services->map(fn ($s) => $this->serviceCard($s))->values(),
        ]);
    }

    /**
     * One service page in full: every section the website renders, so the app
     * can lay the page out without scraping HTML.
     */
    public function serviceDetail(string $slug)
    {
        $service = Service::where('slug', $slug)->where('status', 1)->firstOrFail();

        return response()->json([
            'success' => true,
            'service' => array_merge($this->serviceCard($service), [
                'description' => $service->description,

                'hero' => [
                    'title' => $service->hero_title,
                    'image' => $this->serviceImage($service->hero_image),
                ],
                'intro' => [
                    'content' => $service->intro_content,
                    'image'   => $this->serviceImage($service->intro_image),
                ],
                'trusted' => [
                    'title'       => $service->trusted_title,
                    'description' => $service->trusted_description,
                    'image'       => $this->serviceImage($service->trusted_image),
                ],

                'cards_title'   => $service->services_section_title,
                'cards'         => $this->serviceRows($service->cards, ['title', 'description', 'image', 'icon']),

                'why_choose'    => [
                    'title' => $service->why_choose_title,
                    'image' => $this->serviceImage($service->why_choose_image),
                    'items' => $this->serviceRows($service->features, ['title', 'description', 'image', 'icon']),
                ],

                'process' => [
                    'title' => $service->process_title,
                    'steps' => $this->serviceRows($service->process_steps, ['number', 'title', 'description']),
                ],

                'partner' => [
                    'title'  => $service->partner_title,
                    'image'  => $this->serviceImage($service->partner_image),
                    // Stored as one textarea, one point per line.
                    'points' => $this->serviceLines($service->partner_points),
                ],

                'faq_title' => $service->faq_title,
                'faqs'      => $this->serviceRows($service->faqs, ['question', 'answer']),

                'cta' => [
                    'title'       => $service->cta_title,
                    'description' => $service->cta_description,
                    'button_text' => $service->cta_button_text,
                    'button_link' => $service->cta_button_link,
                    'image'       => $this->serviceImage($service->cta_image),
                ],

                'seo' => [
                    'title'       => $service->meta_title ?: $service->seo_title,
                    'description' => $service->meta_description ?: $service->seo_description,
                    'keywords'    => $service->meta_keywords,
                    'image'       => $this->serviceImage($service->seo_image ?: $service->hero_image),
                ],
            ]),
        ]);
    }

    /** The fields every services list needs. */
    protected function serviceCard(Service $s): array
    {
        return [
            'id'                => $s->id,
            'title'             => $s->title,
            // What the website prints in the SERVICES dropdown.
            'menu_title'        => $s->menu_title ?: $s->title,
            'slug'              => $s->slug,
            'short_description' => $s->short_description,
            'image'             => $this->serviceImage($s->hero_image),
            'show_in_menu'      => (bool) $s->show_in_menu,
            'url'               => url('services/' . $s->slug),
        ];
    }

    /** Service images are stored as a path under public/, or not at all. */
    protected function serviceImage(?string $path): ?string
    {
        return filled($path) ? asset($path) : null;
    }

    /**
     * A repeater field, trimmed down to the keys the app needs. Rows are kept
     * only when something in them is filled, so a half-saved blank row in the
     * admin does not reach the app as an empty card.
     */
    protected function serviceRows($rows, array $keys): array
    {
        return collect(is_array($rows) ? $rows : [])
            ->map(function ($row) use ($keys) {
                $row = (array) $row;
                $out = [];

                foreach ($keys as $key) {
                    $value = isset($row[$key]) && is_scalar($row[$key]) ? trim((string) $row[$key]) : '';
                    $out[$key] = in_array($key, ['image', 'icon'], true) && $value !== ''
                        ? $this->serviceImage($value)
                        : $value;
                }

                return $out;
            })
            ->filter(fn ($row) => collect($row)->filter()->isNotEmpty())
            ->values()
            ->all();
    }

    /** A textarea holding one item per line. */
    protected function serviceLines(?string $text): array
    {
        if (blank($text)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }

    protected function blogCard(Theme $b): array
    {
        $c = $b->content;

        return [
            'id'        => $b->id,
            'title'     => optional($c)->title,
            'sub_title' => optional($c)->sub_title,
            'slug'      => optional($c)->slug,
            'image'     => optional($c)->image ? loadImageWebp('assets/servicebazzar/images/blog/' . $c->image) : null,
            'views'     => (int) ($b->view ?? 0),
            'created_at'=> optional($b->created_at)->toIso8601String(),
        ];
    }
}

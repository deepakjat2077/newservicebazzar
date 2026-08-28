<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Theme;
use Illuminate\Http\Request;

/**
 * Read-only content for the mobile app: the News feed (which shows the site's
 * blog posts — the user confirmed "News me blogs aayenge") and the list of
 * premium plans (Package rows).
 *
 * Blogs live in the CMS `themes` table (name = 'blog', is_multiple = 1) with
 * their fields under a JSON `content` object, exactly as the website reads them
 * in SiteController@blogs / @blogDetails. Behind `user.api` like the rest.
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

    /** Premium / advertise plans — the Package rows, as-is. */
    public function plans()
    {
        return response()->json([
            'success' => true,
            'plans'   => Package::all()->map(fn ($p) => $p->toArray())->values(),
        ]);
    }

    protected function blogCard(Theme $b): array
    {
        $c = $b->content;

        return [
            'id'        => $b->id,
            'title'     => optional($c)->title,
            'sub_title' => optional($c)->sub_title,
            'slug'      => optional($c)->slug,
            'image'     => optional($c)->image ? asset($c->image) : null,
            'views'     => (int) ($b->view ?? 0),
            'created_at'=> optional($b->created_at)->toIso8601String(),
        ];
    }
}

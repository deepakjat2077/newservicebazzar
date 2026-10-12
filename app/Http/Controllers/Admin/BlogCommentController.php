<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/** Visitor comments on blog posts: list and delete (they publish straight away). */
class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $q = BlogComment::query()->latest('id');

        if ($s = trim((string) $request->query('search'))) {
            $q->where(fn ($w) => $w->where('name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%")
                ->orWhere('comment', 'like', "%{$s}%"));
        }

        $comments = $q->paginate(30)->withQueryString();
        $blogs = $this->blogsById($comments->pluck('blog_id')->unique()->all());

        return view('admin.blog_comments.index', compact('comments', 'blogs'));
    }

    public function destroy($id)
    {
        $comment = BlogComment::findOrFail($id);
        $slug = $this->blogsById([$comment->blog_id])[$comment->blog_id]['slug'] ?? null;
        $comment->delete();

        // the post's cached HTML still lists it until forgotten
        if ($slug) {
            Cache::store(config('cache.public_pages.store'))
                ->forget('page:' . sha1(request()->getHost() . '|blog/' . $slug));
        }

        $notify[] = ['success', 'Comment deleted'];
        return back()->withNotify($notify);
    }

    /** blog id => [title, slug] for the blog posts (frontends rows named "blog"). */
    protected function blogsById(array $ids): array
    {
        if (!$ids) {
            return [];
        }

        return \App\Models\Theme::whereIn('id', $ids)->get()->mapWithKeys(function ($b) {
            $c = is_string($b->content) ? json_decode($b->content) : $b->content;
            return [$b->id => ['title' => $c->title ?? ('Blog #' . $b->id), 'slug' => $c->slug ?? null]];
        })->all();
    }
}

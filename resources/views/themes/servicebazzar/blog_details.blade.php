@extends($themeView.'layouts.site')

@php
    $seoTitle = $blog->content->meta_title ?? $blog->content->title ?? 'Blog Details';
    $seoDescription = $blog->content->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->content->content ?? ''), 160);
    $seoKeywords = $blog->content->meta_keywords ?? '';
    $seoImage = !empty($blog->content->image) ? loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) : asset('assets/images/logo/favicon_new.png');
    $seoUrl = url()->current();
    $seoType = 'article';

    /*
     * Byline + Article schema ka data.
     *
     * Published date `created_at` se aata hai — wo blog row banne par ek
     * baar set hota hai aur view counter use nahi chhuta.
     *
     * Modified date `content_updated_at` se aata hai (Template::elementSave()
     * me admin ke save par likha jata hai). `updated_at` par fallback isliye
     * hai ki jo blogs is field ke aane se pehle ke hain unme ye key nahi hai;
     * un par purana updated_at hi best available signal hai.
     */
    $blogPublishedAt = $blog->created_at;

    $blogModifiedAt = !empty($blog->content->content_updated_at)
        ? \Illuminate\Support\Carbon::parse($blog->content->content_updated_at)
        : $blog->updated_at;

    // Modified date kabhi published se pehle na dikhe.
    if ($blogPublishedAt && $blogModifiedAt && $blogModifiedAt->lt($blogPublishedAt)) {
        $blogModifiedAt = $blogPublishedAt;
    }

    $blogAuthorName = trim($blog->content->author_name ?? '');
    $blogAuthorRole = trim($blog->content->author_designation ?? '');

    // Author naam na ho to schema me publisher (site) hi author banta hai —
    // Google ke liye ye khaali/fake Person naam se behtar hai.
    $blogAuthorSchema = $blogAuthorName !== ''
        ? array_filter([
            '@type'    => 'Person',
            'name'     => $blogAuthorName,
            'jobTitle' => $blogAuthorRole !== '' ? $blogAuthorRole : null,
        ])
        : [
            '@type' => 'Organization',
            'name'  => $setting->site_name,
            'url'   => url('/'),
        ];

    $blogPublisherLogo = !empty($setting->seo->image)
        ? asset('assets/images/seo/' . $setting->seo->image)
        : asset('assets/images/logo/logo.png');

    $blogArticleSchema = array_filter([
        '@context'         => 'https://schema.org',
        '@type'            => 'BlogPosting',
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $seoUrl],
        // Google headline ko 110 characters par cut karta hai.
        'headline'         => \Illuminate\Support\Str::limit($blog->content->title ?? '', 110, ''),
        'description'      => $seoDescription,
        'image'            => $seoImage ? [$seoImage] : null,
        'datePublished'    => $blogPublishedAt ? $blogPublishedAt->toIso8601String() : null,
        'dateModified'     => $blogModifiedAt ? $blogModifiedAt->toIso8601String() : null,
        'author'           => $blogAuthorSchema,
        'publisher'        => [
            '@type' => 'Organization',
            'name'  => $setting->site_name,
            'logo'  => ['@type' => 'ImageObject', 'url' => $blogPublisherLogo],
        ],
    ]);
@endphp

@section('content')
@include($themeView.'includes.rich-content')

{{-- BlogPosting structured data. Page par $seoType pehle se 'article' tha,
     par koi Article JSON-LD emit hi nahi hota tha — yaani Google ko author,
     publish date ya update date ka koi structured signal nahi milta tha.
     str_ireplace wahi guard hai jo includes/seo.blade.php use karta hai,
     taaki content ke andar ka "</script" tag block ko todh na sake. --}}
<script type="application/ld+json">
{!! str_ireplace('</script', '<\/script', json_encode($blogArticleSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)) !!}
</script>

<style>
    h1 {
    font-size: 1.5rem;
}

/* Author byline — heading ke neeche, content se pehle. */
.blog-byline{
    display:flex;
    align-items:center;
    flex-wrap:wrap;
    gap:8px;
    margin:10px 0 18px;
    padding-bottom:14px;
    border-bottom:1px solid #ececec;
    font-size:14px;
    color:#555;
    line-height:1.5;
}

.blog-byline .byline-author{
    color:#111;
    font-weight:600;
}

.blog-byline .byline-role{
    color:#666;
    font-weight:400;
}

.blog-byline .byline-sep{
    color:#c4c4c4;
}

.blog-byline time{
    color:#555;
}

.blog-byline .byline-updated{
    color:#0d6efd;
    font-weight:500;
}

@media (max-width:767px){
    .blog-byline{
        font-size:13px;
        gap:6px;
    }
}
</style>
<section class="blog-details pt-80 pb-80">
    <div class="container">
        <div class="row mb__30">
            <div class="col-lg-8">
                <div class="blog-details-area">
                    <div class="thumb">
                        <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$blog->content->image) }}" alt="{{ $blog->content->title }} Blog" fetchpriority="high">
                    </div>
                    <div class="details">
                       
                        <h1>{{ __($blog->content->title) }}</h1>

                        {{-- Author byline + dates. Ye wahi values dikhata hai
                             jo upar BlogPosting schema me jaati hain, taaki
                             visible content aur structured data match karein. --}}
                        <div class="blog-byline">
                            @if($blogAuthorName !== '')
                                <span>
                                    Written by
                                    <span class="byline-author">{{ $blogAuthorName }}</span>@if($blogAuthorRole !== ''), <span class="byline-role">{{ $blogAuthorRole }}</span>@endif
                                </span>
                            @else
                                <span>
                                    Published by
                                    <span class="byline-author">{{ $setting->site_name }}</span>
                                </span>
                            @endif

                            @if($blogPublishedAt)
                                <span class="byline-sep">|</span>
                                <time datetime="{{ $blogPublishedAt->toDateString() }}">
                                    {{ $blogPublishedAt->format('d M Y') }}
                                </time>
                            @endif

                            {{-- "Updated" tabhi dikhta hai jab wo publish date se
                                 alag din ka ho — warna har naye blog par
                                 "Published X | Updated X" do baar same date
                                 dikhata, jo freshness signal ko kamzor karta hai. --}}
                            @if($blogPublishedAt && $blogModifiedAt && !$blogModifiedAt->isSameDay($blogPublishedAt))
                                <span class="byline-sep">|</span>
                                <time class="byline-updated" datetime="{{ $blogModifiedAt->toDateString() }}">
                                    Updated {{ $blogModifiedAt->format('d M Y') }}
                                </time>
                            @endif
                        </div>

                        {{--
                            Blog content bhi Word se paste hota hai, isliye
                            category pages jaisa hi normalise hota hai — warna
                            har blog apna font-size aur apna spacing leke aata
                            hai. Tables ko .rich-content-table wrapper milta
                            hai, jisse mobile par aakhri column cut hone ke
                            bajaye scroll karke dikhta hai.
                        --}}
                        @php
                            $toc = \App\Support\BlogToc::build(\App\Support\ContentHtml::cleanCached($blog->content->content ?? ''));
                            $tocCount = collect($toc['items'])->sum(fn ($i) => 1 + (count($i['children']) > 1 ? count($i['children']) : 0));
                        @endphp

                        {{-- Built from the post's own h2/h3 headings; each has an id, so
                             /blog/slug#Heading-Text links straight to that section. --}}
                        @if($tocCount >= 2)
                            <nav class="sb-toc" aria-label="Table of Content">
                                <p class="sb-toc-title">Table of Content</p>
                                <ul class="sb-toc-list">
                                    @foreach($toc['items'] as $item)
                                        <li>
                                            <a href="#{{ $item['id'] }}">{{ $item['text'] }}</a>
                                            {{-- a lone sub-heading isn't listed; 2+ become a numbered list --}}
                                            @if(count($item['children']) > 1)
                                                <ol class="sb-toc-sub">
                                                    @foreach($item['children'] as $child)
                                                        <li><a href="#{{ $child['id'] }}">{{ preg_replace('/^\s*\d+\s*[.)]\s*/u', '', $child['text']) }}</a></li>
                                                    @endforeach
                                                </ol>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        @endif

                        <div class="rich-content">
                            {!! $toc['html'] !!}
                        </div>
                    </div>
                </div>

                {{-- Comments: anyone can post, no login. --}}
                <div class="sb-comments" id="comments">
                    @if($comments->count())
                        <h2 class="sb-comments-title">{{ $comments->count() }} {{ $comments->count() === 1 ? 'Comment' : 'Comments' }}</h2>
                    @endif
                    <div class="sb-comment-list" id="sbCommentList">
                        @foreach($comments as $c)
                            @include('themes.servicebazzar.partials.blog_comment', ['c' => $c])
                        @endforeach
                    </div>

                    <h2 class="sb-comments-title">Leave a Reply</h2>
                    <p class="sb-comments-note">Your email address will not be published. Required fields are marked *</p>
                    <form id="sbCommentForm" class="sb-comment-form" method="POST" action="{{ route('blog.comment', $blog->content->slug) }}" novalidate>
                        @csrf
                        @include('themes.servicebazzar.partials.form-guard')
                        <textarea name="comment" rows="6" placeholder="Comment:*" maxlength="2000" required></textarea>
                        <input type="text" name="name" placeholder="Name:*" maxlength="100" autocomplete="name" required>
                        <input type="email" name="email" placeholder="Email:*" maxlength="191" autocomplete="email" required>
                        <input type="text" name="website" placeholder="Website:" maxlength="255" autocomplete="url">
                        <label class="sb-comment-save">
                            <input type="checkbox" id="sbCommentRemember">
                            <span>Save my name, email, and website in this browser for the next time I comment.</span>
                        </label>
                        <div class="sb-comment-msg" id="sbCommentMsg" role="status" aria-live="polite"></div>
                        <button type="submit" class="sb-comment-submit">Post Comment</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="blog-widget">
                    <h6 class="widget-title">@lang('Recent Blog')</h6>
                    <div class="widget-body">
                        @foreach($recent_blogs as $recent)
                        <div class="single-blog-area">
                            <div class="blog-thumb-area">
                                <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$recent->content->image) }}" alt="{{ $recent->content->title }} Blog" loading="lazy">
                            </div>
                            <div class="blog-title-area">
                               <a href="{{ route('blog.details', $recent->content->slug) }}">
    {{ __(Str::limit($recent->content->title,40)) }}
</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="blog-widget sb-sticky-widget">
                    <h6 class="widget-title">@lang('Popular Blog')</h6>
                    <div class="widget-body">
                        @foreach($popular_blogs as $popular)
                        <div class="single-blog-area">
                            <div class="blog-thumb-area">
                                <img src="{{ loadImage('assets/servicebazzar/images/blog/'.$popular->content->image) }}"      loading="lazy" alt="{{ $popular->content->title }} Blog">
                            </div>
                            <div class="blog-title-area">
                                <a href="{{ route('blog.details', $popular->content->slug) }}">{{ __(Str::limit($popular->content->title,40)) }}</a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<style>
/* Desktop: "Popular Blog" follows the reader down the post, below the sticky header. */
@media (min-width: 992px) {
    /* body{overflow-x:hidden} turns <body> into its own scroll box, and sticky
       then never engages. clip still blocks sideways scrolling without that. */
    body { overflow-x: clip !important; overflow-y: visible !important; }
    .blog-details .row.mb__30 > .col-lg-4 { align-self: stretch; }
    .blog-details .sb-sticky-widget { position: sticky; top: 72px; }
}
.blog-details-area .thumb img, .blog-details-area .rich-content img[src] { cursor: zoom-in; }
.sb-lb { position: fixed; inset: 0; z-index: 100000; background: rgba(0,0,0,.82); display: none; align-items: center; justify-content: center; }
.sb-lb.open { display: flex; }
.sb-lb-img { max-width: min(92vw, 1100px); max-height: 86vh; object-fit: contain; background: #fff; box-shadow: 0 10px 40px rgba(0,0,0,.5); user-select: none; }
.sb-lb-btn { position: absolute; background: transparent; border: 0; color: #fff; cursor: pointer; line-height: 1; padding: 10px; opacity: .9; }
.sb-lb-btn:hover { opacity: 1; }
.sb-lb-close { top: 14px; right: 18px; font-size: 38px; }
.sb-lb-prev, .sb-lb-next { top: 50%; transform: translateY(-50%); font-size: 46px; }
.sb-lb-prev { left: 14px; } .sb-lb-next { right: 14px; }
.sb-lb-count { position: absolute; bottom: 16px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 13px; background: rgba(0,0,0,.45); padding: 3px 10px; border-radius: 10px; }
.sb-lb.single .sb-lb-prev, .sb-lb.single .sb-lb-next, .sb-lb.single .sb-lb-count { display: none; }
@media (max-width: 767px) { .sb-lb-img { max-width: 100vw; max-height: 80vh; } .sb-lb-prev, .sb-lb-next { font-size: 36px; } }
.sb-toc { margin: 22px 0 26px; }
.sb-toc-title { font-size: 22px; font-weight: 500; color: #111; margin: 0 0 12px; }
.sb-toc-list { margin: 0; padding-left: 20px; list-style: disc; }
.sb-toc-list > li { margin: 0 0 10px; }
.sb-toc-sub { margin: 10px 0 0; padding-left: 22px; }
ol.sb-toc-sub { list-style: decimal; }
ul.sb-toc-sub { list-style: circle; }
.sb-toc-sub li { margin: 0 0 10px; color: #333; }
.sb-toc a { color: #0b66d6; text-decoration: none; font-size: 15px; }
.sb-toc a:hover { text-decoration: underline; }
/* land below the sticky header when a section is opened from the list or a link */
.rich-content h2[id], .rich-content h3[id] { scroll-margin-top: 110px; }
html { scroll-behavior: smooth; }
/* images added from the editor */
.rich-content img { max-width: 100%; height: auto; display: block; margin: 14px auto; border-radius: 6px; }
.rich-content img:not([src]) { display: none; }
.sb-comments { margin-top: 36px; }
.sb-comments-title { font-size: 22px; font-weight: 700; color: #111; margin: 26px 0 12px; text-transform: uppercase; letter-spacing: .2px; }
.sb-comments-note { font-size: 13px; color: #6b7280; margin: -4px 0 14px; }
.sb-comment { display: flex; gap: 18px; padding: 22px 0; border-bottom: 1px dashed #e5e7eb; }
.sb-comment-avatar { flex: 0 0 48px; width: 48px; height: 48px; overflow: hidden; }
.sb-comment-avatar svg { display: block; }
.sb-comment-body { min-width: 0; flex: 1; }
.sb-comment-head { display: flex; flex-wrap: wrap; align-items: baseline; gap: 12px; margin-bottom: 6px; }
.sb-comment-name { font-weight: 700; font-size: 15px; color: #111; }
a.sb-comment-name { color: #111; text-decoration: none; }
a.sb-comment-name:hover { color: #0b66d6; }
.sb-comment-date { font-size: 12px; color: #8a8f98; }
.sb-comment-text { font-size: 15px; line-height: 1.65; color: #222; overflow-wrap: anywhere; }
.sb-comment-new { animation: sbCommentIn 2.4s ease-out; }
@keyframes sbCommentIn { 0%,30% { background: #fff8e1; } 100% { background: transparent; } }
.sb-comment-form textarea, .sb-comment-form input[type=text], .sb-comment-form input[type=email] {
    display: block; width: 100%; margin-bottom: 16px; padding: 14px 18px; font-size: 15px;
    background: #f8f8f8; border: 1px solid #e5e7eb; border-radius: 10px; outline: none; transition: border-color .15s, background .15s;
}
.sb-comment-form textarea { min-height: 150px; resize: vertical; }
.sb-comment-form textarea:focus, .sb-comment-form input:focus { border-color: #0b66d6; background: #fff; }
.sb-comment-form .is-invalid { border-color: #dc3545 !important; }
.sb-comment-save { display: flex; gap: 10px; align-items: flex-start; font-size: 14px; color: #222; margin: 2px 0 14px; cursor: pointer; }
.sb-comment-save input { margin-top: 4px; }
.sb-comment-msg { font-size: 14px; margin-bottom: 12px; min-height: 0; }
.sb-comment-msg.ok { color: #157347; }
.sb-comment-msg.err { color: #dc3545; }
.sb-comment-submit { background: #0b66d6; color: #fff; border: 0; border-radius: 10px; padding: 12px 24px; font-size: 15px; font-weight: 600; cursor: pointer; }
.sb-comment-submit:disabled { opacity: .6; cursor: default; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('sbCommentForm');
    if (!form) return;
    var msg = document.getElementById('sbCommentMsg');
    var remember = document.getElementById('sbCommentRemember');
    var KEY = 'sb_comment_author';
    var fields = ['name', 'email', 'website'];

    // "Save my name, email, and website in this browser"
    try {
        var saved = JSON.parse(localStorage.getItem(KEY) || 'null');
        if (saved) {
            fields.forEach(function (f) { if (saved[f]) form.elements[f].value = saved[f]; });
            remember.checked = true;
        }
    } catch (e) {}

    function say(text, ok) { msg.textContent = text; msg.className = 'sb-comment-msg ' + (ok ? 'ok' : 'err'); }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });

        var missing = ['comment', 'name', 'email'].filter(function (f) { return !form.elements[f].value.trim(); });
        if (missing.length) {
            missing.forEach(function (f) { form.elements[f].classList.add('is-invalid'); });
            form.elements[missing[0]].focus();
            return say('Please fill in the required fields (*).', false);
        }

        var btn = form.querySelector('.sb-comment-submit');
        btn.disabled = true; say('Posting…', true);

        fetch(form.action, {
            method: 'POST',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form),
            credentials: 'same-origin'
        }).then(function (r) {
            return r.json().catch(function () { return {}; }).then(function (d) { return { status: r.status, d: d }; });
        }).then(function (res) {
            btn.disabled = false;
            var d = res.d || {};
            if (res.status === 429) return say('Too many comments in a short time. Please try again in a minute.', false);
            if (res.status === 419) return say('This page was open too long. Please refresh and try again.', false);
            if (!d.ok) {
                var errs = d.errors || {};
                Object.keys(errs).forEach(function (f) { if (form.elements[f]) form.elements[f].classList.add('is-invalid'); });
                var first = Object.keys(errs)[0];
                return say(first ? errs[first][0] : (d.message || 'Could not post your comment. Please try again.'), false);
            }

            try {
                if (remember.checked) {
                    var keep = {}; fields.forEach(function (f) { keep[f] = form.elements[f].value.trim(); });
                    localStorage.setItem(KEY, JSON.stringify(keep));
                } else {
                    localStorage.removeItem(KEY);
                }
            } catch (e) {}

            if (d.html) {
                var list = document.getElementById('sbCommentList');
                var wrap = document.createElement('div'); wrap.innerHTML = d.html.trim();
                var card = wrap.firstElementChild; card.classList.add('sb-comment-new');
                list.appendChild(card);
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            form.elements.comment.value = '';
            say(d.message || 'Thank you! Your comment has been posted.', true);
        }).catch(function () {
            btn.disabled = false;
            say('Network error. Please check your connection and try again.', false);
        });
    });
});
</script>

<div class="sb-lb" id="sbLightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
    <button type="button" class="sb-lb-btn sb-lb-close" aria-label="Close">&times;</button>
    <button type="button" class="sb-lb-btn sb-lb-prev" aria-label="Previous image">&#10094;</button>
    <img class="sb-lb-img" alt="">
    <button type="button" class="sb-lb-btn sb-lb-next" aria-label="Next image">&#10095;</button>
    <span class="sb-lb-count"></span>
</div>
<script>
/* Blog image viewer: the banner and every picture in the post open large,
   with arrows, a counter, keyboard (← → Esc) and swipe. */
document.addEventListener('DOMContentLoaded', function () {
    var lb = document.getElementById('sbLightbox');
    if (!lb) return;
    var imgs = Array.prototype.slice.call(document.querySelectorAll('.blog-details-area .thumb img, .blog-details-area .rich-content img[src]'));
    if (!imgs.length) return;
    var big = lb.querySelector('.sb-lb-img'), count = lb.querySelector('.sb-lb-count'), i = 0;

    function show(n) {
        i = (n + imgs.length) % imgs.length;
        big.src = imgs[i].currentSrc || imgs[i].src;
        big.alt = imgs[i].alt || '';
        count.textContent = (i + 1) + ' of ' + imgs.length;
    }
    function open(n) {
        lb.classList.toggle('single', imgs.length < 2);
        show(n); lb.classList.add('open');
        document.documentElement.style.overflow = 'hidden';
    }
    function close() { lb.classList.remove('open'); document.documentElement.style.overflow = ''; }

    imgs.forEach(function (im, n) {
        im.addEventListener('click', function (e) {
            if (im.closest('a')) e.preventDefault();
            open(n);
        });
    });
    lb.querySelector('.sb-lb-close').addEventListener('click', close);
    lb.querySelector('.sb-lb-prev').addEventListener('click', function (e) { e.stopPropagation(); show(i - 1); });
    lb.querySelector('.sb-lb-next').addEventListener('click', function (e) { e.stopPropagation(); show(i + 1); });
    lb.addEventListener('click', function (e) { if (e.target === lb) close(); });
    document.addEventListener('keydown', function (e) {
        if (!lb.classList.contains('open')) return;
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowLeft') show(i - 1);
        else if (e.key === 'ArrowRight') show(i + 1);
    });
    var x0 = null;
    lb.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; }, { passive: true });
    lb.addEventListener('touchend', function (e) {
        if (x0 === null) return;
        var dx = e.changedTouches[0].clientX - x0; x0 = null;
        if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1));
    });
});
</script>
@endsection
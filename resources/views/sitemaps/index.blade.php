{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($sitemaps as $sm)
    <sitemap>
        <loc>{{ $sm['loc'] }}</loc>
        @if(!empty($sm['lastmod']))
        <lastmod>{{ $sm['lastmod'] }}</lastmod>
        @endif
    </sitemap>
@endforeach
</sitemapindex>

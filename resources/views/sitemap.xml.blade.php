{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($urls as $url)
    <url>
        <loc>{{ $url['loc'] }}</loc>
        <lastmod>{{ ($url['lastmod'] ?? now())->toAtomString() }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>{{ str_ends_with($url['loc'], '/') || $url['loc'] === url('/') ? '1.0' : '0.8' }}</priority>
    </url>
@endforeach
</urlset>

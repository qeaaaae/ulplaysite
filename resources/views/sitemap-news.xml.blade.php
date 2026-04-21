{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:news="http://www.google.com/schemas/sitemap-news/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($newsItems as $item)
    @php
        $published = $item->published_at ?? now();
        $coverImage = $item->image;
    @endphp
    <url>
        <loc>{{ route('news.show', $item->slug) }}</loc>
        <lastmod>{{ ($item->updated_at ?? $published)->toAtomString() }}</lastmod>
        <news:news>
            <news:publication>
                <news:name>{{ $siteName }}</news:name>
                <news:language>ru</news:language>
            </news:publication>
            <news:publication_date>{{ $published->toAtomString() }}</news:publication_date>
            <news:title><![CDATA[{{ $item->title }}]]></news:title>
        </news:news>
        @if($coverImage)
            <image:image>
                <image:loc>{{ $coverImage }}</image:loc>
            </image:image>
        @endif
    </url>
@endforeach
</urlset>

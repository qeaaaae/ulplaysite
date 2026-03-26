@props([
    'news',
])

@if($news->isEmpty())
    <p class="text-stone-500 py-12 text-center">Новости пока не добавлены</p>
@else
    <div id="news-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 md:gap-6">
        @foreach($news as $item)
            @include('components.news-card', ['item' => $item])
        @endforeach
    </div>
    @if($news->hasMorePages())
        <div
            id="news-infinite-sentinel"
            data-next-url="{{ $news->nextPageUrl() }}"
            class="h-1 w-full shrink-0 pointer-events-none"
            aria-hidden="true"
        ></div>
    @endif
@endif

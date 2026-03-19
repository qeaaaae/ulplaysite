@props([
    'news',
])

@if($news->isEmpty())
    <p class="text-stone-500 py-12 text-center">Новости пока не добавлены</p>
@else
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 md:gap-6">
        @foreach($news as $item)
            @include('components.news-card', ['item' => $item])
        @endforeach
    </div>

    <div class="mt-8" data-news-pagination>
        {{ $news->links() }}
    </div>
@endif


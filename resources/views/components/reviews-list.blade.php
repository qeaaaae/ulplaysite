@props([
    'reviews',
])

@php
    $isPaginated = $reviews instanceof \Illuminate\Pagination\AbstractPaginator;
@endphp

@if($isPaginated ? $reviews->isEmpty() : $reviews->isEmpty())
    <p class="text-stone-500 text-sm">Пока ничего нет.</p>
@else
    <ul id="reviews-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($isPaginated ? $reviews->getCollection() : $reviews as $review)
            <li class="p-4 bg-white rounded-xl border border-stone-200 shadow-sm hover:border-stone-300 transition-colors">
                <div class="flex flex-wrap items-center gap-2 mb-1">
                    <span class="flex gap-0.5 text-sky-500 text-lg" aria-hidden="true">@for($i = 0; $i < 5; $i++)<span class="{{ $i < $review->rating ? 'opacity-100' : 'opacity-30' }}">★</span>@endfor</span>
                    <span class="text-sm font-medium text-stone-700">{{ $review->user->name ?? 'Гость' }}</span>
                    <span class="text-xs text-stone-400">{{ $review->created_at->format(config('app.datetime_format')) }}</span>
                </div>
                @if($review->body)
                    <p class="text-stone-600 text-sm leading-snug line-clamp-4">{{ $review->body }}</p>
                @endif
                @if($review->image_urls && count($review->image_urls) > 0)
                    <div class="flex flex-wrap gap-2 mt-3">
                        @foreach($review->image_urls as $url)
                            <a href="{{ $url }}" data-lightbox="image" data-lightbox-group="review-{{ $review->id }}" class="block w-16 h-16 rounded-lg overflow-hidden border border-stone-200 hover:border-sky-300 transition-colors cursor-zoom-in"><img src="{{ $url }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'"></a>
                        @endforeach
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
    @if($isPaginated && $reviews->hasMorePages())
        <div
            id="reviews-infinite-sentinel"
            data-next-url="{{ $reviews->nextPageUrl() }}"
            class="h-1 w-full shrink-0 pointer-events-none mt-2"
            aria-hidden="true"
        ></div>
    @endif
@endif

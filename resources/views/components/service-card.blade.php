@props(['service'])
@php
    $service = (object) $service;
    $avgRating = isset($service->reviews_avg_rating) ? (float) $service->reviews_avg_rating : 0;
    $reviewsCount = $service->reviews_count ?? 0;
@endphp
<article class="group relative flex flex-col h-full min-h-[240px] sm:min-h-[260px] md:min-h-[280px] rounded-xl overflow-hidden border border-stone-200 shadow-[0_1px_3px_0_rgba(0,0,0,0.06),0_1px_2px_-1px_rgba(0,0,0,0.06)] hover:border-sky-200 hover:shadow-[0_4px_6px_-1px_rgba(0,0,0,0.08),0_2px_4px_-2px_rgba(0,0,0,0.06)] transition-all duration-300">
    <a href="{{ route('services.show', $service) }}" class="absolute inset-0 z-0" aria-label="{{ $service->title }}"><span class="sr-only">{{ $service->title }}</span></a>
    <div class="relative flex-1 flex flex-col min-h-0">
        <div class="absolute inset-0">
            <img src="{{ $service->image }}" alt="" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $service->id ?? 0 }}/600/400';">
            <div class="absolute inset-0 bg-gradient-to-t from-stone-900/95 via-stone-900/30 to-transparent"></div>
        </div>
        <div class="relative z-10 mt-auto p-4 sm:p-5 md:p-6 flex flex-col">
            <a href="{{ route('services.show', $service) }}" class="hover:no-underline">
                <h3 class="text-white font-semibold text-lg mb-2 group-hover:text-sky-300 transition-colors">{{ $service->title }}</h3>
            </a>
            <p class="text-stone-300 text-sm line-clamp-2 leading-relaxed mb-3">{{ $service->description }}</p>
            <div class="flex items-center gap-2 mb-4" aria-label="Рейтинг: {{ $avgRating }} из 5">
                <span class="flex gap-0.5 text-lg leading-none text-stone-400">
                    @for($i = 1; $i <= 5; $i++)
                        @php
                            $fill = $avgRating >= $i ? 100 : ($avgRating > $i - 1 ? (int) round(($avgRating - ($i - 1)) * 100) : 0);
                        @endphp
                        <span class="relative inline-block">
                            <span class="text-stone-500/80">★</span>
                            @if($fill > 0)
                                <span class="absolute left-0 top-0 h-full overflow-hidden text-sky-400" style="width: {{ $fill }}%">
                                    <span class="inline-block" style="width: {{ $fill < 100 ? round(10000 / $fill) : 100 }}%">★</span>
                                </span>
                            @endif
                        </span>
                    @endfor
                </span>
                @if($reviewsCount > 0)
                    <a href="{{ route('services.show', $service) }}#reviews" class="text-stone-400 text-sm hover:text-sky-400 transition-colors">{{ $reviewsCount }} @if($reviewsCount === 1)отзыв@elseif($reviewsCount >= 2 && $reviewsCount <= 4)отзыва@else отзывов @endif</a>
                @else
                    <span class="text-stone-500 text-sm">Нет отзывов</span>
                @endif
            </div>
            <a href="{{ route('services.show', $service) }}" class="inline-flex items-center gap-2 text-sky-400 text-sm font-medium group-hover:gap-3 transition-all duration-200 w-fit">
                Подробнее об услуге
                <span class="inline-flex transition-transform duration-200 group-hover:translate-x-1">@svg('heroicon-o-arrow-right', 'w-4 h-4')</span>
            </a>
        </div>
    </div>
</article>

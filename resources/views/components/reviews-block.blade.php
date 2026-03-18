@props([
    'reviewable', // Product or Service
    'reviews' => collect(),
    'canReview' => false,
    'storeRoute',
    'storeRouteParam',
])

@php
    $reviewableName = $reviewable instanceof \App\Models\Product ? 'товара' : 'услуги';
@endphp
<div id="reviews" class="mt-8 pt-6 border-t border-stone-200 scroll-mt-24">
    <h2 class="text-lg font-semibold text-stone-900 mb-4 flex items-center gap-2">
        <span class="text-sky-600 text-xl leading-none">★</span>
        <span>Отзывы {{ $reviews->count() ? '(' . $reviews->count() . ')' : '' }}</span>
    </h2>

    @if($canReview)
    <form action="{{ route($storeRoute, $storeRouteParam) }}" method="POST" enctype="multipart/form-data" class="mb-6 p-4 sm:p-5 bg-white rounded-xl border border-stone-200 shadow-sm">
        @csrf
        <div class="flex flex-col sm:flex-row gap-3 max-w-2xl items-end">
            <div class="flex gap-0.5 text-xl" x-data="{ rating: {{ old('rating', 0) }}, hover: 0 }">
                <input type="hidden" name="rating" :value="rating" required>
                @for($i = 1; $i <= 5; $i++)
                    <button type="button" class="p-0.5 leading-none transition-colors focus:outline-none rounded" @click="rating = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0" aria-label="Оценка {{ $i }}">
                        <span class="block" :class="(hover || rating) >= {{ $i }} ? 'text-sky-600' : 'text-stone-300'">★</span>
                    </button>
                @endfor
            </div>
            <div class="flex-1 w-full">
                <label for="review-body" class="sr-only">Текст</label>
                <textarea name="body" id="review-body" rows="2" maxlength="500" class="w-full px-3 py-2 text-sm bg-white border border-stone-300 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y" placeholder="Текст отзыва...">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="shrink-0 w-full sm:w-auto">
                <label class="block text-xs text-stone-500 mb-1">Фото (макс. 3)</label>
                <input type="file" name="images[]" accept="image/*" multiple class="block w-full text-sm text-stone-500 file:mr-2 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:bg-sky-50 file:text-sky-700 border border-stone-300 rounded-lg bg-white px-0 py-1 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                @error('images')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <x-ui.button type="submit" variant="primary" class="shrink-0">Отправить</x-ui.button>
        </div>
    </form>
    @else
        <x-ui.info-banner>
            @authОтзыв - только на купленный {{ $reviewableName }}.@else<a href="{{ route('login') }}" class="text-sky-600 hover:underline">Войдите</a> и оформите покупку.@endauth
        </x-ui.info-banner>
    @endif

    @if($reviews->isEmpty())
        <p class="text-stone-500 text-sm">Пока ничего нет.</p>
    @else
        <ul class="space-y-3">
            @foreach($reviews as $review)
                <li class="p-3 bg-white rounded-lg border border-stone-200">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="flex gap-0.5 text-amber-500 text-sm" aria-hidden="true">@for($i = 0; $i < 5; $i++)<span class="{{ $i < $review->rating ? 'opacity-100' : 'opacity-30' }}">★</span>@endfor</span>
                        <span class="text-sm font-medium text-stone-700">{{ $review->user->name ?? 'Гость' }}</span>
                        <span class="text-xs text-stone-400">{{ $review->created_at->format(config('app.datetime_format')) }}</span>
                    </div>
                    @if($review->body)
                        <p class="text-stone-600 text-sm leading-snug line-clamp-4">{{ $review->body }}</p>
                    @endif
                    @if($review->image_urls && count($review->image_urls) > 0)
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            @foreach($review->image_urls as $url)
                                <a href="{{ $url }}" target="_blank" rel="noopener" class="block w-14 h-14 rounded overflow-hidden border border-stone-200"><img src="{{ $url }}" alt="" class="w-full h-full object-cover"></a>
                            @endforeach
                        </div>
                    @endif
                </li>
            @endforeach
        </ul>
    @endif
</div>

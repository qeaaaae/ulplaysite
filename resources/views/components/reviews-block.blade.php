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
    <h2 class="text-lg font-semibold text-stone-900 mb-5 flex items-center gap-2">
        @svg('heroicon-o-star', 'w-5 h-5 text-sky-500')
        <span>Отзывы {{ $reviews->count() ? '(' . $reviews->count() . ')' : '' }}</span>
    </h2>

    @if($canReview)
    <form action="{{ route($storeRoute, $storeRouteParam) }}" method="POST" enctype="multipart/form-data" data-ajax-review-store class="mb-6 p-5 sm:p-6 bg-stone-50/60 rounded-xl border border-stone-200 shadow-sm space-y-5">
        @csrf
        <div class="space-y-4">
            <div class="form-field" x-data="{ rating: {{ old('rating', 0) }}, hover: 0 }">
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-2">
                    @svg('heroicon-o-star', 'w-4 h-4 text-sky-500')
                    Оценка
                </label>
                <div class="flex gap-1">
                    <input type="hidden" name="rating" :value="rating" required>
                    @for($i = 1; $i <= 5; $i++)
                        <button type="button" class="p-1 rounded-md transition-colors focus:outline-none focus:ring-2 focus:ring-sky-500/30" @click="rating = {{ $i }}" @mouseenter="hover = {{ $i }}" @mouseleave="hover = 0" aria-label="Оценка {{ $i }}">
                            <span class="block text-2xl leading-none" :class="(hover || rating) >= {{ $i }} ? 'text-sky-500' : 'text-stone-300'">★</span>
                        </button>
                    @endfor
                </div>
                <div class="text-xs text-rose-600 hidden mt-1" data-ajax-review-error="rating"></div>
            </div>
            <div class="form-field">
                <label for="review-body" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4 text-sky-500')
                    Текст отзыва
                </label>
                <textarea name="body" id="review-body" rows="3" maxlength="500" class="w-full px-3 py-2.5 bg-white border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors resize-y" placeholder="Поделитесь впечатлениями о товаре...">{{ old('body') }}</textarea>
                <div class="mt-1 text-xs text-rose-600 hidden" data-ajax-review-error="body"></div>
                @error('body')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <x-ui.file-input
                    name="images[]"
                    accept="image/*"
                    multiple
                    :max-previews="3"
                    label="Фото (макс. 3)"
                    label-icon="heroicon-o-photo"
                    :error="$errors->first('images')"
                />
                <div class="mt-1.5 text-xs text-rose-600 hidden" data-ajax-review-error="images"></div>
            </div>
        </div>
        <div class="pt-1">
            <x-ui.button type="submit" variant="primary" size="lg">
                @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                Отправить отзыв
            </x-ui.button>
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
        <ul class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($reviews as $review)
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
    @endif
</div>

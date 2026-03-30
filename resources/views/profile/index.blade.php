@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <x-ui.section-heading tag="h1" icon="heroicon-o-user-circle" class="mb-0">Личный кабинет</x-ui.section-heading>

            <div class="rounded-2xl border border-stone-200 bg-white p-5 sm:p-6 shadow-sm">
                <h2 class="section-heading text-lg flex items-center gap-2.5 text-stone-900 mb-5">
                    <span class="text-sky-600 shrink-0">@svg('heroicon-o-pencil-square', 'w-5 h-5')</span>
                    Редактировать профиль
                </h2>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-ui.input name="name" label="Имя" value="{{ old('name', $user->name) }}" required :error="$errors->first('name')" />
                    <x-ui.input type="email" name="email" label="Email" value="{{ old('email', $user->email) }}" required :error="$errors->first('email')" />
                    <x-ui.phone-input name="phone" label="Телефон" :value="old('phone', $user->phone)" :error="$errors->first('phone')" />
                    <x-ui.button type="submit" variant="primary">
                        @svg('heroicon-o-check', 'w-4 h-4')
                        Сохранить
                    </x-ui.button>
                </form>
            </div>

            <section id="my-reviews" class="scroll-mt-24 rounded-2xl border border-stone-200 bg-white p-5 sm:p-6 shadow-sm">
                <h2 class="section-heading text-lg flex items-center gap-2.5 text-stone-900 mb-4">
                    <span class="text-sky-600 shrink-0">@svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')</span>
                    Мои отзывы
                </h2>
                @if($reviews->isEmpty())
                    <p class="text-sm text-stone-500">Пока нет отзывов — оцените купленные товары в <a href="{{ route('orders.index') }}" class="text-sky-700 font-medium hover:underline">моих заказах</a> или на странице товара.</p>
                @else
                    <ul class="space-y-4">
                        @foreach($reviews as $review)
                            <li class="p-4 rounded-xl border border-stone-100 bg-stone-50/60">
                                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                                    <div class="min-w-0">
                                        @if($url = $review->publicReviewableUrl())
                                            <a href="{{ $url }}" class="text-sky-700 font-medium hover:text-sky-800 hover:underline">{{ $review->reviewableDisplayTitle() }}</a>
                                        @else
                                            <span class="font-medium text-stone-700">{{ $review->reviewableDisplayTitle() }}</span>
                                        @endif
                                        @if($review->reviewableKindLabel() !== '')
                                            <span class="text-stone-500 text-sm"> — {{ $review->reviewableKindLabel() }}</span>
                                        @endif
                                    </div>
                                    <time class="text-xs text-stone-400 shrink-0" datetime="{{ $review->created_at->toIso8601String() }}">{{ $review->created_at->format(config('app.datetime_format')) }}</time>
                                </div>
                                <div class="flex gap-0.5 text-sky-500 text-base mb-2" aria-hidden="true">
                                    @for($i = 0; $i < 5; $i++)
                                        <span class="{{ $i < $review->rating ? 'opacity-100' : 'opacity-30' }}">★</span>
                                    @endfor
                                </div>
                                @if($review->body)
                                    <p class="text-stone-600 text-sm leading-snug">{{ $review->body }}</p>
                                @endif
                                @if($review->image_urls && count($review->image_urls) > 0)
                                    <div class="flex flex-wrap gap-2 mt-3">
                                        @foreach($review->image_urls as $url)
                                            <a href="{{ $url }}" data-lightbox="image" data-lightbox-group="profile-review-{{ $review->id }}" class="block w-16 h-16 rounded-lg overflow-hidden border border-stone-200 hover:border-sky-300 transition-colors cursor-zoom-in">
                                                <img src="{{ $url }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                    @if($reviews->hasPages())
                        <div class="mt-5 pt-4 border-t border-stone-100">
                            {{ $reviews->links() }}
                        </div>
                    @endif
                @endif
            </section>

            <div class="rounded-2xl border border-stone-200 bg-white p-5 sm:p-6 shadow-sm">
                <h2 class="section-heading text-lg flex items-center gap-2.5 text-stone-900 mb-4">
                    <span class="text-sky-600 shrink-0">@svg('heroicon-o-clipboard-document-list', 'w-5 h-5')</span>
                    Последние заказы
                </h2>
                @if($orders->isEmpty())
                    <p class="text-sm text-stone-500">Пока нет заказов — выберите товары в каталоге.</p>
                @else
                    <div class="rounded-xl border border-stone-100 bg-stone-50/60 overflow-hidden divide-y divide-stone-100">
                        @foreach($orders as $order)
                            <a href="{{ route('orders.show', $order) }}" class="flex justify-between items-center gap-4 px-4 py-3.5 sm:px-5 hover:bg-white transition-colors group">
                                <div class="min-w-0">
                                    <p class="font-semibold text-stone-900 group-hover:text-sky-700 transition-colors">{{ $order->order_number }}</p>
                                    <p class="text-xs text-stone-500 mt-0.5">{{ $order->created_at->format(config('app.datetime_format')) }}</p>
                                </div>
                                <span class="shrink-0 font-semibold text-stone-900 tabular-nums">{{ number_format($order->total, 0, ',', ' ') }}&nbsp;₽</span>
                            </a>
                        @endforeach
                    </div>
                    <x-ui.button href="{{ route('orders.index') }}" variant="primary" size="sm" class="mt-5 w-full sm:w-auto">
                        Все заказы
                        @svg('heroicon-o-arrow-right', 'w-4 h-4')
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div>
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Услуги', 'url' => route('services.index')],
                ['label' => $service->title, 'url' => null],
            ]" class="!mb-0 py-4" />

            @php
                $images = $service->images;
                $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
            @endphp

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 pb-10">
                <div>
                    @if($cover)
                        <div class="aspect-video lg:aspect-[4/3] rounded-xl overflow-hidden bg-stone-50">
                            <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}">
                                <img src="{{ $cover->url }}" alt="{{ $service->title }}" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'">
                            </a>
                        </div>
                    @endif

                    @if($thumbs->count() > 0)
                        <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                            @foreach($thumbs as $image)
                                <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}" class="block w-20 h-20 md:w-24 md:h-24 lg:w-28 lg:h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                                    <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="flex flex-col">
                    @if($service->category)
                        <p class="text-sm text-stone-500 mb-1">
                            <a href="{{ route('services.index', ['category' => $service->category->slug]) }}" class="text-sky-600 hover:underline">{{ $service->category->name }}</a>
                        </p>
                    @endif
                    <h1 class="text-2xl sm:text-3xl font-semibold text-stone-900 mb-4">{{ $service->title }}</h1>

                    @if($service->description)
                        <p class="text-stone-600 leading-relaxed mb-6">{{ $service->description }}</p>
                    @endif

                    <div class="mt-auto pt-4 border-t border-stone-200">
                        @auth
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-600 text-white font-semibold text-sm hover:bg-sky-700 transition-colors shadow-sm w-full sm:w-auto"
                                @click="$dispatch('open-support-ticket-modal', { serviceId: {{ $service->id }}, title: @js($service->title) })"
                            >
                                @svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')
                                Узнать подробнее
                            </button>
                        @else
                            <button
                                type="button"
                                class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-sky-600 text-white font-semibold text-sm hover:bg-sky-700 transition-colors shadow-sm w-full sm:w-auto"
                                @click="openAuthModal('login')"
                            >
                                @svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')
                                Узнать подробнее
                            </button>
                            <p class="text-stone-500 text-sm mt-2">Войдите, чтобы задать вопрос по услуге</p>
                        @endauth
                    </div>
                </div>
            </div>

            @if($service->content)
                <section class="border-t border-stone-200 pt-10 pb-12">
                    <h2 class="text-xl font-semibold text-stone-900 mb-6">Как проходит услуга</h2>
                    <div class="prose prose-stone max-w-none prose-headings:font-semibold prose-a:text-sky-600 prose-img:rounded-lg">
                        {!! \Illuminate\Support\Str::markdown($service->content) !!}
                    </div>
                </section>
            @endif

            @if(($similarServices ?? collect())->isNotEmpty())
                <section class="mt-10 pt-8 border-t border-stone-200 overflow-hidden">
                    <x-ui.section-heading icon="heroicon-o-wrench-screwdriver" class="mb-4">Похожие услуги</x-ui.section-heading>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 md:gap-5">
                        @foreach($similarServices as $similar)
                            @include('components.service-card', ['service' => $similar])
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
@endsection

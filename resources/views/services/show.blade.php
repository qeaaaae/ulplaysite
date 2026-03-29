@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.breadcrumbs :items="array_values(array_filter([
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Услуги', 'url' => route('services.index')],
                $service->category ? ['label' => $service->category->name, 'url' => route('services.index', ['category' => $service->category->slug])] : null,
                ['label' => $service->title, 'url' => null],
            ]))" class="!mb-0" />

            @php
                $images = $service->images;
                $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
            @endphp

            <section class="mt-4 rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden">
                <div class="grid grid-cols-1 xl:grid-cols-[1.1fr_0.9fr] gap-0">
                    <div class="p-0">
                        @if($cover)
                            <div class="h-full min-h-[260px] sm:min-h-[320px] lg:min-h-[480px] overflow-hidden bg-stone-50">
                                <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}" class="block h-full">
                                    <img src="{{ $cover->url }}" alt="{{ $service->title }}" class="w-full h-full object-cover transition-transform duration-300 hover:scale-[1.02]" onerror="this.onerror=null;this.style.display='none'">
                                </a>
                            </div>
                        @else
                            <div class="aspect-video lg:aspect-[16/10] rounded-xl bg-stone-100 border border-stone-200 flex items-center justify-center text-stone-400">
                                @svg('heroicon-o-photo', 'w-10 h-10')
                            </div>
                        @endif

                        @if($thumbs->count() > 0)
                            <div class="mt-3 px-4 sm:px-6 md:px-8 pb-4 sm:pb-6 md:pb-8 flex gap-2 overflow-x-auto">
                                @foreach($thumbs as $image)
                                    <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="service-{{ $service->id }}" class="block w-20 h-20 md:w-24 md:h-24 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0 hover:border-sky-300 transition-colors">
                                        <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover">
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="border-t xl:border-t-0 xl:border-l border-stone-200 p-4 sm:p-6 md:p-8 flex flex-col">
                        <h1 class="text-2xl sm:text-3xl font-semibold text-stone-900 leading-tight">{{ $service->title }}</h1>

                        @if($service->description)
                            <p class="text-stone-600 leading-relaxed mt-4">{{ $service->description }}</p>
                        @endif

                        <div class="mt-6 rounded-xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm">
                            <div class="flex items-start gap-3">
                                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-sky-100 text-sky-700 shrink-0">
                                    @svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-wide text-sky-700 mb-1">Есть вопросы по услуге?</p>
                                    <p class="text-sm text-stone-600">Напишите нам, подскажем по срокам, стоимости и деталям выполнения.</p>
                                </div>
                            </div>
                            <div class="mt-4">
                                @auth
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-sky-600 text-white font-semibold text-sm hover:bg-sky-700 transition-all shadow-sm hover:shadow-md w-full sm:w-auto cursor-pointer"
                                        @click="openSupportTicketModal({ serviceId: {{ $service->id }}, title: {{ \Illuminate\Support\Js::from($service->title) }} })"
                                    >
                                        @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                                        Задать вопрос
                                    </button>
                                @else
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg bg-sky-600 text-white font-semibold text-sm hover:bg-sky-700 transition-all shadow-sm hover:shadow-md w-full sm:w-auto cursor-pointer"
                                        @click="openAuthModal('login')"
                                    >
                                        @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                                        Задать вопрос
                                    </button>
                                    <p class="text-stone-500 text-xs mt-2">Войдите, чтобы отправить обращение</p>
                                @endauth
                            </div>
                        </div>

                    </div>
                </div>
            </section>

            @if($service->content)
                <section class="mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm p-5 sm:p-6 md:p-8">
                    <x-ui.section-heading icon="heroicon-o-document-text" class="mb-4">Как проходит услуга</x-ui.section-heading>
                    <div class="ulplay-markdown-body prose prose-stone max-w-[58.5rem] mx-0 prose-headings:font-heading prose-headings:font-semibold prose-a:text-sky-600 hover:prose-a:text-sky-700 prose-img:rounded-xl prose-hr:border-stone-200">
                        {!! app(\App\Services\MarkdownService::class)->render($service->content) !!}
                    </div>
                </section>
            @endif

            @if(($similarServices ?? collect())->isNotEmpty())
                <section class="mt-8 pb-2">
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

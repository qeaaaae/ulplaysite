@extends('layouts.app')

@section('content')
    <article class="min-h-screen">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Новости', 'url' => route('news.index')],
                ['label' => $news->title, 'url' => null],
            ]" class="!mb-0 py-4" />

            <div class="@if(($similarNews ?? collect())->isNotEmpty()) md:grid md:grid-cols-[6fr_4fr] md:gap-4 lg:grid-cols-[7fr_3fr] lg:gap-5 xl:gap-6 @endif">
                <div class="min-w-0">
                    <header class="mb-4">
                        <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500 mb-2">
                            <time datetime="{{ $news->published_at?->toIso8601String() }}" class="flex items-center gap-1.5">
                                @svg('heroicon-o-calendar', 'w-4 h-4 text-sky-500')
                                {{ $news->published_at?->format(config('app.datetime_format')) }}
                            </time>
                            @if($news->author)
                                <span class="flex items-center gap-1.5">
                                    @svg('heroicon-o-user', 'w-4 h-4 text-sky-500')
                                    {{ $news->author->name }}
                                </span>
                            @endif
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-stone-900 tracking-tight leading-tight">{{ $news->title }}</h1>
                    </header>

                    @php
                        $images = $news->images;
                        $cover = $images->firstWhere('is_cover', true) ?? $images->first();
                        $thumbs = $cover ? $images->filter(fn ($img) => $img->id !== $cover->id) : $images;
                        $videoEmbedUrl = $news->video_embed_url;
                    @endphp

                    @if($videoEmbedUrl)
                        <figure class="rounded-2xl overflow-hidden ring-1 ring-stone-200/50 mb-4">
                            <div class="aspect-video w-full bg-stone-900">
                                <iframe src="{{ $videoEmbedUrl }}" class="w-full h-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
                            </div>
                        </figure>
                    @elseif($cover)
                        <figure class="rounded-2xl overflow-hidden ring-1 ring-stone-200/50 mb-4">
                            <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="news-{{ $news->id }}">
                                <img src="{{ $cover->url }}" alt="{{ $news->title }}" class="w-full aspect-[2/1] object-cover cursor-zoom-in" onerror="this.onerror=null;this.style.display='none'">
                            </a>
                        </figure>
                    @endif

                    @if($thumbs->count() > 0)
                        <div class="mb-6 flex gap-2 overflow-x-auto pb-1">
                            @foreach($thumbs as $image)
                                <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="news-{{ $news->id }}" class="block w-24 h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                                    <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'">
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($news->description)
                        <p class="text-xl text-stone-600 leading-relaxed mb-6 font-medium">{{ $news->description }}</p>
                    @endif

                    @if($news->content)
                        <div class="prose prose-stone prose-lg max-w-none text-stone-600 leading-relaxed space-y-4">
                            {!! nl2br(e($news->content)) !!}
                        </div>
                    @endif

                    <x-comments-block
                        :news="$news"
                        :comments="$comments ?? $news->comments"
                        :can-comment="auth()->check()"
                    />
                </div>

                @if(($similarNews ?? collect())->isNotEmpty())
                    <aside class="md:sticky md:top-4 md:self-start mt-10 md:mt-0 pt-8 md:pt-0 border-t md:border-t-0 border-stone-200">
                        <x-ui.section-heading icon="heroicon-o-newspaper" class="mb-4">Похожие новости</x-ui.section-heading>
                        <div class="space-y-3 sm:space-y-4">
                            @foreach($similarNews as $item)
                                <div class="min-w-0">
                                    @include('components.news-card', ['item' => $item])
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('news.index') }}" class="mt-6 inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                            @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                            Все новости
                        </a>
                    </aside>
                @endif
            </div>

            @if(($similarNews ?? collect())->isEmpty())
                <footer class="mt-12 pt-8 border-t border-stone-200">
                    <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                        @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                        Все новости
                    </a>
                </footer>
            @endif
        </div>
    </article>
@endsection

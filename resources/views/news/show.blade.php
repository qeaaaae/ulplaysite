@extends('layouts.app')

@section('content')
    <article class="min-h-screen">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.breadcrumbs :items="[
                ['label' => 'Главная', 'url' => route('home')],
                ['label' => 'Новости', 'url' => route('news.index')],
                ['label' => $news->title, 'url' => null],
            ]" class="!mb-0 py-4" />

            <header class="mb-8">
                <div class="flex flex-wrap items-center gap-3 text-sm text-stone-500 mb-4">
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
                <figure class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-stone-200/50 mb-4">
                    <div class="aspect-video w-full bg-stone-900">
                        <iframe src="{{ $videoEmbedUrl }}" class="w-full h-full" allowfullscreen allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"></iframe>
                    </div>
                </figure>
            @elseif($cover)
                <figure class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-stone-200/50 mb-4">
                    <a href="{{ $cover->url }}" data-lightbox="image" data-lightbox-group="news-{{ $news->id }}">
                        <img src="{{ $cover->url }}" alt="{{ $news->title }}" class="w-full aspect-video object-cover cursor-zoom-in" onerror="this.onerror=null;this.style.display='none'">
                    </a>
                </figure>
            @endif

            @if($thumbs->count() > 0)
                <div class="mb-8 flex gap-2 overflow-x-auto pb-1">
                    @foreach($thumbs as $image)
                        <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="news-{{ $news->id }}" class="block w-24 h-24 md:w-28 md:h-28 lg:w-32 lg:h-32 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 shrink-0">
                            <img src="{{ $image->url }}" alt="" class="w-full h-full object-cover" onerror="this.onerror=null;this.style.display='none'">
                        </a>
                    @endforeach
                </div>
            @endif

            @if($news->description)
                <p class="text-xl text-stone-600 leading-relaxed mb-8 font-medium">{{ $news->description }}</p>
            @endif

            @if($news->content)
                <div class="prose prose-stone prose-lg max-w-none text-stone-600 leading-relaxed space-y-4">
                    {!! nl2br(e($news->content)) !!}
                </div>
            @endif

            <x-comments-block
                :news="$news"
                :comments="$news->comments"
                :can-comment="auth()->check()"
            />

            @if(($similarNews ?? collect())->isNotEmpty())
                <section class="mt-10 pt-8 border-t border-stone-200 overflow-hidden">
                    <x-ui.section-heading icon="heroicon-o-newspaper" class="mb-4">Похожие новости</x-ui.section-heading>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 md:gap-5">
                        @foreach($similarNews as $item)
                            <div class="min-w-0">
                                @include('components.news-card', ['item' => $item])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            <footer class="mt-12 pt-8 border-t border-stone-200">
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                    Все новости
                </a>
            </footer>
        </div>
    </article>
@endsection

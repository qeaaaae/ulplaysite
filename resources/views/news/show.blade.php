@extends('layouts.app')

@section('content')
    <article class="min-h-screen">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <nav class="py-4 text-sm text-stone-500">
                <a href="{{ route('home') }}" class="hover:text-sky-600 transition-colors">Главная</a>
                <span class="mx-2">/</span>
                <a href="{{ route('news.index') }}" class="hover:text-sky-600 transition-colors">Новости</a>
                <span class="mx-2">/</span>
                <span class="text-stone-800 font-medium line-clamp-1">{{ $news->title }}</span>
            </nav>

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

            @if($news->image ?? $news->image_path)
                <figure class="rounded-2xl overflow-hidden shadow-xl ring-1 ring-stone-200/50 mb-8">
                    <img src="{{ $news->image ?: $news->image_path }}" alt="{{ $news->title }}" class="w-full aspect-video object-cover" onerror="this.style.display='none'">
                </figure>
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

            <footer class="mt-12 pt-8 border-t border-stone-200">
                <a href="{{ route('news.index') }}" class="inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors group">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5 group-hover:-translate-x-0.5 transition-transform')
                    Все новости
                </a>
            </footer>
        </div>
    </article>
@endsection

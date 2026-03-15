@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.news.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
            @svg('heroicon-o-arrow-left', 'w-5 h-5')
        </a>
        <div class="flex items-center gap-2.5">
            @svg('heroicon-o-newspaper', 'w-8 h-8 text-sky-600')
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $news->id ? 'Редактировать новость' : 'Новая новость' }}</h1>
                <p class="text-sm text-stone-500">{{ $news->id ? $news->title : 'Заполните поля' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $news->id ? route('admin.news.update', $news) : route('admin.news.store') }}" enctype="multipart/form-data" class="w-full space-y-6">
        @csrf
        @if($news->id) @method('PATCH') @endif

        <x-admin.form-section title="Основное" icon="heroicon-o-document-text">
            <x-ui.input name="title" label="Название" label-icon="heroicon-o-document-text" value="{{ old('title', $news->title) }}" required :error="$errors->first('title')" />
            <x-ui.input name="slug" label="Ярлык" label-icon="heroicon-o-link" value="{{ old('slug', $news->slug) }}" :error="$errors->first('slug')" />
            <div class="lg:col-span-2">
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document', 'w-4 h-4 text-stone-400')
                    Краткое описание
                </label>
                <textarea name="description" rows="2" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y">{{ old('description', $news->description) }}</textarea>
            </div>
            <div class="lg:col-span-2">
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document-text', 'w-4 h-4 text-stone-400')
                    Содержание
                </label>
                <textarea name="content" rows="6" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y min-h-[120px]">{{ old('content', $news->content) }}</textarea>
            </div>
            <x-ui.input type="date" name="published_at" label="Дата публикации" label-icon="heroicon-o-calendar" value="{{ old('published_at', $news->published_at?->format('Y-m-d')) }}" />
        </x-admin.form-section>

        <x-admin.form-section title="Изображение" icon="heroicon-o-photo">
            <x-ui.file-input name="image" accept="image/*" label="Файл изображения" label-icon="heroicon-o-photo" />
            <x-ui.input name="image_path" label="Или URL изображения" label-icon="heroicon-o-link" value="{{ old('image_path', $news->image_path) }}" placeholder="https://..." :error="$errors->first('image_path')" />
        </x-admin.form-section>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                @svg('heroicon-o-check', 'w-5 h-5')
                Сохранить
            </x-ui.button>
            <a href="{{ route('admin.news.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                @svg('heroicon-o-x-mark', 'w-5 h-5')
                Отмена
            </a>
        </div>
    </form>
@endsection

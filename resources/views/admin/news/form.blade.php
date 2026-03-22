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
            <x-ui.input name="video_url" label="Видео (YouTube или Rutube)" label-icon="heroicon-o-video-camera" value="{{ old('video_url', $news->video_url) }}" placeholder="https://www.youtube.com/watch?v=... или https://rutube.ru/video/..." :error="$errors->first('video_url')" />
            <x-ui.input type="date" name="published_at" label="Дата публикации" label-icon="heroicon-o-calendar" value="{{ old('published_at', $news->published_at?->format('Y-m-d')) }}" />
        </x-admin.form-section>

        <x-admin.form-section title="Изображения" icon="heroicon-o-photo">
            <div
                class="space-y-3 lg:col-span-2"
                x-data="{
                    existing: {{ $news->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url, 'is_cover' => (bool) $img->is_cover])->values()->toJson() }},
                    deleted: [],
                    newPreviews: [],
                    removeExisting(id) {
                        this.deleted.push(id);
                        this.existing = this.existing.filter(img => img.id !== id);
                    },
                    handleFiles(event) {
                        const files = Array.from(event.target.files || []).slice(0, 5);
                        this.newPreviews = files.map(file => ({
                            name: file.name,
                            url: URL.createObjectURL(file),
                        }));
                    },
                }"
            >
                <x-ui.file-input
                    name="images[]"
                    accept="image/*"
                    label="Загрузить изображения (макс. 5)"
                    label-icon="heroicon-o-photo"
                    multiple
                    :error="$errors->first('images')"
                    x-on:change="handleFiles"
                />

                <template x-if="newPreviews.length">
                    <div class="mt-2">
                        <p class="text-xs text-stone-500 mb-2">Новые изображения (предпросмотр):</p>
                        <div class="flex flex-wrap gap-3">
                            <template x-for="(img, idx) in newPreviews" :key="idx">
                                <div class="w-28 h-28 rounded-lg overflow-hidden border border-dashed border-sky-300 bg-stone-50">
                                    <img :src="img.url" alt="" class="w-full h-full object-cover">
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-if="existing.length">
                    <div class="mt-4">
                        <p class="text-xs text-stone-500 mb-2">Текущие изображения новости (макс. 5):</p>
                        <div class="flex flex-wrap gap-4">
                            <template x-for="image in existing" :key="image.id">
                                <div class="relative group">
                                    <button
                                        type="button"
                                        class="absolute -top-2 -right-2 z-20 w-8 h-8 rounded-full bg-rose-600 text-white flex items-center justify-center shadow-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500/50"
                                        x-on:click="removeExisting(image.id)"
                                        title="Удалить"
                                    >
                                        @svg('heroicon-o-trash', 'w-4 h-4')
                                    </button>
                                    <a
                                        :href="image.url"
                                        data-lightbox="image"
                                        data-lightbox-group="admin-news-{{ $news->id }}"
                                        class="block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 relative"
                                    >
                                        <img :src="image.url" alt="" class="w-full h-full object-cover">
                                        <span
                                            x-show="image.is_cover"
                                            class="absolute bottom-0 inset-x-0 text-[10px] text-center text-white bg-black/70 px-1.5 py-0.5"
                                        >
                                            Обложка
                                        </span>
                                    </a>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <template x-for="id in deleted" :key="id">
                    <input type="hidden" name="delete_images[]" :value="id">
                </template>
            </div>
        </x-admin.form-section>

        @if(isset($views) && $views->isNotEmpty())
            <x-admin.form-section title="Просмотры" icon="heroicon-o-eye">
                <div class="space-y-2 max-h-80 overflow-y-auto">
                    @foreach($views as $view)
                        <div class="flex items-center justify-between gap-3 text-sm text-stone-700">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-sky-100 flex items-center justify-center text-sky-700 text-xs font-semibold shrink-0">
                                    {{ mb_substr($view->user->name ?? $view->user->email ?? 'U', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium truncate">{{ $view->user->name ?? $view->user->email ?? 'Пользователь #'.$view->user_id }}</p>
                                    <p class="text-xs text-stone-500 truncate">{{ $view->user->email ?? '' }}</p>
                                </div>
                            </div>
                            <div class="shrink-0 text-xs text-stone-500">
                                {{ $view->created_at?->format(config('app.datetime_format')) }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-stone-400">Всего просмотров: {{ $views->count() }}</p>
            </x-admin.form-section>
        @endif

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

@extends('layouts.admin')

@section('content')
    <form method="POST" action="{{ $service->id ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" class="w-full space-y-4">
        @csrf
        @if($service->id) @method('PATCH') @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.services.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5')
                </a>
                <div class="flex items-center gap-2.5">
                    @svg('heroicon-o-wrench-screwdriver', 'w-8 h-8 text-sky-600')
                    <div>
                        <h1 class="text-2xl font-semibold text-stone-900">{{ $service->id ? 'Редактировать услугу' : 'Новая услуга' }}</h1>
                        @if($service->id)
                            <a href="{{ route('services.show', $service) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-sm text-sky-600 hover:text-sky-700 hover:underline">
                                {{ $service->title }}
                                <svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                            </a>
                        @else
                            <p class="text-sm text-stone-500">Заполните поля</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                    @svg('heroicon-o-check', 'w-5 h-5')
                    Сохранить
                </x-ui.button>
                <a href="{{ route('admin.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                    Отмена
                </a>
            </div>
        </div>

        <x-admin.form-section title="Основное" icon="heroicon-o-document-text">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                <x-ui.input name="title" label="Название" label-icon="heroicon-o-document-text" value="{{ old('title', $service->title) }}" required :error="$errors->first('title')" />
                <x-ui.input name="slug" label="Ярлык" label-icon="heroicon-o-link" value="{{ old('slug', $service->slug) }}" :error="$errors->first('slug')" />
                <div class="lg:col-span-2 flex flex-col">
                    <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-squares-2x2', 'w-4 h-4 text-sky-500 shrink-0')
                        Категория
                    </label>
                    <select name="category_id" data-enhance="tom-select" class="w-full h-11 px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                        <option value="">— без категории —</option>
                        @foreach($categories ?? [] as $cat)
                            <option value="{{ $cat->id }}" {{ (string) old('category_id', $service->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @if($errors->has('category_id'))
                        <p class="mt-1 text-sm text-rose-600">{{ $errors->first('category_id') }}</p>
                    @endif
                </div>
            </div>
            <div class="mt-4 flex flex-col">
                <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document', 'w-4 h-4 text-sky-500 shrink-0')
                    Краткое описание
                </label>
                <textarea name="description" rows="3" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y" placeholder="Анонс для карточки и списка">{{ old('description', $service->description) }}</textarea>
            </div>
            <div class="mt-4 flex flex-col">
                <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document-text', 'w-4 h-4 text-sky-500 shrink-0')
                    Подробно: как проходит услуга (Markdown)
                </label>
                <textarea name="content" rows="12" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y font-mono text-sm" placeholder="Заголовки, списки, ссылки — в формате Markdown">{{ old('content', $service->content) }}</textarea>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Изображения" icon="heroicon-o-photo">
            <div
                class="space-y-3"
                x-data="{
                        existing: {{ $service->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url, 'is_cover' => (bool) $img->is_cover])->values()->toJson() }},
                        deleted: [],
                        removeExisting(id) {
                            this.deleted.push(id);
                            this.existing = this.existing.filter(img => img.id !== id);
                        },
                }"
            >
                <x-ui.file-input
                        name="images[]"
                        accept="image/*"
                        label="Загрузить изображения (макс. 5)"
                        label-icon="heroicon-o-photo"
                        multiple
                        :max-previews="5"
                        :error="$errors->first('images')"
                />

                <template x-if="existing.length">
                    <div class="mt-4">
                        <p class="text-xs text-stone-500 mb-2">Текущие изображения услуги (макс. 5):</p>
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
                                        data-lightbox-group="admin-service-{{ $service->id }}"
                                        class="block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 relative cursor-zoom-in hover:border-sky-300 transition-colors"
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
    </form>
@endsection

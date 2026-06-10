@extends('layouts.admin')

@section('content')
    <form method="POST" action="{{ route('admin.about.update') }}" enctype="multipart/form-data" class="w-full space-y-4">
        @csrf
        @method('PATCH')

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('about') }}" target="_blank" rel="noopener" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="Открыть на сайте">
                    @svg('heroicon-o-arrow-top-right-on-square', 'w-5 h-5')
                </a>
                <div class="flex items-center gap-2.5">
                    @svg('heroicon-o-information-circle', 'w-8 h-8 text-sky-600')
                    <div>
                        <h1 class="text-2xl font-semibold text-stone-900">Страница «О нас»</h1>
                        <p class="text-sm text-stone-500">Адрес и фото на публичной странице</p>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                    @svg('heroicon-o-check', 'w-5 h-5')
                    Сохранить
                </x-ui.button>
            </div>
        </div>

        <x-admin.form-section title="Контент" icon="heroicon-o-map-pin">
            <div class="flex flex-col">
                <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-map-pin', 'w-4 h-4 text-sky-500 shrink-0')
                    Адрес
                </label>
                <textarea
                    name="address"
                    rows="3"
                    required
                    class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y"
                    placeholder="г. Ульяновск, ул. ..."
                >{{ old('address', $about->address) }}</textarea>
                @error('address')
                    <p class="mt-1.5 text-sm text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div
                class="mt-4 space-y-3"
                x-data="{
                    existing: {{ $about->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url])->values()->toJson() }},
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
                    :hint="'До 5 фото за раз, максимум ' . (int) round(\App\Support\UploadLimits::imageMaxKb() / 1024) . ' МБ на файл. После загрузки изображения сжимаются автоматически.'"
                    :error="$errors->first('images')"
                />

                <template x-if="existing.length">
                    <div class="mt-2">
                        <p class="text-xs text-stone-500 mb-2">Текущие изображения (макс. 5):</p>
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
                                        data-lightbox-group="admin-about"
                                        class="block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 relative cursor-zoom-in hover:border-sky-300 transition-colors"
                                    >
                                        <img :src="image.url" alt="" class="w-full h-full object-cover">
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

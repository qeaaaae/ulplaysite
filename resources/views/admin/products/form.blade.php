@extends('layouts.admin')

@section('content')
    <form method="POST" action="{{ $product->id ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="w-full space-y-4">
        @csrf
        @if($product->id) @method('PATCH') @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.products.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5')
                </a>
                <div class="flex items-center gap-2.5">
                    @svg('heroicon-o-cube', 'w-8 h-8 text-sky-600')
                    <div>
                        <h1 class="text-2xl font-semibold text-stone-900">{{ $product->id ? 'Редактировать товар' : 'Новый товар' }}</h1>
                        @if($product->id)
                            <a href="{{ route('products.show', $product) }}" target="_blank" rel="noopener" class="text-sm text-sky-600 hover:text-sky-700 hover:underline">
                                {{ $product->title }}
                            </a>
                        @else
                            <p class="text-sm text-stone-500">Заполните поля и сохраните</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                    @svg('heroicon-o-check', 'w-5 h-5')
                    Сохранить
                </x-ui.button>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                    Отмена
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <x-admin.form-section title="Основное" icon="heroicon-o-document-text">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                    <x-ui.input name="title" label="Название" label-icon="heroicon-o-document-text" value="{{ old('title', $product->title) }}" required :error="$errors->first('title')" />
                    <x-ui.input name="slug" label="Ярлык" label-icon="heroicon-o-link" value="{{ old('slug', $product->slug) }}" :error="$errors->first('slug')" />
                </div>
                <div class="mt-4 flex flex-col">
                    <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-document', 'w-4 h-4 text-sky-500 shrink-0')
                        Описание
                    </label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y" placeholder="Краткое описание товара">{{ old('description', $product->description) }}</textarea>
                </div>
            </x-admin.form-section>

            <x-admin.form-section title="Цена, категория и склад" icon="heroicon-o-tag">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                    <x-ui.input type="number" name="price" label="Цена (₽)" label-icon="heroicon-o-currency-dollar" value="{{ old('price', $product->price) }}" step="0.01" required :error="$errors->first('price')" />
                    <div class="flex flex-col">
                        <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                            @svg('heroicon-o-squares-2x2', 'w-4 h-4 text-sky-500 shrink-0')
                            Категория
                        </label>
                        <select name="category_id" data-enhance="tom-select" class="w-full h-11 px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150" required>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>{{ $c->parent?->name }} — {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-ui.input type="number" name="stock" label="Количество на складе" label-icon="heroicon-o-cube" value="{{ old('stock', $product->stock ?? 0) }}" min="0" required :error="$errors->first('stock')" />
                    <x-ui.input type="number" name="discount_percent" label="Скидка (%)" label-icon="heroicon-o-tag" value="{{ old('discount_percent', $product->discount_percent) }}" min="0" max="100" />
                </div>
                <div class="mt-4 flex flex-wrap items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="in_stock" value="1" {{ old('in_stock', $product->in_stock ?? true) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                        <span class="text-sm text-stone-700">В наличии</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_new" value="1" {{ old('is_new', $product->is_new) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                        <span class="text-sm text-stone-700">Новинка</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_recommended" value="1" {{ old('is_recommended', $product->is_recommended ?? false) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                        <span class="text-sm text-stone-700">Рекомендуемый</span>
                    </label>
                </div>
            </x-admin.form-section>
        </div>

        <div class="grid grid-cols-1 gap-4">
            <x-admin.form-section title="Изображения" icon="heroicon-o-photo">
            <div
                class="space-y-3 lg:col-span-2"
                x-data="{
                    existing: {{ $product->images->map(fn($img) => ['id' => $img->id, 'url' => $img->url, 'is_cover' => (bool) $img->is_cover])->values()->toJson() }},
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
                        <p class="text-xs text-stone-500 mb-2">Текущие изображения товара (макс. 5):</p>
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
                                        data-lightbox-group="admin-product-{{ $product->id }}"
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
        </div>
    </form>
@endsection

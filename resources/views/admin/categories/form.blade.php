@extends('layouts.admin')

@section('content')
    <form method="POST" action="{{ $category->id ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="w-full space-y-4">
        @csrf
        @if($category->id) @method('PATCH') @endif

        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.categories.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
                    @svg('heroicon-o-arrow-left', 'w-5 h-5')
                </a>
                <div class="flex items-center gap-2.5">
                    @svg('heroicon-o-squares-2x2', 'w-8 h-8 text-sky-600')
                    <div>
                        <h1 class="text-2xl font-semibold text-stone-900">{{ $category->id ? 'Редактировать категорию' : 'Новая категория' }}</h1>
                        @if($category->id)
                            <a href="{{ route('products.index', ['category' => $category->slug]) }}" target="_blank" rel="noopener" class="text-sm text-sky-600 hover:text-sky-700 hover:underline">
                                {{ $category->name }}
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
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                    Отмена
                </a>
            </div>
        </div>

        <x-admin.form-section title="Категория" icon="heroicon-o-squares-2x2">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
                <x-ui.input name="name" label="Название" label-icon="heroicon-o-document-text" value="{{ old('name', $category->name) }}" required :error="$errors->first('name')" />
                <x-ui.input name="slug" label="Ярлык" label-icon="heroicon-o-link" value="{{ old('slug', $category->slug) }}" :error="$errors->first('slug')" />
                <div class="flex flex-col lg:col-span-2">
                    <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-squares-2x2', 'w-4 h-4 text-sky-500 shrink-0')
                        Родитель
                    </label>
                    <select name="parent_id" data-enhance="tom-select" class="w-full h-11 px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                        <option value="">- Нет -</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" {{ old('parent_id', $category->parent_id) == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-4 flex flex-col">
                <label class="flex items-center gap-2 min-h-[1.5rem] text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document', 'w-4 h-4 text-sky-500 shrink-0')
                    Описание
                </label>
                <textarea name="description" rows="2" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y" placeholder="Краткое описание категории">{{ old('description', $category->description) }}</textarea>
            </div>
            <div class="mt-4">
                <x-ui.file-input name="image" accept="image/*" label="Файл изображения" label-icon="heroicon-o-photo" :existing-url="$category->id ? $category->image : null" :lightbox-group="$category->id ? 'admin-category-' . $category->id : null" />
            </div>
            <div class="mt-4 flex flex-wrap items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $category->is_featured) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-sm text-stone-700">Избранная категория</span>
                </label>
            </div>
        </x-admin.form-section>
    </form>
@endsection

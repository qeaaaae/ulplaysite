@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.services.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
            @svg('heroicon-o-arrow-left', 'w-5 h-5')
        </a>
        <div class="flex items-center gap-2.5">
            @svg('heroicon-o-wrench-screwdriver', 'w-8 h-8 text-sky-600')
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $service->id ? 'Редактировать услугу' : 'Новая услуга' }}</h1>
                <p class="text-sm text-stone-500">{{ $service->id ? $service->title : 'Заполните поля' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $service->id ? route('admin.services.update', $service) : route('admin.services.store') }}" enctype="multipart/form-data" class="w-full space-y-6">
        @csrf
        @if($service->id) @method('PATCH') @endif

        <x-admin.form-section title="Основное" icon="heroicon-o-document-text">
            <x-ui.input name="title" label="Название" label-icon="heroicon-o-document-text" value="{{ old('title', $service->title) }}" required :error="$errors->first('title')" />
            <x-ui.input name="slug" label="Ярлык" label-icon="heroicon-o-link" value="{{ old('slug', $service->slug) }}" :error="$errors->first('slug')" />
            <div class="lg:col-span-2">
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-document', 'w-4 h-4 text-stone-400')
                    Описание
                </label>
                <textarea name="description" rows="3" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y">{{ old('description', $service->description) }}</textarea>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Цена и тип" icon="heroicon-o-tag">
            <x-ui.input type="number" name="price" label="Цена (₽)" label-icon="heroicon-o-currency-dollar" value="{{ old('price', $service->price) }}" step="0.01" :error="$errors->first('price')" />
            <div>
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-cog-6-tooth', 'w-4 h-4 text-stone-400')
                    Тип
                </label>
                <select name="type" data-enhance="tom-select" class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150" required>
                    <option value="repair" {{ old('type', $service->type) === 'repair' ? 'selected' : '' }}>Ремонт</option>
                    <option value="buy" {{ old('type', $service->type) === 'buy' ? 'selected' : '' }}>Покупка</option>
                </select>
            </div>
        </x-admin.form-section>

        <x-admin.form-section title="Изображение" icon="heroicon-o-photo">
            <x-ui.file-input name="image" accept="image/*" label="Файл изображения" label-icon="heroicon-o-photo" />
            <x-ui.input name="image_path" label="Или URL изображения" label-icon="heroicon-o-link" value="{{ old('image_path', $service->image_path) }}" placeholder="https://..." :error="$errors->first('image_path')" />
        </x-admin.form-section>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                @svg('heroicon-o-check', 'w-5 h-5')
                Сохранить
            </x-ui.button>
            <a href="{{ route('admin.services.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                @svg('heroicon-o-x-mark', 'w-5 h-5')
                Отмена
            </a>
        </div>
    </form>
@endsection

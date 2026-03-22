@extends('layouts.app')

@section('content')
    @php
        $selectClass = 'w-full sm:min-w-[160px] sm:max-w-full px-3 py-2.5 bg-white border border-stone-300 rounded-lg text-sm font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 appearance-none bg-no-repeat pr-9 bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center]';
        $selectStyle = "background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')";
    @endphp
    <div class="py-12 max-w-xl mx-auto px-4" x-data="{ filesCount: 0, onFilesChange(e) { this.filesCount = e.target.files ? e.target.files.length : 0; } }">
        <div class="mb-8">
            <a href="{{ url()->previous() }}" class="inline-flex items-center gap-2 text-sm text-stone-500 hover:text-stone-700 transition-colors mb-4">
                @svg('heroicon-o-arrow-left', 'w-4 h-4')
                Назад
            </a>
            <h1 class="text-2xl font-semibold text-stone-900 flex items-center gap-2">
                @svg('heroicon-o-lifebuoy', 'w-8 h-8 text-sky-600')
                Техническая поддержка
            </h1>
            <p class="text-stone-500 text-sm mt-1">Опишите вашу проблему, и мы постараемся помочь как можно скорее</p>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 shadow-sm p-6 sm:p-8">
            <form method="POST" action="{{ route('support-tickets.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <div class="form-field {{ $errors->has('type') ? 'is-invalid' : '' }}">
                    <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-tag', 'w-4 h-4 text-sky-500')
                        Тип обращения
                    </label>
                    <select
                            name="type"
                            data-enhance="tom-select"
                            class="{{ $selectClass }}"
                            style="{{ $selectStyle }}"
                        >
                            @foreach(\App\Enums\SupportTicketTypeEnum::cases() as $type)
                                <option value="{{ $type->value }}" {{ old('type', \App\Enums\SupportTicketTypeEnum::TECHNICAL_ISSUE->value) === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                            @endforeach
                    </select>
                    @if($errors->has('type'))
                        <p class="mt-1.5 text-sm text-rose-600">{{ $errors->first('type') }}</p>
                    @endif
                </div>

                <x-ui.input
                    name="title"
                    label="Заголовок"
                    label-icon="heroicon-o-chat-bubble-left-ellipsis"
                    value="{{ old('title') }}"
                    required
                    maxlength="255"
                    :error="$errors->first('title')"
                    class="[&_input]:bg-stone-50/50 [&_input]:border-stone-200 [&_input]:rounded-lg [&_input]:focus:border-sky-400 [&_input]:focus:bg-white"
                />

                <div class="form-field {{ $errors->has('description') ? 'is-invalid' : '' }}">
                    <label for="support-description" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-document-text', 'w-4 h-4 text-sky-500')
                        Описание проблемы
                    </label>
                    <textarea
                        name="description"
                        id="support-description"
                        rows="4"
                        required
                        maxlength="3000"
                        class="w-full px-3 py-2.5 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors resize-y"
                        placeholder="Опишите вашу проблему или вопрос..."
                    >{{ old('description') }}</textarea>
                    @if($errors->has('description'))
                        <p class="mt-1.5 text-sm text-rose-600">{{ $errors->first('description') }}</p>
                    @endif
                </div>

                <div class="form-field {{ $errors->has('images') || $errors->has('images.*') ? 'is-invalid' : '' }}">
                    <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-photo', 'w-4 h-4 text-sky-500')
                        Фото (до 3 шт)
                    </label>
                    <input
                        type="file"
                        name="images[]"
                        accept="image/*"
                        multiple
                        @change="onFilesChange($event)"
                        class="block w-full text-sm text-stone-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-sky-50 file:text-sky-700 file:hover:bg-sky-100 file:transition-colors border border-stone-200 rounded-lg bg-white px-3 py-2 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400"
                    >
                    <p class="mt-1.5 text-xs text-stone-500">Выбрано: <span x-text="filesCount" class="font-medium text-stone-700">0</span> / 3</p>
                    @if($errors->has('images') || $errors->has('images.*'))
                        <p class="mt-1.5 text-sm text-rose-600">{{ $errors->first('images') ?: $errors->first('images.*') }}</p>
                    @endif
                </div>

                <div class="pt-2 flex flex-wrap gap-3 justify-end">
                    <a href="{{ url()->previous() }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 border border-stone-300 rounded-xl text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                        Отмена
                    </a>
                    <x-ui.button type="submit" variant="primary">
                        @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                        Отправить
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>
@endsection

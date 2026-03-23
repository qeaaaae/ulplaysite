@extends('layouts.app')

@php
    $statusCode = isset($exception) && method_exists($exception, 'getStatusCode')
        ? $exception->getStatusCode()
        : 500;
    $message = match ((int) $statusCode) {
        404 => 'Страница не найдена. Возможно, она была удалена или ссылка указана неверно.',
        403 => 'Доступ запрещён. У вас нет прав для просмотра этой страницы.',
        500 => 'Внутренняя ошибка сервера. Мы уже работаем над исправлением.',
        503 => 'Сервис временно недоступен. Попробуйте позже.',
        419 => 'Сессия истекла. Обновите страницу и попробуйте снова.',
        429 => 'Слишком много запросов. Подождите немного и попробуйте снова.',
        default => 'Что-то пошло не так.',
    };
    $icon = match ((int) $statusCode) {
        404 => 'heroicon-o-question-mark-circle',
        403 => 'heroicon-o-shield-exclamation',
        500, 503 => 'heroicon-o-server-stack',
        419, 429 => 'heroicon-o-clock',
        default => 'heroicon-o-exclamation-triangle',
    };
@endphp

@section('bodyClass', 'page-error')

@section('content')
    <div class="min-h-[70vh] flex items-center">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto">
                <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-[0_10px_30px_rgba(2,6,23,0.06)]">
                    <div class="p-8 sm:p-10">
                        <div class="flex flex-col items-center text-center">
                            <div class="mx-auto w-16 h-16 rounded-2xl bg-sky-600 flex items-center justify-center mb-6">
                                @svg($icon, 'w-9 h-9 text-white')
                            </div>

                            <h1 class="text-4xl sm:text-5xl font-bold text-stone-900 mb-3">{{ $statusCode }}</h1>
                            <p class="text-stone-600 text-base sm:text-lg mb-8 max-w-2xl">
                                {{ $message }}
                            </p>

                            <div class="flex flex-col sm:flex-row gap-3 justify-center w-full max-w-md">
                                <x-ui.button href="{{ route('home') }}" variant="primary" size="lg">
                                    На главную
                                </x-ui.button>
                                @if ($statusCode === 404)
                                    <x-ui.button href="{{ route('products.index') }}" variant="outline" size="lg">
                                        В каталог
                                    </x-ui.button>
                                @else
                                    <x-ui.button href="javascript:history.back()" variant="outline" size="lg">
                                        Назад
                                    </x-ui.button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 text-center text-xs text-stone-400">
                    Если вам нужна помощь — напишите нам в «Контакты».
                    <a href="{{ route('contacts') }}" class="text-sky-600 hover:text-sky-700 font-medium underline underline-offset-4">
                        Перейти
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

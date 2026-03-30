@extends('layouts.app')

@section('content')
<div class="min-h-[60vh] py-12 px-4 flex items-center justify-center">
    <div class="w-full max-w-md">
        <h1 class="text-2xl font-semibold text-stone-900 mb-2">Восстановление пароля</h1>
        <p class="text-stone-500 text-sm mb-6">Введите email — мы отправим ссылку для сброса пароля.</p>
        @if (session('status'))
            <p class="mb-4 text-sm text-emerald-600">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('password.email') }}" class="space-y-5" data-ajax-forgot-password>
            @csrf
            <x-ui.input
                type="email"
                name="email"
                label="Email"
                value="{{ old('email') }}"
                required
                autofocus
                :error="$errors->first('email')"
            />
            <p class="hidden mt-1.5 text-sm text-rose-600" data-ajax-forgot-error></p>
            <x-ui.button type="submit" variant="primary" class="w-full">
                @svg('heroicon-o-envelope', 'w-4 h-4')
                Отправить ссылку
            </x-ui.button>
            <a href="{{ route('home') }}" class="block text-center text-sm text-sky-600 hover:text-sky-700">На главную</a>
        </form>
    </div>
</div>
@endsection

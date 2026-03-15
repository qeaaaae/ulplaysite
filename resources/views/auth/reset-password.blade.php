@extends('layouts.app')

@section('content')
    <div class="min-h-[60vh] py-12 flex items-center justify-center">
        <div class="w-full max-w-md">
            <h1 class="text-2xl font-semibold text-stone-900 mb-6">Новый пароль</h1>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <x-ui.input
                    type="email"
                    name="email"
                    label="Email"
                    value="{{ $email }}"
                    required
                    readonly
                />
                <x-ui.input
                    type="password"
                    name="password"
                    label="Новый пароль"
                    required
                    autofocus
                    autocomplete="new-password"
                    :error="$errors->first('password')"
                />
                <x-ui.input
                    type="password"
                    name="password_confirmation"
                    label="Подтвердите пароль"
                    required
                    autocomplete="new-password"
                />
                <x-ui.button type="submit" variant="primary" class="w-full">Сохранить пароль</x-ui.button>
            </form>
        </div>
    </div>
@endsection

@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
            @svg('heroicon-o-arrow-left', 'w-5 h-5')
        </a>
        <div class="flex items-center gap-2.5">
            @svg('heroicon-o-users', 'w-8 h-8 text-sky-600')
            <div>
                <h1 class="text-2xl font-semibold text-stone-900">{{ $user->id ? 'Редактировать пользователя' : 'Новый пользователь' }}</h1>
                <p class="text-sm text-stone-500">{{ $user->id ? $user->name : 'Заполните поля' }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ $user->id ? route('admin.users.update', $user) : route('admin.users.store') }}" class="w-full space-y-6">
        @csrf
        @if($user->id) @method('PATCH') @endif

        <x-admin.form-section title="Контакты" icon="heroicon-o-user-circle">
            <x-ui.input name="name" label="Имя" label-icon="heroicon-o-user" value="{{ old('name', $user->name) }}" required :error="$errors->first('name')" />
            <x-ui.input name="email" label="Email" label-icon="heroicon-o-envelope" type="email" value="{{ old('email', $user->email) }}" required :error="$errors->first('email')" />
            <x-ui.phone-input name="phone" label="Телефон" label-icon="heroicon-o-phone" :value="old('phone', $user->phone)" :error="$errors->first('phone')" />
        </x-admin.form-section>

        <x-admin.form-section title="Пароль" icon="heroicon-o-key">
            @if($user->id)
                <x-ui.input name="password" label="Новый пароль (оставьте пустым, чтобы не менять)" label-icon="heroicon-o-key" type="password" :error="$errors->first('password')" placeholder="••••••••" />
            @else
                <x-ui.input name="password" label="Пароль" label-icon="heroicon-o-key" type="password" required :error="$errors->first('password')" placeholder="••••••••" />
            @endif
        </x-admin.form-section>

        <x-admin.form-section title="Права" icon="heroicon-o-shield-check">
            <div class="flex flex-wrap gap-6 lg:col-span-2">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_admin" value="1" {{ old('is_admin', $user->is_admin) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-sm text-stone-700">Администратор</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_blocked" value="1" {{ old('is_blocked', $user->is_blocked) ? 'checked' : '' }} class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-sm text-stone-700">Заблокирован</span>
                </label>
            </div>
        </x-admin.form-section>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <x-ui.button type="submit" variant="primary" class="inline-flex items-center gap-2">
                @svg('heroicon-o-check', 'w-5 h-5')
                Сохранить
            </x-ui.button>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                @svg('heroicon-o-x-mark', 'w-5 h-5')
                Отмена
            </a>
        </div>
    </form>
@endsection

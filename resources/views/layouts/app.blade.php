<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $metaTitle ?? 'Главная' }} - {{ config('app.name', 'UlPlay') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    @endif

    @stack('styles')
</head>
<body class="font-sans antialiased min-h-screen overflow-x-hidden @yield('bodyClass')" data-notyf-message="{{ session('message') }}">
    @include('partials.loader')
    <div id="app" class="min-h-screen flex flex-col overflow-x-hidden" x-data="{
        mobileMenuOpen: false,
        searchOpen: false,
        authModalOpen: false,
        authModalType: 'login',
        authLoading: false,
        authErrors: {},
        dialogOpen: false,
        dialogTitle: 'Сообщение',
        dialogMessage: '',
        dialogShowCancel: false,
        dialogCallback: null,
        openAuthModal(type) { this.authModalType = type; this.authModalOpen = true; this.authErrors = {}; },
        closeAuthModal() { this.authModalOpen = false; this.authErrors = {}; },
        openAlert(message, title) {
            this.dialogTitle = title || 'Сообщение';
            this.dialogMessage = message || '';
            this.dialogShowCancel = false;
            this.dialogCallback = null;
            this.dialogOpen = true;
        },
        openConfirm(message, callback, title) {
            this.dialogTitle = title || 'Подтверждение';
            this.dialogMessage = message || '';
            this.dialogShowCancel = true;
            this.dialogCallback = typeof callback === 'function' ? callback : null;
            this.dialogOpen = true;
        },
        closeDialog() {
            this.dialogOpen = false;
            this.dialogCallback = null;
        },
        confirmDialog() {
            if (this.dialogCallback) this.dialogCallback(true);
            this.closeDialog();
        },
        cancelDialog() {
            if (this.dialogCallback) this.dialogCallback(false);
            this.closeDialog();
        },
        init() {
            window.ulplayAlert = (msg, title) => this.openAlert(msg, title);
            window.ulplayConfirm = (msg, callback, title) => this.openConfirm(msg, callback, title);
        },
        async submitAuthForm(form) {
            const ctx = this;
            ctx.authLoading = true;
            ctx.authErrors = {};
            const formData = new FormData(form);
            const url = this.authModalType === 'login' ? '{{ route('login') }}' : '{{ route('register') }}';
            let timeoutId;
            try {
                const controller = new AbortController();
                timeoutId = window.setTimeout(() => controller.abort(), 15000);
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: controller.signal
                });

                let data = {};
                const contentType = (res.headers.get('content-type') || '');
                try {
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    }
                } catch (e) {}

                if (!res.ok && !data) data = {};
                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                if (data.errors) ctx.authErrors = data.errors;
                else if (data.message) ctx.authErrors = { email: [data.message] };
            } catch (e) {
                const msg = e?.name === 'AbortError' ? 'Превышено время ожидания.' : 'Ошибка соединения';
                ctx.authErrors = { email: [msg] };
            } finally {
                ctx.authLoading = false;
                if (timeoutId != null) clearTimeout(timeoutId);
            }
        }
    }"
         x-on:open-auth-modal.window="openAuthModal($event.detail?.type || 'login')">
        @php
            $needsEmailVerify = auth()->check()
                && !auth()->user()->hasVerifiedEmail();
        @endphp

        @if($needsEmailVerify)
            <div class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
                <div class="w-full max-w-md bg-white rounded-xl shadow-2xl p-6 sm:p-8 ring-1 ring-black/5 text-center">
                    <h2 class="text-xl font-semibold text-stone-900 mb-2">Подтвердите адрес электронной почты</h2>
                    <p class="text-stone-500 text-sm mb-6">
                        Мы отправили письмо с ссылкой подтверждения. Пожалуйста, перейдите по ней.
                    </p>

                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-ui.button type="submit" variant="primary" class="w-full">
                            Отправить повторно
                        </x-ui.button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <x-ui.button type="submit" variant="outline" class="w-full">
                            Выйти
                        </x-ui.button>
                    </form>

                    <p class="mt-3 text-xs text-stone-400">
                        Повторная отправка ограничена (rate limit).
                    </p>
                </div>
            </div>
        @endif

        @include('partials.header')

        <main class="flex-1">
            @yield('content')
        </main>

        <div x-data="{ open: false, filesCount: 0, onFilesChange(event) { this.filesCount = event.target.files ? event.target.files.length : 0; } }">
            <button
                type="button"
                @click="open = true"
                aria-label="Техническая поддержка"
                class="fixed right-4 bottom-4 z-[9997] inline-flex items-center justify-center w-12 h-12 rounded-full bg-sky-600 text-white shadow-lg hover:bg-sky-700 transition-colors"
            >
                @svg('heroicon-o-lifebuoy', 'w-5 h-5')
            </button>

            <div
                x-show="open"
                x-cloak
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[9998] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="open = false"
            >
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full max-w-xl bg-white rounded-xl shadow-2xl p-6 sm:p-8 ring-1 ring-black/5"
                >
                    <div class="flex items-start justify-between gap-3 mb-6">
                        <div class="flex items-center gap-2">
                            @svg('heroicon-o-lifebuoy', 'w-6 h-6 text-sky-500')
                            <h2 class="text-xl font-semibold text-stone-900">Техническая поддержка</h2>
                        </div>
                        <button type="button" @click="open = false" class="p-2 rounded-lg text-stone-400 hover:bg-stone-100 hover:text-stone-700 transition-colors" aria-label="Закрыть">
                            @svg('heroicon-o-x-mark', 'w-5 h-5')
                        </button>
                    </div>

                    <form method="POST" action="{{ route('support-tickets.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf
                        <div class="form-field {{ $errors->has('type') ? 'is-invalid' : '' }}">
                            <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                                @svg('heroicon-o-tag', 'w-4 h-4 text-sky-500')
                                Тип обращения
                            </label>
                            <select
                                name="type"
                                data-enhance="tom-select"
                                class="w-full h-11 px-3 py-2.5 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors"
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
                            <x-ui.button type="button" variant="outline" @click="open = false">Отмена</x-ui.button>
                            <x-ui.button type="submit" variant="primary">
                                @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                                Отправить
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @include('partials.footer')

        @include('partials.auth-modal')

        <x-ui.dialog />
    </div>

    <template id="cart-in-button-tpl">
        @if(auth()->check())
            <a href="{{ route('cart.index') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 rounded-md cursor-pointer transition-colors">
                @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                В корзине
            </a>
        @else
            <button type="button" @click="openAuthModal('login')" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 rounded-md cursor-pointer transition-colors">
                @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                В корзине
            </button>
        @endif
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const msg = document.body?.dataset?.notyfMessage;
            if (msg) notyf.success(msg);
        });
    </script>

    @stack('scripts')
</body>
</html>

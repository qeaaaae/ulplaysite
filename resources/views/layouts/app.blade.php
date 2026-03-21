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
            this.authLoading = true;
            this.authErrors = {};
            const formData = new FormData(form);
            const url = this.authModalType === 'login' ? '{{ route('login') }}' : '{{ route('register') }}';
            try {
                const controller = new AbortController();
                const timeoutId = window.setTimeout(() => controller.abort(), 15000);
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: controller.signal
                });

                let data = {};
                const contentType = res.headers.get('content-type') || '';
                try {
                    if (contentType.includes('application/json')) {
                        data = await res.json();
                    } else {
                        // На случай редиректа/HTML-ответа вместо JSON
                        data = {};
                    }
                } catch (e) {
                    data = {};
                }

                if (!res.ok && !data) data = {};
                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                if (data.errors) this.authErrors = data.errors;
                if (data.message) this.authErrors = { email: [data.message] };
            } catch (e) {
                const msg = e?.name === 'AbortError' ? 'Превышено время ожидания.' : 'Ошибка соединения';
                this.authErrors = { email: [msg] };
            } finally {
                clearTimeout(timeoutId);
                this.authLoading = false;
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

        <div x-data="{ open: false, filesCount: 0, selectedType: '{{ old('type', \App\Enums\SupportTicketTypeEnum::TECHNICAL_ISSUE->value) }}', typeClasses: { technical_issue: 'text-rose-700', order_issue: 'text-amber-700', delivery: 'text-cyan-700', service_repair: 'text-violet-700', return_exchange: 'text-orange-700', suggestion: 'text-emerald-700' }, onFilesChange(event) { this.filesCount = event.target.files ? event.target.files.length : 0; } }">
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
                class="fixed inset-0 z-[9998] bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
                @click.self="open = false"
            >
                <div class="w-full max-w-xl bg-white rounded-xl shadow-2xl p-5 sm:p-6 ring-1 ring-black/5">
                    <div class="flex items-center justify-between gap-3 mb-4">
                        <h2 class="text-lg sm:text-xl font-semibold text-stone-900">Техническая поддержка</h2>
                        <button type="button" @click="open = false" class="inline-flex items-center justify-center w-8 h-8 rounded-md text-stone-500 hover:bg-stone-100 hover:text-stone-700">
                            @svg('heroicon-o-x-mark', 'w-5 h-5')
                        </button>
                    </div>

                    <form method="POST" action="{{ route('support-tickets.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                                @svg('heroicon-o-tag', 'w-4 h-4 text-stone-400')
                                Тип обращения
                            </label>
                            <select
                                name="type"
                                x-model="selectedType"
                                :class="typeClasses[selectedType] || 'text-stone-900'"
                                class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150"
                            >
                                @foreach(\App\Enums\SupportTicketTypeEnum::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                            @if($errors->has('type'))
                                <p class="mt-1 text-sm text-rose-600">{{ $errors->first('type') }}</p>
                            @endif
                        </div>

                        <x-ui.input
                            name="title"
                            label="Заголовок"
                            value="{{ old('title') }}"
                            required
                            maxlength="255"
                            :error="$errors->first('title')"
                        />

                        <x-ui.textarea
                            name="description"
                            label="Описание проблемы"
                            rows="4"
                            required
                            maxlength="3000"
                            :error="$errors->first('description')"
                        >{{ old('description') }}</x-ui.textarea>

                        <div>
                            <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                                @svg('heroicon-o-photo', 'w-4 h-4 text-stone-400')
                                Фото (до 3 шт)
                            </label>
                            <input
                                type="file"
                                name="images[]"
                                accept="image/*"
                                multiple
                                @change="onFilesChange($event)"
                                class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-sm text-stone-900 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:bg-sky-50 file:text-sky-700 file:cursor-pointer"
                            >
                            <p class="mt-1 text-xs text-stone-500">Выбрано файлов: <span x-text="filesCount"></span> / 3</p>
                            @if($errors->has('images') || $errors->has('images.*'))
                                <p class="mt-1 text-sm text-rose-600">{{ $errors->first('images') ?: $errors->first('images.*') }}</p>
                            @endif
                        </div>

                        <div class="pt-1 flex flex-wrap gap-2 justify-end">
                            <x-ui.button type="button" variant="outline" @click="open = false">Отмена</x-ui.button>
                            <x-ui.button type="submit" variant="primary">Отправить</x-ui.button>
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

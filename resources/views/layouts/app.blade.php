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

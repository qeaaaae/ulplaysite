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
<body class="font-sans antialiased min-h-screen overflow-x-hidden @yield('bodyClass')">
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
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (res.ok && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                if (data.errors) this.authErrors = data.errors;
                if (data.message) this.authErrors = { email: [data.message] };
            } catch (e) {
                this.authErrors = { email: ['Ошибка соединения'] };
            } finally {
                this.authLoading = false;
            }
        }
    }"
         x-on:open-auth-modal.window="openAuthModal($event.detail?.type || 'login')">
        @include('partials.header')

        <main class="flex-1">
            @yield('content')
        </main>

        @include('partials.footer')

        @include('partials.auth-modal')

        <x-ui.dialog />
    </div>

    <template id="cart-in-button-tpl">
        <a href="{{ route('cart.index') }}" class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 rounded-md cursor-pointer transition-colors">
            @svg('heroicon-o-shopping-cart', 'w-4 h-4')
            В корзине
        </a>
    </template>

    @if (session('message'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            notyf.success(@json(session('message')));
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>

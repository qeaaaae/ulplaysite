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
<body class="font-sans antialiased min-h-screen flex flex-col overflow-x-hidden @yield('bodyClass')" data-notyf-message="{{ session('message') }}" data-auth-intended-checkout="{{ (session()->has('url.intended') && str_contains(session('url.intended', ''), 'checkout')) ? '1' : '0' }}">
    @include('partials.loader')
    <div id="app" class="flex-1 flex flex-col min-h-0 overflow-x-hidden" x-data="{
        mobileMenuOpen: false,
        userMenuOpen: false,
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
        supportTicketModalOpen: false,
        supportTicketServiceId: null,
        supportTicketType: '{{ \App\Enums\SupportTicketTypeEnum::SERVICE_INQUIRY->value }}',
        supportTicketModalTitle: '',
        openSupportTicketModal(detail) {
            const d = detail || {};
            const type = d.type || '{{ \App\Enums\SupportTicketTypeEnum::SERVICE_INQUIRY->value }}';
            this.supportTicketServiceId = d.serviceId != null ? d.serviceId : null;
            this.supportTicketType = type;
            if (d.title) {
                this.supportTicketModalTitle = type === '{{ \App\Enums\SupportTicketTypeEnum::SUGGESTION->value }}'
                    ? d.title
                    : ('Вопрос по услуге: ' + d.title);
            } else {
                this.supportTicketModalTitle = type === '{{ \App\Enums\SupportTicketTypeEnum::SUGGESTION->value }}'
                    ? 'Предложение товара'
                    : 'Вопрос по услуге';
            }
            this.supportTicketModalOpen = true;
        },
        closeSupportTicketModal() {
            this.supportTicketModalOpen = false;
            this.supportTicketServiceId = null;
            this.supportTicketType = '{{ \App\Enums\SupportTicketTypeEnum::SERVICE_INQUIRY->value }}';
        },
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
            try {
                const raw = localStorage.getItem('ulplay_guest_cart');
                if (raw) formData.append('_guest_cart', raw);
            } catch (e) {}
            const url = this.authModalType === 'login' ? '{{ route('login') }}' : '{{ route('register') }}';
            let timeoutId;
            try {
                const controller = new AbortController();
                timeoutId = window.setTimeout(() => controller.abort(), 15000);
                const res = await fetch(url, {
                    method: 'POST',
                    body: formData,
                    credentials: 'include',
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
                    try { localStorage.removeItem('ulplay_guest_cart'); } catch (e) {}
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
         x-on:open-auth-modal.window="openAuthModal($event.detail?.type || 'login')"
         x-on:open-support-ticket-modal.window="openSupportTicketModal($event.detail)"
         @keydown.escape.window="userMenuOpen = false; if (supportTicketModalOpen) closeSupportTicketModal()">
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

                    <form method="POST" action="{{ route('verification.send') }}" data-ajax-verification-send>
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
                        Не более 3 отправок в 5 минут.
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

        @auth
            <x-support-ticket-modal />
        @endauth

        <x-ui.dialog />
    </div>

    <template id="cart-in-button-tpl">
        @if(auth()->check())
            <a href="{{ route('cart.index') }}" class="inline-flex shrink-0 items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 rounded-md cursor-pointer transition-colors">
                @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                В корзине
            </a>
        @else
            <button type="button" @click="openAuthModal('login')" class="inline-flex shrink-0 items-center justify-center gap-2 px-3 py-1.5 text-sm font-medium border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 rounded-md cursor-pointer transition-colors">
                @svg('heroicon-o-shopping-cart', 'w-4 h-4')
                В корзине
            </button>
        @endif
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const msg = document.body?.dataset?.notyfMessage;
            if (msg) notyf.success(msg);
            if (document.body?.dataset?.authIntendedCheckout === '1') {
                window.dispatchEvent(new CustomEvent('open-auth-modal', { detail: { type: 'login' } }));
            }
        });
    </script>

    @stack('scripts')
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(config('webpush.vapid.public_key'))
    <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endif
    <title>{{ $metaTitle ?? 'Админка' }} - UlPlay</title>
    <style>[x-cloak]{display:none!important}</style>
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    @endif
</head>
<body class="ulplay-admin bg-stone-100 text-stone-900 font-sans antialiased" x-data="{
    sidebarOpen: false,
    dialogOpen: false,
    dialogTitle: 'Подтверждение',
    dialogMessage: '',
    dialogShowCancel: true,
    dialogCallback: null,
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
    promptOpen: false,
    promptTitle: '',
    promptValue: '',
    promptPlaceholder: '',
    promptResolve: null,
    openPrompt(title, defaultValue, placeholder) {
        this.promptTitle = title || '';
        this.promptValue = defaultValue || '';
        this.promptPlaceholder = placeholder || '';
        this.promptOpen = true;
        this.$nextTick(() => { this.$refs.promptInput?.focus(); this.$refs.promptInput?.select(); });
        return new Promise((resolve) => { this.promptResolve = resolve; });
    },
    confirmPrompt() {
        if (this.promptResolve) this.promptResolve(this.promptValue);
        this.promptResolve = null;
        this.promptOpen = false;
    },
    cancelPrompt() {
        if (this.promptResolve) this.promptResolve(null);
        this.promptResolve = null;
        this.promptOpen = false;
    }
}" x-init="(function(d){
    window.ulplayConfirm = function(msg, cb, title) { d.openConfirm(msg, cb, title); };
    window.ulplayPrompt = function(title, defaultValue, placeholder) { return d.openPrompt(title, defaultValue, placeholder); };
})($data)">
    <div class="flex min-h-screen">
        <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
             class="fixed inset-0 z-40 bg-stone-900/50 lg:hidden" x-transition:enter="transition-opacity ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <aside class="fixed lg:static inset-y-0 left-0 z-50 w-64 bg-sky-900 text-white flex flex-col transform transition-transform duration-200 ease-out lg:translate-x-0 -translate-x-full"
             :class="{ 'translate-x-0': sidebarOpen }">
            <div class="flex items-center justify-between p-4 border-b border-sky-800">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-2 text-lg font-semibold">
                    @svg('heroicon-o-cog-6-tooth', 'w-6 h-6 text-sky-400')
                    UlPlay Admin
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-2 -mr-2 rounded-md hover:bg-sky-800" aria-label="Закрыть меню">
                    @svg('heroicon-o-x-mark', 'w-5 h-5')
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto p-4 space-y-1">
                <a href="{{ route('admin.statistics.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.statistics.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-chart-bar', 'w-5 h-5 shrink-0')
                    Статистика
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.products.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-cube', 'w-5 h-5 shrink-0')
                    Товары
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.categories.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-squares-2x2', 'w-5 h-5 shrink-0')
                    Категории
                </a>
                <a href="{{ route('admin.services.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.services.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-wrench-screwdriver', 'w-5 h-5 shrink-0')
                    Услуги
                </a>
                <a href="{{ route('admin.news.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.news.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-newspaper', 'w-5 h-5 shrink-0')
                    Новости
                </a>
                <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.banners.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-photo', 'w-5 h-5 shrink-0')
                    Баннеры
                </a>
                <a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.orders.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5 shrink-0')
                    Заказы
                </a>
                <a href="{{ route('admin.tickets.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.tickets.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-lifebuoy', 'w-5 h-5 shrink-0')
                    Тикеты
                </a>
                <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-sky-800/80 {{ request()->routeIs('admin.users.*') ? 'bg-sky-800' : '' }}" @click="sidebarOpen = false">
                    @svg('heroicon-o-users', 'w-5 h-5 shrink-0')
                    Пользователи
                </a>
            </nav>
            <div class="p-4 border-t border-sky-800 space-y-2">
                @if (config('webpush.vapid.public_key'))
                <button type="button" id="admin-push-enable" class="flex items-center gap-2 w-full text-left text-sm text-sky-400 hover:text-white transition-colors py-1" @click="sidebarOpen = false" title="Показать запрос браузера «Разрешить уведомления»">
                    @svg('heroicon-o-bell', 'w-4 h-4 shrink-0')
                    Включить уведомления
                </button>
                <button type="button" id="admin-push-test" class="flex items-center gap-2 w-full text-left text-sm text-sky-400 hover:text-white transition-colors py-1" @click="sidebarOpen = false">
                    @svg('heroicon-o-bell-alert', 'w-4 h-4 shrink-0')
                    Проверить уведомления
                </button>
                @endif
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-sm text-sky-400 hover:text-white transition-colors" @click="sidebarOpen = false">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4')
                    На сайт
                </a>
            </div>
        </aside>
        <div class="flex-1 flex flex-col min-w-0">
            <header class="sticky top-0 z-30 flex items-center gap-3 px-4 py-3 bg-white border-b border-stone-200 lg:hidden">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-md hover:bg-stone-100" aria-label="Меню">
                    @svg('heroicon-o-bars-3', 'w-6 h-6 text-stone-600')
                </button>
                <span class="font-semibold text-stone-900">UlPlay Admin</span>
            </header>
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
                @yield('content')
            </main>
        </div>
    </div>
    <x-ui.dialog />
    @stack('scripts')
    @if (session('message'))
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            notyf.success(@json(session('message')));
        });
    </script>
    @endif
    @if (config('webpush.vapid.public_key'))
    <script>
        (function() {
            var vapidKey = document.querySelector('meta[name="vapid-public-key"]');
            if (!vapidKey || !('Notification' in window) || !('serviceWorker' in navigator)) return;

            function urlBase64ToUint8Array(base64String) {
                var padding = '='.repeat((4 - base64String.length % 4) % 4);
                var base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
                var rawData = window.atob(base64);
                var out = new Uint8Array(rawData.length);
                for (var i = 0; i < rawData.length; ++i) out[i] = rawData.charCodeAt(i);
                return out;
            }

            function saveSubscription(subscription) {
                var body = JSON.stringify({
                    endpoint: subscription.endpoint,
                    keys: {
                        p256dh: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('p256dh')))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, ''),
                        auth: btoa(String.fromCharCode.apply(null, new Uint8Array(subscription.getKey('auth')))).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '')
                    },
                    contentEncoding: 'aes128gcm'
                });
                var csrf = document.querySelector('meta[name="csrf-token"]');
                return fetch('{{ route('admin.push-subscription.store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body
                }).then(function(r) { return r.ok ? r.json() : Promise.reject(); }).then(function() { return true; }).catch(function() { return false; });
            }

            function subscribe(reg) {
                reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidKey.getAttribute('content'))
                }).then(function(sub) { saveSubscription(sub); });
            }

            function playNotificationSound(url) {
                var audio = new Audio(url);
                audio.volume = 0.6;
                audio.play().catch(function() {});
            }

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.addEventListener('message', function(event) {
                    if (event.data && event.data.type === 'PLAY_NOTIFICATION_SOUND' && event.data.url) {
                        playNotificationSound(event.data.url);
                    }
                });
            }

            document.addEventListener('DOMContentLoaded', function() {
                navigator.serviceWorker.register('{{ asset('sw.js') }}').then(function(reg) {
                    if (Notification.permission === 'granted') {
                        reg.pushManager.getSubscription().then(function(sub) { if (!sub) subscribe(reg); });
                    } else if (Notification.permission !== 'denied') {
                        Notification.requestPermission().then(function(p) { if (p === 'granted') subscribe(reg); });
                    }
                });

                document.getElementById('admin-push-enable')?.addEventListener('click', function() {
                    if (window.adminPushEnable) window.adminPushEnable();
                });
                document.getElementById('admin-push-test')?.addEventListener('click', function() {
                    var btn = this;
                    btn.disabled = true;
                    fetch('{{ route('admin.push-subscription.test') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }).then(function(r) { return r.json(); }).then(function(d) {
                        if (d.success) notyf.success(d.message);
                        else notyf.error(d.message || 'Не удалось отправить');
                    }).catch(function() { notyf.error('Ошибка запроса'); }).finally(function() { btn.disabled = false; });
                });
            });

            window.adminPushEnable = function() {
                if (!('Notification' in window)) { notyf.error('Браузер не поддерживает уведомления'); return; }
                if (Notification.permission === 'denied') {
                    notyf.error('Уведомления заблокированы. Разрешите в настройках сайта.');
                    return;
                }
                function doSubscribe() {
                    navigator.serviceWorker.ready.then(function(reg) {
                        reg.pushManager.getSubscription().then(function(sub) {
                            if (sub) {
                                saveSubscription(sub).then(function(ok) {
                                    if (ok) notyf.success('Подписка сохранена.');
                                    else notyf.error('Не удалось сохранить подписку.');
                                });
                                return;
                            }
                            reg.pushManager.subscribe({
                                userVisibleOnly: true,
                                applicationServerKey: urlBase64ToUint8Array(vapidKey.getAttribute('content'))
                            }).then(function(subscription) {
                                saveSubscription(subscription).then(function(ok) {
                                    if (ok) notyf.success('Подписка сохранена.');
                                    else notyf.error('Не удалось сохранить подписку.');
                                });
                            }).catch(function() {
                                notyf.error('Ошибка подписки. Попробуйте снова.');
                            });
                        });
                    }).catch(function() {
                        notyf.error('Service Worker не готов. Обновите страницу.');
                    });
                }
                if (Notification.permission === 'granted') doSubscribe();
                else Notification.requestPermission().then(function(p) {
                    if (p === 'granted') doSubscribe();
                    else if (p === 'denied') notyf.error('Уведомления отклонены');
                });
            };
        })();
    </script>
    @endif
</body>
</html>

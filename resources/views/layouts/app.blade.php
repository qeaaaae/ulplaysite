<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $metaTitle ?? 'Главная' }} — {{ config('app.name', 'UlPlay') }}</title>

    <link rel="icon" type="image/svg+xml" href="https://api.dicebear.com/7.x/identicon/svg?seed=ulplay&backgroundColor=0ea5e9">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="{{ asset('build/assets/app.css') }}">
    @endif

    @stack('styles')
</head>
<body class="bg-stone-50 text-stone-900 font-sans antialiased">
    <div id="app" x-data="{ mobileMenuOpen: false, searchOpen: false, toastShow: false, toastMessage: '' }"
         x-on:toast.window="toastMessage = $event.detail?.message || 'Товар добавлен в корзину'; toastShow = true; setTimeout(() => toastShow = false, 2500)">
        @include('partials.header')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')

        <div x-show="toastShow" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 px-4 py-3 rounded-lg bg-stone-800 text-white text-sm font-medium shadow-lg flex items-center gap-2"
             role="alert">
            @svg('heroicon-o-check-circle', 'w-5 h-5 text-emerald-400 shrink-0')
            <span x-text="toastMessage"></span>
        </div>
    </div>

    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    @stack('scripts')
</body>
</html>

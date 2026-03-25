@php
    $company = ($footerData ?? [])['company'] ?? config('site.footer.company', []);
@endphp
<div class="bg-stone-800 text-white border-b border-stone-700 overflow-x-hidden">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 md:px-8 min-w-0">
        <div class="flex flex-wrap items-center justify-center sm:justify-between gap-x-4 gap-y-1.5 py-2 text-xs min-w-0">
            @if(!empty($company['address']))
                <a href="{{ route('contacts') }}" class="order-1 basis-full sm:basis-auto flex items-center justify-center sm:justify-start gap-1.5 hover:text-sky-400 transition-colors">
                    @svg('heroicon-o-map-pin', 'w-3.5 h-3.5 text-sky-400 shrink-0')
                    <span class="hidden sm:inline">{{ $company['address'] }}</span>
                    <span class="sm:hidden">г. Ульяновск, ул. Игошина, д. 3</span>
                </a>
            @endif
            @if(!empty($company['phone']))
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company['phone']) }}" class="order-2 flex items-center gap-1.5 hover:text-sky-400 transition-colors">
                    @svg('heroicon-o-phone', 'w-3.5 h-3.5 text-sky-400 shrink-0')
                    {{ $company['phone'] }}
                </a>
            @endif
            @if(!empty($company['email']))
                <a href="mailto:{{ $company['email'] }}" class="order-3 flex items-center gap-1.5 hover:text-sky-400 transition-colors">
                    @svg('heroicon-o-envelope', 'w-3.5 h-3.5 text-sky-400 shrink-0')
                    {{ $company['email'] }}
                </a>
            @endif
            @if(!empty($company['schedule']))
                <span class="order-4 flex items-center justify-center sm:justify-start gap-1.5 text-white min-w-0 w-full sm:w-auto basis-full sm:basis-auto">
                    <span class="shrink-0 text-sky-400">@svg('heroicon-o-clock', 'w-3.5 h-3.5')</span>
                    <span class="text-center sm:text-left break-words">{{ $company['schedule'] }}</span>
                </span>
            @endif
        </div>
    </div>
</div>
<header class="sticky top-0 z-40 bg-white/98 backdrop-blur-sm border-b border-stone-200 shadow-[0_1px_0_0_rgba(0,0,0,0.04)]">
    <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
        <div class="flex items-center justify-between lg:justify-start gap-3 lg:gap-5 h-14 sm:h-16 min-w-0">
            <div class="flex items-center gap-4 min-w-0 shrink-0">
                <a href="{{ route('home') }}" class="flex items-center gap-2 text-stone-900 hover:text-sky-600 transition-colors duration-200 shrink-0">
                    <span class="font-heading text-xl font-semibold">UlPlay</span>
                </a>

                <nav class="hidden lg:flex items-center gap-0.5">
                    <a href="/products?category=playstation" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                        <x-icons.playstation class="w-4 h-4 shrink-0" />
                        PlayStation
                    </a>
                    <a href="/products?category=xbox" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                        <x-icons.xbox class="w-4 h-4 shrink-0" />
                        Xbox
                    </a>
                    <a href="/products?category=nintendo" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                        <x-icons.nintendo class="w-4 h-4 shrink-0" />
                        Nintendo
                    </a>
                    <a href="{{ route('services.index') }}" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                        @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')
                        Услуги
                    </a>
                    <a href="/news" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                        @svg('heroicon-o-newspaper', 'w-4 h-4')
                        Новости
                    </a>
                </nav>
            </div>

            <div class="hidden lg:flex flex-1 min-w-0 justify-center px-2 xl:px-4">
                <div class="w-full max-w-xl">
                    <x-ui.search-form action="{{ route('search.index') }}" placeholder="Товары, услуги, новости..." :value="request('q', '')" />
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                @if($isAuthenticated ?? false)
                    <a href="{{ route('cart.index') }}" class="hidden lg:flex relative items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer">
                        @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-sky-600 text-white text-[10px] font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </a>
                @else
                    <button type="button" @click="openAuthModal('login')" class="hidden lg:flex relative items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer">
                        @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                        <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-sky-600 text-white text-[10px] font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </button>
                @endif
                @if($isAuthenticated ?? false)
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-stone-300 text-stone-700 rounded-md hover:border-rose-400 hover:text-rose-600 hover:bg-rose-50/80 transition-colors cursor-pointer">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'w-4 h-4')
                            Выйти
                        </button>
                    </form>
                    <div class="relative hidden lg:block" @click.outside="userMenuOpen = false">
                        <button
                            type="button"
                            id="user-menu-trigger"
                            @click="userMenuOpen = !userMenuOpen"
                            :aria-expanded="userMenuOpen"
                            aria-haspopup="menu"
                            aria-controls="user-menu-dropdown"
                            class="flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer outline-none focus-visible:ring-2 focus-visible:ring-sky-500/35 focus-visible:ring-offset-0"
                            :class="userMenuOpen ? 'bg-sky-50/90 text-sky-700 ring-1 ring-sky-200/80' : ''"
                            title="Уведомления, поддержка, профиль и выход"
                        >
                            @svg('heroicon-o-squares-2x2', 'w-5 h-5')
                        </button>
                        <div
                            id="user-menu-dropdown"
                            x-show="userMenuOpen"
                            x-cloak
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                            class="absolute right-0 top-full mt-1.5 w-64 rounded-xl border border-stone-200 bg-white py-1.5 shadow-lg z-50 ring-1 ring-black/5"
                            role="menu"
                            aria-labelledby="user-menu-trigger"
                        >
                            <a href="{{ route('profile') }}" @click="userMenuOpen = false" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 text-sm text-stone-700 hover:bg-stone-50 transition-colors">
                                <span class="shrink-0 text-stone-500">@svg('heroicon-o-user-circle', 'w-5 h-5')</span>
                                <span class="font-medium">Профиль</span>
                            </a>
                            <a href="{{ route('notifications.index') }}" @click="userMenuOpen = false" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 text-sm text-stone-700 hover:bg-stone-50 transition-colors relative">
                                <span class="shrink-0 text-stone-500">@svg('heroicon-o-bell', 'w-5 h-5')</span>
                                <span class="font-medium">Уведомления</span>
                                @if(($notificationsUnreadCount ?? 0) > 0)
                                    <span class="inline-flex min-w-[1.25rem] h-5 px-1.5 ml-auto rounded-full bg-rose-600 text-white text-xs font-semibold items-center justify-center">{{ $notificationsUnreadCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('tickets.my.index') }}" @click="userMenuOpen = false" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 text-sm text-stone-700 hover:bg-stone-50 transition-colors">
                                <span class="shrink-0 text-stone-500">@svg('heroicon-o-lifebuoy', 'w-5 h-5')</span>
                                <span class="font-medium">Техподдержка</span>
                            </a>
                            @if(auth()->user()?->is_admin)
                                <a href="{{ route('admin.index') }}" @click="userMenuOpen = false" role="menuitem" class="flex items-center gap-3 px-3 py-2.5 text-sm text-stone-700 hover:bg-stone-50 transition-colors">
                                    <span class="shrink-0 text-stone-500">@svg('heroicon-o-cog-6-tooth', 'w-5 h-5')</span>
                                    <span class="font-medium">Админка</span>
                                </a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}" class="w-full" role="none">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 px-3 py-2.5 text-left text-sm font-medium text-stone-700 hover:bg-stone-50 transition-colors cursor-pointer" role="menuitem">
                                    <span class="shrink-0 text-stone-500">@svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5')</span>
                                    Выйти
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <button type="button" @click="openAuthModal('login')" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-stone-300 text-stone-700 rounded-md hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 transition-colors cursor-pointer">
                        @svg('heroicon-o-arrow-right-on-rectangle', 'w-4 h-4')
                        Войти
                    </button>
                @endif
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:bg-stone-100 transition-colors duration-200 touch-manipulation cursor-pointer shrink-0" aria-label="Меню">
                    <span x-show="!mobileMenuOpen" class="inline-block">
                        @svg('heroicon-o-bars-3', 'w-6 h-6')
                    </span>
                    <span x-show="mobileMenuOpen" x-cloak class="inline-block">
                        @svg('heroicon-o-x-mark', 'w-6 h-6')
                    </span>
                </button>
            </div>
        </div>

        <div x-show="mobileMenuOpen" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="lg:hidden border-t border-stone-200 py-3">
            <div class="px-4 sm:px-6 pb-3">
                <x-ui.search-form action="{{ route('search.index') }}" placeholder="Товары, услуги, новости..." :value="request('q', '')" size="mobile" formClass="rounded-lg" />
            </div>
            <nav class="flex flex-col gap-0.5 px-4 sm:px-6">
                <a href="/products?category=playstation" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    <x-icons.playstation class="w-5 h-5 shrink-0" />
                    PlayStation
                </a>
                <a href="/products?category=xbox" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    <x-icons.xbox class="w-5 h-5 shrink-0" />
                    Xbox
                </a>
                <a href="/products?category=nintendo" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    <x-icons.nintendo class="w-5 h-5 shrink-0" />
                    Nintendo
                </a>
                <a href="{{ route('services.index') }}" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    @svg('heroicon-o-wrench-screwdriver', 'w-5 h-5')
                    Услуги
                </a>
                <a href="/news" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    @svg('heroicon-o-newspaper', 'w-5 h-5')
                    Новости
                </a>
            </nav>
            <div class="px-4 sm:px-6 pt-4 mt-2 border-t border-stone-200">
                @if($isAuthenticated ?? false)
                    <a href="{{ route('cart.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-colors cursor-pointer">
                        @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                        Корзина
                        <span data-cart-count class="min-w-[20px] h-5 px-1 rounded-full bg-sky-600 text-white text-xs font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </a>
                @else
                    <button type="button" @click="openAuthModal('login')" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-colors cursor-pointer">
                        @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                        Корзина
                        <span data-cart-count class="min-w-[20px] h-5 px-1 rounded-full bg-sky-600 text-white text-xs font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                    </button>
                @endif
                @if($isAuthenticated ?? false)
                    <div class="mt-2 space-y-2">
                        <a href="{{ route('profile') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer">
                            @svg('heroicon-o-user-circle', 'w-5 h-5 shrink-0')
                            Профиль
                        </a>
                        <a href="{{ route('notifications.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer relative">
                            @svg('heroicon-o-bell', 'w-5 h-5 shrink-0')
                            <span>Уведомления</span>
                            @if(($notificationsUnreadCount ?? 0) > 0)
                                <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-600 text-white text-[10px] font-semibold flex items-center justify-center">
                                    {{ $notificationsUnreadCount }}
                                </span>
                            @endif
                        </a>
                        <a href="{{ route('tickets.my.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer">
                            @svg('heroicon-o-lifebuoy', 'w-5 h-5 shrink-0')
                            Техподдержка
                        </a>
                        @if(auth()->user()?->is_admin)
                            <a href="{{ route('admin.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer">
                                @svg('heroicon-o-cog-6-tooth', 'w-5 h-5 shrink-0')
                                Админка
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>

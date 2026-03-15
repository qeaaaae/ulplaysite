@php
    $company = ($footerData ?? [])['company'] ?? config('site.footer.company', []);
@endphp
<div class="bg-stone-800 text-white border-b border-stone-700 overflow-x-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 min-w-0">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-stone-900 hover:text-sky-600 transition-colors duration-200">
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
                <a href="/services/repair" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                    @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')
                    Услуги
                </a>
                <a href="/news" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200 cursor-pointer">
                    @svg('heroicon-o-newspaper', 'w-4 h-4')
                    Новости
                </a>
            </nav>

            <div class="hidden lg:flex flex-1 max-w-sm mx-4">
                <x-ui.search-form action="{{ route('products.index') }}" placeholder="Поиск товаров..." :value="request('q', '')" />
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="{{ route('cart.index') }}" class="hidden lg:flex relative items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                    <span data-cart-count class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-sky-600 text-white text-[10px] font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                </a>
                @if($isAuthenticated ?? false)
                    <form method="POST" action="{{ route('logout') }}" class="lg:hidden inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-stone-300 text-stone-700 rounded-md hover:border-rose-400 hover:text-rose-600 hover:bg-rose-50/80 transition-colors cursor-pointer">
                            @svg('heroicon-o-arrow-right-on-rectangle', 'w-4 h-4')
                            Выйти
                        </button>
                    </form>
                    <div class="hidden lg:flex items-center gap-2">
                        @if(auth()->user()?->is_admin)
                            <a href="{{ route('admin.index') }}" class="flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer" title="Админка">@svg('heroicon-o-cog-6-tooth', 'w-5 h-5')</a>
                        @endif
                        <a href="{{ route('profile') }}" class="flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer" title="Профиль">
                            @svg('heroicon-o-user-circle', 'w-5 h-5')
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer" title="Выйти">
                                @svg('heroicon-o-arrow-right-on-rectangle', 'w-5 h-5')
                            </button>
                        </form>
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
                <x-ui.search-form action="{{ route('products.index') }}" placeholder="Поиск товаров..." :value="request('q', '')" size="mobile" formClass="rounded-lg" />
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
                <a href="/services/repair" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    @svg('heroicon-o-wrench-screwdriver', 'w-5 h-5')
                    Услуги
                </a>
                <a href="/news" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation cursor-pointer">
                    @svg('heroicon-o-newspaper', 'w-5 h-5')
                    Новости
                </a>
            </nav>
            <div class="px-4 sm:px-6 pt-4 mt-2 border-t border-stone-200">
                <a href="{{ route('cart.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-colors cursor-pointer">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                    Корзина
                    <span data-cart-count class="min-w-[20px] h-5 px-1 rounded-full bg-sky-600 text-white text-xs font-semibold flex items-center justify-center {{ ($cartCount ?? 0) > 0 ? '' : '!hidden' }}">{{ $cartCount ?? 0 }}</span>
                </a>
                @if($isAuthenticated ?? false)
                    <div class="mt-2 space-y-2">
                        @if(auth()->user()?->is_admin)
                            <a href="{{ route('admin.index') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer">
                                @svg('heroicon-o-cog-6-tooth', 'w-5 h-5 shrink-0')
                                Админка
                            </a>
                        @endif
                        <a href="{{ route('profile') }}" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 text-sm font-medium cursor-pointer">
                            @svg('heroicon-o-user-circle', 'w-5 h-5 shrink-0')
                            Профиль
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</header>

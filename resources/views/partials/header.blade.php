<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-sm border-b border-stone-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
        <div class="flex justify-between items-center h-14 sm:h-16">
            <a href="{{ route('home') }}" class="flex items-center gap-2 text-stone-900 hover:text-sky-600 transition-colors duration-200">
                <span class="text-xl font-semibold">UlPlay</span>
            </a>

            <nav class="hidden lg:flex items-center gap-0.5">
                <a href="/products?category=playstation" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200">
                    <x-icons.playstation class="w-4 h-4 shrink-0" />
                    PlayStation
                </a>
                <a href="/products?category=xbox" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200">
                    <x-icons.xbox class="w-4 h-4 shrink-0" />
                    Xbox
                </a>
                <a href="/services/repair" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200">
                    @svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')
                    Услуги
                </a>
                <a href="/news" class="flex items-center gap-2 px-3 py-2 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-all duration-200">
                    @svg('heroicon-o-newspaper', 'w-4 h-4')
                    Новости
                </a>
            </nav>

            <div class="hidden lg:flex flex-1 max-w-sm mx-4">
                <form action="/products" method="GET" class="w-full flex rounded-md border border-stone-300 overflow-hidden focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-500">
                    <input type="search" name="q" placeholder="Поиск товаров..." class="flex-1 min-w-0 px-3 py-2 text-sm text-stone-900 placeholder-stone-400 focus:outline-none border-0">
                    <button type="submit" class="px-3 py-2 text-stone-500 hover:text-sky-600 hover:bg-sky-50/80 transition-colors cursor-pointer">
                        @svg('heroicon-o-magnifying-glass', 'w-5 h-5')
                    </button>
                </form>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                <a href="/cart" class="hidden lg:flex relative items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                    @if(($cartCount ?? 0) > 0)
                        <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-sky-600 text-white text-[10px] font-semibold flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>
                @if($isAuthenticated ?? false)
                    <a href="/profile" class="hidden lg:flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 transition-all duration-200 cursor-pointer">
                        @svg('heroicon-o-user-circle', 'w-5 h-5')
                    </a>
                @else
                    <x-ui.button href="/login" variant="outline" size="sm" class="hidden lg:inline-flex">
                        @svg('heroicon-o-arrow-right-on-rectangle', 'w-4 h-4')
                        Войти
                    </x-ui.button>
                @endif
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="lg:hidden flex items-center justify-center w-10 h-10 rounded-md text-stone-600 hover:bg-stone-100 transition-colors duration-200 touch-manipulation cursor-pointer" aria-label="Меню">
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
            <form action="/products" method="GET" class="px-4 sm:px-6 pb-3">
                <div class="flex rounded-lg border border-stone-300 overflow-hidden focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-500">
                    <input type="search" name="q" placeholder="Поиск товаров..." class="flex-1 min-w-0 px-3 py-3 sm:py-3.5 text-base sm:text-sm text-stone-900 placeholder-stone-400 focus:outline-none border-0" style="font-size: 16px;">
                    <button type="submit" class="px-3 py-2.5 text-stone-500 bg-stone-50 cursor-pointer">
                        @svg('heroicon-o-magnifying-glass', 'w-5 h-5')
                    </button>
                </div>
            </form>
            <nav class="flex flex-col gap-0.5 px-4 sm:px-6">
                <a href="/products?category=playstation" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation">
                    <x-icons.playstation class="w-5 h-5 shrink-0" />
                    PlayStation
                </a>
                <a href="/products?category=xbox" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation">
                    <x-icons.xbox class="w-5 h-5 shrink-0" />
                    Xbox
                </a>
                <a href="/services/repair" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation">
                    @svg('heroicon-o-wrench-screwdriver', 'w-5 h-5')
                    Услуги
                </a>
                <a href="/news" class="flex items-center gap-2.5 py-3 sm:py-3.5 px-4 rounded-md text-stone-600 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium active:bg-sky-50 touch-manipulation">
                    @svg('heroicon-o-newspaper', 'w-5 h-5')
                    Новости
                </a>
            </nav>
            <div class="px-4 sm:px-6 pt-4 mt-2 border-t border-stone-200">
                <a href="/cart" class="w-full flex items-center justify-center gap-2 py-3.5 rounded-md border border-stone-300 text-stone-700 hover:border-sky-400 hover:text-sky-600 hover:bg-sky-50/80 text-sm font-medium transition-colors cursor-pointer">
                    @svg('heroicon-o-shopping-cart', 'w-5 h-5')
                    Корзина
                    @if(($cartCount ?? 0) > 0)
                        <span class="min-w-[20px] h-5 px-1 rounded-full bg-sky-600 text-white text-xs font-semibold flex items-center justify-center">{{ $cartCount }}</span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</header>

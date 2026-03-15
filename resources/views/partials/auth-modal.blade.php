{{-- Модальное окно входа и регистрации --}}
<div x-show="authModalOpen" x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
     @click.self="closeAuthModal()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="auth-modal-title">
    <div x-show="authModalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-4"
         class="relative w-full max-w-md bg-white rounded-xl shadow-2xl p-6 sm:p-8 ring-1 ring-black/5"
         @click.stop>
        <button type="button" @click="closeAuthModal()" class="absolute top-4 right-4 p-2 text-stone-400 hover:text-stone-600 rounded-md transition-colors cursor-pointer" aria-label="Закрыть">
            @svg('heroicon-o-x-mark', 'w-5 h-5')
        </button>

        {{-- Вход --}}
        <div x-show="authModalType === 'login'" x-cloak>
            <h2 id="auth-modal-title" class="text-xl font-semibold text-stone-900 mb-6">Вход в аккаунт</h2>
            <form @submit.prevent="submitAuthForm($event.target)" class="space-y-5">
                @csrf
                <div>
                    <label for="login-email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="login-email" required autocomplete="email"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.email" x-text="authErrors.email?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div>
                    <label for="login-password" class="block text-sm font-medium text-stone-700 mb-1.5">Пароль</label>
                    <input type="password" name="password" id="login-password" required autocomplete="current-password"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.password" x-text="authErrors.password?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-sm text-stone-600">Запомнить меня</span>
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" :disabled="authLoading"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 text-white font-medium rounded-md hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span x-show="authLoading" class="animate-spin">@svg('heroicon-o-arrow-path', 'w-4 h-4')</span>
                        <span x-text="authLoading ? 'Вход...' : 'Войти'"></span>
                    </button>
                    <a href="{{ route('password.request') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 text-stone-600 hover:bg-stone-100 rounded-md text-sm font-medium cursor-pointer">Забыли пароль?</a>
                </div>
                <p class="text-sm text-stone-500 text-center">
                    Нет аккаунта?
                    <button type="button" @click="authModalType = 'register'; authErrors = {}" class="font-medium text-sky-600 hover:text-sky-700 cursor-pointer">Зарегистрироваться</button>
                </p>
            </form>
        </div>

        {{-- Регистрация --}}
        <div x-show="authModalType === 'register'" x-cloak>
            <h2 id="auth-modal-title" class="text-xl font-semibold text-stone-900 mb-6">Регистрация</h2>
            <form @submit.prevent="submitAuthForm($event.target)" class="space-y-5">
                @csrf
                <div>
                    <label for="reg-name" class="block text-sm font-medium text-stone-700 mb-1.5">Имя</label>
                    <input type="text" name="name" id="reg-name" required autocomplete="name"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.name" x-text="authErrors.name?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div>
                    <label for="reg-email" class="block text-sm font-medium text-stone-700 mb-1.5">Email</label>
                    <input type="email" name="email" id="reg-email" required autocomplete="email"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.email" x-text="authErrors.email?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div>
                    <label for="reg-phone" class="block text-sm font-medium text-stone-700 mb-1.5">Телефон (необязательно)</label>
                    <input type="tel" name="phone" id="reg-phone" autocomplete="tel"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.phone" x-text="authErrors.phone?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div>
                    <label for="reg-password" class="block text-sm font-medium text-stone-700 mb-1.5">Пароль</label>
                    <input type="password" name="password" id="reg-password" required autocomplete="new-password"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                    <p x-show="authErrors.password" x-text="authErrors.password?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div>
                    <label for="reg-password-confirm" class="block text-sm font-medium text-stone-700 mb-1.5">Подтвердите пароль</label>
                    <input type="password" name="password_confirmation" id="reg-password-confirm" required autocomplete="new-password"
                           class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                </div>
                <button type="submit" :disabled="authLoading"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 text-white font-medium rounded-md hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <span x-show="authLoading" class="animate-spin">@svg('heroicon-o-arrow-path', 'w-4 h-4')</span>
                    <span x-text="authLoading ? 'Регистрация...' : 'Зарегистрироваться'"></span>
                </button>
                <p class="text-sm text-stone-500 text-center">
                    Уже есть аккаунт?
                    <button type="button" @click="authModalType = 'login'; authErrors = {}" class="font-medium text-sky-600 hover:text-sky-700 cursor-pointer">Войти</button>
                </p>
            </form>
        </div>
    </div>
</div>

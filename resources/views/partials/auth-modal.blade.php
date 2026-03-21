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
                <div class="form-field" :class="{ 'is-invalid': authErrors.email }">
                    <label for="login-email" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-envelope', 'w-4 h-4 text-sky-500')
                        Email
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="login-email" required autocomplete="email"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                    <p x-show="authErrors.email" x-text="authErrors.email?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div class="form-field" :class="{ 'is-invalid': authErrors.password }">
                    <label for="login-password" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-lock-closed', 'w-4 h-4 text-sky-500')
                        Пароль
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="login-password" required autocomplete="current-password"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                    <p x-show="authErrors.password" x-text="authErrors.password?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="checkbox" name="remember" value="1" class="rounded border-stone-300 text-sky-600 accent-sky-600 focus:outline-none focus:ring-2 focus:ring-sky-500/30">
                    <span class="text-sm text-stone-600 group-hover:text-stone-700">Запомнить меня</span>
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <button type="submit" :disabled="authLoading"
                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 text-white font-medium rounded-md hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                        <span x-show="authLoading" class="animate-spin">@svg('heroicon-o-arrow-path', 'w-4 h-4')</span>
                        <span x-text="authLoading ? 'Вход...' : 'Войти'"></span>
                    </button>
                    <a href="{{ route('password.request') }}" class="flex-1 inline-flex items-center justify-center px-4 py-2 text-stone-600 hover:bg-stone-100 rounded-md text-sm font-medium cursor-pointer">Забыли пароль?</a>
                </div>
                <p class="text-sm text-stone-500 text-center pt-2 border-t border-stone-100">
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
                <div class="form-field" :class="{ 'is-invalid': authErrors.name }">
                    <label for="reg-name" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-user', 'w-4 h-4 text-sky-500')
                        Имя
                    </label>
                    <div class="relative">
                        <input type="text" name="name" id="reg-name" required autocomplete="name"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                    <p x-show="authErrors.name" x-text="authErrors.name?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div class="form-field" :class="{ 'is-invalid': authErrors.email }">
                    <label for="reg-email" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-envelope', 'w-4 h-4 text-sky-500')
                        Email
                    </label>
                    <div class="relative">
                        <input type="email" name="email" id="reg-email" required autocomplete="email"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                    <p x-show="authErrors.email" x-text="authErrors.email?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div class="form-field" :class="{ 'is-invalid': authErrors.phone }">
                    <x-ui.phone-input name="phone" label="Телефон" label-icon="heroicon-o-phone" />
                    <p x-show="authErrors.phone" x-text="authErrors.phone?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div class="form-field" :class="{ 'is-invalid': authErrors.password }">
                    <label for="reg-password" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-key', 'w-4 h-4 text-sky-500')
                        Пароль
                    </label>
                    <div class="relative">
                        <input type="password" name="password" id="reg-password" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                    <p x-show="authErrors.password" x-text="authErrors.password?.[0]" class="mt-1.5 text-sm text-rose-600"></p>
                </div>
                <div class="form-field" :class="{ 'is-invalid': authErrors.password_confirmation }">
                    <label for="reg-password-confirm" class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-lock-closed', 'w-4 h-4 text-sky-500')
                        Подтвердите пароль
                    </label>
                    <div class="relative">
                        <input type="password" name="password_confirmation" id="reg-password-confirm" required autocomplete="new-password"
                            class="w-full px-3 py-2.5 pr-11 h-11 bg-stone-50/50 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-400 focus:bg-white transition-colors">
                        <div class="invalid-feedback-icon absolute right-3 top-1/2 -translate-y-1/2 hidden items-center justify-center w-5 h-5 pointer-events-none text-rose-500" aria-hidden="true">
                            @svg('heroicon-o-exclamation-circle', 'w-5 h-5')
                        </div>
                    </div>
                </div>
                <button type="submit" :disabled="authLoading"
                    class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-sky-600 text-white font-medium rounded-md hover:bg-sky-700 focus:ring-2 focus:ring-sky-500 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer">
                    <span x-show="authLoading" class="animate-spin">@svg('heroicon-o-arrow-path', 'w-4 h-4')</span>
                    <span x-text="authLoading ? 'Регистрация...' : 'Зарегистрироваться'"></span>
                </button>
                <p class="text-sm text-stone-500 text-center pt-2 border-t border-stone-100">
                    Уже есть аккаунт?
                    <button type="button" @click="authModalType = 'login'; authErrors = {}" class="font-medium text-sky-600 hover:text-sky-700 cursor-pointer">Войти</button>
                </p>
            </form>
        </div>
    </div>
</div>
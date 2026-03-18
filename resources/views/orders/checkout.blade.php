@extends('layouts.app')

@section('bodyClass', 'page-checkout')

@section('content')
    <div class="min-h-screen py-6 sm:py-8 md:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-ui.breadcrumbs :items="[
                ['label' => 'Корзина', 'url' => route('cart.index')],
                ['label' => 'Оформление заказа', 'url' => null],
            ]" class="sm:mb-8" />

            <h1 class="text-2xl sm:text-3xl font-bold text-stone-900 mb-6 sm:mb-8">Оформление заказа</h1>

            <form method="POST" action="{{ route('orders.store') }}" class="space-y-6 lg:space-y-0 lg:grid lg:grid-cols-12 lg:gap-8">
                @csrf
                <div class="lg:col-span-7 space-y-6">
                    <section class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/50">
                            <h2 class="text-base sm:text-lg font-semibold text-stone-900 flex items-center gap-2">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100 text-sky-600">@svg('heroicon-o-user', 'w-4 h-4')</span>
                                Контактные данные
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6 space-y-4">
                            <x-ui.input name="name" label="Имя *" value="{{ old('name', $user?->name) }}" required :error="$errors->first('name')" />
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <x-ui.input type="tel" name="phone" label="Телефон *" value="{{ old('phone', $user?->phone) }}" required :error="$errors->first('phone')" />
                                <x-ui.input type="email" name="email" label="Email *" value="{{ old('email', $user?->email) }}" required :error="$errors->first('email')" />
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/50">
                            <h2 class="text-base sm:text-lg font-semibold text-stone-900 flex items-center gap-2">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100 text-sky-600">@svg('heroicon-o-truck', 'w-4 h-4')</span>
                                Доставка
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <x-ui.input name="address" label="Адрес доставки *" value="{{ old('address') }}" required placeholder="Город, улица, дом, квартира" :error="$errors->first('address')" />
                            <p class="mt-2 text-sm text-stone-500 flex items-center gap-1.5">
                                @svg('heroicon-o-information-circle', 'w-4 h-4 text-sky-500 shrink-0')
                                Бесплатная доставка по Ульяновску при заказе от 3 000 ₽
                            </p>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/50">
                            <h2 class="text-base sm:text-lg font-semibold text-stone-900 flex items-center gap-2">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-sky-100 text-sky-600">@svg('heroicon-o-credit-card', 'w-4 h-4')</span>
                                Оплата
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6" x-data="{ payment: '{{ old('payment', 'cash') }}' }">
                            <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                                <label @click="payment = 'cash'" class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                                    :class="payment === 'cash' ? 'border-sky-500 bg-sky-50/70 shadow-sm' : 'border-stone-200 hover:border-sky-300 hover:bg-sky-50/40'">
                                    <input type="radio" name="payment" value="cash" {{ old('payment', 'cash') === 'cash' ? 'checked' : '' }} class="text-sky-600 accent-sky-600 focus:ring-2 focus:ring-sky-500/30">
                                    <span class="font-medium text-stone-800">Наличными при получении</span>
                                </label>
                                <label @click="payment = 'card'" class="flex items-center gap-3 p-4 rounded-xl border-2 cursor-pointer transition-all duration-200"
                                    :class="payment === 'card' ? 'border-sky-500 bg-sky-50/70 shadow-sm' : 'border-stone-200 hover:border-sky-300 hover:bg-sky-50/40'">
                                    <input type="radio" name="payment" value="card" {{ old('payment') === 'card' ? 'checked' : '' }} class="text-sky-600 accent-sky-600 focus:ring-2 focus:ring-sky-500/30">
                                    <span class="font-medium text-stone-800">Картой при получении</span>
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/50">
                            <h2 class="text-base sm:text-lg font-semibold text-stone-900 flex items-center gap-2">
                                <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-stone-100 text-stone-600">@svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4')</span>
                                Комментарий к заказу
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <x-ui.textarea name="comment" label="Пожелания, время доставки, подъезд и т.д. (необязательно)" placeholder="Например: позвоните за час до доставки" :error="$errors->first('comment')" rows="3" maxlength="1000">{{ old('comment') }}</x-ui.textarea>
                        </div>
                    </section>
                </div>

                <div class="lg:col-span-5">
                    <div class="lg:sticky lg:top-24 bg-white rounded-2xl border border-stone-200 shadow-sm overflow-hidden">
                        <div class="px-4 sm:px-6 py-4 border-b border-stone-200 bg-stone-50">
                            <h2 class="text-base sm:text-lg font-semibold text-stone-900 flex items-center gap-2">
                                @svg('heroicon-o-shopping-bag', 'w-5 h-5 text-sky-600')
                                Ваш заказ
                            </h2>
                        </div>
                        <div class="p-4 sm:p-6">
                            <ul class="space-y-3 max-h-[280px] sm:max-h-[320px] overflow-y-auto pr-1">
                                @foreach($items as $item)
                                    <li class="flex justify-between gap-3 text-sm">
                                        <span class="text-stone-700 line-clamp-2">{{ $item->title }} × {{ $item->quantity }}</span>
                                        <span class="shrink-0 font-medium tabular-nums">{{ number_format($item->subtotal, 0, ',', ' ') }} ₽</span>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="border-t border-stone-200 mt-4 pt-4 space-y-2">
                                <div class="flex justify-between text-sm text-stone-600">
                                    <span>Подытог</span>
                                    <span class="tabular-nums">{{ number_format($subtotal, 0, ',', ' ') }} ₽</span>
                                </div>
                                <div class="flex justify-between text-sm text-stone-600">
                                    <span>Доставка</span>
                                    <span class="tabular-nums">{{ $deliveryCost === 0 ? 'Бесплатно' : number_format($deliveryCost, 0, ',', ' ') . ' ₽' }}</span>
                                </div>
                                <div class="flex justify-between items-baseline gap-2 pt-2">
                                    <span class="font-semibold text-stone-900">Итого</span>
                                    <span class="text-xl font-bold text-stone-900 tabular-nums">{{ number_format($total, 0, ',', ' ') }} ₽</span>
                                </div>
                            </div>
                            <x-ui.button type="submit" variant="primary" size="lg" class="w-full mt-6">
                                Подтвердить заказ
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

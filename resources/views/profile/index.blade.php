@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="text-2xl font-semibold text-stone-900 mb-8">Личный кабинет</h1>

            <div class="bg-white rounded-xl border border-stone-200 p-6 mb-8">
                <h2 class="text-lg font-medium text-stone-800 mb-4">Редактировать профиль</h2>
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-ui.input name="name" label="Имя" value="{{ old('name', $user->name) }}" required :error="$errors->first('name')" />
                    <x-ui.input type="email" name="email" label="Email" value="{{ old('email', $user->email) }}" required :error="$errors->first('email')" />
                    <x-ui.phone-input name="phone" label="Телефон" :value="old('phone', $user->phone)" :error="$errors->first('phone')" />
                    <x-ui.button type="submit" variant="primary">Сохранить</x-ui.button>
                </form>
            </div>

            <div class="bg-white rounded-xl border border-stone-200 p-6">
                <h2 class="text-lg font-medium text-stone-800 mb-4">Последние заказы</h2>
                @if($orders->isEmpty())
                    <p class="text-stone-500">У вас пока нет заказов</p>
                @else
                    <ul class="space-y-3">
                        @foreach($orders as $order)
                            <li>
                                <a href="{{ route('orders.show', $order) }}" class="flex justify-between items-center py-2 hover:text-sky-600">
                                    <span>{{ $order->order_number }} - {{ $order->created_at->format(config('app.datetime_format')) }}</span>
                                    <span>{{ number_format($order->total, 0, ',', ' ') }} ₽</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <x-ui.button href="{{ route('orders.index') }}" variant="ghost" size="sm" class="mt-4">
                        Все заказы
                    </x-ui.button>
                @endif
            </div>
        </div>
    </div>
@endsection

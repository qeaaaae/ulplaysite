@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-cube', 'w-8 h-8 text-sky-600')
            Товары
        </h1>
        <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-sm font-medium shrink-0 transition-colors">
            @svg('heroicon-o-plus', 'w-5 h-5')
            Добавить
        </a>
    </div>
    <div class="mb-4">
        <x-admin.search-bar :action="route('admin.products.index')" placeholder="По названию, ярлыку или описанию..." :value="$search ?? ''" />
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Цена</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Категория</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Кол-во</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">В наличии</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach($products as $p)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 text-sm">{{ $p->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->title }}</td>
                            <td class="px-4 py-3">{{ number_format($p->price, 0, ',', ' ') }} ₽</td>
                            <td class="px-4 py-3">{{ $p->category?->name }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $p->stock ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $p->in_stock ? 'Да' : 'Нет' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.action-buttons :edit-href="route('admin.products.edit', $p)" :delete-action="route('admin.products.destroy', $p)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $products->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @foreach($products as $p)
            <div class="bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $p->title }}</p>
                        <p class="text-sm text-stone-500 mt-0.5">{{ number_format($p->price, 0, ',', ' ') }} ₽ · {{ $p->category?->name ?? '-' }} · Кол-во: {{ $p->stock ?? 0 }}</p>
                        <p class="text-sm mt-1">{{ $p->in_stock ? 'В наличии' : 'Нет в наличии' }}</p>
                    </div>
                    <div class="shrink-0">
                        <x-admin.action-buttons :edit-href="route('admin.products.edit', $p)" :delete-action="route('admin.products.destroy', $p)" />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="py-2">{{ $products->links() }}</div>
    </div>
@endsection

@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-photo', 'w-8 h-8 text-sky-600')
            Баннеры
        </h1>
        <a href="{{ route('admin.banners.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-sm font-medium shrink-0 transition-colors">
            @svg('heroicon-o-plus', 'w-5 h-5')
            Добавить
        </a>
    </div>
    <div class="mb-4">
        <x-admin.search-bar :action="route('admin.banners.index')" placeholder="По названию или описанию..." :value="$search ?? ''" />
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Активен</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach($banners as $b)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 text-sm">{{ $b->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $b->title }}</td>
                            <td class="px-4 py-3">{{ $b->active ? 'Да' : 'Нет' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.action-buttons :edit-href="route('admin.banners.edit', $b)" :delete-action="route('admin.banners.destroy', $b)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $banners->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @foreach($banners as $b)
            <div class="bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div class="flex justify-between items-center gap-3">
                    <div>
                        <p class="font-medium">{{ $b->title }}</p>
                        <p class="text-sm text-stone-500">{{ $b->active ? 'Активен' : 'Неактивен' }}</p>
                    </div>
                    <div class="shrink-0">
                        <x-admin.action-buttons :edit-href="route('admin.banners.edit', $b)" :delete-action="route('admin.banners.destroy', $b)" />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="py-2">{{ $banners->links() }}</div>
    </div>
@endsection

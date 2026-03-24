@extends('layouts.admin')

@section('content')
    @if (session('error'))
        <p class="mb-4 text-rose-600">{{ session('error') }}</p>
    @endif
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-users', 'w-8 h-8 text-sky-600')
            Пользователи
        </h1>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-sm font-medium shrink-0 transition-colors">
            @svg('heroicon-o-plus', 'w-5 h-5')
            Добавить
        </a>
    </div>
    <div class="mb-4">
        <x-admin.search-bar :action="route('admin.users.index')" placeholder="По имени, email или телефону..." :value="$search ?? ''" />
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Имя</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Email</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase hidden sm:table-cell">Телефон</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Роль</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Статус</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach($users as $u)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 text-sm">{{ $u->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $u->name }}</td>
                            <td class="px-4 py-3">{{ $u->email }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell">{{ $u->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ $u->is_admin ? 'Админ' : 'Пользователь' }}
                                @if($u->is_bot)
                                    <span class="ml-1 text-xs px-1.5 py-0.5 rounded bg-stone-200 text-stone-600">бот</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($u->trashed())
                                    <span class="text-stone-400">Удалён</span>
                                @else
                                    {{ $u->is_blocked ? 'Заблокирован' : 'Активен' }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <x-admin.action-buttons
                                    :edit-href="route('admin.users.edit', $u)"
                                    :block-action="(!$u->trashed() && !$u->is_admin && $u->id !== auth()->id() && !$u->is_blocked) ? route('admin.users.block', $u) : null"
                                    :block-confirm="'Заблокировать пользователя?'"
                                    :unblock-action="(!$u->trashed() && !$u->is_admin && $u->id !== auth()->id() && $u->is_blocked) ? route('admin.users.unblock', $u) : null"
                                    :delete-action="(!$u->trashed() && $u->id !== auth()->id()) ? route('admin.users.destroy', $u) : null"
                                    delete-title="Удалить пользователя"
                                    :delete-confirm="'Удалить пользователя?'"
                                    :restore-action="$u->trashed() && $u->id !== auth()->id() ? route('admin.users.restore', $u) : null"
                                    restore-title="Восстановить"
                                />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $users->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @foreach($users as $u)
            <div class="bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <p class="font-medium">{{ $u->name }}</p>
                        <p class="text-sm text-stone-500">{{ $u->email }}</p>
                        <p class="text-sm mt-1">
                            <span class="{{ $u->is_admin ? 'text-sky-600' : 'text-stone-600' }}">{{ $u->is_admin ? 'Админ' : 'Пользователь' }}</span>
                            @if($u->is_bot)<span class="text-xs px-1.5 py-0.5 rounded bg-stone-200 text-stone-600">бот</span>@endif
                            ·
                            @if($u->trashed())
                                <span class="text-stone-400">Удалён</span>
                            @else
                                <span class="{{ $u->is_blocked ? 'text-rose-600' : 'text-emerald-600' }}">{{ $u->is_blocked ? 'Заблокирован' : 'Активен' }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="shrink-0">
                        <x-admin.action-buttons
                            :edit-href="route('admin.users.edit', $u)"
                            :block-action="(!$u->trashed() && !$u->is_admin && $u->id !== auth()->id() && !$u->is_blocked) ? route('admin.users.block', $u) : null"
                            :block-confirm="'Заблокировать пользователя?'"
                            :unblock-action="(!$u->trashed() && !$u->is_admin && $u->id !== auth()->id() && $u->is_blocked) ? route('admin.users.unblock', $u) : null"
                            :delete-action="(!$u->trashed() && $u->id !== auth()->id()) ? route('admin.users.destroy', $u) : null"
                            delete-title="Удалить пользователя"
                            :delete-confirm="'Удалить пользователя?'"
                            :restore-action="$u->trashed() && $u->id !== auth()->id() ? route('admin.users.restore', $u) : null"
                            restore-title="Восстановить"
                        />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="py-2">{{ $users->links() }}</div>
    </div>
@endsection

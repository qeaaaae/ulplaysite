@extends('layouts.admin')

@section('content')
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 mb-4 sm:mb-6">
        <h1 class="text-xl sm:text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-lifebuoy', 'w-8 h-8 text-sky-600')
            Тикеты
        </h1>
    </div>

    <div class="mb-4 flex flex-col sm:flex-row sm:flex-wrap gap-3 items-stretch sm:items-center">
        <x-admin.search-bar :action="route('admin.tickets.index')" placeholder="Заголовок, описание, имя или email..." :value="request('q', '')" />
        <form method="GET" action="{{ route('admin.tickets.index') }}" class="flex gap-2 items-center">
            @if(request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            <div class="admin-ticket-type-select">
            <select name="type" data-enhance="tom-select" data-submit-on-change class="h-11 px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                <option value="">Все типы</option>
                @foreach(\App\Enums\SupportTicketTypeEnum::cases() as $type)
                    <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                @endforeach
            </select>
            </div>
            <select name="status" data-enhance="tom-select" data-submit-on-change class="h-11 px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[140px]">
                <option value="">Все статусы</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В работе</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Решён</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Закрыт</option>
            </select>
        </form>
    </div>

    <div class="space-y-2">
        @forelse($tickets as $ticket)
            @php
                $type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type);
                $statusLabel = match($ticket->status) {
                    'new' => 'Новый',
                    'in_progress' => 'В работе',
                    'resolved' => 'Решён',
                    'closed' => 'Закрыт',
                    default => (string) $ticket->status,
                };
                $statusClass = match($ticket->status) {
                    'resolved' => 'bg-emerald-100 text-emerald-800',
                    'closed' => 'bg-stone-200 text-stone-700',
                    'in_progress' => 'bg-sky-100 text-sky-800',
                    default => 'bg-amber-100 text-amber-800',
                };
            @endphp
            <a href="{{ route('admin.tickets.show', $ticket) }}" class="flex items-center gap-3 sm:gap-4 p-3 sm:p-4 bg-white rounded-xl border border-stone-200 hover:border-sky-200 hover:shadow-sm transition-all group">
                <div class="shrink-0 w-10 h-10 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center group-hover:bg-sky-50 transition-colors">
                    @svg('heroicon-o-lifebuoy', 'w-5 h-5')
                </div>
                {{-- Мобильная версия: 2 строки, только важное --}}
                <div class="min-w-0 flex-1 sm:hidden">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="font-semibold text-stone-900">#{{ $ticket->id }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                        <span class="text-stone-300">·</span>
                        <span class="text-sm text-stone-700 truncate" title="{{ $ticket->title }}">
                            {{ \Illuminate\Support\Str::limit($ticket->title, 30) }}
                        </span>
                    </div>
                    <div class="text-xs text-stone-500 mt-1.5 flex flex-wrap items-center gap-x-2">
                        <span>{{ $ticket->created_at->format('d.m.Y') }}</span>
                        <span class="text-stone-300">·</span>
                        <span>@if($ticket->user){{ $ticket->user->name }}@else Гость @endif</span>
                        <span class="text-stone-300">·</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded mt-2 {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                            {{ $type?->label() ?? '—' }}
                        </span>
                    </div>
                </div>
                {{-- ПК версия: оригинальная сетка --}}
                <div class="min-w-0 flex-1 hidden sm:grid grid-cols-2 lg:grid-cols-4 gap-x-4 gap-y-1">
                    <div class="sm:col-span-2 lg:col-span-1">
                        <span class="font-semibold text-stone-900">#{{ $ticket->id }}</span>
                        <span class="inline-flex ml-2 px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                    </div>
                    <div class="text-sm text-stone-800 min-w-0 truncate" title="{{ $ticket->title }}">
                        {{ \Illuminate\Support\Str::limit($ticket->title, 40) }}
                    </div>
                    <div class="text-sm text-stone-600 truncate">
                        @if($ticket->user)
                            {{ $ticket->user->name }}
                            <span class="text-stone-400"> · </span>
                            <span class="text-stone-500 truncate">{{ $ticket->user->email }}</span>
                        @else
                            <span class="text-stone-400">Гость</span>
                        @endif
                    </div>
                    <div class="text-sm text-stone-500 flex flex-wrap items-center gap-x-2 gap-y-0.5 sm:col-span-2 lg:col-span-1">
                        <span>{{ $ticket->created_at->format('d.m.Y') }}</span>
                        <span class="text-stone-300">·</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                            {{ $type?->label() ?? '—' }}
                        </span>
                        <span class="text-stone-300">·</span>
                        <span>{{ $ticket->images->count() }} фото</span>
                    </div>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <span class="text-stone-300 group-hover:text-sky-500 transition-colors">@svg('heroicon-o-chevron-right', 'w-5 h-5')</span>
                </div>
            </a>
        @empty
            <div class="p-8 bg-white rounded-xl border border-stone-200 text-center text-stone-500">
                Тикетов пока нет
            </div>
        @endforelse
    </div>

    @if($tickets->isNotEmpty())
        <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
@endsection

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
            <select name="type" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                <option value="">Все типы</option>
                @foreach(\App\Enums\SupportTicketTypeEnum::cases() as $type)
                    <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150">
                <option value="">Все статусы</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>В работе</option>
                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Решён</option>
                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Закрыт</option>
            </select>
        </form>
    </div>

    <div class="lg:hidden space-y-3">
        @forelse($tickets as $ticket)
            @php($type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type))
            @php($statusLabel = match($ticket->status) {
                'new' => 'Новый',
                'in_progress' => 'В работе',
                'resolved' => 'Решён',
                'closed' => 'Закрыт',
                default => (string) $ticket->status,
            })
            @php($statusClass = match($ticket->status) {
                'resolved' => 'bg-emerald-100 text-emerald-800',
                'closed' => 'bg-stone-200 text-stone-700',
                'in_progress' => 'bg-sky-100 text-sky-800',
                default => 'bg-amber-100 text-amber-800',
            })
            <a href="{{ route('admin.tickets.show', $ticket) }}" class="block p-4 bg-white rounded-lg shadow border border-stone-200 hover:border-sky-200 transition-colors">
                <div class="flex justify-between items-start gap-3 mb-2">
                    <span class="text-xs font-medium text-stone-500">#{{ $ticket->id }}</span>
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded shrink-0 {{ $statusClass }}">{{ $statusLabel }}</span>
                </div>
                <h3 class="font-medium text-stone-800 line-clamp-2 mb-1">{{ $ticket->title }}</h3>
                <p class="text-sm text-stone-500 line-clamp-2 mb-3">{{ \Illuminate\Support\Str::limit($ticket->description, 80) }}</p>
                <div class="flex flex-wrap items-center gap-2 text-xs text-stone-500">
                    @if($ticket->user)
                        <span>{{ $ticket->user->name }}</span>
                        <span>·</span>
                    @endif
                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                        {{ $type?->label() ?? '—' }}
                    </span>
                    <span>·</span>
                    <span>{{ $ticket->created_at->format(config('app.datetime_format')) }}</span>
                    <span>·</span>
                    <span>{{ $ticket->images->count() }} фото</span>
                </div>
            </a>
        @empty
            <div class="p-8 bg-white rounded-lg shadow border border-stone-200 text-center text-stone-500">Тикетов пока нет</div>
        @endforelse
        @if($tickets->isNotEmpty())
            <div class="pt-2">{{ $tickets->links() }}</div>
        @endif
    </div>

    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200 table-fixed">
                <colgroup>
                    <col class="w-14">
                    <col class="w-[min(18%,220px)]">
                    <col>
                    <col class="w-32">
                    <col class="w-14">
                    <col class="w-24">
                    <col class="w-36">
                    <col class="w-12">
                </colgroup>
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Заголовок</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Отправитель</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Тип</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Фото</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Статус</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Дата</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @forelse($tickets as $ticket)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 font-medium">{{ $ticket->id }}</td>
                            <td class="px-4 py-3 min-w-0">
                                <span class="font-medium text-stone-800 block truncate">{{ $ticket->title }}</span>
                                <p class="text-sm text-stone-500 line-clamp-2 mt-0.5 truncate">{{ $ticket->description }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                @if($ticket->user)
                                    <span class="block">{{ $ticket->user->name }}</span>
                                    <span class="text-stone-500">{{ $ticket->user->email }}</span>
                                @else
                                    <span class="text-stone-500">Гость</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @php($type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type))
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                                    {{ $type?->label() ?? '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $ticket->images->count() }}</td>
                            <td class="px-4 py-3">
                                @php($statusLabel = match($ticket->status) {
                                    'new' => 'Новый',
                                    'in_progress' => 'В работе',
                                    'resolved' => 'Решён',
                                    'closed' => 'Закрыт',
                                    default => (string) $ticket->status,
                                })
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded
                                    @if($ticket->status === 'resolved') bg-emerald-100 text-emerald-800
                                    @elseif($ticket->status === 'closed') bg-stone-200 text-stone-700
                                    @elseif($ticket->status === 'in_progress') bg-sky-100 text-sky-800
                                    @else bg-amber-100 text-amber-800
                                    @endif">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm">{{ $ticket->created_at->format(config('app.datetime_format')) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex p-2 text-stone-500 hover:text-sky-600 hover:bg-sky-50 rounded-md transition-colors" title="Подробнее">@svg('heroicon-o-eye', 'w-5 h-5')</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-stone-500">Тикетов пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $tickets->links() }}</div>
    </div>
@endsection


@extends('layouts.admin')

@section('content')
    @php
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

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.tickets.index') }}" class="p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку">
                @svg('heroicon-o-arrow-left', 'w-5 h-5')
            </a>
            <div class="flex items-center gap-2.5">
                <div class="p-2 rounded-xl bg-sky-100 text-sky-600">
                    @svg('heroicon-o-lifebuoy', 'w-8 h-8')
                </div>
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900">Тикет #{{ $ticket->id }}</h1>
                    <p class="text-sm text-stone-500 flex items-center gap-2 flex-wrap">
                        <span>{{ Str::limit($ticket->title, 50) }} · {{ $ticket->created_at->format(config('app.datetime_format')) }}</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">{{ $statusLabel }}</span>
                    </p>
                </div>
            </div>
        </div>
        <form action="{{ route('admin.tickets.update-status', $ticket) }}" method="POST" class="flex items-center gap-2">
            @csrf
            @method('PATCH')
            <label class="text-sm font-medium text-stone-600 flex items-center gap-2">
                @svg('heroicon-o-flag', 'w-4 h-4 text-sky-500')
                Статус
            </label>
            <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 h-11 bg-white border border-stone-300 rounded-md text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[160px]">
                <option value="new" {{ $ticket->status === 'new' ? 'selected' : '' }}>Новый</option>
                <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>В работе</option>
                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Решён</option>
                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Закрыт</option>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[40%_60%] gap-0 lg:gap-6 lg:items-stretch">
        {{-- Левая колонка: информация о тикете --}}
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden flex flex-col">
            <div class="p-4 sm:p-6 border-b border-stone-200 bg-gradient-to-r from-stone-50/80 to-white flex-shrink-0">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="p-2 sm:p-2.5 rounded-lg sm:rounded-xl bg-sky-100 text-sky-600 shrink-0">
                        @svg('heroicon-o-information-circle', 'w-6 h-6 sm:w-7 sm:h-7')
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-lg sm:text-xl font-semibold text-stone-900">Информация</h2>
                        <p class="text-stone-500 text-sm mt-0.5">Вложений: {{ $ticket->images->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 sm:p-6 border-b border-stone-200 flex-shrink-0">
                <h3 class="text-sm font-medium text-stone-800 mb-3">Отправитель</h3>
                <div class="text-sm text-stone-700 space-y-1">
                    @if($ticket->user)
                        <div><span class="text-stone-500">Пользователь:</span> {{ $ticket->user->name }} ({{ $ticket->user->email }})</div>
                    @else
                        <div><span class="text-stone-500">Пользователь:</span> Гость</div>
                    @endif
                    <div><span class="text-stone-500">IP:</span> {{ $ticket->ip_address ?? '-' }}</div>
                    @php($type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type))
                    <div>
                        <span class="text-stone-500">Тип:</span>
                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                            {{ $type?->label() ?? '—' }}
                        </span>
                    </div>
                    @if($ticket->service)
                        <div class="pt-2 mt-2 border-t border-stone-100">
                            <span class="text-stone-500">Страница услуги:</span>
                            <a href="{{ route('services.show', $ticket->service) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-sky-600 hover:text-sky-800 hover:underline font-medium break-all">
                                {{ $ticket->service->title }}
                                @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 shrink-0')
                            </a>
                            <p class="text-xs text-stone-400 mt-1 break-all">{{ route('services.show', $ticket->service) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="p-4 sm:p-6 flex-1 flex flex-col min-h-0">
                <h3 class="text-sm font-medium text-stone-800 mb-3">Вложения</h3>
                @if($ticket->images->isEmpty())
                    <p class="text-stone-500">Фотографии не приложены</p>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($ticket->images as $image)
                            <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="admin-ticket-{{ $ticket->id }}" class="block rounded-lg overflow-hidden border border-stone-200 bg-stone-50 cursor-zoom-in hover:border-sky-300 transition-colors">
                                <img src="{{ $image->url }}" alt="Фото тикета" class="w-full h-40 object-cover">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Правая колонка: диалог --}}
        <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden flex flex-col mt-4 lg:mt-0 min-h-[400px]">
            <div class="p-4 sm:p-6 border-b border-stone-200 bg-gradient-to-r from-stone-50/80 to-white flex-shrink-0">
                <div class="flex items-start gap-3 min-w-0">
                    <div class="p-2 sm:p-2.5 rounded-lg sm:rounded-xl bg-sky-100 text-sky-600 shrink-0">
                        @svg('heroicon-o-chat-bubble-left-right', 'w-6 h-6 sm:w-7 sm:h-7')
                    </div>
                    <div class="min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold text-stone-900">Диалог</h1>
                        <p class="text-stone-500 text-sm mt-0.5">Сообщений: <span id="admin-ticket-messages-count">{{ $ticket->messages->count() }}</span></p>
                    </div>
                </div>
            </div>

            <div class="flex-1 flex flex-col min-h-0">
                <div id="admin-ticket-messages" class="flex-1 overflow-y-auto p-4 sm:p-6 sm:pr-5 space-y-4 min-h-[200px] max-h-[400px] sm:max-h-[500px] scroll-smooth">
                    @forelse($ticket->messages as $message)
                        @include('admin.tickets.partials.message', ['message' => $message])
                    @empty
                        <div id="admin-ticket-messages-empty" class="py-12 text-center">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400 mb-3">
                                @svg('heroicon-o-chat-bubble-left-right', 'w-6 h-6')
                            </div>
                            <p class="text-stone-500 text-sm">Сообщений пока нет</p>
                            <p class="text-stone-400 text-xs mt-1">Ваш ответ появится здесь</p>
                        </div>
                    @endforelse
                </div>

                <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" class="p-4 sm:p-6 border-t border-stone-200 flex-shrink-0" data-ajax-admin-ticket-reply>
                    @csrf
                    <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                        @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4 text-sky-500')
                        Ответ админа
                    </label>
                    <textarea
                        name="message"
                        rows="3"
                        required
                        class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 resize-y"
                        placeholder="Введите текст ответа...">{{ old('message') }}</textarea>

                    <div class="flex flex-wrap items-center gap-3 pt-3">
                        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-md transition-colors text-sm font-medium">
                            @svg('heroicon-o-check', 'w-5 h-5')
                            Отправить ответ
                        </button>
                        <a href="{{ route('admin.tickets.show', $ticket) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-md text-stone-700 hover:bg-stone-50 transition-colors text-sm font-medium">
                            Отмена
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection


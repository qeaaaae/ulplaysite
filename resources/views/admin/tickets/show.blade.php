@extends('layouts.admin')

@section('content')
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center gap-2 p-2 rounded-lg text-stone-500 hover:bg-white hover:text-stone-700 transition-colors" title="К списку тикетов">
            @svg('heroicon-o-arrow-left', 'w-5 h-5')
            <span class="hidden sm:inline">К списку тикетов</span>
        </a>
    </div>

    <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-5 sm:p-6 border-b border-stone-200 bg-gradient-to-r from-stone-50/80 to-white">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-sky-100 text-sky-600">
                        @svg('heroicon-o-lifebuoy', 'w-7 h-7')
                    </div>
                    <div>
                        <h1 class="text-xl font-semibold text-stone-900">Тикет #{{ $ticket->id }} — {{ $ticket->title }}</h1>
                        <p class="text-stone-500 text-sm mt-0.5">{{ $ticket->created_at->format(config('app.datetime_format')) }}</p>
                    </div>
                </div>
                <form action="{{ route('admin.tickets.update-status', $ticket) }}" method="POST" class="flex flex-wrap items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <label class="text-sm font-medium text-stone-600">Статус</label>
                    <select name="status" data-enhance="tom-select" data-submit-on-change class="px-3 py-2 bg-white border border-stone-300 rounded-lg text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 min-w-[170px]">
                        <option value="new" {{ $ticket->status === 'new' ? 'selected' : '' }}>Новый</option>
                        <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>В работе</option>
                        <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Решён</option>
                        <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Закрыт</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="p-5 sm:p-6 border-b border-stone-200">
            <h2 class="font-medium text-stone-800 mb-3">Отправитель</h2>
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
            </div>
        </div>

        <div class="p-5 sm:p-6 border-b border-stone-200">
            <h2 class="font-medium text-stone-800 mb-3">Описание</h2>
            @if($ticket->messages->isEmpty())
                <div class="text-stone-700 whitespace-pre-wrap">{{ $ticket->description }}</div>
            @else
                <p class="text-stone-500 text-sm">Текст автора уже отображён в диалоге ниже</p>
            @endif
        </div>

        <div class="p-5 sm:p-6">
            <h2 class="font-medium text-stone-800 mb-4">Вложения</h2>
            @if($ticket->images->isEmpty())
                <p class="text-stone-500">Фотографии не приложены</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($ticket->images as $image)
                        <a href="{{ $image->url }}" target="_blank" class="block rounded-lg overflow-hidden border border-stone-200 bg-stone-50">
                            <img src="{{ $image->url }}" alt="Фото тикета" class="w-full h-48 object-cover">
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="p-5 sm:p-6 border-t border-stone-200">
            <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
                <h2 class="font-medium text-stone-800">Диалог</h2>
                <div class="text-xs text-stone-500">
                    Всего сообщений: {{ $ticket->messages->count() }}
                </div>
            </div>

            <div class="space-y-3 max-h-[380px] overflow-y-auto pr-1">
                @forelse($ticket->messages as $message)
                    @php($isAdmin = $message->sender_role === 'admin')
                    <div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-lg border px-3 py-2 {{ $isAdmin ? 'bg-sky-50 border-sky-200' : 'bg-stone-50 border-stone-200' }}">
                            <div class="text-xs font-medium mb-1 {{ $isAdmin ? 'text-sky-800' : 'text-stone-800' }}">
                                {{ $isAdmin ? 'Администратор' : ($message->senderUser?->name ?? 'Пользователь') }}
                            </div>
                            <div class="text-sm text-stone-800 whitespace-pre-wrap">
                                {{ $message->content }}
                            </div>
                            <div class="text-[11px] text-stone-500 mt-2">
                                {{ $message->created_at->format(config('app.datetime_format')) }}
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-stone-500 text-sm">Пока нет сообщений. Ваш ответ появится здесь.</p>
                @endforelse
            </div>

            <form action="{{ route('admin.tickets.reply', $ticket) }}" method="POST" class="mt-6">
                @csrf
                <label class="flex items-center gap-2 text-sm font-medium text-stone-700 mb-1.5">
                    @svg('heroicon-o-chat-bubble-left-ellipsis', 'w-4 h-4 text-stone-400')
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
@endsection


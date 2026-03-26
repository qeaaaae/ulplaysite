@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center sm:justify-between gap-3 sm:gap-4">
                <div class="min-w-0">
                    <h1 class="text-xl sm:text-2xl font-semibold text-stone-900 break-words">Обращение #{{ $ticket->id }} — {{ $ticket->title }}</h1>
                    <p class="text-sm text-stone-500 mt-1">{{ $ticket->created_at->format(config('app.datetime_format')) }}</p>
                </div>
                <a href="{{ route('tickets.my.index') }}" class="text-sm text-sky-600 hover:underline">К списку</a>
            </div>

            @if(session('error'))
                <p class="text-rose-600 text-sm mb-4">{{ session('error') }}</p>
            @endif
            @php($type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type))
            @php($statusLabel = match($ticket->status) {
                'new' => 'Новый',
                'in_progress' => 'В работе',
                'resolved' => 'Решён',
                'closed' => 'Закрыт',
                default => (string) $ticket->status
            })
            @php($statusClass = match($ticket->status) {
                'resolved' => 'bg-emerald-100 text-emerald-800',
                'closed' => 'bg-stone-200 text-stone-700',
                'in_progress' => 'bg-sky-100 text-sky-800',
                default => 'bg-amber-100 text-amber-800'
            })
            <div class="flex flex-wrap items-center gap-3">
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                    {{ $type?->label() ?? '—' }}
                </span>
                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
                <span class="text-sm text-stone-500">
                    Сообщений: {{ $ticket->messages->count() }}
                </span>
            </div>

            @if($ticket->service)
                <div class="flex flex-wrap items-center gap-2 p-4 rounded-xl bg-sky-50 border border-sky-100/80">
                    @svg('heroicon-o-wrench-screwdriver', 'w-5 h-5 text-sky-600 shrink-0')
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-stone-500 uppercase tracking-wide">Страница услуги</p>
                        <a href="{{ route('services.show', $ticket->service) }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-sky-700 hover:text-sky-900 hover:underline inline-flex items-center gap-1.5 break-words">
                            {{ $ticket->service->title }}
                            @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 shrink-0')
                        </a>
                    </div>
                </div>
            @endif

            @if($ticket->images->isEmpty())
                <p class="text-sm text-stone-500">Фото не приложены</p>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                    @foreach($ticket->images as $image)
                        <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="ticket-{{ $ticket->id }}" class="block rounded-xl overflow-hidden border border-stone-200 bg-stone-50 hover:border-sky-300 transition-colors group cursor-zoom-in">
                            <img src="{{ $image->url }}" alt="Фото обращения" class="w-full h-32 sm:h-40 md:h-48 object-cover group-hover:scale-[1.02] transition-transform duration-200">
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-stone-200 bg-stone-50/80">
                    <h2 class="font-semibold text-stone-900 flex items-center gap-2">
                        @svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5 text-sky-500')
                        Диалог
                    </h2>
                    <p class="text-sm text-stone-500 mt-0.5">Сообщений: {{ $ticket->messages->count() }}</p>
                </div>

                <div class="p-4 sm:p-6">
                    <div class="space-y-4 max-h-[360px] sm:max-h-[440px] md:max-h-[520px] overflow-y-auto pr-1 -mr-1 scroll-smooth">
                        @forelse($ticket->messages as $message)
                            @php($isAdmin = $message->sender_role === 'admin')
                            <div class="flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}">
                                <div class="flex flex-col max-w-[95%] sm:max-w-[85%] md:max-w-[75%] {{ $isAdmin ? 'items-start' : 'items-end' }}">
                                    <div class="flex items-center gap-2 mb-1 {{ $isAdmin ? '' : 'flex-row-reverse' }}">
                                        <span class="text-xs font-medium {{ $isAdmin ? 'text-stone-500' : 'text-sky-600' }}">
                                            {{ $isAdmin ? 'Администратор' : 'Вы' }}
                                        </span>
                                        <span class="text-[11px] text-stone-400">
                                            {{ $message->created_at->format(config('app.datetime_format')) }}
                                        </span>
                                    </div>
                                    <div class="rounded-xl px-4 py-3 {{ $isAdmin ? 'bg-stone-100 border border-stone-200 rounded-tl-sm' : 'bg-sky-50 border border-sky-200 rounded-tr-sm' }}">
                                        <div class="text-sm leading-relaxed whitespace-pre-wrap {{ $isAdmin ? 'text-stone-800' : 'text-stone-800' }}">{{ $message->content }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center">
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-stone-100 text-stone-400 mb-3">
                                    @svg('heroicon-o-chat-bubble-left-right', 'w-6 h-6')
                                </div>
                                <p class="text-stone-500 text-sm">Сообщений пока нет</p>
                                <p class="text-stone-400 text-xs mt-1">Ответ специалиста появится здесь</p>
                            </div>
                        @endforelse
                    </div>

                    @if(!in_array($ticket->status, ['resolved', 'closed'], true))
                        <form action="{{ route('tickets.my.reply', $ticket) }}" method="POST" class="mt-4 pt-4 border-t border-stone-200">
                            @csrf
                            <textarea name="message" rows="3" maxlength="2000" required placeholder="Написать ответ..." class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-lg focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y">{{ old('message') }}</textarea>
                            @error('message')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
                            <div class="mt-2">
                                <x-ui.button type="submit" variant="primary" size="sm">Отправить</x-ui.button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection


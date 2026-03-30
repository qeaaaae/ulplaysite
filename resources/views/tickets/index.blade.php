@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.section-heading tag="h1" icon="heroicon-o-chat-bubble-left-right" class="mb-0">Мои обращения</x-ui.section-heading>
                <x-ui.button href="{{ route('home') }}" variant="outline" size="sm" class="w-full sm:w-auto justify-center shrink-0">
                    @svg('heroicon-o-home', 'w-4 h-4')
                    На главную
                </x-ui.button>
            </div>

            @if($tickets->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-200 bg-stone-50/60 px-6 py-14 text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white border border-stone-200 shadow-sm text-sky-600 mb-4">
                        @svg('heroicon-o-inbox', 'w-7 h-7')
                    </span>
                    <p class="text-stone-700 font-medium">Пока нет обращений</p>
                    <p class="text-sm text-stone-500 mt-1 max-w-sm mx-auto">Напишите нам с карточки услуги, из корзины или через форму поддержки — диалог появится здесь.</p>
                </div>
            @else
                <div class="flex flex-col gap-4">
                    @foreach($tickets as $ticket)
                        @php
                            $type = $ticket->type instanceof \App\Enums\SupportTicketTypeEnum ? $ticket->type : \App\Enums\SupportTicketTypeEnum::tryFrom((string) $ticket->type);
                            $statusLabel = match ($ticket->status) {
                                'new' => 'Новый',
                                'in_progress' => 'В работе',
                                'resolved' => 'Решён',
                                'closed' => 'Закрыт',
                                default => (string) $ticket->status,
                            };
                            $statusClass = match ($ticket->status) {
                                'resolved' => 'bg-emerald-100 text-emerald-800',
                                'closed' => 'bg-stone-200 text-stone-700',
                                'in_progress' => 'bg-sky-100 text-sky-800',
                                default => 'bg-amber-100 text-amber-900',
                            };
                        @endphp
                        <a
                            href="{{ route('tickets.my.show', $ticket) }}"
                            class="group flex gap-4 rounded-2xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-sky-300 hover:shadow-md"
                        >
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sky-700">
                                @svg('heroicon-o-chat-bubble-left-right', 'w-6 h-6')
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                                        {{ $type?->label() ?? '—' }}
                                    </span>
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-md {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <p class="font-semibold text-stone-900 mt-2 line-clamp-2 group-hover:text-sky-800 transition-colors">{{ $ticket->title }}</p>
                                @if($ticket->service)
                                    <p class="text-sm text-stone-600 mt-1">
                                        <span class="text-stone-500">Услуга:</span>
                                        {{ $ticket->service->title }}
                                    </p>
                                @endif
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 mt-2 text-xs text-stone-500">
                                    <span>Обновлено: {{ $ticket->updated_at->format(config('app.datetime_format')) }}</span>
                                    <span class="text-stone-300 hidden sm:inline" aria-hidden="true">·</span>
                                    <span>Сообщений: {{ $ticket->messages_count }}</span>
                                </div>
                            </div>
                            <span class="hidden sm:flex shrink-0 items-center self-center text-stone-400 group-hover:text-sky-600 transition-colors" aria-hidden="true">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5')
                            </span>
                        </a>
                    @endforeach
                </div>

                @if($tickets->hasPages())
                    <div class="pt-2 border-t border-stone-100">
                        {{ $tickets->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

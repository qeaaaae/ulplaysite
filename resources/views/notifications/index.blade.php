@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.section-heading tag="h1" icon="heroicon-o-bell" class="mb-0">Уведомления</x-ui.section-heading>
                <x-ui.button href="{{ route('tickets.my.index') }}" variant="outline" size="sm" class="w-full sm:w-auto justify-center shrink-0">
                    @svg('heroicon-o-chat-bubble-left-right', 'w-4 h-4')
                    Мои обращения
                </x-ui.button>
            </div>

            @if($notifications->isEmpty())
                <div class="rounded-2xl border border-dashed border-stone-200 bg-stone-50/60 px-6 py-14 text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-white border border-stone-200 shadow-sm text-sky-600 mb-4">
                        @svg('heroicon-o-bell-slash', 'w-7 h-7')
                    </span>
                    <p class="text-stone-700 font-medium">Пока нет уведомлений</p>
                    <p class="text-sm text-stone-500 mt-1 max-w-sm mx-auto">Когда появятся ответы по обращениям или изменения по заказам — всё будет здесь.</p>
                </div>
            @else
                <div class="flex flex-col gap-4">
                    @foreach($notifications as $notification)
                        @php
                            $type = $notification->type;
                            [$iconName, $iconWrapClass, $typeLabel] = match (true) {
                                $type === 'ticket_reply' => [
                                    'heroicon-o-chat-bubble-left-right',
                                    'bg-sky-100 text-sky-700',
                                    'Обращение',
                                ],
                                $type === 'ticket_status_changed' => [
                                    'heroicon-o-check-badge',
                                    'bg-emerald-100 text-emerald-800',
                                    'Обращение',
                                ],
                                $type === 'order_status_changed' => [
                                    'heroicon-o-shopping-bag',
                                    'bg-amber-100 text-amber-900',
                                    'Заказ',
                                ],
                                $type === 'admin_new_order' => [
                                    'heroicon-o-shopping-bag',
                                    'bg-sky-100 text-sky-700',
                                    'Новый заказ',
                                ],
                                $type === 'admin_new_ticket' => [
                                    'heroicon-o-lifebuoy',
                                    'bg-amber-100 text-amber-900',
                                    'Новый тикет',
                                ],
                                default => [
                                    'heroicon-o-bell',
                                    'bg-stone-100 text-stone-700',
                                    'Сообщение',
                                ],
                            };
                        @endphp
                        <a
                            href="{{ $notification->url ?? route('tickets.my.index') }}"
                            class="group flex gap-4 rounded-2xl border border-stone-200 bg-white p-4 sm:p-5 shadow-sm transition-all hover:border-sky-300 hover:shadow-md"
                        >
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $iconWrapClass }}">
                                @svg($iconName, 'w-6 h-6')
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 gap-y-1">
                                    <span class="text-[11px] font-semibold uppercase tracking-wide text-stone-500">{{ $typeLabel }}</span>
                                    <span class="text-xs text-stone-400 tabular-nums sm:ml-auto sm:order-last">{{ $notification->created_at->format(config('app.datetime_format')) }}</span>
                                </div>
                                <p class="font-semibold text-stone-900 mt-1 group-hover:text-sky-800 transition-colors">{{ $notification->title ?? 'Уведомление' }}</p>
                                @if(!empty($notification->body))
                                    <p class="text-sm text-stone-600 mt-1.5 whitespace-pre-wrap line-clamp-3">{{ $notification->body }}</p>
                                @endif
                            </div>
                            <span class="hidden sm:flex shrink-0 items-center self-center text-stone-400 group-hover:text-sky-600 transition-colors" aria-hidden="true">
                                @svg('heroicon-o-chevron-right', 'w-5 h-5')
                            </span>
                        </a>
                    @endforeach
                </div>

                @if($notifications->hasPages())
                    <div class="pt-2 border-t border-stone-100">
                        {{ $notifications->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

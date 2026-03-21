@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex items-center justify-between gap-4 mb-8">
                <h1 class="text-2xl font-semibold text-stone-900">Мои обращения</h1>
                <a href="{{ route('home') }}" class="text-sm text-sky-600 hover:underline">На главную</a>
            </div>

            @if($tickets->isEmpty())
                <p class="text-stone-500 py-12">У вас пока нет обращений</p>
            @else
                <div class="space-y-4">
                    @foreach($tickets as $ticket)
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

                        <a href="{{ route('tickets.my.show', $ticket) }}" class="block p-6 bg-white rounded-xl border border-stone-200 hover:border-sky-200 transition-colors">
                            <div class="flex justify-between gap-4 items-start">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2 mb-2">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                                            {{ $type?->label() ?? '—' }}
                                        </span>
                                        <span class="font-medium text-stone-900 line-clamp-1">{{ $ticket->title }}</span>
                                    </div>
                                    <p class="text-sm text-stone-500">
                                        Обновлено: {{ $ticket->updated_at->format(config('app.datetime_format')) }}
                                    </p>
                                </div>
                                <div class="text-right shrink-0">
                                    <div class="inline-flex px-2 py-0.5 text-xs font-medium rounded {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </div>
                                    <p class="text-sm text-stone-500 mt-2">
                                        Сообщений: {{ $ticket->messages_count }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection


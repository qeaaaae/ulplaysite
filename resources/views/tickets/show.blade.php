@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8 space-y-6">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-stone-900">Обращение #{{ $ticket->id }} — {{ $ticket->title }}</h1>
                    <p class="text-sm text-stone-500 mt-1">{{ $ticket->created_at->format(config('app.datetime_format')) }}</p>
                </div>
                <a href="{{ route('tickets.my.index') }}" class="text-sm text-sky-600 hover:underline">К списку</a>
            </div>

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

            @if($ticket->images->isEmpty())
                <p class="text-stone-500">Фото не приложены</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($ticket->images as $image)
                        <a href="{{ $image->url }}" target="_blank" class="block rounded-lg overflow-hidden border border-stone-200 bg-stone-50">
                            <img src="{{ $image->url }}" alt="Фото обращения" class="w-full h-48 object-cover">
                        </a>
                    @endforeach
                </div>
            @endif

            <div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-stone-200 bg-stone-50">
                    <h2 class="font-medium text-stone-900">Диалог</h2>
                </div>

                <div class="p-4 sm:p-6 space-y-3">
                    <div class="space-y-3 max-h-[520px] overflow-y-auto pr-1">
                        @forelse($ticket->messages as $message)
                            @php($isAdmin = $message->sender_role === 'admin')
                            <div class="flex {{ $isAdmin ? 'justify-start' : 'justify-end' }}">
                                <div class="max-w-[80%] rounded-lg border px-3 py-2 {{ $isAdmin ? 'bg-stone-50 border-stone-200' : 'bg-sky-50 border-sky-200' }}">
                                    <div class="text-xs font-medium mb-1 {{ $isAdmin ? 'text-stone-700' : 'text-sky-800' }}">
                                        {{ $isAdmin ? 'Администратор' : 'Вы' }}
                                    </div>
                                    <div class="text-sm text-stone-800 whitespace-pre-wrap">{{ $message->content }}</div>
                                    <div class="text-[11px] text-stone-500 mt-2">
                                        {{ $message->created_at->format(config('app.datetime_format')) }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-stone-500">Сообщений пока нет</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


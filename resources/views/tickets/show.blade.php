@extends('layouts.app')

@section('content')
    <div class="py-4">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <h1 class="section-heading text-xl sm:text-2xl text-stone-900 break-words leading-tight">
                        Обращение #{{ $ticket->id }}
                    </h1>
                    <p class="text-sm text-stone-500 mt-1.5 font-medium line-clamp-2">{{ $ticket->title }}</p>
                    <p class="text-xs text-stone-400 mt-1">{{ $ticket->created_at->format(config('app.datetime_format')) }}</p>
                </div>
                <x-ui.button href="{{ route('tickets.my.index') }}" variant="outline" size="sm" class="w-full sm:w-auto justify-center shrink-0">
                    @svg('heroicon-o-arrow-left', 'w-4 h-4')
                    К списку
                </x-ui.button>
            </div>

            @if(session('error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                    {{ session('error') }}
                </div>
            @endif

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

            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg {{ $type?->badgeClass() ?? 'bg-stone-100 text-stone-700' }}">
                    {{ $type?->label() ?? '—' }}
                </span>
                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-lg {{ $statusClass }}">
                    {{ $statusLabel }}
                </span>
                <span class="text-sm text-stone-500 ml-auto sm:ml-0">
                    Сообщений: {{ $ticket->messages->count() }}
                </span>
            </div>

            @if($ticket->service)
                <div class="flex flex-wrap items-center gap-3 rounded-2xl border border-sky-100 bg-sky-50/90 p-4 sm:p-5 shadow-sm">
                    @svg('heroicon-o-wrench-screwdriver', 'w-6 h-6 text-sky-600 shrink-0')
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-stone-500 uppercase tracking-wide">Услуга</p>
                        <a href="{{ route('services.show', $ticket->service) }}" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-sky-700 hover:text-sky-900 inline-flex items-center gap-1.5 break-words mt-0.5">
                            {{ $ticket->service->title }}
                            @svg('heroicon-o-arrow-top-right-on-square', 'w-4 h-4 shrink-0')
                        </a>
                    </div>
                </div>
            @endif

            @if($ticket->images->isEmpty())
                <p class="text-sm text-stone-500">Фото не приложены</p>
            @else
                <div>
                    <h2 class="section-heading text-base text-stone-900 mb-3">Прикреплённые фото</h2>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-3">
                        @foreach($ticket->images as $image)
                            <a href="{{ $image->url }}" data-lightbox="image" data-lightbox-group="ticket-{{ $ticket->id }}" class="block rounded-xl overflow-hidden border border-stone-200 bg-stone-50 hover:border-sky-300 transition-colors group cursor-zoom-in shadow-sm">
                                <img src="{{ $image->url }}" alt="Фото обращения" class="w-full h-32 sm:h-40 md:h-48 object-cover group-hover:scale-[1.02] transition-transform duration-200">
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div
                class="rounded-2xl border border-stone-200 bg-white shadow-sm overflow-hidden"
                x-data="{
                    sending: false,
                    text: '',
                    error: '',
                    msgCount: {{ $ticket->messages->count() }},
                    replyUrl: {{ \Illuminate\Support\Js::from(route('tickets.my.reply', $ticket)) }},
                    csrf: {{ \Illuminate\Support\Js::from(csrf_token()) }},
                    scrollToBottom() {
                        const el = this.$refs.messagesScroll;
                        if (el) el.scrollTop = el.scrollHeight;
                    },
                    appendMessage(msg) {
                        const isAdmin = msg.sender_role === 'admin';
                        const wrap = document.createElement('div');
                        wrap.className = 'flex ' + (isAdmin ? 'justify-start' : 'justify-end');
                        wrap.innerHTML = \`
                            <div class='flex flex-col max-w-[95%] sm:max-w-[85%] md:max-w-[75%] \${isAdmin ? 'items-start' : 'items-end'}'>
                                <div class='flex items-center gap-2 mb-1 \${isAdmin ? '' : 'flex-row-reverse'}'>
                                    <span class='text-xs font-medium \${isAdmin ? 'text-stone-500' : 'text-sky-600'}'>\${isAdmin ? 'Администратор' : 'Вы'}</span>
                                    <span class='text-[11px] text-stone-400'>\${msg.created_at}</span>
                                </div>
                                <div class='rounded-xl px-4 py-3 \${isAdmin ? 'bg-stone-100 border border-stone-200 rounded-tl-sm' : 'bg-sky-50 border border-sky-200 rounded-tr-sm'}'>
                                    <div class='text-sm leading-relaxed whitespace-pre-wrap text-stone-800'>\${msg.content.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                                </div>
                            </div>
                        \`;
                        const emptyState = this.$refs.messagesScroll.querySelector('[data-empty-state]');
                        if (emptyState) emptyState.remove();
                        this.$refs.messagesScroll.appendChild(wrap);
                        this.msgCount++;
                        this.$nextTick(() => this.scrollToBottom());
                    },
                    async submit() {
                        if (!this.text.trim() || this.sending) return;
                        this.sending = true;
                        this.error = '';
                        try {
                            const res = await fetch(this.replyUrl, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': this.csrf,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify({ message: this.text }),
                            });
                            const data = await res.json().catch(() => ({}));
                            if (res.ok && data?.result) {
                                this.appendMessage(data.message);
                                this.text = '';
                            } else {
                                this.error = data?.error || data?.errors?.message?.[0] || 'Не удалось отправить сообщение';
                            }
                        } catch {
                            this.error = 'Ошибка соединения. Попробуйте ещё раз.';
                        } finally {
                            this.sending = false;
                        }
                    }
                }"
                x-init="$nextTick(() => scrollToBottom())"
            >
                <div class="px-5 sm:px-6 py-4 border-b border-stone-100 bg-stone-50/90">
                    <h2 class="section-heading text-lg flex items-center gap-2.5 text-stone-900">
                        <span class="text-sky-600 shrink-0">@svg('heroicon-o-chat-bubble-left-right', 'w-5 h-5')</span>
                        Диалог
                    </h2>
                    <p class="text-sm text-stone-500 mt-0.5">Сообщений: <span x-text="msgCount">{{ $ticket->messages->count() }}</span></p>
                </div>

                <div class="p-4 sm:p-6">
                    <div x-ref="messagesScroll" class="space-y-4 max-h-[360px] sm:max-h-[440px] md:max-h-[520px] overflow-y-auto pr-1 -mr-1 scroll-smooth ulplay-scrollbar-sky">
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
                                        <div class="text-sm leading-relaxed whitespace-pre-wrap text-stone-800">{{ $message->content }}</div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center" data-empty-state>
                                <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-stone-100 text-stone-400 mb-3 border border-stone-200">
                                    @svg('heroicon-o-chat-bubble-left-right', 'w-6 h-6')
                                </div>
                                <p class="text-stone-600 text-sm font-medium">Сообщений пока нет</p>
                                <p class="text-stone-400 text-xs mt-1">Ответ специалиста появится здесь</p>
                            </div>
                        @endforelse
                    </div>

                    @if(!in_array($ticket->status, ['resolved', 'closed'], true))
                        <form @submit.prevent="submit()" class="mt-5 pt-5 border-t border-stone-100">
                            <textarea
                                x-model="text"
                                @keydown.ctrl.enter.prevent="submit()"
                                @keydown.meta.enter.prevent="submit()"
                                rows="3"
                                maxlength="2000"
                                required
                                placeholder="Написать ответ... (Ctrl+Enter для отправки)"
                                class="w-full px-3 py-2.5 text-sm border border-stone-200 rounded-xl focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y transition-colors"
                                :class="error ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/30' : ''"
                                :disabled="sending"
                            ></textarea>
                            <p x-show="error" x-text="error" x-cloak class="mt-1 text-xs text-rose-600"></p>
                            <div class="mt-3">
                                <x-ui.button type="submit" variant="primary" ::disabled="sending || !text.trim()">
                                    <span x-show="!sending">@svg('heroicon-o-paper-airplane', 'w-4 h-4')</span>
                                    <span x-show="sending" x-cloak class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin shrink-0"></span>
                                    <span x-text="sending ? 'Отправка...' : 'Отправить'">Отправить</span>
                                </x-ui.button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

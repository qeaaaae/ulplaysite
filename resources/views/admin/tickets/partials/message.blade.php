@php($isAdmin = $message->sender_role === 'admin')
<div class="flex {{ $isAdmin ? 'justify-end' : 'justify-start' }}">
    <div class="flex flex-col max-w-[95%] sm:max-w-[85%] md:max-w-[75%] {{ $isAdmin ? 'items-end' : 'items-start' }}">
        <div class="flex items-center gap-2 mb-1 {{ $isAdmin ? 'flex-row-reverse' : '' }}">
            <span class="text-xs font-medium {{ $isAdmin ? 'text-sky-600' : 'text-stone-500' }}">
                {{ $isAdmin ? 'Вы' : ($message->senderUser?->name ?? 'Пользователь') }}
            </span>
            <span class="text-[11px] text-stone-400">
                {{ $message->created_at->format(config('app.datetime_format')) }}
            </span>
        </div>
        <div class="rounded-xl px-4 py-3 {{ $isAdmin ? 'bg-sky-50 border border-sky-200 rounded-tr-sm' : 'bg-stone-100 border border-stone-200 rounded-tl-sm' }}">
            <div class="text-sm leading-relaxed whitespace-pre-wrap text-stone-800">{{ $message->content }}</div>
        </div>
    </div>
</div>

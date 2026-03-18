@props([
    'news',
    'comments' => collect(),
    'canComment' => false,
])

<div id="comments" class="mt-8 pt-6 border-t border-stone-200">
    <h2 class="text-lg font-semibold text-stone-900 mb-4">Комментарии {{ $comments->count() ? '(' . $comments->count() . ')' : '' }}</h2>

    @if($canComment)
    <form action="{{ route('comments.store', $news) }}" method="POST" class="mb-6 p-4 sm:p-5 bg-white rounded-xl border border-stone-200 shadow-sm" x-data="{
        emojiOpen: false,
        insertEmoji(em) {
            const ta = document.getElementById('comment-body');
            if (!ta) return;
            const s = ta.selectionStart, e = ta.selectionEnd;
            ta.value = ta.value.slice(0, s) + em + ta.value.slice(e);
            ta.focus();
            ta.setSelectionRange(s + em.length, s + em.length);
            this.emojiOpen = false;
        }
    }">
        @csrf
        <div class="flex flex-col gap-3 sm:gap-4 w-full">
            <div class="min-w-0 w-full">
                <label for="comment-body" class="sr-only">Комментарий</label>
                <textarea name="body" id="comment-body" rows="3" maxlength="500" required class="w-full min-h-[88px] sm:min-h-[80px] px-3 py-2.5 text-sm bg-stone-50/80 border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 focus:bg-white resize-y transition-colors" placeholder="Комментарий...">{{ old('body') }}</textarea>
                @error('body')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-nowrap sm:flex-wrap items-center gap-2 sm:gap-3">
                <x-ui.button type="submit" variant="primary" class="flex-1 min-w-0 sm:flex-none sm:min-w-[120px] shrink-0 py-2.5">Отправить</x-ui.button>
                <div class="relative shrink-0">
                    <button type="button" @click="emojiOpen = !emojiOpen" class="inline-flex items-center justify-center w-10 h-10 text-stone-600 hover:text-stone-800 border border-stone-300 rounded-md hover:bg-stone-50 transition-colors cursor-pointer" title="Эмодзи" aria-label="Вставить эмодзи">
                        @svg('heroicon-o-face-smile', 'w-5 h-5')
                    </button>
                    <div x-show="emojiOpen" x-cloak x-transition @click.outside="emojiOpen = false" class="absolute right-0 bottom-full mb-1 p-3 bg-white border border-stone-200 rounded-lg shadow-lg z-10 w-[280px]">
                        <div class="grid grid-cols-8 gap-0.5 max-h-[180px] overflow-y-auto overflow-x-hidden">
                            @foreach(['😀','😃','😄','😁','😅','😂','🤣','😊','😇','🙂','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😜','🤪','😝','🤑','🤗','🤭','🤫','🤔','🤐','🤨','😐','😑','😶','😏','😣','😥','😮','🤐','😯','😪','😫','😴','🤤','😷','🤒','🤕','🤢','🤮','👍','👎','👌','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','☝️','❤️','🧡','💛','💚','💙','💜','🖤','🤍','🤎','💔','❣️','💕','💞','💓','💗','💖','💘','💝','🔥','⭐','🌟','✨','💫','🎮','🎯','🎲','🧩','🎸','🎹','🎺','🎻','🥁','🎨','🎭','📷','📸','💻','📱','🖥️','⌨️','🖱️'] as $em)
                                <button type="button" @click="insertEmoji('{{ $em }}')" class="w-8 h-8 flex items-center justify-center text-lg hover:bg-stone-100 rounded cursor-pointer shrink-0">{{ $em }}</button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @else
        <x-ui.info-banner>
            <a href="{{ route('login') }}" class="text-sky-600 hover:underline">Войдите</a>, чтобы оставить комментарий.
        </x-ui.info-banner>
    @endif

    @if($comments->isEmpty())
        <p class="text-stone-500 text-sm">Пока ничего нет.</p>
    @else
        <ul class="space-y-3">
            @foreach($comments as $comment)
                <li class="p-3 bg-white rounded-lg border border-stone-200">
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-stone-700">{{ $comment->user->name ?? 'Гость' }}</span>
                        <span class="text-xs text-stone-400">{{ $comment->created_at->format(config('app.datetime_format')) }}</span>
                    </div>
                    <p class="text-stone-600 text-sm leading-snug line-clamp-4">{{ $comment->body }}</p>
                </li>
            @endforeach
        </ul>
    @endif
</div>

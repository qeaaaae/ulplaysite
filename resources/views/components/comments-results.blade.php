@props([
    'news',
    'comments' => collect(),
    'canComment' => false,
])
@php
    $isPaginated = $comments instanceof \Illuminate\Pagination\AbstractPaginator;
    $commentsList = $isPaginated ? $comments->getCollection() : $comments;
@endphp
@if($commentsList->isEmpty())
    <p class="text-stone-500 text-sm">Пока ничего нет.</p>
@else
    <ul id="comments-grid" class="space-y-3">
        @foreach($commentsList as $comment)
            @php
                $canEdit = auth()->check() && $comment->isEditableBy(auth()->user());
                $canDelete = auth()->check() && $comment->isDeletableBy(auth()->user());
                $helpfulCount = $comment->helpfulVotes->count();
                $hasHelpful = auth()->check() && $comment->helpfulVotes->contains('user_id', auth()->id());
            @endphp
            <li class="p-3 sm:p-5 bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden" data-comment-id="{{ $comment->id }}" x-data="{
                editing: false,
                emojiOpen: false,
                insertEmoji(em) {
                    const ta = document.getElementById('comment-edit-body-{{ $comment->id }}');
                    if (!ta) return;
                    const s = ta.selectionStart, e = ta.selectionEnd;
                    ta.value = ta.value.slice(0, s) + em + ta.value.slice(e);
                    ta.focus();
                    ta.setSelectionRange(s + em.length, s + em.length);
                    this.emojiOpen = false;
                }
            }" @comment-edit-done.window="if ($event.detail.commentId == $el.dataset.commentId) editing = false">
                <div class="relative">
                    <div class="min-w-0 @if($canEdit || $canDelete) pe-[5.25rem] sm:pe-24 @endif" data-comment-header="{{ $comment->id }}">
                        <div class="flex flex-col gap-0.5 sm:flex-row sm:flex-wrap sm:items-baseline sm:gap-x-2 sm:gap-y-0">
                            <span class="text-sm sm:text-base font-medium text-stone-800 break-words">{{ $comment->user->name ?? 'Гость' }}</span>
                            <span class="text-xs sm:text-sm text-stone-400 tabular-nums shrink-0">{{ $comment->created_at->format(config('app.datetime_format')) }}</span>
                            @if($comment->edited_at)
                                <span class="text-xs sm:text-sm text-stone-400 italic" data-comment-edited-label="{{ $comment->id }}">(изменено)</span>
                            @endif
                        </div>
                    </div>
                    @if($canEdit || $canDelete)
                        <div class="absolute end-0 top-0 flex items-center gap-0.5 sm:gap-1">
                            @if($canEdit)
                                <button type="button" @click="editing = true" x-show="!editing" class="p-2.5 sm:p-2 min-w-[44px] min-h-[44px] sm:min-w-0 sm:min-h-0 inline-flex items-center justify-center text-stone-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors touch-manipulation" title="Изменить" aria-label="Изменить комментарий">
                                    @svg('heroicon-o-pencil-square', 'w-5 h-5')
                                </button>
                            @endif
                            @if($canDelete)
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline" data-ajax-comment-delete data-comment-delete-id="{{ $comment->id }}" data-confirm-message="Удалить комментарий?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2.5 sm:p-2 min-w-[44px] min-h-[44px] sm:min-w-0 sm:min-h-0 inline-flex items-center justify-center text-stone-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors touch-manipulation" title="Удалить" aria-label="Удалить комментарий">
                                        @svg('heroicon-o-trash', 'w-5 h-5')
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
                <div x-show="!editing" class="mt-1.5 sm:mt-2">
                    <p class="text-stone-700 text-sm sm:text-base leading-relaxed line-clamp-6 break-words" data-comment-body="{{ $comment->id }}">{{ $comment->body }}</p>
                </div>
                @if($canEdit)
                    <form x-show="editing" x-cloak method="POST" action="{{ route('comments.update', $comment) }}" class="mt-3 p-3 sm:p-4 bg-stone-50 rounded-xl border border-stone-200" data-ajax-comment-edit data-comment-edit-id="{{ $comment->id }}">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-col gap-3">
                            <textarea id="comment-edit-body-{{ $comment->id }}" name="body" rows="3" maxlength="500" required class="w-full min-h-[88px] px-3 py-2.5 text-sm sm:text-base bg-white border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y transition-colors" placeholder="Комментарий...">{{ $comment->body }}</textarea>
                            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-3">
                                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                                <x-ui.button type="submit" variant="primary" size="sm" class="min-h-[44px] sm:min-h-0 px-4 sm:px-3 touch-manipulation">Сохранить</x-ui.button>
                                <button type="button" @click="editing = false" class="min-h-[44px] sm:min-h-0 px-4 sm:px-3 py-2 text-sm font-medium text-stone-600 hover:text-stone-800 border border-stone-300 rounded-lg hover:bg-stone-100 transition-colors touch-manipulation">Отмена</button>
                                </div>
                                <div class="relative shrink-0">
                                    <button type="button" @click="emojiOpen = !emojiOpen" class="inline-flex items-center justify-center w-11 h-11 sm:w-10 sm:h-10 text-stone-600 hover:text-stone-800 border border-stone-300 rounded-lg hover:bg-white transition-colors cursor-pointer touch-manipulation" title="Эмодзи" aria-label="Вставить эмодзи">
                                        @svg('heroicon-o-face-smile', 'w-5 h-5')
                                    </button>
                                    <div x-show="emojiOpen" x-cloak x-transition @click.outside="emojiOpen = false" class="absolute right-0 sm:left-0 sm:right-auto bottom-full mb-1 p-2 sm:p-3 bg-white border border-stone-200 rounded-lg shadow-lg z-10 w-[min(280px,calc(100vw-2rem))] max-w-[calc(100vw-2rem)]">
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
                @endif

                <div class="mt-1.5 sm:mt-2">
                    @if($canComment)
                        <form
                            method="POST"
                            action="{{ route('comments.helpful', $comment) }}"
                            class="inline-flex"
                            data-ajax-comment-helpful
                            data-comment-helpful-comment-id="{{ $comment->id }}"
                        >
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center gap-1.5 min-h-[44px] sm:min-h-0 px-2 py-1.5 -ml-2 sm:ml-0 text-sm font-medium transition-colors cursor-pointer text-sky-600 hover:text-sky-700 touch-manipulation rounded-lg hover:bg-sky-50/80"
                                aria-label="{{ $hasHelpful ? 'Убрать оценку' : 'Отметить комментарий как полезный' }}"
                            >
                                <span class="comment-helpful-icon-outline shrink-0 {{ $hasHelpful ? 'hidden' : '' }}" aria-hidden="true">
                                    @svg('heroicon-o-hand-thumb-up', 'w-5 h-5')
                                </span>
                                <span class="comment-helpful-icon-filled shrink-0 {{ $hasHelpful ? '' : 'hidden' }}" aria-hidden="true">
                                    @svg('heroicon-s-hand-thumb-up', 'w-5 h-5')
                                </span>
                                <span class="text-sm text-stone-500 tabular-nums leading-none" data-comment-helpful-count="{{ $comment->id }}">{{ $helpfulCount }}</span>
                            </button>
                        </form>
                    @else
                        <div class="inline-flex items-center gap-2 flex-wrap">
                            <a href="{{ route('login') }}" class="text-sm font-medium text-sky-600 hover:text-sky-700 transition-colors">Войдите</a>
                            <span class="text-sm text-stone-500 tabular-nums leading-none" data-comment-helpful-count="{{ $comment->id }}">{{ $helpfulCount }}</span>
                        </div>
                    @endif
                </div>
            </li>
        @endforeach
    </ul>
    @if($isPaginated && $comments->hasMorePages())
        <div
            id="comments-infinite-sentinel"
            data-next-url="{{ $comments->nextPageUrl() }}"
            class="h-1 w-full shrink-0 pointer-events-none mt-2"
            aria-hidden="true"
        ></div>
    @endif
@endif

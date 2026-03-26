@props([
    'news',
    'comments' => collect(),
    'canComment' => false,
])
@php
    $isPaginated = $comments instanceof \Illuminate\Pagination\AbstractPaginator;
    $totalCount = $isPaginated ? $comments->total() : $comments->count();
    $currentSort = request('comments_sort', 'newest');
    $commentsIndexUrl = fn ($sort, $page = 1) => route('comments.index', ['news' => $news, 'comments_sort' => $sort, 'comments_page' => $page]);
    $selectClass = 'h-11 px-3 py-2.5 bg-white border border-stone-300 rounded-lg text-sm font-medium text-stone-800 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 appearance-none bg-no-repeat pr-9 bg-[length:1.25rem_1.25rem] bg-[right_0.5rem_center]';
@endphp
<div
    id="comments"
    class="mt-8 pt-6 border-t border-stone-200 relative"
    data-comments-page="{{ $isPaginated ? $comments->currentPage() : 1 }}"
    data-comments-sort="{{ $currentSort }}"
    x-data="{
        loading: false,
        loadingMore: false,
        abortController: null,
        infiniteObserver: null,
        async load(url, append = false) {
            if (!url) return;
            if (this.abortController) this.abortController.abort();
            this.abortController = new AbortController();
            this.loading = !append;
            this.loadingMore = append;
            try {
                const res = await fetch(url, {
                    method: 'GET',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    signal: this.abortController.signal,
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data?.result && data?.html) {
                    const el = document.getElementById('comments-results');
                    if (!el) return;

                    if (!append) {
                        el.innerHTML = data.html;
                        if (window.Alpine?.initTree) window.Alpine.initTree(el);
                    } else {
                        const tmp = document.createElement('div');
                        tmp.innerHTML = data.html;

                        const currentGrid = el.querySelector('#comments-grid');
                        const newGrid = tmp.querySelector('#comments-grid');
                        if (currentGrid && newGrid) {
                            currentGrid.append(...Array.from(newGrid.children));
                        }

                        const currentSentinel = el.querySelector('#comments-infinite-sentinel');
                        const newSentinel = tmp.querySelector('#comments-infinite-sentinel');
                        if (newSentinel) {
                            if (currentSentinel) currentSentinel.replaceWith(newSentinel);
                            else el.appendChild(newSentinel);
                        } else if (currentSentinel) {
                            currentSentinel.remove();
                        }

                        if (window.Alpine?.initTree) window.Alpine.initTree(el);
                    }

                    queueMicrotask(() => this.setupInfiniteScroll());
                }
            } catch (e) {
                if (e?.name !== 'AbortError') console.error(e);
            } finally {
                this.loading = false;
                this.loadingMore = false;
            }
        },
        setupInfiniteScroll() {
            if (this.infiniteObserver) {
                this.infiniteObserver.disconnect();
                this.infiniteObserver = null;
            }
            const resultsEl = document.getElementById('comments-results');
            if (!resultsEl) return;
            const sentinel = resultsEl.querySelector('#comments-infinite-sentinel');
            if (!sentinel) return;
            const nextUrl = sentinel.dataset.nextUrl || '';
            if (!nextUrl) return;
            this.infiniteObserver = new IntersectionObserver(
                (entries) => {
                    for (const entry of entries) {
                        if (!entry.isIntersecting) continue;
                        if (this.loading || this.loadingMore) continue;
                        const u = entry.target?.dataset?.nextUrl || '';
                        if (!u) continue;
                        this.infiniteObserver?.disconnect();
                        this.infiniteObserver = null;
                        this.load(u, true);
                        break;
                    }
                },
                { root: null, rootMargin: '480px 0px 0px 0px', threshold: 0 }
            );
            this.infiniteObserver.observe(sentinel);
        },
        init() {
            const root = this.$el;
            root.querySelectorAll('select[data-ajax-comments-filter]').forEach((sel) => {
                if (sel.tomselect?.on) sel.tomselect.on('change', (value) => this.load(value));
                sel.addEventListener('change', (e) => this.load(e.target.value));
            });
            this.setupInfiniteScroll();
        }
    }"
    x-init="init()"
>
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h2 class="text-lg font-semibold text-stone-900">Комментарии {{ $totalCount ? '(' . $totalCount . ')' : '' }}</h2>
        @if($totalCount > 0)
            <div class="flex items-center gap-2 comments-sort-select-wrap">
                <select
                    data-enhance="tom-select"
                    data-ajax-comments-filter
                    class="{{ $selectClass }}"
                    style="background-image:url('data:image/svg+xml,%3csvg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 viewBox=%220 0 20 20%22%3e%3cpath stroke=%22%2378716c%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22 stroke-width=%221.5%22 d=%22M6 8l4 4 4-4%22/%3e%3c/svg%3e')"
                >
                    <option value="{{ $commentsIndexUrl('newest') }}" {{ $currentSort === 'newest' ? 'selected' : '' }}>Сначала новые</option>
                    <option value="{{ $commentsIndexUrl('oldest') }}" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Сначала старые</option>
                    <option value="{{ $commentsIndexUrl('popular') }}" {{ $currentSort === 'popular' ? 'selected' : '' }}>Сначала популярные</option>
                </select>
            </div>
        @endif
    </div>

    @if($canComment)
    <form action="{{ route('comments.store', $news) }}" method="POST" data-ajax-comments-store class="mb-6 p-4 sm:p-5 bg-white rounded-xl border border-stone-200 shadow-sm" x-data="{
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
                <textarea name="body" id="comment-body" rows="3" maxlength="500" required class="w-full min-h-[88px] sm:min-h-[80px] px-3 py-2.5 text-sm bg-white border border-stone-200 rounded-lg text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y transition-colors" placeholder="Комментарий...">{{ old('body') }}</textarea>
                <div class="mt-1.5 text-xs text-rose-600 hidden" data-ajax-comments-error="body"></div>
                @error('body')<p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex flex-nowrap sm:flex-wrap items-center gap-2 sm:gap-3" x-data="{
                commentCooldown: 0,
                cooldownInterval: null,
                startCooldown(seconds) {
                    this.commentCooldown = seconds;
                    if (this.cooldownInterval) clearInterval(this.cooldownInterval);
                    this.cooldownInterval = setInterval(() => {
                        this.commentCooldown--;
                        if (this.commentCooldown <= 0) clearInterval(this.cooldownInterval);
                    }, 1000);
                }
            }" x-on:comment-cooldown-start.window="startCooldown($event.detail?.seconds ?? 30)">
                <x-ui.button type="submit" variant="primary" class="flex-1 min-w-0 sm:flex-none sm:min-w-[120px] shrink-0 py-2.5" x-bind:disabled="commentCooldown > 0" x-show="commentCooldown === 0">Отправить</x-ui.button>
                <span class="text-sm text-stone-500" x-show="commentCooldown > 0" x-cloak x-transition>
                    Следующий комментарий через <span x-text="commentCooldown"></span> сек.
                </span>
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

    <div class="relative">
        <div id="comments-results">
            <x-comments-results :news="$news" :comments="$comments" :can-comment="$canComment" />
        </div>
        <div
            x-show="loading || loadingMore"
            x-cloak
            x-transition.opacity.duration.200ms
            class="flex justify-center py-6 mt-1"
            role="status"
            aria-live="polite"
            aria-busy="true"
            aria-label="Загрузка"
        >
            <span
                class="inline-block h-10 w-10 shrink-0 rounded-full border-4 border-stone-200 border-t-sky-600 animate-spin [animation-duration:0.85s]"
                aria-hidden="true"
            ></span>
        </div>
    </div>
</div>

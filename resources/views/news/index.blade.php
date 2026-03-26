@extends('layouts.app')

@section('content')
    <div
        class="py-4"
        x-data="{
            loading: false,
            loadingMore: false,
            abortController: null,
            infiniteObserver: null,
            newsIndexUrl: @js(route('news.index')),
            buildNewsListUrl() {
                const u = new URL(this.newsIndexUrl, window.location.origin);
                const params = new URLSearchParams();
                const qInput = this.$el.querySelector('form[method=\'GET\'][action*=\'/news\'] input[name=\'q\']');
                if (qInput && qInput.value.trim()) params.set('q', qInput.value.trim());
                const qs = params.toString();
                return u.pathname + (qs ? '?' + qs : '');
            },
            async load(url, append = false) {
                if (!url) return;
                if (this.abortController) this.abortController.abort();
                this.abortController = new AbortController();
                this.loading = true;
                this.loadingMore = append;
                try {
                    const res = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        signal: this.abortController.signal,
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data?.result && data?.html) {
                        const el = document.getElementById('news-results');
                        if (!el) return;

                        if (!append) {
                            el.innerHTML = data.html;
                        } else {
                            const tmp = document.createElement('div');
                            tmp.innerHTML = data.html;

                            const currentGrid = el.querySelector('#news-grid');
                            const newGrid = tmp.querySelector('#news-grid');
                            if (currentGrid && newGrid) {
                                currentGrid.append(...Array.from(newGrid.children));
                            }

                            const currentSentinel = el.querySelector('#news-infinite-sentinel');
                            const newSentinel = tmp.querySelector('#news-infinite-sentinel');
                            if (newSentinel) {
                                if (currentSentinel) currentSentinel.replaceWith(newSentinel);
                                else el.appendChild(newSentinel);
                            } else if (currentSentinel) {
                                currentSentinel.remove();
                            }
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
                const resultsEl = document.getElementById('news-results');
                if (!resultsEl) return;
                const sentinel = resultsEl.querySelector('#news-infinite-sentinel');
                if (!sentinel) return;
                const nextUrl = sentinel.dataset.nextUrl || '';
                if (!nextUrl) return;
                this.infiniteObserver = new IntersectionObserver(
                    (entries) => {
                        for (const entry of entries) {
                            if (!entry.isIntersecting) continue;
                            if (this.loading) continue;
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
                const searchForm = root.querySelector('form[method=\'GET\'][action*=\'/news\']');
                if (searchForm) {
                    searchForm.addEventListener('submit', (e) => {
                        e.preventDefault();
                        this.load(this.buildNewsListUrl());
                    });
                }
                this.setupInfiniteScroll();
            }
        }"
        x-init="init()"
    >
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
                <div>
                    <x-ui.section-heading tag="h1" icon="heroicon-o-newspaper" class="mb-0">Новости</x-ui.section-heading>
                    <p class="text-stone-500 text-sm mt-1">
                        {{ $news->total() }} новостей
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-6 mb-6">
                <div class="p-4 sm:p-5 bg-white rounded-xl sm:rounded-2xl border border-stone-200 shadow-sm overflow-visible">
                    <x-ui.search-form
                        action="{{ route('news.index') }}"
                        placeholder="Поиск по новостям..."
                        :value="request('q')"
                        formClass="h-11 w-full"
                    />
                </div>
            </div>

            <div class="relative">
                <div id="news-results">
                    @include('news._results', ['news' => $news])
                </div>
                <div
                    x-show="loadingMore"
                    x-cloak
                    x-transition.opacity.duration.200ms
                    class="flex justify-center py-8 mt-1"
                    role="status"
                    aria-live="polite"
                    aria-busy="true"
                    aria-label="Загрузка"
                >
                    <span
                        class="inline-block h-12 w-12 shrink-0 rounded-full border-4 border-stone-200 border-t-sky-600 animate-spin [animation-duration:0.85s]"
                        aria-hidden="true"
                    ></span>
                </div>
            </div>
        </div>
    </div>
@endsection

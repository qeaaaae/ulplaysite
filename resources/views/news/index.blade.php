@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading tag="h1" icon="heroicon-o-newspaper" class="mb-8">Новости</x-ui.section-heading>
            <div
                class="relative"
                x-data="{
                    loading: false,
                    abortController: null,
                    async load(url) {
                        if (!url) return;
                        if (this.abortController) this.abortController.abort();
                        this.abortController = new AbortController();
                        this.loading = true;

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
                                if (el) el.innerHTML = data.html;
                            }
                        } catch (e) {
                            // Ignore aborts
                            if (e?.name !== 'AbortError') console.error(e);
                        } finally {
                            this.loading = false;
                        }
                    },
                    init() {
                        const resultsEl = document.getElementById('news-results');
                        if (!resultsEl) return;

                        resultsEl.addEventListener('click', (e) => {
                            const a = e.target?.closest?.('a');
                            if (!a) return;
                            if (!a.closest('[data-news-pagination]')) return;

                            const href = a.getAttribute('href');
                            if (!href) return;
                            e.preventDefault();
                            this.load(href);
                        });
                    }
                }"
                x-init="init()"
            >
                <div
                    x-show="loading"
                    x-cloak
                    class="absolute inset-0 bg-white/60 backdrop-blur-sm flex items-center justify-center rounded-2xl z-10"
                >
                    <div class="text-stone-600 text-sm">Загрузка...</div>
                </div>

                <div id="news-results">
                    @include('news._results', ['news' => $news])
                </div>
            </div>
        </div>
    </div>
@endsection

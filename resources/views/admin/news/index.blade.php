@extends('layouts.admin')

@section('content')
    <div
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6"
        x-data="{
            open: false,
            url: '',
            loading: false,
            error: '',
            parseUrl: {{ \Illuminate\Support\Js::from(route('admin.news.parse-url')) }},
            csrf: {{ \Illuminate\Support\Js::from(csrf_token()) }},
            async submit() {
                if (!this.url.trim() || this.loading) return;
                this.loading = true;
                this.error = '';
                try {
                    const res = await fetch(this.parseUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({ url: this.url }),
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok && data?.result) {
                        window.location.href = data.redirect;
                    } else {
                        this.error = data?.error || data?.errors?.url?.[0] || 'Не удалось распарсить страницу';
                        this.loading = false;
                    }
                } catch {
                    this.error = 'Ошибка соединения. Попробуйте ещё раз.';
                    this.loading = false;
                }
            }
        }"
        @keydown.escape.window="open = false"
    >
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-newspaper', 'w-8 h-8 text-sky-600')
            Новости
        </h1>
        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                @click="open = true; url = ''; error = ''"
                class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 bg-white text-stone-700 rounded-lg hover:bg-stone-50 hover:border-sky-400 hover:text-sky-700 text-sm font-medium transition-colors"
            >
                @svg('heroicon-o-arrow-down-tray', 'w-5 h-5')
                Импорт
            </button>
            <a href="{{ route('admin.news.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-sm font-medium transition-colors">
                @svg('heroicon-o-plus', 'w-5 h-5')
                Добавить
            </a>
        </div>

        {{-- Import modal --}}
        <div
            x-show="open"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
            role="dialog"
            aria-modal="true"
            aria-labelledby="news-import-title"
        >
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl border border-stone-200 shadow-xl p-6">
                <div class="flex items-center justify-between gap-4 mb-5">
                    <h2 id="news-import-title" class="text-lg font-semibold text-stone-900 flex items-center gap-2">
                        @svg('heroicon-o-arrow-down-tray', 'w-5 h-5 text-sky-600 shrink-0')
                        Импорт новости с gamemag.ru
                    </h2>
                    <button
                        type="button"
                        @click="open = false"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors"
                        aria-label="Закрыть"
                    >
                        @svg('heroicon-o-x-mark', 'w-5 h-5')
                    </button>
                </div>

                <form @submit.prevent="submit()">
                    <label for="import-url" class="block text-sm font-medium text-stone-700 mb-1.5">
                        Ссылка на статью
                    </label>
                    <input
                        id="import-url"
                        type="url"
                        x-model="url"
                        placeholder="https://gamemag.ru/reviews/..."
                        required
                        :disabled="loading"
                        class="w-full px-3 py-2.5 border border-stone-300 rounded-lg text-sm text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors"
                        :class="error ? 'border-rose-400 focus:border-rose-500 focus:ring-rose-500/30' : ''"
                    >
                    <p x-show="error" x-text="error" x-cloak class="mt-1.5 text-xs text-rose-600"></p>
                    <p class="mt-1.5 text-xs text-stone-400">Парсится заголовок, описание и текст из блока <code class="font-mono bg-stone-100 px-1 rounded">.content-text</code></p>

                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button
                            type="button"
                            @click="open = false"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-50 text-sm font-medium transition-colors"
                        >
                            @svg('heroicon-o-x-mark', 'w-4 h-4')
                            Отмена
                        </button>
                        <button
                            type="submit"
                            :disabled="loading || !url.trim()"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors"
                        >
                            <span x-show="!loading">@svg('heroicon-o-arrow-down-tray', 'w-4 h-4')</span>
                            <span x-show="loading" x-cloak class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin shrink-0"></span>
                            <span x-text="loading ? 'Загрузка...' : 'Импортировать'">Импортировать</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="mb-4">
        <x-admin.search-bar :action="route('admin.news.index')" placeholder="По названию или описанию..." :value="$search ?? ''" />
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Дата</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach($news as $n)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 text-sm">{{ $n->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $n->title }}</td>
                            <td class="px-4 py-3">{{ $n->published_at?->format(config('app.datetime_format')) ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.action-buttons :edit-href="route('admin.news.edit', $n)" :delete-action="route('admin.news.destroy', $n)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $news->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @foreach($news as $n)
            <div class="bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-medium">{{ $n->title }}</p>
                        <p class="text-sm text-stone-500">{{ $n->published_at?->format(config('app.datetime_format')) ?? '-' }}</p>
                    </div>
                    <div class="shrink-0">
                        <x-admin.action-buttons :edit-href="route('admin.news.edit', $n)" :delete-action="route('admin.news.destroy', $n)" />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="py-2">{{ $news->links() }}</div>
    </div>
@endsection

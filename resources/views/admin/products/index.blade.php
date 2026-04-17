@extends('layouts.admin')

@section('content')
    <div
        class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6"
        x-data="{
            open: false,
            loading: false,
            fileName: '',
            error: '',
            importUrl: {{ \Illuminate\Support\Js::from(route('admin.products.import-xlsx')) }},
            csrf: {{ \Illuminate\Support\Js::from(csrf_token()) }},
            resetFile() {
                this.fileName = '';
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
            },
            openModal() {
                this.open = true;
                this.error = '';
                this.loading = false;
                this.$nextTick(() => this.resetFile());
            },
            async submitImport() {
                const input = this.$refs.fileInput;
                if (!input?.files?.length || this.loading) return;
                this.loading = true;
                this.error = '';
                const fd = new FormData();
                fd.append('xlsx_file', input.files[0]);
                fd.append('_token', this.csrf);
                try {
                    const res = await fetch(this.importUrl, {
                        method: 'POST',
                        body: fd,
                        credentials: 'same-origin',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                    });
                    const ct = (res.headers.get('Content-Type') || '').toLowerCase();
                    if (ct.includes('application/json')) {
                        const data = await res.json().catch(() => ({}));
                        if (res.ok && data.redirect) {
                            window.location.href = data.redirect;
                            return;
                        }
                        this.error = data.errors?.xlsx_file?.[0]
                            || data.message
                            || (data.errors ? Object.values(data.errors).flat().join(' ') : '')
                            || `Ошибка ${res.status}`;
                        this.loading = false;
                        return;
                    }
                    if (res.status === 302 || res.status === 303) {
                        const loc = res.headers.get('Location');
                        if (loc) window.location.href = loc;
                        return;
                    }
                    if (res.ok) {
                        window.location.reload();
                        return;
                    }
                    const raw = await res.text();
                    const plain = raw.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
                    this.error = plain
                        ? `Ошибка ${res.status}: ${plain.slice(0, 500)}${plain.length > 500 ? '…' : ''}`
                        : `Ошибка HTTP ${res.status}`;
                    this.loading = false;
                } catch (e) {
                    this.error = e?.message || 'Ошибка соединения. Попробуйте ещё раз.';
                    this.loading = false;
                }
            }
        }"
        @keydown.escape.window="open = false"
    >
        <h1 class="text-2xl font-semibold flex items-center gap-2 text-stone-900">
            @svg('heroicon-o-cube', 'w-8 h-8 text-sky-600')
            Товары
        </h1>
        <div class="flex items-center gap-2 shrink-0">
            <button
                type="button"
                @click="openModal()"
                class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 bg-white text-stone-700 rounded-lg hover:bg-stone-50 hover:border-sky-400 hover:text-sky-700 text-sm font-medium transition-colors"
            >
                @svg('heroicon-o-arrow-down-tray', 'w-5 h-5')
                Импорт
            </button>
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 text-sm font-medium transition-colors">
                @svg('heroicon-o-plus', 'w-5 h-5')
                Добавить
            </a>
        </div>

        {{-- Import XLSX modal --}}
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
            aria-labelledby="products-import-title"
        >
            <div class="absolute inset-0" @click="open = false" aria-hidden="true"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl border border-stone-200 shadow-xl p-6" @click.stop>
                <div class="flex items-center justify-between gap-4 mb-5">
                    <h2 id="products-import-title" class="text-lg font-semibold text-stone-900 flex items-center gap-2">
                        @svg('heroicon-o-arrow-up-tray', 'w-5 h-5 text-sky-600 shrink-0')
                        Импорт товаров из XLSX
                    </h2>
                    <button
                        type="button"
                        @click="open = false"
                        :disabled="loading"
                        class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors disabled:opacity-50"
                        aria-label="Закрыть"
                    >
                        @svg('heroicon-o-x-mark', 'w-5 h-5')
                    </button>
                </div>

                <form @submit.prevent="submitImport()">
                    <label class="block text-sm font-medium text-stone-700 mb-1.5 flex items-center gap-2">
                        @svg('heroicon-o-document', 'w-4 h-4 text-sky-500 shrink-0')
                        Файл выгрузки Avito (.xlsx)
                    </label>
                    <div
                        class="flex items-center h-11 border border-stone-300 rounded-lg bg-white overflow-hidden cursor-pointer hover:border-sky-300 transition-colors focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-400"
                        role="button"
                        tabindex="0"
                        @click="$refs.fileInput.click()"
                        @keydown.enter.prevent="$refs.fileInput.click()"
                        @keydown.space.prevent="$refs.fileInput.click()"
                    >
                        <input
                            x-ref="fileInput"
                            type="file"
                            name="xlsx_file"
                            accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            :disabled="loading"
                            class="sr-only [outline:none] [&:focus]:[outline:none]"
                            @change="fileName = $refs.fileInput.files?.length ? $refs.fileInput.files[0].name : ''"
                        >
                        <span class="flex items-center justify-center h-full px-4 text-sm font-medium text-sky-700 bg-sky-50 shrink-0 hover:bg-sky-100 transition-colors pointer-events-none">
                            Выбрать файл
                        </span>
                        <span class="flex-1 flex items-center px-3 text-sm text-stone-500 min-w-0 truncate pointer-events-none" x-text="fileName || 'Файл не выбран'"></span>
                    </div>
                    <p x-show="error" x-text="error" x-cloak class="mt-1.5 text-xs text-rose-600"></p>
                    <p class="mt-1.5 text-xs text-stone-400">Выгрузка из личного кабинета Avito (автозагрузка). После импорта подтянутся цены, описания, фото и видео по ссылкам из файла.</p>

                    <div class="flex items-center justify-end gap-3 mt-5">
                        <button
                            type="button"
                            @click="open = false"
                            :disabled="loading"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-stone-300 rounded-lg text-stone-700 hover:bg-stone-50 text-sm font-medium transition-colors disabled:opacity-50"
                        >
                            @svg('heroicon-o-x-mark', 'w-4 h-4')
                            Отмена
                        </button>
                        <button
                            type="submit"
                            :disabled="loading || !fileName"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium transition-colors"
                        >
                            <span x-show="!loading">@svg('heroicon-o-arrow-up-tray', 'w-4 h-4')</span>
                            <span x-show="loading" x-cloak class="inline-block w-4 h-4 border-2 border-white/40 border-t-white rounded-full animate-spin shrink-0"></span>
                            <span x-text="loading ? 'Импорт…' : 'Импортировать'">Импортировать</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @error('xlsx_file')
        <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            {{ $message }}
        </div>
    @enderror
    <div class="mb-4">
        <x-admin.search-bar :action="route('admin.products.index')" placeholder="По названию, ярлыку или описанию..." :value="$search ?? ''" />
    </div>
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Название</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Цена</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Категория</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">Кол-во</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-stone-500 uppercase">В наличии</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-200">
                    @foreach($products as $p)
                        <tr class="hover:bg-stone-50/50">
                            <td class="px-4 py-3 text-sm">{{ $p->id }}</td>
                            <td class="px-4 py-3 font-medium">{{ $p->title }}</td>
                            <td class="px-4 py-3">{{ number_format($p->price, 0, ',', ' ') }} ₽</td>
                            <td class="px-4 py-3">{{ $p->category?->name }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ $p->stock ?? 0 }}</td>
                            <td class="px-4 py-3">{{ $p->in_stock ? 'Да' : 'Нет' }}</td>
                            <td class="px-4 py-3">
                                <x-admin.action-buttons :edit-href="route('admin.products.edit', $p)" :delete-action="route('admin.products.destroy', $p)" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-stone-200">{{ $products->links() }}</div>
    </div>
    <div class="lg:hidden space-y-3">
        @foreach($products as $p)
            <div class="bg-white rounded-lg border border-stone-200 p-4 shadow-sm">
                <div class="flex justify-between items-start gap-3">
                    <div class="min-w-0">
                        <p class="font-medium truncate">{{ $p->title }}</p>
                        <p class="text-sm text-stone-500 mt-0.5">{{ number_format($p->price, 0, ',', ' ') }} ₽ · {{ $p->category?->name ?? '-' }} · Кол-во: {{ $p->stock ?? 0 }}</p>
                        <p class="text-sm mt-1">{{ $p->in_stock ? 'В наличии' : 'Нет в наличии' }}</p>
                    </div>
                    <div class="shrink-0">
                        <x-admin.action-buttons :edit-href="route('admin.products.edit', $p)" :delete-action="route('admin.products.destroy', $p)" />
                    </div>
                </div>
            </div>
        @endforeach
        <div class="py-2">{{ $products->links() }}</div>
    </div>
@endsection

@props([
    'defaultType' => \App\Enums\SupportTicketTypeEnum::SERVICE_INQUIRY->value,
])
<div
    x-show="supportTicketModalOpen"
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
    aria-labelledby="support-ticket-modal-title"
    @keydown.escape.window="closeSupportTicketModal()"
>
    <div
        class="absolute inset-0"
        @click="closeSupportTicketModal()"
        aria-hidden="true"
    ></div>
    <div
        class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto bg-white rounded-2xl border border-stone-200 shadow-xl p-6 sm:p-8"
        @click.stop
    >
        <div class="flex items-start justify-between gap-4 mb-6">
            <h2 id="support-ticket-modal-title" class="text-lg font-semibold text-stone-900 flex items-center gap-2">
                @svg('heroicon-o-lifebuoy', 'w-6 h-6 text-sky-600 shrink-0')
                Обращение в поддержку
            </h2>
            <button
                type="button"
                class="p-1.5 rounded-lg text-stone-400 hover:text-stone-700 hover:bg-stone-100 transition-colors"
                @click="closeSupportTicketModal()"
                aria-label="Закрыть"
            >
                @svg('heroicon-o-x-mark', 'w-5 h-5')
            </button>
        </div>

        <form
            method="POST"
            action="{{ route('support-tickets.store') }}"
            enctype="multipart/form-data"
            class="space-y-4"
        >
            @csrf
            <input type="hidden" name="type" value="{{ $defaultType }}">
            <input type="hidden" name="service_id" x-bind:value="supportTicketServiceId ?? ''">

            <div>
                <label for="support-modal-title" class="block text-sm font-medium text-stone-700 mb-1.5">Тема</label>
                <input
                    type="text"
                    id="support-modal-title"
                    name="title"
                    required
                    maxlength="255"
                    x-model="supportTicketModalTitle"
                    class="w-full px-3 py-2.5 border border-stone-300 rounded-lg text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500"
                />
            </div>

            <div>
                <label for="support-modal-desc" class="block text-sm font-medium text-stone-700 mb-1.5">Сообщение</label>
                <textarea
                    id="support-modal-desc"
                    name="description"
                    required
                    rows="4"
                    maxlength="3000"
                    class="w-full px-3 py-2.5 border border-stone-300 rounded-lg text-stone-900 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 resize-y"
                    placeholder="Опишите ваш вопрос..."
                ></textarea>
            </div>

            <div>
                <x-ui.file-input
                    name="images[]"
                    accept="image/*"
                    multiple
                    :max-previews="3"
                    label="Фото (до 3 шт)"
                    label-icon="heroicon-o-photo"
                />
            </div>

            <div class="flex flex-wrap gap-3 justify-end pt-2">
                <button
                    type="button"
                    class="inline-flex items-center justify-center px-4 py-2.5 border border-stone-300 rounded-xl text-stone-700 hover:bg-stone-50 text-sm font-medium"
                    @click="closeSupportTicketModal()"
                >
                    Отмена
                </button>
                <x-ui.button type="submit" variant="primary">
                    @svg('heroicon-o-paper-airplane', 'w-4 h-4')
                    Отправить
                </x-ui.button>
            </div>
        </form>
    </div>
</div>

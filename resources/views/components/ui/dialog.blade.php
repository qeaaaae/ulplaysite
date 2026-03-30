{{-- Кастомный диалог (alert / confirm) в стиле приложения. Управляется через Alpine state в #app. --}}
<div x-show="dialogOpen" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @click.self="closeDialog()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ulplay-dialog-title">
    <div x-show="dialogOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-sm bg-white rounded-2xl border border-stone-200 shadow-xl p-6"
         @click.stop>
        <h2 id="ulplay-dialog-title" class="text-lg font-semibold text-stone-900 mb-2" x-text="dialogTitle"></h2>
        <p class="text-stone-600 text-sm leading-relaxed mb-6" x-text="dialogMessage"></p>
        <div class="flex flex-col-reverse sm:flex-row gap-2 sm:justify-end">
            <template x-if="dialogShowCancel">
                <button type="button" @click="cancelDialog()"
                        class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-stone-300 text-stone-700 hover:bg-stone-50 rounded-md cursor-pointer transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-300">
                    @svg('heroicon-o-x-mark', 'w-4 h-4')
                    Отмена
                </button>
            </template>
            <button type="button" @click="confirmDialog()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-sky-600 text-white hover:bg-sky-700 rounded-md cursor-pointer transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                @svg('heroicon-o-check', 'w-4 h-4')
                OK
            </button>
        </div>
    </div>
</div>

{{-- Prompt-диалог (ввод текста). --}}
<div x-show="promptOpen" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm"
     @click.self="cancelPrompt()"
     @keydown.escape.window="promptOpen && cancelPrompt()"
     role="dialog"
     aria-modal="true"
     aria-labelledby="ulplay-prompt-title">
    <div x-show="promptOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="relative w-full max-w-md bg-white rounded-2xl border border-stone-200 shadow-xl p-6"
         @click.stop>
        <h2 id="ulplay-prompt-title" class="text-lg font-semibold text-stone-900 mb-4" x-text="promptTitle"></h2>
        <input type="text"
               x-model="promptValue"
               :placeholder="promptPlaceholder"
               x-ref="promptInput"
               @keydown.enter="confirmPrompt()"
               class="w-full px-3 py-2.5 bg-white border border-stone-300 rounded-md text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition-colors duration-150 text-sm mb-5">
        <div class="flex gap-2 justify-end">
            <button type="button" @click="cancelPrompt()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium border border-stone-300 text-stone-700 hover:bg-stone-50 rounded-md cursor-pointer transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-stone-300">
                @svg('heroicon-o-x-mark', 'w-4 h-4')
                Отмена
            </button>
            <button type="button" @click="confirmPrompt()"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-sky-600 text-white hover:bg-sky-700 rounded-md cursor-pointer transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500">
                @svg('heroicon-o-check', 'w-4 h-4')
                OK
            </button>
        </div>
    </div>
</div>

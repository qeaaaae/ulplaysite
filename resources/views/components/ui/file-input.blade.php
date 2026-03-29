@props([
    'label' => null,
    'labelIcon' => null,
    'error' => null,
    'hint' => null,
    'existingUrl' => null,
    'maxPreviews' => null,
    'lightboxGroup' => null,
])

@php
    $inputId = $attributes->get('id') ?? 'file-' . bin2hex(random_bytes(4));
    $isMultiple = $attributes->get('multiple', false);
    $accept = $attributes->get('accept', '');
    $showPreview = str_contains($accept, 'image');
    $max = $maxPreviews ?? ($isMultiple ? 5 : 1);
    $parentChange = $attributes->get('x-on:change');
    $changeHandler = $parentChange ? "handleChange(\$event); {$parentChange}(\$event)" : 'handleChange($event)';
@endphp

<div
    {{ $attributes->except('name', 'accept', 'multiple', 'id', 'x-on:change')->merge(['class' => $attributes->get('class', '')]) }}
    x-data="{
        filename: '',
        newPreviews: [],
        existingUrl: {{ \Illuminate\Support\Js::from($existingUrl) }},
        maxPreviews: {{ $max }},
        handleChange(event) {
            const files = Array.from(event.target.files || []);
            this.filename = files.length ? files.map(f => f.name).join(', ') : '';
            this.newPreviews = files.slice(0, this.maxPreviews).map(f => ({ url: URL.createObjectURL(f) }));
            $dispatch('file-change', event);
        }
    }"
>
    @if($label)
        <label for="{{ $inputId }}" class="{{ $labelIcon ? 'flex items-center gap-2' : 'block' }} text-sm font-medium text-stone-700 mb-1.5">
            @if($labelIcon)
                @svg($labelIcon, 'w-4 h-4 text-sky-500 shrink-0')
            @endif
            {{ $label }}
        </label>
    @endif
    <label for="{{ $inputId }}" class="flex items-center h-11 border border-stone-300 rounded-md bg-white overflow-hidden cursor-pointer hover:border-sky-300 transition-colors focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-400 [outline:none] [&:hover]:[outline:none] [&:focus]:[outline:none]">
        <input
            type="file"
            id="{{ $inputId }}"
            {{ $attributes->except('class', 'id', 'x-on:change')->merge([
                'class' => 'sr-only [outline:none] [&:focus]:[outline:none] [&:hover]:[outline:none]',
            ]) }}
            x-on:change="{{ $changeHandler }}"
        >
        <span class="flex items-center justify-center h-full px-4 text-sm font-medium text-sky-700 bg-sky-50 shrink-0 hover:bg-sky-100 transition-colors">
            Выбрать файл{{ $isMultiple ? 'ы' : '' }}
        </span>
        <span class="flex-1 flex items-center px-3 text-sm text-stone-500 min-w-0 truncate" x-text="filename || 'Файл не выбран'"></span>
    </label>

    @if($showPreview)
        <div class="mt-3 space-y-3">
            <template x-if="existingUrl && !newPreviews.length">
                <div>
                    <p class="text-xs text-stone-500 mb-2">Текущее изображение:</p>
                    <a
                        :href="existingUrl"
                        @if($lightboxGroup)
                            data-lightbox="image"
                            data-lightbox-group="{{ $lightboxGroup }}"
                            class="inline-block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50 cursor-zoom-in hover:border-sky-300 transition-colors"
                        @else
                            target="_blank"
                            rel="noopener"
                            class="inline-block w-28 h-28 rounded-lg overflow-hidden border border-stone-200 bg-stone-50"
                        @endif
                    >
                        <img :src="existingUrl" alt="" class="w-full h-full object-cover">
                    </a>
                </div>
            </template>
            <template x-if="newPreviews.length">
                <div>
                    <p class="text-xs text-stone-500 mb-2" x-text="existingUrl ? 'Новое изображение (предпросмотр):' : 'Предпросмотр:'"></p>
                    <div class="flex flex-wrap gap-3">
                        <template x-for="(img, idx) in newPreviews" :key="idx">
                            @if($lightboxGroup)
                                <a
                                    :href="img.url"
                                    data-lightbox="image"
                                    data-lightbox-group="{{ $lightboxGroup }}"
                                    class="block w-28 h-28 rounded-lg overflow-hidden border border-dashed border-sky-300 bg-stone-50 cursor-zoom-in hover:border-sky-400 transition-colors"
                                >
                                    <img :src="img.url" alt="" class="w-full h-full object-cover">
                                </a>
                            @else
                                <div class="w-28 h-28 rounded-lg overflow-hidden border border-dashed border-sky-300 bg-stone-50">
                                    <img :src="img.url" alt="" class="w-full h-full object-cover">
                                </div>
                            @endif
                        </template>
                    </div>
                </div>
            </template>
        </div>
    @endif

    @if($error)
        <p class="mt-1.5 text-sm text-rose-600">{{ $error }}</p>
    @endif
    @if($hint && !$error)
        <p class="mt-1.5 text-sm text-stone-500">{{ $hint }}</p>
    @endif
</div>

@props([
    'action' => '',
    'name' => 'q',
    'value' => '',
    'placeholder' => 'Поиск...',
    'formClass' => '',
    'hiddens' => [],
    'size' => 'default',
])
@php
    $inputSizeClass = $size === 'mobile' ? 'px-3 py-3 sm:py-3.5 text-base sm:text-sm' : 'px-3 py-2 text-sm';
    $buttonSizeClass = $size === 'mobile' ? 'px-3 py-2.5' : 'px-3 py-2';
@endphp
<form method="GET" action="{{ $action }}" class="w-full flex rounded-md border border-stone-300 overflow-hidden focus-within:ring-2 focus-within:ring-sky-500/30 focus-within:border-sky-500 {{ $formClass }}">
    @foreach(array_filter($hiddens) as $hiddenName => $hiddenVal)
        <input type="hidden" name="{{ $hiddenName }}" value="{{ $hiddenVal }}">
    @endforeach
    <input type="search" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}" autocomplete="off"
           class="flex-1 min-w-0 {{ $inputSizeClass }} text-stone-900 placeholder-stone-400 focus:outline-none border-0" @if($size === 'mobile') style="font-size: 16px;" @endif>
    <button type="submit" class="{{ $buttonSizeClass }} text-stone-500 hover:text-sky-600 hover:bg-sky-50/80 transition-colors cursor-pointer shrink-0 {{ $size === 'mobile' ? 'bg-stone-50' : '' }}" aria-label="Найти">
        @svg('heroicon-o-magnifying-glass', 'w-5 h-5')
    </button>
</form>

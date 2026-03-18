@props([
    'items' => [], // [['label' => '...', 'url' => null|'...'], ...]
    'class' => '',
])

<nav {{ $attributes->merge(['class' => 'flex flex-wrap md:flex-nowrap items-center gap-x-2 gap-y-1 text-sm text-stone-500 mb-6 ' . $class]) }}>
    @foreach($items as $index => $item)
        @if($index > 0)
            <span class="text-stone-400 shrink-0">/</span>
        @endif
        @if(!empty($item['url']))
            <a href="{{ $item['url'] }}" class="hover:text-sky-600 transition-colors shrink-0">{{ $item['label'] }}</a>
        @else
            <span class="text-stone-800 font-medium min-w-0 truncate">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>

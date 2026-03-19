@props([
    'title' => '',
    'icon' => 'heroicon-o-document-text',
])

<section {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden']) }}>
    @if($title)
        <div class="flex items-center gap-2.5 px-4 py-3 border-b border-stone-100 bg-stone-50">
            @svg($icon, 'w-5 h-5 text-sky-600 shrink-0')
            <h2 class="font-medium text-stone-800">{{ $title }}</h2>
        </div>
    @endif
    <div class="p-4 sm:p-5 grid grid-cols-1 lg:grid-cols-2 gap-x-6 gap-y-4">
        {{ $slot }}
    </div>
</section>

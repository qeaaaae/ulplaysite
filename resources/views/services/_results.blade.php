@props([
    'services',
])

@if($services->isEmpty())
    <p class="text-stone-500 py-12 text-center">Услуги пока не добавлены</p>
@else
    <div id="services-grid" class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        @foreach($services as $service)
            @include('components.service-card', ['service' => $service])
        @endforeach
    </div>
    @if($services->hasMorePages())
        <div
            id="services-infinite-sentinel"
            data-next-url="{{ $services->nextPageUrl() }}"
            class="h-1 w-full shrink-0 pointer-events-none"
            aria-hidden="true"
        ></div>
    @endif
@endif

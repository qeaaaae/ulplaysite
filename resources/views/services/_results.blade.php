@props([
    'services',
])

@if($services->isEmpty())
    <p class="text-stone-500 py-12 text-center">Услуги пока не добавлены</p>
@else
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
        @foreach($services as $service)
            @include('components.service-card', ['service' => $service])
        @endforeach
    </div>

    <div class="mt-8" data-services-pagination>
        {{ $services->links() }}
    </div>
@endif


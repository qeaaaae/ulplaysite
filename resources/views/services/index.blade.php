@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading tag="h1" icon="heroicon-o-wrench-screwdriver" class="mb-8">Наши услуги</x-ui.section-heading>
            @if($services->isEmpty())
                <p class="text-stone-500 py-12 text-center">Услуги пока не добавлены</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                    @foreach($services as $service)
                        @include('components.service-card', ['service' => $service])
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $services->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

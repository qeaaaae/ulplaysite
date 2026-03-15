@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <h1 class="section-heading text-2xl mb-8 flex items-center gap-2.5">
                <span class="text-sky-600">@svg('heroicon-o-wrench-screwdriver', 'w-6 h-6 shrink-0')</span>
                Наши услуги
            </h1>
            @if($services->isEmpty())
                <p class="text-stone-500 py-12 text-center">Услуги пока не добавлены</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6">
                    @foreach($services as $service)
                        @include('components.service-card', ['service' => $service])
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection

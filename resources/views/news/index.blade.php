@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8">
            <x-ui.section-heading tag="h1" icon="heroicon-o-newspaper" class="mb-8">Новости</x-ui.section-heading>
            @if($news->isEmpty())
                <p class="text-stone-500 py-12 text-center">Новости пока не добавлены</p>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 sm:gap-5 md:gap-6">
                    @foreach($news as $item)
                        @include('components.news-card', ['item' => $item])
                    @endforeach
                </div>
                <div class="mt-8">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

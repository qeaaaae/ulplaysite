@extends('layouts.app')

@section('content')
    <div class="py-12 max-w-3xl mx-auto px-4">
        <h1 class="text-2xl font-semibold text-stone-900 mb-6">Контакты</h1>
        @php $company = config('site.footer.company', []); @endphp
        <div class="prose prose-stone max-w-none space-y-4">
            <p>
                <strong>Адрес офиса:</strong><br>
                {{ $company['address'] ?? '' }}
            </p>
            <p><strong>Телефон:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company['phone'] ?? '') }}">{{ $company['phone'] ?? '' }}</a></p>
            <p><strong>Email:</strong> <a href="mailto:{{ $company['email'] ?? '' }}">{{ $company['email'] ?? '' }}</a></p>
            @if(!empty($company['schedule_full']))
                <p><strong>График работы:</strong> {{ $company['schedule_full'] }}</p>
            @endif
            @if(!empty($company['visit_notice']))
                <p class="text-stone-600 text-sm border-l-2 border-sky-200 pl-4">{{ $company['visit_notice'] }}</p>
            @endif
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="py-8 md:py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 md:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
                <h1 class="text-2xl font-semibold text-stone-900">Уведомления</h1>
                <a href="{{ route('tickets.my.index') }}" class="text-sm text-sky-600 hover:underline">Мои обращения</a>
            </div>

            @if($notifications->isEmpty())
                <p class="text-stone-500 py-12">У вас пока нет уведомлений</p>
            @else
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        <a
                            href="{{ $notification->url ?? route('tickets.my.index') }}"
                            class="block p-5 bg-white rounded-xl border border-stone-200 hover:border-sky-200 transition-colors"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="font-medium text-stone-900">{{ $notification->title ?? 'Уведомление' }}</p>
                                    @if(!empty($notification->body))
                                        <p class="text-sm text-stone-600 mt-1 whitespace-pre-wrap line-clamp-3">{{ $notification->body }}</p>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-stone-500">{{ $notification->created_at->format(config('app.datetime_format')) }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection


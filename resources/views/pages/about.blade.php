@extends('layouts.app')

@section('content')
    @php
        $company = config('site.footer.company', []);
        $address = (string) ($company['address'] ?? '');
        $phone = (string) ($company['phone'] ?? '');
        $email = (string) ($company['email'] ?? '');
        $schedule = (string) ($company['schedule_full'] ?? ($company['schedule'] ?? ''));
        $visitNotice = (string) ($company['visit_notice'] ?? '');
        $mapQuery = trim($address !== '' ? $address : 'Ульяновск');
    @endphp

    <div class="py-4 pb-10 sm:pb-12">
        <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8 flex flex-col gap-6">
            <x-ui.section-heading tag="h1" icon="heroicon-o-information-circle" class="mb-0">О нас</x-ui.section-heading>

            <div class="lg:hidden">
                <div class="w-full bg-white rounded-xl border border-stone-200 shadow-sm p-4 sm:p-5">
                    <h2 class="text-base font-semibold text-stone-900 mb-2">UlPlay</h2>
                    <div class="space-y-2 text-sm text-stone-700">
                        @if($address !== '')
                            <p class="flex items-start gap-2">
                                <span class="text-sky-600 shrink-0 mt-0.5">@svg('heroicon-o-map-pin', 'w-4 h-4')</span>
                                <span>{{ $address }}</span>
                            </p>
                        @endif
                        @if($phone !== '')
                            <p class="flex items-center gap-2">
                                <span class="text-sky-600 shrink-0">@svg('heroicon-o-phone', 'w-4 h-4')</span>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-sky-600 transition-colors">{{ $phone }}</a>
                            </p>
                        @endif
                        @if($email !== '')
                            <p class="flex items-center gap-2">
                                <span class="text-sky-600 shrink-0">@svg('heroicon-o-envelope', 'w-4 h-4')</span>
                                <a href="mailto:{{ $email }}" class="hover:text-sky-600 transition-colors break-all">{{ $email }}</a>
                            </p>
                        @endif
                        @if($schedule !== '')
                            <p class="flex items-start gap-2">
                                <span class="text-sky-600 shrink-0 mt-0.5">@svg('heroicon-o-clock', 'w-4 h-4')</span>
                                <span>{{ $schedule }}</span>
                            </p>
                        @endif
                    </div>
                    @if($visitNotice !== '')
                        <p class="mt-3 text-xs text-stone-500 border-l-2 border-sky-200 pl-2.5">{{ $visitNotice }}</p>
                    @endif
                </div>
            </div>

            <div class="relative w-full min-w-0">
                <div class="relative h-[380px] sm:h-[500px] lg:h-[560px] bg-stone-100 rounded-xl sm:rounded-2xl border border-stone-200 overflow-hidden">
                    <iframe
                        src="https://yandex.ru/map-widget/v1/?um=constructor%3A353cb28e73e2db1d0b980214192bf3380ee84ef6ddb0a276b859f1c73fb5a255&amp;source=constructor"
                        class="w-full h-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        title="Карта UlPlay"
                        aria-label="Карта расположения UlPlay"
                    ></iframe>

                    <div class="hidden lg:flex pointer-events-none absolute inset-x-3 top-3 sm:inset-x-6 sm:top-6 md:inset-x-8 md:top-8 lg:justify-start">
                        <div class="pointer-events-auto w-full max-w-[320px] sm:max-w-sm lg:max-w-md bg-white/95 backdrop-blur rounded-xl sm:rounded-2xl border border-stone-200 shadow-lg sm:shadow-xl p-3.5 sm:p-6">
                        <h2 class="text-base sm:text-lg font-semibold text-stone-900 mb-2 sm:mb-3">UlPlay</h2>
                        <div class="space-y-2 sm:space-y-2.5 text-xs sm:text-sm text-stone-700">
                            @if($address !== '')
                                <p class="flex items-start gap-2">
                                    <span class="text-sky-600 shrink-0 mt-0.5">@svg('heroicon-o-map-pin', 'w-4 h-4')</span>
                                    <span>{{ $address }}</span>
                                </p>
                            @endif
                            @if($phone !== '')
                                <p class="flex items-center gap-2">
                                    <span class="text-sky-600 shrink-0">@svg('heroicon-o-phone', 'w-4 h-4')</span>
                                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="hover:text-sky-600 transition-colors">{{ $phone }}</a>
                                </p>
                            @endif
                            @if($email !== '')
                                <p class="flex items-center gap-2">
                                    <span class="text-sky-600 shrink-0">@svg('heroicon-o-envelope', 'w-4 h-4')</span>
                                    <a href="mailto:{{ $email }}" class="hover:text-sky-600 transition-colors break-all">{{ $email }}</a>
                                </p>
                            @endif
                            @if($schedule !== '')
                                <p class="flex items-start gap-2">
                                    <span class="text-sky-600 shrink-0 mt-0.5">@svg('heroicon-o-clock', 'w-4 h-4')</span>
                                    <span>{{ $schedule }}</span>
                                </p>
                            @endif
                        </div>

                        @if($visitNotice !== '')
                            <p class="mt-3 sm:mt-4 text-[11px] sm:text-xs text-stone-500 border-l-2 border-sky-200 pl-2.5 sm:pl-3 line-clamp-3 sm:line-clamp-none">{{ $visitNotice }}</p>
                        @endif
                    </div>
                </div>
                </div>
            </div>

            <section class="min-w-0" aria-label="Отзывы">
                <div class="rounded-2xl border border-stone-200 bg-white shadow-sm w-full min-w-0 overflow-hidden p-4 sm:p-6 md:p-8">
                    <div class="sw-app about-sw-widget w-full min-w-0 max-w-none" data-app="1295878e202fb9acbc3326290253664e"></div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://res.smartwidgets.ru/app.js" async></script>
@endpush

@php
    $footer = $footerData ?? [];
    $company = $footer['company'] ?? ['name' => 'UlPlay', 'description' => '', 'phone' => '', 'email' => ''];
    $categories = $footer['categories'] ?? [];
    $services = $footer['services'] ?? [];
    $links = $footer['links'] ?? [];
    $social = $footer['social'] ?? [];
@endphp
<footer class="site-footer mt-16 bg-stone-800 overflow-x-hidden shadow-[0_-1px_0_0_rgba(68,64,60,0.5)] [.page-checkout_&]:mt-0">
    <div class="max-w-[1420px] mx-auto px-4 sm:px-6 md:px-8 py-10 sm:py-12 min-w-0">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-y-6 gap-x-5 sm:gap-y-6 sm:gap-x-6 overflow-hidden">
            <div class="min-w-0">
                <span class="font-heading text-lg font-semibold text-white block mb-3">{{ $company['name'] }}</span>
                <p class="text-stone-400 text-sm mb-3 leading-relaxed">{{ $company['description'] }}</p>
                <div class="flex flex-col">
                    @if(!empty($company['phone']))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company['phone']) }}" class="inline-flex items-center gap-2 text-stone-300 hover:text-sky-400 text-sm mb-1 transition-colors duration-150">
                            <span class="text-sky-500 shrink-0">@svg('heroicon-o-phone', 'w-4 h-4')</span>
                            <span class="link-underline">{{ $company['phone'] }}</span>
                        </a>
                    @endif
                    @if(!empty($company['email']))
                        <a href="mailto:{{ $company['email'] }}" class="inline-flex items-center gap-2 text-stone-300 hover:text-sky-400 text-sm transition-colors duration-150">
                            <span class="text-sky-500 shrink-0">@svg('heroicon-o-envelope', 'w-4 h-4')</span>
                            <span class="link-underline">{{ $company['email'] }}</span>
                        </a>
                    @endif
                </div>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="text-sky-500">@svg('heroicon-o-folder', 'w-4 h-4')</span>
                    Каталог
                </h3>
                <ul class="list-none pl-0 space-y-2">
                    @foreach($categories as $cat)
                        <li><a href="{{ $cat['url'] }}" class="text-stone-400 hover:text-sky-400 text-sm transition-colors duration-150 link-underline inline-block">{{ $cat['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="text-sky-500">@svg('heroicon-o-wrench-screwdriver', 'w-4 h-4')</span>
                    Услуги
                </h3>
                <ul class="list-none pl-0 space-y-2">
                    @foreach($services as $srv)
                        <li><a href="{{ $srv['url'] }}" class="text-stone-400 hover:text-sky-400 text-sm transition-colors duration-150 link-underline inline-block">{{ $srv['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div class="min-w-0">
                <h3 class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3 flex items-center gap-2">
                    <span class="text-sky-500">@svg('heroicon-o-information-circle', 'w-4 h-4')</span>
                    Информация
                </h3>
                <ul class="list-none pl-0 space-y-2">
                    @foreach($links as $link)
                        <li><a href="{{ $link['url'] }}" class="text-stone-400 hover:text-sky-400 text-sm transition-colors duration-150 link-underline inline-block">{{ $link['name'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="mt-8 pt-6 flex flex-col sm:flex-row justify-between items-center gap-4 min-w-0">
            <p class="text-stone-500 text-sm">© {{ date('Y') }} {{ $company['name'] }}. Все права защищены.</p>
            <div class="flex gap-3">
                @foreach($social as $s)
                    <a href="{{ $s['url'] }}" target="_blank" rel="noopener noreferrer" class="text-stone-500 hover:text-sky-400 p-1.5 rounded-md transition-colors duration-150" aria-label="{{ $s['name'] }}">
                        @if($s['icon'] === 'vk')
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M15.684 0H8.316C1.592 0 0 1.592 0 8.316v7.368C0 22.408 1.592 24 8.316 24h7.368C22.408 24 24 22.408 24 15.684V8.316C24 1.592 22.408 0 15.684 0zm3.692 17.123h-1.744c-.66 0-.864-.525-2.05-1.727-1.033-1-1.49-1.135-1.744-1.135-.356 0-.458.102-.458.593v1.575c0 .424-.135.678-1.253.678-1.846 0-3.896-1.118-5.335-3.202C4.624 10.857 4.03 8.57 4.03 8.096c0-.254.102-.491.593-.491h1.744c.44 0 .61.203.78.678.863 2.49 2.303 4.675 2.896 4.675.22 0 .322-.102.322-.66V9.721c-.068-1.186-.695-1.287-.695-1.71 0-.203.17-.407.44-.407h2.744c.373 0 .508.203.508.643v3.473c0 .372.17.508.271.508.22 0 .407-.136.813-.542 1.254-1.406 2.151-3.574 2.151-3.574.119-.254.322-.491.763-.491h1.744c.525 0 .644.27.525.643-.22 1.017-2.354 4.031-2.354 4.031-.186.305-.254.44 0 .78.186.254.796.779 1.203 1.253.745.847 1.32 1.558 1.473 2.05.17.49-.085.744-.576.744z"/></svg>
                        @elseif($s['icon'] === 'avito')
                            <svg class="w-5 h-5" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor" fill-rule="evenodd" clip-rule="evenodd" aria-hidden="true"><path d="M7.357 4.092a3.108 3.108 0 11-6.216 0 3.108 3.108 0 016.216 0zm4.001 2.435a2.434 2.434 0 100-4.869 2.434 2.434 0 000 4.869zm3.783 4.675a3.782 3.782 0 11-7.565 0 3.782 3.782 0 017.565 0zm-9.132 0a1.76 1.76 0 11-3.52 0 1.76 1.76 0 013.52 0z"/></svg>
                        @else
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</footer>

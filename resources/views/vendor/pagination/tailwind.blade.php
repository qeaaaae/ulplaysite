@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}">

        @php
            $current = $paginator->currentPage();
            $last = $paginator->lastPage();
            $mobileItems = [];
            $window = 2;
            if ($last <= 7) {
                for ($i = 1; $i <= $last; $i++) {
                    $mobileItems[] = ['page' => $i, 'url' => $paginator->url($i)];
                }
            } else {
                $mobileItems[] = ['page' => 1, 'url' => $paginator->url(1)];
                if ($current - $window > 2) {
                    $mobileItems[] = ['ellipsis' => true];
                }
                $start = max(2, $current - $window);
                $end = min($last - 1, $current + $window);
                foreach (range($start, $end) as $p) {
                    $mobileItems[] = ['page' => $p, 'url' => $paginator->url($p)];
                }
                if ($current + $window < $last - 1) {
                    $mobileItems[] = ['ellipsis' => true];
                }
                $mobileItems[] = ['page' => $last, 'url' => $paginator->url($last)];
            }
        @endphp
        <div class="flex sm:hidden items-center justify-center overflow-x-auto py-2">
            <span class="inline-flex rtl:flex-row-reverse rounded-lg border border-stone-300 bg-white shadow-sm">
                @foreach ($mobileItems as $item)
                    @if (!empty($item['ellipsis']))
                        <span class="inline-flex items-center justify-center min-w-[2.75rem] w-11 h-11 text-sm font-medium text-stone-500 cursor-default rounded-none first:rounded-l-lg last:rounded-r-lg">…</span>
                    @elseif ($item['page'] == $current)
                        <span aria-current="page" class="inline-flex items-center justify-center min-w-[2.75rem] w-11 h-11 text-sm font-medium text-white bg-sky-600 border-0 cursor-default rounded-none first:rounded-l-lg last:rounded-r-lg">{{ $item['page'] }}</span>
                    @else
                        <a href="{{ $item['url'] }}" class="inline-flex items-center justify-center min-w-[2.75rem] w-11 h-11 text-sm font-medium text-sky-600 hover:bg-sky-50 active:bg-sky-100 transition rounded-none first:rounded-l-lg last:rounded-r-lg" aria-label="{{ __('Go to page :page', ['page' => $item['page']]) }}">{{ $item['page'] }}</a>
                    @endif
                @endforeach
            </span>
        </div>

        @php
            $desktopWindow = 2;
            $desktopItems = [];
            if ($last <= 7) {
                for ($i = 1; $i <= $last; $i++) {
                    $desktopItems[] = ['page' => $i, 'url' => $paginator->url($i)];
                }
            } else {
                $desktopItems[] = ['page' => 1, 'url' => $paginator->url(1)];
                if ($current - $desktopWindow > 2) {
                    $desktopItems[] = ['ellipsis' => true];
                }
                $start = max(2, $current - $desktopWindow);
                $end = min($last - 1, $current + $desktopWindow);
                foreach (range($start, $end) as $p) {
                    $desktopItems[] = ['page' => $p, 'url' => $paginator->url($p)];
                }
                if ($current + $desktopWindow < $last - 1) {
                    $desktopItems[] = ['ellipsis' => true];
                }
                $desktopItems[] = ['page' => $last, 'url' => $paginator->url($last)];
            }
        @endphp
        <div class="hidden sm:flex sm:flex-1 sm:gap-2 sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-stone-600 leading-5">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-medium text-stone-800">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-medium text-stone-800">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-medium text-stone-800">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <span class="inline-flex rtl:flex-row-reverse shadow-sm rounded-md">
                    @foreach ($desktopItems as $item)
                        @if (!empty($item['ellipsis']))
                            <span class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-stone-500 bg-white border border-stone-300 cursor-default">…</span>
                        @elseif ($item['page'] == $current)
                            <span aria-current="page" class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-sky-600 border border-sky-600 cursor-default first:rounded-l-md last:rounded-r-md">{{ $item['page'] }}</span>
                        @else
                            <a href="{{ $item['url'] }}" class="inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-sky-600 bg-white border border-stone-300 hover:bg-sky-50 hover:text-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 transition first:rounded-l-md last:rounded-r-md" aria-label="{{ __('Go to page :page', ['page' => $item['page']]) }}">
                                {{ $item['page'] }}
                            </a>
                        @endif
                    @endforeach
                </span>
            </div>
        </div>
    </nav>
@endif

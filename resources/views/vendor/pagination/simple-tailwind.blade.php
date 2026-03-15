@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex gap-2 items-center justify-between">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-stone-400 bg-white border border-stone-300 cursor-not-allowed rounded-md">
                {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-sky-600 bg-white border border-stone-300 rounded-md hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 transition">
                {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-sky-600 bg-white border border-stone-300 rounded-md hover:bg-sky-50 hover:text-sky-700 hover:border-sky-300 transition">
                {!! __('pagination.next') !!}
            </a>
        @else
            <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-stone-400 bg-white border border-stone-300 cursor-not-allowed rounded-md">
                {!! __('pagination.next') !!}
            </span>
        @endif
    </nav>
@endif

@if ($paginator->hasPages())
    @php
        $base = 'inline-flex h-9 min-w-9 items-center justify-center rounded-lg border px-3 text-sm font-medium transition';
        $idle = $base.' border-line bg-card text-muted hover:border-accent/40 hover:text-accent';
        $active = $base.' border-transparent bg-gradient-to-r from-accent to-accent2 text-white shadow-md shadow-accent/25';
        $disabled = $base.' cursor-not-allowed border-line bg-elevated text-muted/40';
    @endphp

    <nav role="navigation" aria-label="Pagination" class="flex items-center gap-1.5">

        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <span class="{{ $disabled }}" aria-disabled="true">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="{{ $idle }}" aria-label="Previous page">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
        @endif

        {{-- Page numbers, hidden on the narrowest screens where they would wrap --}}
        <span class="hidden items-center gap-1.5 sm:inline-flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="{{ $disabled }}">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $active }}" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $idle }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>

        {{-- Compact indicator for phones --}}
        <span class="inline-flex h-9 items-center rounded-lg border border-line bg-elevated px-3 text-sm font-medium text-muted sm:hidden">
            {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
        </span>

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="{{ $idle }}" aria-label="Next page">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        @else
            <span class="{{ $disabled }}" aria-disabled="true">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </span>
        @endif
    </nav>
@endif

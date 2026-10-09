{{--
    ProductSphere pagination (set as the default view in AppServiceProvider).
    Phones: large Previous / Next buttons around "Page X of Y".
    Larger screens: "Showing A–B of N" with numbered page pills.
--}}
@if ($paginator->hasPages())
    @php
        $arrowLeft = 'M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z';
        $arrowRight = 'M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z';
    @endphp

    <nav role="navigation" aria-label="Pagination">
        {{-- Phones --}}
        <div class="flex items-center justify-between gap-3 sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="btn btn-secondary flex-1 opacity-40" aria-disabled="true">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowLeft }}" clip-rule="evenodd" /></svg>
                    Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary flex-1">
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowLeft }}" clip-rule="evenodd" /></svg>
                    Previous
                </a>
            @endif

            <p class="shrink-0 text-sm whitespace-nowrap text-ink-500">
                Page <span class="font-medium text-ink-900">{{ $paginator->currentPage() }}</span> of {{ $paginator->lastPage() }}
            </p>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-primary flex-1">
                    Next
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowRight }}" clip-rule="evenodd" /></svg>
                </a>
            @else
                <span class="btn btn-secondary flex-1 opacity-40" aria-disabled="true">
                    Next
                    <svg class="size-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowRight }}" clip-rule="evenodd" /></svg>
                </span>
            @endif
        </div>

        {{-- Tablets and desktops --}}
        <div class="hidden items-center justify-between gap-4 sm:flex">
            <p class="text-sm text-ink-500">
                Showing <span class="font-medium text-ink-900">{{ number_format($paginator->firstItem()) }}–{{ number_format($paginator->lastItem()) }}</span>
                of <span class="font-medium text-ink-900">{{ number_format($paginator->total()) }}</span>
            </p>

            <ul class="flex items-center gap-1">
                <li>
                    @if ($paginator->onFirstPage())
                        <span class="flex size-9 items-center justify-center rounded-full text-ink-300" aria-disabled="true" aria-label="Previous page">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowLeft }}" clip-rule="evenodd" /></svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page"
                           class="flex size-9 items-center justify-center rounded-full text-ink-600 hover:bg-ink-100 hover:text-ink-900 focus-visible:outline-2 focus-visible:outline-brand-700">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowLeft }}" clip-rule="evenodd" /></svg>
                        </a>
                    @endif
                </li>

                @foreach ($elements as $element)
                    @if (is_string($element))
                        <li><span class="flex size-9 items-center justify-center text-sm text-ink-400" aria-hidden="true">…</span></li>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            <li>
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="flex size-9 items-center justify-center rounded-full bg-brand-800 text-sm font-medium text-brand-50 tabular-nums">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" aria-label="Page {{ $page }}"
                                       class="flex size-9 items-center justify-center rounded-full text-sm text-ink-600 tabular-nums hover:bg-ink-100 hover:text-ink-900 focus-visible:outline-2 focus-visible:outline-brand-700">{{ $page }}</a>
                                @endif
                            </li>
                        @endforeach
                    @endif
                @endforeach

                <li>
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page"
                           class="flex size-9 items-center justify-center rounded-full text-ink-600 hover:bg-ink-100 hover:text-ink-900 focus-visible:outline-2 focus-visible:outline-brand-700">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowRight }}" clip-rule="evenodd" /></svg>
                        </a>
                    @else
                        <span class="flex size-9 items-center justify-center rounded-full text-ink-300" aria-disabled="true" aria-label="Next page">
                            <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="{{ $arrowRight }}" clip-rule="evenodd" /></svg>
                        </span>
                    @endif
                </li>
            </ul>
        </div>
    </nav>
@endif

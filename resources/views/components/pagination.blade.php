@props([
    'paginator'
])

@if ($paginator && $paginator->total() > 0)
    <footer class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="m-0 text-xs text-slate-500">
            Menampilkan <strong class="text-slate-700">{{ $paginator->firstItem() }}</strong>–<strong class="text-slate-700">{{ $paginator->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($paginator->total(), 0, ',', '.') }}</strong> data
        </p>

        @if ($paginator->hasPages())
            <div class="flex flex-wrap items-center gap-2">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-left text-xs"></i>
                    </a>
                @endif

                {{-- Smart Page Elements with Ellipsis (...) --}}
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $pages = collect([1, 2, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage - 1, $lastPage])
                        ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                        ->unique()
                        ->sort()
                        ->values();
                    $previousPageNumber = null;
                @endphp

                @foreach ($pages as $page)
                    @if ($previousPageNumber && $page - $previousPageNumber > 1)
                        <span class="inline-flex h-9 min-w-9 items-center justify-center text-xs text-slate-400">…</span>
                    @endif

                    @if ($page === $currentPage)
                        <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-[#145239] bg-[#145239] px-3 text-xs font-bold text-white">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                            {{ $page }}
                        </a>
                    @endif

                    @php
                        $previousPageNumber = $page;
                    @endphp
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </a>
                @else
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                        <i class="fa-solid fa-chevron-right text-xs"></i>
                    </span>
                @endif
            </div>
        @endif
    </footer>
@endif

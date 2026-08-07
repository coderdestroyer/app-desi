@props([
    'action',
    'availableYears' => []
])

<section class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <form
        action="{{ $action }}"
        method="GET"
        data-live-filter
        data-no-loader
        class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12"
    >
        {{-- Filter Tahun PDB --}}
        <div class="relative min-w-0 md:col-span-1 xl:col-span-11">
            <select
                name="tahun"
                id="filterTahun"
                class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            >
                <option value="">Semua Tahun PDB</option>
                @foreach ($availableYears as $yr)
                    <option
                        value="{{ $yr }}"
                        @selected(request('tahun') == $yr)
                    >
                        {{ $yr }}
                    </option>
                @endforeach
            </select>

            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
        </div>

        {{-- Reset Filter Button --}}
        <div class="flex min-w-0 items-center justify-end xl:col-span-1">
            <a
                href="{{ $action }}"
                title="Reset filter"
                class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs"
            >
                <i class="fa-solid fa-rotate-left text-sm"></i>
            </a>
        </div>
    </form>
</section>

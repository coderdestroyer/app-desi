@props([
    'action',
    'availableYears' => []
])

<section class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <form
        action="{{ $action }}"
        method="GET"
        class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12"
    >
        {{-- Filter Tahun PDB (50%) --}}
        <div class="relative min-w-0 md:col-span-1 xl:col-span-6">
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

        {{-- Action Buttons (50%) --}}
        <div class="flex min-w-0 gap-2 md:col-span-1 xl:col-span-6">
            <button
                type="submit"
                class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm"
            >
                <i class="fa-solid fa-filter"></i>
                <span class="truncate">Terapkan</span>
            </button>

            <a
                href="{{ $action }}"
                title="Reset filter"
                class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600 shadow-sm"
            >
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</section>

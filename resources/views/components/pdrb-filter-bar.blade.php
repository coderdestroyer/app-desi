@props([
    'action',
    'provinsis' => [],
    'kabupatens' => null,
    'availableYears' => [],
    'selectedProvinsiId' => null,
    'selectedKabupatenId' => null,
    'tab' => null,
    'searchPlaceholder' => 'Cari...'
])

<section class="{{ ($tab ?? 'own') === 'all' ? 'mt-4' : 'mt-6' }} rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
    <form action="{{ $action }}" method="GET" class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12">
        @if($tab)
            <input type="hidden" name="tab" value="{{ $tab }}">
        @endif

        {{-- Filter Provinsi --}}
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-3' : 'xl:col-span-4' }}">
            <select
                name="provinsi_id"
                id="filterProvinsi"
                onchange="this.form.submit()"
                class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            >
                @if(count($provinsis) > 1)
                    <option value="">Semua Provinsi</option>
                @endif
                @foreach ($provinsis as $prov)
                    <option
                        value="{{ $prov->provinsi_id }}"
                        @selected(($selectedProvinsiId ?? request('provinsi_id')) == $prov->provinsi_id)
                    >
                        {{ $prov->nama_provinsi }}
                    </option>
                @endforeach
            </select>

            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
        </div>

        {{-- Filter Kabupaten / Kota --}}
        @if($kabupatens !== null)
            <div class="relative min-w-0 xl:col-span-3">
                <select
                    name="kabupaten_id"
                    id="filterKabupaten"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >
                    @if(count($kabupatens) > 1)
                        <option value="">Semua Kab/Kota</option>
                    @endif
                    @foreach ($kabupatens as $kab)
                        <option
                            value="{{ $kab->kab_id }}"
                            data-provinsi="{{ $kab->provinsi_id }}"
                            @selected(($selectedKabupatenId ?? request('kabupaten_id')) == $kab->kab_id)
                        >
                            {{ $kab->nama_kabupaten }}
                        </option>
                    @endforeach
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>
        @endif

        {{-- Filter Tahun PDRB --}}
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-2' : 'xl:col-span-3' }}">
            <select
                name="tahun"
                id="filterTahun"
                class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            >
                <option value="">Semua Tahun</option>
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

        {{-- Input Search --}}
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-2' : 'xl:col-span-3' }}">
            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="{{ $searchPlaceholder }}"
                class="h-11 w-full min-w-0 rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
            >

            <button
                type="submit"
                aria-label="Cari PDRB"
                class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100"
            >
                <i class="fa-solid fa-magnifying-glass"></i>
            </button>
        </div>

        {{-- Action Buttons --}}
        <div class="flex min-w-0 gap-2 md:col-span-2 {{ $kabupatens !== null ? 'xl:col-span-2' : 'xl:col-span-2' }}">
            <button
                type="submit"
                class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm"
            >
                <i class="fa-solid fa-filter"></i>
                <span class="truncate">Terapkan</span>
            </button>

            <a
                href="{{ $action . ($tab ? '?tab='.$tab : '') }}"
                title="Reset filter"
                class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600 shadow-sm"
            >
                <i class="fa-solid fa-rotate-left"></i>
            </a>
        </div>
    </form>
</section>

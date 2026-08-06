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
    <form action="{{ $action }}" method="GET" class="grid min-w-0 grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12 items-center">
        @if($tab)
            <input type="hidden" name="tab" value="{{ $tab }}">
        @endif

        {{-- Filter Provinsi --}}
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-3' : 'xl:col-span-4' }}">
            <select
                name="provinsi_id"
                id="filterProvinsi"
                onchange="this.form.submit()"
                class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
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
                    onchange="this.form.submit()"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">Semua Wilayah (Provinsi & Kab/Kota)</option>
                    
                    @if(request('provinsi_id'))
                        @php
                            $currProv = collect($provinsis)->firstWhere('provinsi_id', request('provinsi_id'));
                        @endphp
                        @if($currProv)
                            <option value="prov_only" class="font-extrabold text-slate-900 bg-emerald-50" @selected(request('kabupaten_id') === 'prov_only')>
                                🏛️ PROVINSI {{ strtoupper($currProv->nama_provinsi) }}
                            </option>
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
                    @else
                        @if(count($provinsis) > 0)
                            <optgroup label="Tingkat Provinsi">
                                @foreach ($provinsis as $prov)
                                    <option value="prov_{{ $prov->provinsi_id }}" class="font-bold text-slate-800" @selected(request('kabupaten_id') === 'prov_' . $prov->provinsi_id)>
                                        🏛️ PROVINSI {{ strtoupper($prov->nama_provinsi) }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                        @if(count($kabupatens) > 0)
                            <optgroup label="Tingkat Kabupaten / Kota">
                                @foreach ($kabupatens as $kab)
                                    <option
                                        value="{{ $kab->kab_id }}"
                                        data-provinsi="{{ $kab->provinsi_id }}"
                                        @selected(($selectedKabupatenId ?? request('kabupaten_id')) == $kab->kab_id)
                                    >
                                        {{ $kab->nama_kabupaten }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endif
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>
        @endif

        {{-- Filter Tahun PDRB (Disamakan font nya tanpa font-mono) --}}
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-2' : 'xl:col-span-3' }}">
            <select
                name="tahun"
                id="filterTahun"
                onchange="this.form.submit()"
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
        <div class="relative min-w-0 {{ $kabupatens !== null ? 'xl:col-span-3' : 'xl:col-span-4' }}">
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

        {{-- Reset Filter Button (Icon Only, Pas 1 Col Grid tanpa Gap Kosong) --}}
        <div class="flex min-w-0 items-center justify-end xl:col-span-1">
            <a
                href="{{ $action . ($tab ? '?tab='.$tab : '') }}"
                title="Reset Filter"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs"
            >
                <i class="fa-solid fa-rotate-left text-sm"></i>
            </a>
        </div>
    </form>
</section>

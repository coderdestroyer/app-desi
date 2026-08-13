@extends('partials.layouts.operator')

@section('title', 'Data Realisasi Investasi')

@section('content')
    @php
        $statStyles = [
            'green' => [
                'icon' => 'bg-emerald-50 text-emerald-600 border border-emerald-200',
                'corner' => 'bg-emerald-100/50',
            ],
            'blue' => [
                'icon' => 'bg-blue-50 text-blue-600 border border-blue-200',
                'corner' => 'bg-blue-100/50',
            ],
            'orange' => [
                'icon' => 'bg-amber-50 text-amber-600 border border-amber-200',
                'corner' => 'bg-amber-100/50',
            ],
            'violet' => [
                'icon' => 'bg-purple-50 text-purple-600 border border-purple-200',
                'corner' => 'bg-purple-100/50',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-[#f7f9fc] space-y-6">
        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-chart-line text-[#FFD54F]"></i>
                    <span>Menu Operator</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Manajemen Data Investasi
                </h1>
                <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Lihat dan pantau data realisasi investasi LKPM, perusahaan, sektor bisnis, status modal (PMDN/PMA), dan sebaran wilayah kabupaten.
                </p>
            </div>
        </section>

        {{-- STATS CARDS --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $statStyle = $statStyles[$stat['tone']] ?? $statStyles['green'];
                @endphp
                <article class="group relative min-h-[112px] overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md sm:min-h-[118px] sm:p-5">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-bl-full transition-transform duration-500 group-hover:scale-125 {{ $statStyle['corner'] }}"></div>
                    <div class="relative z-10 flex h-full items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="m-0 text-sm font-bold leading-5 text-slate-500">
                                {{ $stat['label'] }}
                            </p>
                            <p class="mb-0 mt-2 text-2xl font-black tracking-tight text-slate-900">
                                {{ $stat['value'] }}
                            </p>
                            <p class="mb-0 mt-1 text-xs font-medium leading-5 text-slate-400">
                                {{ $stat['description'] }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-lg shadow-sm sm:h-14 sm:w-14 sm:text-xl {{ $statStyle['icon'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- FILTERS --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
            <form action="{{ route('operator.data-investasi.index') }}" method="GET" data-live-filter data-no-loader
                class="grid w-full min-w-0 grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-12 items-center">
                
                {{-- 1. Provinsi Filter (Default: Sumatera Utara / 12) --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="provinsi_id"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="all" @selected($selectedProvId === 'all')>Semua Provinsi</option>
                        @foreach ($provinsiList as $prov)
                            <option value="{{ $prov->provinsi_id }}" @selected((string)$selectedProvId === (string)$prov->provinsi_id)>
                                {{ $prov->nama_provinsi }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 2. Kabupaten / Kota Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="kabupaten_id"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kabupaten / Kota</option>
                        @foreach ($kabupatenFilterList as $kab)
                            <option value="{{ $kab->kab_id }}" @selected((string)request('kabupaten_id') === (string)$kab->kab_id)>
                                {{ $kab->nama_kabupaten }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 3. Tahun Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="tahun"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Tahun</option>
                        @foreach ($tahunList as $th)
                            <option value="{{ $th }}" @selected((string)request('tahun') === (string)$th)>{{ $th }}</option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 4. Status Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="status"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Status Modal</option>
                        <option value="PMDN" @selected(request('status') === 'PMDN')>PMDN</option>
                        <option value="PMA" @selected(request('status') === 'PMA')>PMA</option>
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 5. Search Input --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                </div>

                {{-- 6. Reset Button --}}
                <div class="flex min-w-0 items-center justify-end sm:col-span-1 xl:col-span-1">
                    <a href="{{ route('operator.data-investasi.index') }}" title="Reset filter"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                        <span class="xl:hidden text-xs font-semibold">Reset</span>
                    </a>
                </div>
            </form>
        </section>

        {{-- TABLE SECTION --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="m-0 text-base sm:text-lg font-black text-slate-900">
                        Daftar Realisasi Data Investasi
                    </h2>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Menampilkan entri data investasi berdasarkan filter pencarian.
                    </p>
                </div>

                <div class="inline-flex h-10 w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-black text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($dataInvestasi->total(), 0, ',', '.') }} Data
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left min-w-[1000px]">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                            <th class="w-[60px] px-5 py-3 text-center">No</th>
                            <th class="min-w-[200px] px-4 py-3">Perusahaan & ID LKPM</th>
                            <th class="w-[110px] px-4 py-3 text-center">Status</th>
                            <th class="min-w-[180px] px-4 py-3">Sektor Usaha</th>
                            <th class="min-w-[180px] px-4 py-3">Wilayah</th>
                            <th class="w-[90px] px-4 py-3 text-center">Tahun</th>
                            <th class="min-w-[170px] px-4 py-3 text-right">Nilai Investasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataInvestasi as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4 text-center text-xs font-bold text-slate-400">
                                    {{ $dataInvestasi->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800 leading-snug">
                                        {{ $item->nama_perusahaan }}
                                    </div>
                                    @if ($item->id_laporan_lkpm)
                                        <div class="mt-1 inline-flex items-center gap-1 text-[11px] font-mono text-slate-400">
                                            <span>LKPM: #{{ $item->id_laporan_lkpm }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if ($item->status === 'PMA')
                                        <span class="inline-flex rounded-xl border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                                            PMA
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                            PMDN
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-xs font-semibold text-slate-700 leading-relaxed max-w-[250px]">
                                        {{ $item->nama_sektor ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-xs font-medium text-slate-700">
                                        {{ $item->kabupaten->nama_kabupaten ?? ($item->provinsi->nama_provinsi ?? '-') }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-700">
                                        {{ $item->tahun }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold text-emerald-800">
                                    Rp {{ number_format($item->nilai_investasi, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </div>
                                        <h3 class="mt-4 text-base font-bold text-slate-800">Tidak ada data investasi</h3>
                                        <p class="mt-1 text-xs text-slate-500">
                                            Cobalah mengubah kata kunci atau mereset filter yang sedang aktif.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="p-4 border-t border-slate-100">
                <x-pagination :paginator="$dataInvestasi" />
            </div>
        </section>
    </div>
@endsection

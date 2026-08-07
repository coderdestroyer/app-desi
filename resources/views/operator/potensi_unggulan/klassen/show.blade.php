@extends('partials.layouts.operator')

@section('title', 'Detail Sektor Klassen - ' . $namaDaerah . ' (' . $tahunAwal . '-' . $tahunAkhir . ')')

@section('content')
<div class="space-y-6">

    <!-- Top Action Toolbar & Back Button -->
    <div class="flex items-center justify-between">
        <a href="{{ route('operator.klassen.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 font-bold text-xs md:text-sm transition-all shadow-xs">
            <i class="fa-solid fa-arrow-left text-[#145239]"></i>
            <span>Kembali ke Ringkasan Analisis</span>
        </a>

        <button type="button" onclick="exportToExcel()" class="inline-flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all shadow-xs">
            <i class="fa-solid fa-file-excel text-[#FFD54F]"></i>
            <span>Unduh Detail 17 Sektor (Excel)</span>
        </button>
    </div>

    <!-- Header Banner Info Card -->
    <div class="bg-white rounded-2xl p-6 border border-[#CFE3D5] shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EEF8F2] text-[#145239] text-xs font-bold mb-2 border border-[#CFE3D5]">
                <i class="fa-solid fa-chart-line text-[#D8A62A]"></i>
                <span>Rincian 17 Sektor Tipologi Klassen</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">
                Analisis Tipologi Klassen {{ $namaDaerah }}
            </h1>
            <p class="text-slate-500 text-xs md:text-sm mt-1">
                Tingkat Wilayah: <strong class="text-slate-700">{{ $tingkatWilayah }}</strong> | Pembanding: <strong class="text-slate-700">{{ $namaPembanding }}</strong> | Periode: <strong class="text-[#145239] font-extrabold">{{ $tahunAwal }} - {{ $tahunAkhir }}</strong>
            </p>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Detail Laju Pertumbuhan & Kontribusi Per Sektor</h2>
                <p class="text-slate-500 text-xs mt-0.5">Laju Pertumbuhan Sektor (ri) vs Acuan (r) dan Share Sektor (yi) vs Acuan (y)</p>
            </div>
            <form action="{{ route('operator.klassen.show') }}" method="GET" class="relative w-full md:w-80">
                <input type="hidden" name="tingkat_wilayah" value="{{ $tingkatWilayah }}">
                <input type="hidden" name="tahun" value="{{ $tahunAkhir }}">
                @if(request('kabupaten_id'))
                    <input type="hidden" name="kabupaten_id" value="{{ request('kabupaten_id') }}">
                @endif
                @if(request('provinsi_id'))
                    <input type="hidden" name="provinsi_id" value="{{ request('provinsi_id') }}">
                @endif
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Sektor atau Klasifikasi..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:border-[#145239] focus:ring-1 focus:ring-[#145239] outline-none transition-all">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400">
                    <i class="fa-solid fa-magnifying-glass text-xs"></i>
                </div>
            </form>
        </div>

        <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
            <table id="klassenDetailTable" class="w-full text-left border-collapse min-w-[1000px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                    <tr>
                        <th class="px-4 py-3.5 w-12 text-center">No</th>
                        <th class="px-4 py-3.5 min-w-[200px]">SEKTOR</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">DAERAH ANALISIS</th>
                        <th class="px-4 py-3.5 whitespace-nowrap">PEMBANDING</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">LAJU SEKTOR (ri)</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">LAJU ACUAN (r)</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">KONTRIBUSI SEKTOR (yi)</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">KONTRIBUSI ACUAN (y)</th>
                        <th class="px-4 py-3.5 text-center whitespace-nowrap">KUADRAN</th>
                        <th class="px-4 py-3.5 min-w-[220px]">KLASIFIKASI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                    @forelse($sectorData as $index => $data)
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-500">{{ ($sectorData->currentPage() - 1) * $sectorData->perPage() + $loop->iteration }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $data['sektor'] }}</td>
                            <td class="px-4 py-3.5 font-bold text-slate-800">{{ $data['daerah_analisis'] }}</td>
                            <td class="px-4 py-3.5 text-slate-600 font-medium">{{ $data['daerah_pembanding'] }}</td>
                            <td class="px-4 py-3.5 text-center font-mono text-xs">{{ number_format($data['ri'] ?? 0, 2, ',', '.') }}%</td>
                            <td class="px-4 py-3.5 text-center font-mono text-xs">{{ number_format($data['r'] ?? 0, 2, ',', '.') }}%</td>
                            <td class="px-4 py-3.5 text-center font-mono text-xs">{{ number_format($data['yi'] ?? 0, 2, ',', '.') }}%</td>
                            <td class="px-4 py-3.5 text-center font-mono text-xs">{{ number_format($data['y'] ?? 0, 2, ',', '.') }}%</td>
                            <td class="px-4 py-3.5 text-center whitespace-nowrap font-bold">
                                @if($data['kuadran'] === 'Kuadran I')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Kuadran I
                                    </span>
                                @elseif($data['kuadran'] === 'Kuadran II')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Kuadran II
                                    </span>
                                @elseif($data['kuadran'] === 'Kuadran III')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        Kuadran III
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Kuadran IV
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 text-slate-700 font-medium">{{ $data['klasifikasi'] ?? ($data['klasifikasi_sektor'] ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-500 font-medium">
                                Belum ada data sektor untuk daerah ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Smart Pagination Section with Ellipsis (...) -->
        @if ($sectorData->total() > 0)
            <footer class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="m-0 text-xs text-slate-500">
                    Menampilkan <strong class="text-slate-700">{{ $sectorData->firstItem() }}</strong>–<strong class="text-slate-700">{{ $sectorData->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($sectorData->total(), 0, ',', '.') }}</strong> sektor
                </p>

                @if ($sectorData->hasPages())
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($sectorData->onFirstPage())
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </span>
                        @else
                            <a href="{{ $sectorData->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </a>
                        @endif

                        @php
                            $currentPage = $sectorData->currentPage();
                            $lastPage = $sectorData->lastPage();
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
                                <a href="{{ $sectorData->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                                    {{ $page }}
                                </a>
                            @endif

                            @php
                                $previousPageNumber = $page;
                            @endphp
                        @endforeach

                        @if ($sectorData->hasMorePages())
                            <a href="{{ $sectorData->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
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
    </div>

</div>

<script>
    function exportToExcel() {
        var table = document.getElementById("klassenDetailTable");
        var clone = table.cloneNode(true);
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Detail Klassen {{ $namaDaerah }}"});
        XLSX.writeFile(wb, "Detail_Klassen_{{ $namaDaerah }}_{{ $tahunAwal }}_{{ $tahunAkhir }}.xlsx");
    }
</script>
@endsection

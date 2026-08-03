@extends('partials.layouts.admin')

@section('title', 'Peninjauan Dokumen IPRO: ' . $project->nama_proyek)

@section('content')
<div class="min-h-screen bg-[#F7FAF8] p-5 md:p-7 lg:p-8" x-data="adminIproManager()" x-init="initData()">

    <!-- Top Action & Navigation Bar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.proyek-ipro.index') }}" class="px-4 py-2.5 rounded-xl bg-white border border-[#CFE3D5] text-slate-700 text-xs font-bold hover:bg-slate-50 inline-flex items-center gap-2 transition-all shadow-xs">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Daftar Proyek</span>
            </a>
            <span class="text-xs text-[#667069]">ID Proyek: #{{ $project->id }}</span>
        </div>

        <div class="flex items-center gap-3">
            <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-[#E7F2EB] text-[#145239] border border-[#CFE3D5] inline-flex items-center gap-1.5">
                <i class="fa-solid fa-lock text-[10px]"></i>
                Mode Read-Only Admin
            </span>

            @if($project->status_publikasi === 'published')
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    Status: Published
                </span>
            @else
                <span class="px-3.5 py-1 rounded-full text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-300 inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-600"></span>
                    Status: Draft
                </span>
            @endif
        </div>
    </div>

    <!-- Project Overview Banner -->
    <div class="bg-gradient-to-r from-[#145239] via-[#0B5D3D] to-[#1E5D41] rounded-2xl p-7 text-white shadow-xl mb-8 relative overflow-hidden">
        <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-[#FFD54F] opacity-15 blur-3xl mix-blend-overlay"></div>

        <div class="relative z-10">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-emerald-100">
                    <i class="fa-solid fa-building text-[#FFD54F] mr-1"></i> {{ $project->sektor ? $project->sektor->nama_sektor : 'Sektor Investasi' }}
                </span>
                <span class="px-3 py-1 rounded-full bg-white/10 border border-white/20 text-xs font-semibold text-emerald-100">
                    <i class="fa-solid fa-location-dot text-[#FFD54F] mr-1"></i> {{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Sumatera Utara' }}
                </span>
            </div>

            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight mb-3 text-white">
                {{ $project->nama_proyek }}
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-3xl leading-relaxed mb-6">
                {{ $project->deskripsi ?: 'Tidak ada deskripsi rinci.' }}
            </p>

            <!-- Key Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-emerald-700/60">
                <div>
                    <span class="text-xs text-emerald-200 block uppercase font-semibold">Total Nilai CAPEX Riil</span>
                    <span class="text-lg md:text-xl font-black text-[#FFD54F]" x-text="formatRupiah(totalCapex)"></span>
                </div>
                <div>
                    <span class="text-xs text-emerald-200 block uppercase font-semibold">Tahun Awal & Tenor</span>
                    <span class="text-lg md:text-xl font-bold text-white">{{ $project->tahun_awal }} ({{ $project->jangka_waktu_tahun }} Th)</span>
                </div>
                <div>
                    <span class="text-xs text-emerald-200 block uppercase font-semibold">Struktur Modal (Equity : Debt)</span>
                    <span class="text-lg md:text-xl font-bold text-white">{{ $project->rasio_modal_sendiri ?: 60 }}% : {{ $project->rasio_pinjaman_kredit ?: 40 }}%</span>
                </div>
                <div>
                    <span class="text-xs text-emerald-200 block uppercase font-semibold">Penginput (Operator)</span>
                    <span class="text-base font-bold text-white">{{ $project->user ? $project->user->name : 'Operator Data' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex items-center gap-2 border-b border-[#CFE3D5] mb-6 overflow-x-auto pb-1">
        <button 
            @click="activeTab = 'overview'"
            :class="activeTab === 'overview' ? 'border-[#145239] text-[#145239] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
            class="px-5 py-3 rounded-t-xl border-b-2 text-sm flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap"
        >
            <i class="fa-solid fa-circle-info text-base"></i>
            <span>Ringkasan Proyek & GIS</span>
        </button>

        <button 
            @click="activeTab = 'capex'"
            :class="activeTab === 'capex' ? 'border-[#145239] text-[#145239] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
            class="px-5 py-3 rounded-t-xl border-b-2 text-sm flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap"
        >
            <i class="fa-solid fa-boxes-packing text-base"></i>
            <span>1. Rincian Estimasi CAPEX</span>
        </button>

        <button 
            @click="activeTab = 'pl'"
            :class="activeTab === 'pl' ? 'border-[#145239] text-[#145239] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
            class="px-5 py-3 rounded-t-xl border-b-2 text-sm flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap"
        >
            <i class="fa-solid fa-chart-line text-base"></i>
            <span>2. Proyeksi Laba Rugi (P&L)</span>
        </button>

        <button 
            @click="activeTab = 'cashflow'"
            :class="activeTab === 'cashflow' ? 'border-[#145239] text-[#145239] bg-white shadow-xs font-bold' : 'border-transparent text-slate-500 hover:text-slate-700 font-semibold'"
            class="px-5 py-3 rounded-t-xl border-b-2 text-sm flex items-center gap-2 transition-all cursor-pointer whitespace-nowrap"
        >
            <i class="fa-solid fa-wallet text-base"></i>
            <span>3. Laporan Arus Kas (Cash Flow)</span>
        </button>
    </div>

    <!-- ================= TAB 0: OVERVIEW & GIS ================= -->
    <div x-show="activeTab === 'overview'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <h3 class="text-lg font-bold text-[#17201C] mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                <i class="fa-solid fa-sliders text-[#145239]"></i>
                <span>Parameter Utama Finansial & GIS Proyek</span>
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-sm">
                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Kabupaten / Kota</span>
                    <span class="font-bold text-slate-800 text-base">{{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : '-' }}</span>
                </div>

                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Sektor Ekonomi</span>
                    <span class="font-bold text-slate-800 text-base">{{ $project->sektor ? $project->sektor->nama_sektor : '-' }}</span>
                </div>

                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Lokasi GIS / Titik Koordinat</span>
                    <span class="font-bold text-slate-800 text-base">
                        @if($project->lokasi)
                            {{ $project->lokasi->nama }} ({{ $project->lokasi->latitude }}, {{ $project->lokasi->longitude }})
                        @else
                            Belum ditentukan
                        @endif
                    </span>
                </div>

                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Pajak Penghasilan (PPh)</span>
                    <span class="font-bold text-[#145239] text-base">{{ $project->pl_persentase_pajak_penghasilan ?: 22 }}%</span>
                </div>

                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Suku Bunga Kredit (Debt Interest)</span>
                    <span class="font-bold text-[#145239] text-base">{{ $project->suku_bunga_kredit ?: 8.05 }}%</span>
                </div>

                <div class="p-4 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="text-xs text-[#667069] block font-semibold mb-1">Tenor Pinjaman Kredit</span>
                    <span class="font-bold text-[#145239] text-base">{{ $project->tenor_kredit_tahun ?: 5 }} Tahun</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ================= TAB 1: RINCIAN ESTIMASI CAPEX ================= -->
    <div x-show="activeTab === 'capex'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-[#17201C]">Tabel Rincian Capital Expenditure (CAPEX) Riil</h3>
                    <p class="text-xs text-[#667069]">Data komponen anggaran modal awal proyek dari database</p>
                </div>
                <div class="text-right">
                    <span class="text-xs font-semibold text-slate-400 block">Total Investasi CAPEX</span>
                    <span class="text-2xl font-black text-[#145239]" x-text="formatRupiah(totalCapex)"></span>
                </div>
            </div>

            @if(count($capexList) > 0)
                <div class="overflow-x-auto border border-[#CFE3D5] rounded-xl pb-6">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[60px] border-r border-[#0B5D3D] text-white">No</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[380px] border-r border-[#0B5D3D] text-white">Nama Komponen</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[120px] border-r border-[#0B5D3D] text-white">Volume</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider text-center min-w-[120px] border-r border-[#0B5D3D] text-white">Satuan</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[130px] border-r border-[#0B5D3D] text-white">Luas (m²)</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[200px] border-r border-[#0B5D3D] text-white">Harga Satuan</th>
                                <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[200px] text-white">Total Harga</th>
                            </tr>
                        </thead>
                        @foreach($capexList as $parentIndex => $kategori)
                        <tbody>
                            <!-- Parent Row (Kategori Utama) -->
                            <tr class="border-b border-[#CFE3D5] font-bold bg-[#E7F2EB] text-[#145239]">
                                <td class="px-4 py-3 font-mono border-r border-[#CFE3D5]">{{ $parentIndex + 1 }}</td>
                                <td class="px-4 py-3 border-r border-[#CFE3D5] uppercase font-bold">{{ $kategori['kategori'] }}</td>
                                <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                                <td class="px-4 py-3 text-center text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                                <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                                <td class="px-4 py-3 text-right text-slate-400 font-mono border-r border-[#CFE3D5]">-</td>
                                <td class="px-4 py-3 text-right text-[#145239] font-bold font-mono text-base whitespace-nowrap">Rp {{ number_format($kategori['subtotal'], 0, ',', '.') }}</td>
                            </tr>

                            <!-- Children Rows (Sub-komponen) -->
                            @foreach($kategori['items'] as $childIndex => $item)
                            <tr class="border-b border-slate-200 hover:bg-slate-50 transition-colors">
                                <td class="px-8 py-3 text-slate-500 font-mono border-r border-slate-100 text-xs">{{ ($parentIndex + 1) . '.' . ($childIndex + 1) }}</td>
                                <td class="px-4 py-3 pl-8 border-r border-slate-100 font-semibold text-slate-700">{{ $item['nama'] }}</td>
                                <td class="px-4 py-3 text-right border-r border-slate-100 font-mono text-slate-700">{{ $item['vol'] ? number_format($item['vol']) : '-' }}</td>
                                <td class="px-4 py-3 text-center border-r border-slate-100 font-mono text-slate-700">{{ $item['satuan'] ?: '-' }}</td>
                                <td class="px-4 py-3 text-right border-r border-slate-100 font-mono text-slate-700">{{ $item['luas'] && $item['luas'] > 0 ? number_format($item['luas']) : '-' }}</td>
                                <td class="px-4 py-3 text-right border-r border-slate-100 font-mono text-slate-700 whitespace-nowrap">Rp {{ number_format($item['harga_m2'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-mono font-medium text-slate-800 whitespace-nowrap">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        @endforeach
                    </table>
                </div>
            @else
                <div class="py-12 text-center text-slate-400">
                    <i class="fa-solid fa-boxes-packing text-3xl text-slate-300 mb-2"></i>
                    <p class="font-bold text-slate-700">Komponen CAPEX Belum Diisi Operator</p>
                    <p class="text-xs text-slate-400 mt-1">Operator belum menginput rincian estimasi biaya modal untuk proyek ini.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- ================= TAB 2: PROYEKSI LABA RUGI (P&L READ-ONLY) ================= -->
    <div x-show="activeTab === 'pl'" class="space-y-6">
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6 overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-lg font-bold text-[#17201C]">Proyeksi Laba Rugi (Profit & Loss Statement)</h3>
                    <p class="text-xs text-[#667069]">Struktur multi-level kategori Pendapatan & Biaya Operasional serta perhitungan EBITDA, EBIT, EBT, & EAT.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="px-3 py-1.5 bg-[#E7F2EB] border border-[#CFE3D5] rounded-lg text-xs font-semibold text-[#145239]">
                        PPh: <strong><span x-text="settings.pl_persentase_pajak_penghasilan"></span>%</strong>
                    </div>
                    <div class="px-3 py-1.5 bg-amber-50 border border-amber-200 rounded-lg text-xs font-semibold text-[#D4A017]">
                        Bunga Credit: <strong><span x-text="settings.suku_bunga_kredit"></span>%</strong>
                    </div>
                    <div class="px-3 py-1.5 bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700">
                        Depresiasi: <strong><span x-text="formatRupiah(settings.pl_nominal_depresiasi)"></span> /Thn</strong>
                    </div>
                </div>
            </div>

            <!-- Tabel Responsive Multi-Tahun -->
            <div class="overflow-x-auto border border-[#CFE3D5] rounded-xl">
                <table class="w-full text-sm text-left border-collapse">
                    <thead>
                        <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                            <th class="px-4 py-3.5 font-bold text-xs tracking-wider min-w-[360px] sticky left-0 bg-[#145239] z-30 border-r border-[#0B5D3D] shadow-[2px_0_5px_rgba(0,0,0,0.15)]">Kategori / Komponen P&L</th>
                            <template x-for="year in years" :key="year">
                                <th class="px-4 py-3.5 font-bold text-xs tracking-wider text-right min-w-[160px] border-r border-[#0B5D3D] whitespace-nowrap" x-text="'Tahun Ke-' + year"></th>
                            </template>
                        </tr>
                    </thead>
                    
                    <!-- ================= 1. BLOK PENDAPATAN ================= -->
                    <tbody x-show="getParents('PENDAPATAN').length > 0">
                        <tr class="bg-[#E7F2EB] text-[#145239] font-bold">
                            <td class="px-4 py-2.5 sticky left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase tracking-wider text-xs">A. PENDAPATAN</td>
                            <td :colspan="years.length" class="bg-[#E7F2EB]"></td>
                        </tr>
                    </tbody>

                    <template x-for="parent in getParents('PENDAPATAN')" :key="parent.temp_id">
                        <tbody class="border-b border-slate-200">
                            <!-- Level 1 -->
                            <tr class="bg-slate-100 font-bold">
                                <td class="px-4 py-2.5 text-slate-800 sticky left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase" x-text="parent.nama_komponen"></td>
                                <template x-for="year in years" :key="year">
                                    <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-800 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))"></td>
                                </template>
                            </tr>

                            <!-- Level 2 -->
                            <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                                <tr class="bg-white">
                                    <td class="px-4 py-2 pl-10 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] font-semibold text-slate-700" x-text="child.nama_komponen"></td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-100 whitespace-nowrap" x-text="formatRupiah(getRowYearlyTotal(child.temp_id, year))"></td>
                                    </template>
                                </tr>
                            </template>

                            <!-- Level 3 -->
                            <template x-for="child in getChildren(parent.temp_id)" :key="'l3_'+child.temp_id">
                                <template x-for="subchild in getChildren(child.temp_id)" :key="subchild.temp_id">
                                    <tr class="bg-slate-50 border-t border-slate-100">
                                        <td class="px-4 py-2 pl-16 sticky left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-600 text-xs" x-text="subchild.nama_komponen"></td>
                                        <template x-for="year in years" :key="year">
                                            <td class="px-4 py-2 text-right font-mono text-slate-600 text-xs border-r border-slate-100 whitespace-nowrap" x-text="formatRupiah(subchild.yearly_data[year] || 0)"></td>
                                        </template>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </template>

                    <!-- ================= 2. BLOK BIAYA OPERASIONAL ================= -->
                    <tbody x-show="getParents('BIAYA_OPERASIONAL').length > 0">
                        <tr class="bg-rose-100 text-rose-900 font-bold">
                            <td class="px-4 py-2.5 sticky left-0 bg-rose-100 z-20 border-r border-rose-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase tracking-wider text-xs">B. BIAYA OPERASIONAL</td>
                            <td :colspan="years.length" class="bg-rose-100"></td>
                        </tr>
                    </tbody>

                    <template x-for="parent in getParents('BIAYA_OPERASIONAL')" :key="parent.temp_id">
                        <tbody class="border-b border-slate-200">
                            <!-- Level 1 -->
                            <tr class="bg-slate-100 font-bold">
                                <td class="px-4 py-2.5 text-slate-800 sticky left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase" x-text="parent.nama_komponen"></td>
                                <template x-for="year in years" :key="year">
                                    <td class="px-4 py-2.5 text-right font-mono font-bold text-slate-800 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))"></td>
                                </template>
                            </tr>

                            <!-- Level 2 -->
                            <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                                <tr class="bg-white">
                                    <td class="px-4 py-2 pl-10 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] font-semibold text-slate-700" x-text="child.nama_komponen"></td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-100 whitespace-nowrap" x-text="formatRupiah(getRowYearlyTotal(child.temp_id, year))"></td>
                                    </template>
                                </tr>
                            </template>

                            <!-- Level 3 -->
                            <template x-for="child in getChildren(parent.temp_id)" :key="'l3_'+child.temp_id">
                                <template x-for="subchild in getChildren(child.temp_id)" :key="subchild.temp_id">
                                    <tr class="bg-slate-50 border-t border-slate-100">
                                        <td class="px-4 py-2 pl-16 sticky left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-600 text-xs" x-text="subchild.nama_komponen"></td>
                                        <template x-for="year in years" :key="year">
                                            <td class="px-4 py-2 text-right font-mono text-slate-600 text-xs border-r border-slate-100 whitespace-nowrap" x-text="formatRupiah(subchild.yearly_data[year] || 0)"></td>
                                        </template>
                                    </tr>
                                </template>
                            </template>
                        </tbody>
                    </template>

                    <!-- Empty State P&L -->
                    <tbody x-show="rows.length === 0">
                        <tr>
                            <td :colspan="years.length + 1" class="text-center py-10 text-slate-400 text-sm">
                                Operator belum menambahkan komponen Laba Rugi untuk proyek ini.
                            </td>
                        </tr>
                    </tbody>

                    <!-- ================= 3. REKAPITULASI FINANSIAL P&L ================= -->
                    <tbody x-show="rows.length > 0">
                        <tr><td :colspan="years.length + 1" class="h-6 bg-white border-0"></td></tr>

                        <!-- Total Pendapatan & OPEX -->
                        <tr class="bg-[#E7F2EB] border-t border-[#CFE3D5] font-bold">
                            <td class="px-4 py-2.5 sticky left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-[#145239] uppercase tracking-wider text-xs">TOTAL PENDAPATAN</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2.5 text-right font-mono text-[#145239] border-r border-[#CFE3D5] whitespace-nowrap" x-text="formatRupiah(calculateTotal('PENDAPATAN', year))"></td>
                            </template>
                        </tr>
                        <tr class="bg-rose-100 border-b border-rose-200 font-bold">
                            <td class="px-4 py-2.5 sticky left-0 bg-rose-100 z-20 border-r border-rose-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-rose-900 uppercase tracking-wider text-xs">TOTAL BIAYA OPERASIONAL</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2.5 text-right font-mono text-rose-900 border-r border-rose-200 whitespace-nowrap" x-text="formatRupiah(calculateTotal('BIAYA_OPERASIONAL', year))"></td>
                            </template>
                        </tr>

                        <!-- EBITDA -->
                        <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                            <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBITDA</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBITDA(year))"></td>
                            </template>
                        </tr>

                        <!-- Depresiasi -->
                        <tr class="bg-white border-b border-slate-200">
                            <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">Depresiasi (-)</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(settings.pl_nominal_depresiasi)"></td>
                            </template>
                        </tr>

                        <!-- EBIT -->
                        <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                            <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBIT (Laba Operasional)</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBIT(year))"></td>
                            </template>
                        </tr>

                        <!-- Bunga Pinjaman -->
                        <tr class="bg-white border-b border-slate-200">
                            <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">
                                Beban Bunga Pinjaman Bank (-) <span class="text-xs text-[#145239] font-bold" x-text="'(' + (settings.suku_bunga_kredit || 8.05) + '%)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getBebanBunga(year))"></td>
                            </template>
                        </tr>

                        <!-- EBT -->
                        <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                            <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBT (Laba Sebelum Pajak)</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBT(year))"></td>
                            </template>
                        </tr>

                        <!-- Pajak PPh -->
                        <tr class="bg-white border-b border-slate-200">
                            <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">
                                Pajak PPh (-) <span class="text-xs text-slate-500 italic" x-text="'(' + (settings.pl_persentase_pajak_penghasilan || 22) + '% dari EBT)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getPajakPenghasilan(year))"></td>
                            </template>
                        </tr>

                        <!-- EAT / NET INCOME -->
                        <tr class="bg-[#145239] text-white font-bold border-y-2 border-[#0B5D3D]">
                            <td class="px-4 py-3.5 sticky left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] shadow-[2px_0_5px_rgba(0,0,0,0.15)] text-white text-base">EAT / LABA BERSIH (NET INCOME)</td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-3.5 text-right font-mono font-black text-[#FFD54F] border-r border-[#0B5D3D] text-base whitespace-nowrap" x-text="formatRupiah(getEAT(year))"></td>
                            </template>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ================= TAB 3: ANALISIS ARUS KAS (CASH FLOW READ-ONLY) ================= -->
    <div x-show="activeTab === 'cashflow'" class="space-y-6">
        
        <!-- Parameter Card Display -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-[#17201C] flex items-center gap-2">
                        <i class="fa-solid fa-sliders text-[#145239]"></i>
                        <span>Parameter Pembiayaan & Kredit Bank</span>
                    </h3>
                    <p class="text-xs text-[#667069] mt-0.5">Rasio ekuitas, beban bunga, dan tenor pembiayaan proyek yang diset oleh Operator.</p>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-3.5 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="block text-xs font-bold text-slate-600 mb-0.5">Rasio Equity (Modal Sendiri)</span>
                    <span class="text-lg font-black text-[#145239]" x-text="settings.rasio_modal_sendiri + '%'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="block text-xs font-bold text-slate-600 mb-0.5">Rasio Debt (Pinjaman Kredit)</span>
                    <span class="text-lg font-black text-[#D4A017]" x-text="settings.rasio_pinjaman_kredit + '%'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="block text-xs font-bold text-slate-600 mb-0.5">Suku Bunga Kredit</span>
                    <span class="text-lg font-black text-emerald-700" x-text="settings.suku_bunga_kredit + '% / Thn'"></span>
                </div>
                <div class="p-3.5 rounded-xl bg-[#F7FAF8] border border-[#CFE3D5]">
                    <span class="block text-xs font-bold text-slate-600 mb-0.5">Tenor Kredit Bank</span>
                    <span class="text-lg font-black text-purple-700" x-text="settings.tenor_kredit_tahun + ' Tahun'"></span>
                </div>
            </div>
        </div>

        <!-- Tabel Laporan Arus Kas -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6 overflow-hidden">
            <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h2 class="text-xl font-bold text-[#17201C]">Laporan Proyeksi Arus Kas (Cash Flow Statement)</h2>
                    <p class="text-xs text-[#667069] mt-1">Aliran kas masuk, kas keluar operasional, debt service, dan saldo kas dari Tahun 0 s/d Tahun {{ $project->jangka_waktu_tahun }}.</p>
                </div>
                <div class="bg-[#E7F2EB] border border-[#CFE3D5] px-4 py-2 rounded-xl text-right">
                    <span class="block text-[10px] font-extrabold text-[#145239] uppercase tracking-wider">TOTAL CAPEX TERHITUNG</span>
                    <span class="text-lg font-black text-[#145239] font-mono whitespace-nowrap" x-text="formatRupiah(totalCapex)"></span>
                </div>
            </div>

                    <!-- Tabel Responsive Cash Flow -->
                    <div class="overflow-x-auto border border-[#CFE3D5] rounded-xl">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wider min-w-[280px] sticky left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] shadow-[2px_0_5px_rgba(0,0,0,0.15)]">
                                        Tahun
                                    </th>
                                    <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wider text-right min-w-[160px] border-r border-[#0B5D3D] bg-[#0E422D]">
                                        0
                                    </th>
                                    <template x-for="t in years" :key="t">
                                        <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wider text-right min-w-[160px] border-r border-[#0B5D3D]" x-text="t"></th>
                                    </template>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">

                                <!-- ================= ARUS KAS OPERASIONAL ================= -->
                                <tr class="bg-[#E7F2EB] text-[#145239] font-bold border-y border-[#CFE3D5]">
                                    <td class="px-4 py-2.5 sticky left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] font-bold uppercase tracking-wider text-xs">
                                        Arus Kas Operasional
                                    </td>
                                    <td :colspan="years.length + 1" class="bg-[#E7F2EB]"></td>
                                </tr>
                                
                                <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-semibold">
                                        Kas Masuk
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="pendapatanPerTahun[t] ? formatRupiah(pendapatanPerTahun[t]) : '-'"></td>
                                    </template>
                                </tr>

                                <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-semibold">
                                        Kas Keluar
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="opexPerTahun[t] ? formatRupiah(-opexPerTahun[t]) : '-'"></td>
                                    </template>
                                </tr>

                                <!-- ================= ARUS KAS NON-OPERASIONAL ================= -->
                                <tr class="bg-blue-100 text-blue-900 font-bold border-y border-blue-200">
                                    <td class="px-4 py-2.5 sticky left-0 bg-blue-100 z-20 border-r border-blue-200 font-bold uppercase tracking-wider text-xs">
                                        Arus Kas Non-Operasional
                                    </td>
                                    <td :colspan="years.length + 1" class="bg-blue-100"></td>
                                </tr>

                                <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-medium">
                                        Setoran Modal
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="formatRupiah(getEquityAmount())"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                                    </template>
                                </tr>

                                <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-medium">
                                        Penarikan Kredit
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="formatRupiah(getDebtAmount())"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                                    </template>
                                </tr>

                                <tr class="bg-[#E7F2EB] font-bold text-[#145239] border-b border-[#CFE3D5]">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] font-bold uppercase text-xs tracking-wider">
                                        Kas Masuk Non-operasional
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-[#145239] font-bold border-r border-[#CFE3D5]" x-text="formatRupiah(getTotalKasMasukNonOps(0))"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                                    </template>
                                </tr>

                                <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                                        Investasi
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="formatRupiah(-totalCapex)"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                                    </template>
                                </tr>

                                <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                                        Pokok
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="getAngsuranPokok(t) > 0 ? formatRupiah(-getAngsuranPokok(t)) : '-'"></td>
                                    </template>
                                </tr>

                                <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                                        Bunga
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="getBebanBunga(t) > 0 ? formatRupiah(-getBebanBunga(t)) : '-'"></td>
                                    </template>
                                </tr>

                                <tr class="bg-rose-100 font-bold text-rose-900 border-b border-rose-200">
                                    <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-100 z-20 border-r border-rose-200 font-bold uppercase text-xs tracking-wider">
                                        Kas Keluar Non-Operasional
                                    </td>
                                    <td class="px-4 py-2 text-right font-mono text-rose-900 font-bold border-r border-rose-200" x-text="formatRupiah(-totalCapex)"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-bold border-r border-rose-200" x-text="(getAngsuranPokok(t) + getBebanBunga(t)) > 0 ? formatRupiah(-(getAngsuranPokok(t) + getBebanBunga(t))) : '-'"></td>
                                    </template>
                                </tr>

                                <!-- ================= RINGKASAN SALDO ================= -->
                                <tr class="bg-slate-200 font-bold text-slate-900 border-b border-slate-300">
                                    <td class="px-4 py-2.5 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 font-bold uppercase text-xs tracking-wider">
                                        Saldo
                                    </td>
                                    <td class="px-4 py-2.5 text-right font-mono text-slate-900 font-bold border-r border-slate-300" x-text="formatRupiah(getNetCashflow(0))"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-2.5 text-right font-mono text-slate-900 font-bold border-r border-slate-300" x-text="formatRupiah(getNetCashflow(t))"></td>
                                    </template>
                                </tr>

                                <tr class="bg-[#145239] border-y-2 border-[#0B5D3D] font-bold text-white">
                                    <td class="px-4 py-3 sticky left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] font-bold text-white text-base">
                                        Akumulasi Saldo
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono text-[#FFD54F] font-black border-r border-[#0B5D3D] text-base" x-text="formatRupiah(getAkumulasiSaldo(0))"></td>
                                    <template x-for="t in years" :key="t">
                                        <td class="px-4 py-3 text-right font-mono text-[#FFD54F] font-black border-r border-[#0B5D3D] text-base" x-text="formatRupiah(getAkumulasiSaldo(t))"></td>
                                    </template>
                                </tr>

                            </tbody>
                        </table>
                    </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('adminIproManager', () => ({
            activeTab: 'overview',
            jangkaWaktuTahun: {{ $project->jangka_waktu_tahun ?? 10 }},
            years: [],

            totalCapex: {{ $totalCapex }},

            pendapatanPerTahun: @json($pendapatanPerTahun),
            opexPerTahun: @json($opexPerTahun),

            settings: {
                pl_persentase_pajak_penghasilan: {{ $project->pl_persentase_pajak_penghasilan ?: 22 }},
                pl_nominal_depresiasi: {{ $project->pl_nominal_depresiasi ?: 0 }},
                rasio_modal_sendiri: {{ $project->rasio_modal_sendiri ?: 60 }},
                rasio_pinjaman_kredit: {{ $project->rasio_pinjaman_kredit ?: 40 }},
                suku_bunga_kredit: {{ $project->suku_bunga_kredit ?: 8.05 }},
                tenor_kredit_tahun: {{ $project->tenor_kredit_tahun ?: 5 }},
            },

            rows: [],

            initData() {
                this.years = [];
                for (let i = 1; i <= this.jangkaWaktuTahun; i++) {
                    this.years.push(i);
                }

                // Map data komponen P&L dari Controller
                const rawComponents = @json($plComponentsFormatted);
                let loadedRows = [];
                rawComponents.forEach(comp => {
                    let yearly_data = {};
                    this.years.forEach(y => yearly_data[y] = 0);

                    if (comp.yearly_data) {
                        Object.keys(comp.yearly_data).forEach(yKey => {
                            yearly_data[parseInt(yKey)] = parseFloat(comp.yearly_data[yKey]) || 0;
                        });
                    }

                    loadedRows.push({
                        temp_id: comp.id.toString(),
                        parent_temp_id: comp.parent_id ? comp.parent_id.toString() : null,
                        tipe_kategori: comp.tipe_kategori,
                        nama_komponen: comp.nama_komponen,
                        yearly_data: yearly_data,
                    });
                });

                this.rows = loadedRows;
            },

            formatRupiah(value) {
                if (value === null || value === undefined || isNaN(value)) return 'Rp 0,00';
                const isNegative = value < 0;
                const absVal = Math.abs(value);
                const formatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                }).format(absVal);

                return isNegative ? '-' + formatted : formatted;
            },

            // --- HELPER UNTUK LABA RUGI (P&L) ---
            getParents(tipe) {
                return this.rows.filter(r => r.tipe_kategori === tipe && !r.parent_temp_id);
            },

            getChildren(parentTempId) {
                return this.rows.filter(r => r.parent_temp_id === parentTempId);
            },

            getRowYearlyTotal(tempId, year) {
                const row = this.rows.find(r => r.temp_id === tempId);
                if (!row) return 0;

                const children = this.getChildren(tempId);
                if (children.length === 0) {
                    return parseFloat(row.yearly_data[year]) || 0;
                }

                let sum = 0;
                children.forEach(c => {
                    sum += this.getRowYearlyTotal(c.temp_id, year);
                });
                return sum;
            },

            calculateTotal(tipe, year) {
                const parents = this.getParents(tipe);
                let total = 0;
                parents.forEach(p => {
                    total += this.getRowYearlyTotal(p.temp_id, year);
                });
                return total;
            },

            getEBITDA(year) {
                return this.calculateTotal('PENDAPATAN', year) - this.calculateTotal('BIAYA_OPERASIONAL', year);
            },

            getEBIT(year) {
                return this.getEBITDA(year) - (parseFloat(this.settings.pl_nominal_depresiasi) || 0);
            },

            getBebanBunga(year) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                const rate = (parseFloat(this.settings.suku_bunga_kredit) || 8.05) / 100;

                if (year > tenor || debt <= 0) return 0;
                const sisaPokokAwal = debt - ((year - 1) * (debt / tenor));
                return sisaPokokAwal > 0 ? sisaPokokAwal * rate : 0;
            },

            getEBT(year) {
                return this.getEBIT(year) - this.getBebanBunga(year);
            },

            getPajakPenghasilan(year) {
                const ebt = this.getEBT(year);
                if (ebt <= 0) return 0;
                const pct = (parseFloat(this.settings.pl_persentase_pajak_penghasilan) || 22) / 100;
                return ebt * pct;
            },

            getEAT(year) {
                return this.getEBT(year) - this.getPajakPenghasilan(year);
            },

            // --- HELPER UNTUK ARUS KAS (CASH FLOW) ---
            getEquityAmount() {
                return this.totalCapex * ((parseFloat(this.settings.rasio_modal_sendiri) || 60) / 100);
            },

            getDebtAmount() {
                return this.totalCapex * ((parseFloat(this.settings.rasio_pinjaman_kredit) || 40) / 100);
            },

            getAngsuranPokok(year) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                if (year <= tenor && tenor > 0) {
                    return debt / tenor;
                }
                return 0;
            },

            getTotalKasMasukNonOps(t) {
                if (t === 0) {
                    return this.getEquityAmount() + this.getDebtAmount();
                }
                return 0;
            },

            getOperasionalBersih(year) {
                const inc = parseFloat(this.pendapatanPerTahun[year]) || 0;
                const opex = parseFloat(this.opexPerTahun[year]) || 0;
                return inc - opex;
            },

            getNetCashflow(year) {
                if (year === 0) {
                    return -this.totalCapex;
                }
                const ops = this.getOperasionalBersih(year);
                const pokok = this.getAngsuranPokok(year);
                const bunga = this.getBebanBunga(year);
                return ops - (pokok + bunga);
            },

            getSaldoAwalKas(year) {
                if (year === 1) {
                    return this.getNetCashflow(0);
                }
                return this.getSaldoAkhirKas(year - 1);
            },

            getSaldoAkhirKas(year) {
                if (year === 0) {
                    return this.getNetCashflow(0);
                }
                return this.getSaldoAwalKas(year) + this.getNetCashflow(year);
            },

            getAkumulasiSaldo(t) {
                let cumulative = this.getNetCashflow(0);
                for (let i = 1; i <= t; i++) {
                    cumulative += this.getNetCashflow(i);
                }
                return cumulative;
            }
        }));
    });
</script>
@endsection

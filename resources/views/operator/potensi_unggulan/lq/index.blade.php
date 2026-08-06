@extends('partials.layouts.operator')

@section('title', 'Analisis Location Quotient (LQ)')

@section('content')
<div x-data="{ activeTab: '{{ $editItem ? 'simulasi' : 'real' }}' }" class="space-y-6">

    <!-- Alert Messages -->
    @if (session('success'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            <p class="text-sm font-semibold">{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl flex items-center gap-3 shadow-xs">
            <i class="fa-solid fa-circle-exclamation text-rose-600 text-base"></i>
            <p class="text-sm font-semibold">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Header & Mode Pill Switcher Card (55:45 Ratio & Multi-Line Flexible Buttons) -->
    <div class="bg-white rounded-2xl p-6 border border-[#CFE3D5] shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <!-- Title & Subtitle Section (55% Width) -->
        <div class="w-full lg:w-[55%]">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EEF8F2] text-[#145239] text-xs font-bold mb-2 border border-[#CFE3D5]">
                <i class="fa-solid fa-chart-pie text-[#D8A62A]"></i>
                <span>Analisis Makroekonomi Daerah</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">Analisis Location Quotient (LQ)</h1>
            <p class="text-slate-500 text-xs md:text-sm mt-0.5">Identifikasi Sektor Basis & Non-Basis Wilayah Makroekonomi</p>
        </div>

        <!-- Pill Tabs Switcher (45% Width with Multi-Line Text Wrapping) -->
        <div class="w-full lg:w-[45%] flex justify-start lg:justify-end">
            <div class="inline-flex w-full p-1.5 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-inner">
                <button type="button" @click="activeTab = 'real'" 
                    :class="activeTab === 'real' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-chart-line text-[#FFD54F] shrink-0"></i>
                    <span>Data Real PDRB</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-700/80 text-emerald-100 font-bold shrink-0">Otomatis</span>
                </button>

                <button type="button" @click="activeTab = 'simulasi'" 
                    :class="activeTab === 'simulasi' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-vials text-[#FFD54F] shrink-0"></i>
                    <span>Simulasi & Upload Excel</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] bg-emerald-700/80 text-emerald-100 font-bold shrink-0">Custom</span>
                </button>
            </div>
        </div>
    </div>

    <!-- TAB 1: DATA REAL PDRB (Ringkasan Multi-Tahun Per Daerah) -->
    <div x-show="activeTab === 'real'" x-transition class="space-y-6">

        <!-- Component Filter Bar (Provinsi, Kabupaten, Tahun, Search) -->
        <x-analisa-filter-bar 
            :action="route('operator.lq.index')"
            :provinsis="$provinsis ?? []"
            :kabupatens="$kabupatens ?? []"
            :availableYears="$availableYears ?? []"
            searchPlaceholder="Cari Wilayah atau Tahun..."
        />

        <!-- Table Container -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Ringkasan Analisis LQ Per Wilayah & Tahun</h2>
                    <p class="text-slate-500 text-xs mt-0.5">Prioritas Wilayah Provinsi Ditampilkan Teratas untuk Setiap Tahun</p>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table id="lqTable" class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">No</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">Tingkat Wilayah</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">DAERAH ANALISIS</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">PEMBANDING</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">TAHUN</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">SEKTOR BASIS</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">SEKTOR NON-BASIS</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">STATUS DOMINAN</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($lqData as $index => $data)
                            <tr class="hover:bg-emerald-50/30 transition-colors {{ !empty($data['is_provinsi']) ? 'bg-emerald-50/40 font-semibold' : '' }}">
                                <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-500">{{ ($lqData->currentPage() - 1) * $lqData->perPage() + $loop->iteration }}</td>
                                <td class="px-4 py-3.5">
                                    @if(!empty($data['is_provinsi']) || $data['tingkat_wilayah'] === 'Provinsi')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#145239] text-white border border-[#145239]">
                                            <i class="fa-solid fa-building-columns text-[10px] text-[#FFD54F]"></i> Provinsi
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                            Kabupaten/Kota
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 font-bold text-slate-800">{{ $data['daerah_analisis'] }}</td>
                                <td class="px-4 py-3.5 text-slate-600 font-medium">{{ $data['daerah_pembanding'] }}</td>
                                <td class="px-4 py-3.5 text-center font-mono font-bold">{{ $data['tahun'] }}</td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        {{ $data['sektor_basis_count'] }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        {{ $data['sektor_non_basis_count'] }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-xs text-slate-700">
                                    {{ $data['status_dominan'] }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <a href="{{ route('operator.lq.show', [
                                        'tingkat_wilayah' => $data['tingkat_wilayah'],
                                        'tahun' => $data['tahun'],
                                        'kabupaten_id' => $data['kabupaten_id'] ?? null,
                                        'provinsi_id' => $data['provinsi_id'] ?? null
                                    ]) }}" class="inline-flex items-center gap-1.5 bg-[#145239] hover:bg-[#0F8A5F] text-white px-3 py-1.5 rounded-lg text-xs font-bold transition-all shadow-xs">
                                        <i class="fa-solid fa-list-check text-[#FFD54F]"></i>
                                        <span>Lihat Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-8 text-center text-slate-500 font-medium">
                                    Belum ada data ringkasan LQ yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Smart Pagination Section with Ellipsis (...) -->
            @if ($lqData->total() > 0)
                <footer class="mt-5 flex flex-col gap-4 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="m-0 text-xs text-slate-500">
                        Menampilkan <strong class="text-slate-700">{{ $lqData->firstItem() }}</strong>–<strong class="text-slate-700">{{ $lqData->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($lqData->total(), 0, ',', '.') }}</strong> data
                    </p>

                    @if ($lqData->hasPages())
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Previous Page Link --}}
                            @if ($lqData->onFirstPage())
                                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </span>
                            @else
                                <a href="{{ $lqData->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                    <i class="fa-solid fa-chevron-left text-xs"></i>
                                </a>
                            @endif

                            {{-- Smart Page Elements with Ellipsis (...) --}}
                            @php
                                $currentPage = $lqData->currentPage();
                                $lastPage = $lqData->lastPage();
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
                                    <a href="{{ $lqData->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                                        {{ $page }}
                                    </a>
                                @endif

                                @php
                                    $previousPageNumber = $page;
                                @endphp
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($lqData->hasMorePages())
                                <a href="{{ $lqData->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
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

    <!-- TAB 2: SIMULASI & UPLOAD EXCEL (Custom Mode) -->
    <div x-show="activeTab === 'simulasi'" x-transition class="space-y-6">
        <!-- Info Banner -->
        <div class="p-4 rounded-2xl bg-amber-50/90 border border-amber-200 flex items-start justify-between gap-4 text-slate-700 text-xs md:text-sm">
            <div class="flex items-start gap-3.5">
                <div class="w-8 h-8 rounded-xl bg-amber-600 text-white flex items-center justify-center shrink-0 font-bold mt-0.5">
                    <i class="fa-solid fa-vials text-[#FFD54F]"></i>
                </div>
                <div>
                    <span class="font-bold text-amber-900 block text-sm">Mode Simulasi & Upload Excel (Custom)</span>
                    Gunakan form di bawah ini atau unggah berkas Excel untuk melakukan pengujian skenario custom tanpa mengubah data PDRB resmi.
                </div>
            </div>

            <button type="button" onclick="document.getElementById('importModal').style.display='flex'" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2.5 rounded-xl font-bold transition-all text-xs md:text-sm shadow-sm shrink-0">
                <i class="fa-solid fa-file-excel text-[#FFD54F]"></i>
                <span>Unggah Excel</span>
            </button>
        </div>

        <!-- Form Card Container -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="mb-6 border-b border-slate-100 pb-4">
                <h2 class="text-xl font-bold text-slate-800">{{ $editItem ? 'Edit Data Simulasi LQ' : 'Tambah Simulasi LQ Baru' }}</h2>
                <p class="text-slate-500 text-xs mt-0.5">Masukkan variabel nilai PDRB custom untuk diuji</p>
            </div>

            <form action="{{ $editItem ? route('operator.lq.update', $editItem['id']) : route('operator.lq.store') }}" method="POST" x-data="{ 
                tingkat_wilayah: '{{ old('tingkat_wilayah', $editItem['tingkat_wilayah'] ?? 'Kabupaten/Kota') }}',
                provinsi: '{{ old('provinsi', $editItem['provinsi'] ?? '') }}',
                get listKabupaten() {
                    return window.daftarWilayah[this.provinsi] || [];
                }
            }">
                @csrf
                @if($editItem)
                    @method('PUT')
                @endif
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 md:gap-6 mb-6">
                    <!-- Tingkat Wilayah -->
                    <div class="space-y-2 md:col-span-1">
                        <label class="op-label">Tingkat Wilayah</label>
                        <div class="relative">
                            <select name="tingkat_wilayah" x-model="tingkat_wilayah" class="op-input op-input-icon op-select" required>
                                <option value="Kabupaten/Kota">Kabupaten/Kota</option>
                                <option value="Provinsi">Provinsi</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>
                    <!-- Sektor -->
                    <div class="space-y-2 md:col-span-1">
                        <label class="op-label">Sektor</label>
                        <div class="relative">
                            <input list="sektor-list" name="sektor" value="{{ old('sektor', $editItem['sektor'] ?? '') }}" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Sektor" required>
                            <datalist id="sektor-list">
                                <option value="PERTANIAN, KEHUTANAN, DAN PERIKANAN">
                                <option value="PERTAMBANGAN DAN PENGGALIAN">
                                <option value="INDUSTRI PENGOLAHAN">
                                <option value="PENGADAAN LISTRIK DAN GAS">
                                <option value="PENGADAAN AIR, PENGELOLAAN SAMPAH, LIMBAH DAN DAUR ULANG">
                                <option value="KONSTRUKSI">
                                <option value="PERDAGANGAN BESAR DAN ECERAN">
                                <option value="TRANSPORTASI DAN PERGUDANGAN">
                                <option value="PENYEDIAAN AKOMODASI DAN MAKAN MINUM">
                                <option value="INFORMASI DAN KOMUNIKASI">
                                <option value="JASA KEUANGAN DAN ASURANSI">
                                <option value="REAL ESTAT">
                                <option value="JASA PERUSAHAAN">
                                <option value="ADMINISTRASI PEMERINTAHAN, PERTAHANAN DAN JAMINAN SOSIAL WAJIB">
                                <option value="JASA PENDIDIKAN">
                                <option value="JASA KESEHATAN DAN KEGIATAN SOSIAL">
                                <option value="JASA LAINNYA">
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Tahun -->
                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Tahun</label>
                        <input type="number" name="tahun" value="{{ old('tahun', $editItem['tahun'] ?? '') }}" class="op-input" placeholder="Contoh: 2024" required>
                    </div>
                    <!-- Provinsi -->
                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Provinsi</label>
                        <div class="relative">
                            <input list="provinsi-list" name="provinsi" x-model="provinsi" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Provinsi" required>
                            <datalist id="provinsi-list">
                                <template x-for="prov in Object.keys(window.daftarWilayah)" :key="prov">
                                    <option :value="prov"></option>
                                </template>
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                    <!-- Kabupaten / Kota -->
                    <div class="space-y-2 col-span-1" x-show="tingkat_wilayah === 'Kabupaten/Kota'">
                        <label class="op-label">Kabupaten / Kota</label>
                        <div class="relative">
                            <input list="kabupaten-list" name="kabupaten" value="{{ old('kabupaten', $editItem['kabupaten'] ?? '') }}" :required="tingkat_wilayah === 'Kabupaten/Kota'" autocomplete="off" class="op-input op-input-icon op-datalist" placeholder="Pilih atau ketik Kab/Kota">
                            <datalist id="kabupaten-list">
                                <template x-for="kab in listKabupaten" :key="kab">
                                    <option :value="kab"></option>
                                </template>
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_analisis', $editItem['pdrb_sektor_analisis'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'PDRB Sektor Kabupaten' : 'PDRB Sektor Provinsi'">PDRB Sektor Analisis</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 50.000" required>
                        <input type="hidden" name="pdrb_sektor_analisis" :value="val.replace(/\./g, '')">
                    </div>
                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('total_pdrb_analisis', $editItem['total_pdrb_analisis'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'Total PDRB Kabupaten' : 'Total PDRB Provinsi'">Total PDRB Analisis</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 50.000" required>
                        <input type="hidden" name="total_pdrb_analisis" :value="val.replace(/\./g, '')">
                    </div>
                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_pembanding', $editItem['pdrb_sektor_pembanding'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'PDRB Sektor Provinsi' : 'PDB Sektor Nasional'">PDRB Sektor Pembanding</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 50.000" required>
                        <input type="hidden" name="pdrb_sektor_pembanding" :value="val.replace(/\./g, '')">
                    </div>
                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('total_pdrb_pembanding', $editItem['total_pdrb_pembanding'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'Total PDRB Provinsi' : 'Total PDB Nasional'">Total PDRB Pembanding</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 50.000" required>
                        <input type="hidden" name="total_pdrb_pembanding" :value="val.replace(/\./g, '')">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk text-[#FFD54F]"></i>
                        <span>{{ $editItem ? 'Perbarui Data Simulasi' : 'Simpan Data Simulasi' }}</span>
                    </button>
                    @if($editItem)
                        <a href="{{ route('operator.lq.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Batal Edit</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Saved Simulations Action Toolbar -->
        <div class="flex flex-col md:flex-row justify-end items-center gap-3">
            <button type="button" onclick="exportToExcel()" class="flex items-center justify-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all shadow-xs w-full sm:w-auto">
                <i class="fa-solid fa-file-excel text-[#FFD54F]"></i>
                <span>Unduh Hasil Analisis (Excel)</span>
            </button>

            <form action="{{ route('operator.lq.empty') }}" method="POST" onsubmit="return confirmDeleteAll(event, this);" class="w-full sm:w-auto">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-4 py-2.5 rounded-xl text-xs md:text-sm font-bold transition-all shadow-xs">
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Hapus Semua Simulasi</span>
                </button>
            </form>
        </div>
    </div>

</div>

<x-import-modal action="{{ route('operator.lq.import') }}" type="lq" />

<script>
    function exportToExcel() {
        var table = document.getElementById("lqTable");
        var clone = table.cloneNode(true);
        
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            if(rows[i].cells.length > 0) {
                rows[i].deleteCell(-1); 
            }
        }
        
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Ringkasan LQ"});
        XLSX.writeFile(wb, "Hasil_Ringkasan_Analisis_LQ.xlsx");
    }
</script>
@endsection

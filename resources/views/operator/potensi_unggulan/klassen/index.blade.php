@extends('partials.layouts.operator')

@section('title', 'Analisis Klassen')

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

    <!-- Header & Mode Pill Switcher Card (Exact 55:45 Ratio & Multi-Line Flexible Buttons) -->
    <div class="bg-[#FFFFFF] rounded-2xl p-6 border border-[#CFE3D5] shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <!-- Title & Subtitle Section (55% Width) -->
        <div class="w-full lg:w-[55%]">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EEF8F2] text-[#145239] text-xs font-bold mb-2 border border-[#CFE3D5]">
                <i class="fa-solid fa-chart-line text-[#D8A62A]"></i>
                <span>Analisis Pertumbuhan & Kontribusi Sektor</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">Analisis Tipologi Klassen</h1>
            <p class="text-slate-500 text-xs md:text-sm mt-0.5">Klasifikasi Sektor Wilayah Berdasarkan Laju Pertumbuhan & Kontribusi PDRB</p>
        </div>

        <!-- Pill Tabs Switcher (45% Width with Multi-Line Text Wrapping) -->
        <div class="w-full lg:w-[45%] flex justify-start lg:justify-end">
            <div class="inline-flex w-full p-1.5 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-inner">
                <button type="button" @click="activeTab = 'real'" 
                    :class="activeTab === 'real' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-chart-pie text-[#FFD54F] shrink-0"></i>
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
            :action="route('operator.klassen.index')"
            :provinsis="$provinsis ?? []"
            :kabupatens="$kabupatens ?? []"
            :availableYears="$availableYears ?? []"
            searchPlaceholder="Cari Wilayah atau Tahun..."
        />

        <!-- Table Container -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Ringkasan Tipologi Klassen Per Wilayah & Periode</h2>
                    <p class="text-slate-500 text-xs mt-0.5">Prioritas Wilayah Provinsi Ditampilkan Teratas untuk Setiap Tahun</p>
                </div>
            </div>

            <div class="overflow-x-auto border border-slate-200/80 rounded-xl">
                <table id="klassenTable" class="w-full text-left border-collapse">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 text-xs font-bold uppercase tracking-wider">
                        <tr>
                            <th class="px-4 py-3.5 w-12 text-center">No</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">Tingkat Wilayah</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">DAERAH ANALISIS</th>
                            <th class="px-4 py-3.5 whitespace-nowrap">PEMBANDING</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">PERIODE TAHUN</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">KUADRAN I</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">KUADRAN II</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">KUADRAN III</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">KUADRAN IV</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">KLASIFIKASI DOMINAN</th>
                            <th class="px-4 py-3.5 text-center whitespace-nowrap">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
                        @forelse($klassenData as $index => $data)
                            <tr class="hover:bg-emerald-50/30 transition-colors {{ !empty($data['is_provinsi']) ? 'bg-emerald-50/40 font-semibold' : '' }}">
                                <td class="px-4 py-3.5 text-center font-mono font-semibold text-slate-500">{{ ($klassenData->currentPage() - 1) * $klassenData->perPage() + $loop->iteration }}</td>
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
                                        {{ $data['c1_count'] ?? 0 }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        {{ $data['c2_count'] ?? 0 }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-orange-100 text-orange-800 border border-orange-200">
                                        {{ $data['c3_count'] ?? 0 }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        {{ $data['c4_count'] ?? 0 }} Sektor
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-center font-bold text-xs text-slate-700">
                                    {{ $data['status_dominan'] }}
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    <a href="{{ route('operator.klassen.show', [
                                        'tingkat_wilayah' => $data['tingkat_wilayah'],
                                        'tahun' => $data['tahun_akhir'] ?? 2024,
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
                                <td colspan="11" class="px-4 py-8 text-center text-slate-500 font-medium">
                                    Belum ada data ringkasan Tipologi Klassen yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Component -->
            <x-pagination :paginator="$klassenData" />
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
                    Gunakan form di bawah ini atau unggah berkas Excel untuk melakukan pengujian skenario Tipologi Klassen custom tanpa mengubah data PDRB resmi.
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
                <h2 class="text-xl font-bold text-slate-800">{{ $editItem ? 'Edit Data Simulasi Klassen' : 'Tambah Simulasi Klassen Baru' }}</h2>
                <p class="text-slate-500 text-xs mt-0.5">Masukkan variabel nilai PDRB multi-tahun custom untuk diuji</p>
            </div>

            <form action="{{ $editItem ? route('operator.klassen.update', $editItem['id']) : route('operator.klassen.store') }}" method="POST" class="space-y-6" x-data="{ 
                tingkat_wilayah: '{{ old('tingkat_wilayah', $editItem['tingkat_wilayah'] ?? 'Kabupaten/Kota') }}',
                provinsi: '{{ old('provinsi', $editItem['provinsi'] ?? '') }}',
                get listKabupaten() {
                    return window.daftarWilayah[this.provinsi] || [];
                },
                years: [
                    { 
                        tahun: '{{ old('tahun_awal', $editItem['tahun_awal'] ?? '') }}', 
                        pdrb_sektor_analisis: '{{ old('pdrb_sektor_analisis_awal', $editItem['pdrb_sektor_analisis_awal'] ?? '') }}'.split('.')[0], 
                        total_pdrb_analisis: '{{ old('total_pdrb_analisis_awal', $editItem['total_pdrb_analisis_awal'] ?? '') }}'.split('.')[0],
                        pdrb_sektor_pembanding: '{{ old('pdrb_sektor_pembanding_awal', $editItem['pdrb_sektor_pembanding_awal'] ?? '') }}'.split('.')[0], 
                        total_pdrb_pembanding: '{{ old('total_pdrb_pembanding_awal', $editItem['total_pdrb_pembanding_awal'] ?? '') }}'.split('.')[0],
                        pdrb_sektor_analisis_fmt: '', total_pdrb_analisis_fmt: '', pdrb_sektor_pembanding_fmt: '', total_pdrb_pembanding_fmt: ''
                    },
                    { 
                        tahun: '{{ old('tahun_akhir', $editItem['tahun_akhir'] ?? '') }}', 
                        pdrb_sektor_analisis: '{{ old('pdrb_sektor_analisis_akhir', $editItem['pdrb_sektor_analisis_akhir'] ?? '') }}'.split('.')[0], 
                        total_pdrb_analisis: '{{ old('total_pdrb_analisis_akhir', $editItem['total_pdrb_analisis_akhir'] ?? '') }}'.split('.')[0],
                        pdrb_sektor_pembanding: '{{ old('pdrb_sektor_pembanding_akhir', $editItem['pdrb_sektor_pembanding_akhir'] ?? '') }}'.split('.')[0], 
                        total_pdrb_pembanding: '{{ old('total_pdrb_pembanding_akhir', $editItem['total_pdrb_pembanding_akhir'] ?? '') }}'.split('.')[0],
                        pdrb_sektor_analisis_fmt: '', total_pdrb_analisis_fmt: '', pdrb_sektor_pembanding_fmt: '', total_pdrb_pembanding_fmt: ''
                    }
                ],
                format(v) { 
                    if (v === undefined || v === null || v === '') return '';
                    let raw = v.toString().replace(/[^0-9]/g, ''); 
                    return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); 
                },
                addYear() {
                    this.years.push({ tahun: '', pdrb_sektor_analisis: '', total_pdrb_analisis: '', pdrb_sektor_pembanding: '', total_pdrb_pembanding: '', pdrb_sektor_analisis_fmt: '', total_pdrb_analisis_fmt: '', pdrb_sektor_pembanding_fmt: '', total_pdrb_pembanding_fmt: '' });
                },
                removeYear(index) {
                    if (this.years.length > 2) {
                        this.years.splice(index, 1);
                    }
                }
            }">
                @csrf
                @if($editItem)
                    @method('PUT')
                @endif
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 md:gap-6 mb-6">
                    <div class="space-y-2 col-span-1">
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

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Sektor</label>
                        <div class="relative">
                            <input list="sektor-list" name="sektor" value="{{ old('sektor', $editItem['sektor'] ?? '') }}" class="op-input op-input-icon op-datalist" placeholder="Pilih Sektor" required>
                            <datalist id="sektor-list">
                                <option value="PERTANIAN, KEHUTANAN, DAN PERIKANAN">
                                <option value="PERTAMBANGAN DAN PENGGALIAN">
                                <option value="INDUSTRI PENGOLAHAN">
                                <option value="PENGADAAN LISTRIK DAN GAS">
                                <option value="KONSTRUKSI">
                                <option value="PERDAGANGAN BESAR DAN ECERAN">
                                <option value="TRANSPORTASI DAN PERGUDANGAN">
                                <option value="INFORMASI DAN KOMUNIKASI">
                                <option value="JASA KEUANGAN DAN ASURANSI">
                            </datalist>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

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
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

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
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multi-Year Dynamic Input Cards -->
                <template x-for="(year, index) in years" :key="index">
                    <div class="mb-4 border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                        <div class="flex flex-wrap justify-between items-center mb-4 gap-2">
                            <div class="flex items-center gap-3">
                                <span class="bg-[#145239] text-white px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap" x-text="'Data Tahun ' + (index + 1)"></span>
                                <input type="number" name="tahun[]" x-model="year.tahun" min="1900" max="2100" class="op-input !w-32 !py-1 !text-sm" placeholder="Tahun" required>
                            </div>
                            <button type="button" @click="removeYear(index)" x-show="years.length > 2" class="text-red-500 hover:text-red-700 p-1.5 bg-red-100 rounded-md transition-colors" title="Hapus Tahun">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <div class="bg-white rounded-lg border border-sky-200 p-4 shadow-xs">
                                <h4 class="text-sm font-bold text-slate-800 mb-3" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'PDRB Analisis (Kab/Kota)' : 'PDRB Analisis (Provinsi)'"></h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-600">Sektor (Rp)</label>
                                        <input type="text" x-model="year.pdrb_sektor_analisis_fmt" @input="year.pdrb_sektor_analisis_fmt = format($event.target.value); year.pdrb_sektor_analisis = year.pdrb_sektor_analisis_fmt.replace(/\./g, '')" x-init="year.pdrb_sektor_analisis_fmt = format(year.pdrb_sektor_analisis)" class="op-input !py-1.5 !text-sm" placeholder="1.000.000" required>
                                        <input type="hidden" name="pdrb_sektor_analisis[]" :value="year.pdrb_sektor_analisis">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-600">Total (Rp)</label>
                                        <input type="text" x-model="year.total_pdrb_analisis_fmt" @input="year.total_pdrb_analisis_fmt = format($event.target.value); year.total_pdrb_analisis = year.total_pdrb_analisis_fmt.replace(/\./g, '')" x-init="year.total_pdrb_analisis_fmt = format(year.total_pdrb_analisis)" class="op-input !py-1.5 !text-sm" placeholder="1.000.000" required>
                                        <input type="hidden" name="total_pdrb_analisis[]" :value="year.total_pdrb_analisis">
                                    </div>
                                </div>
                            </div>

                            <div class="bg-white rounded-lg border border-sky-200 p-4 shadow-xs">
                                <h4 class="text-sm font-bold text-slate-800 mb-3" x-text="tingkat_wilayah === 'Kabupaten/Kota' ? 'PDRB Pembanding (Provinsi)' : 'PDB Pembanding (Nasional)'"></h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-600">Sektor (Rp)</label>
                                        <input type="text" x-model="year.pdrb_sektor_pembanding_fmt" @input="year.pdrb_sektor_pembanding_fmt = format($event.target.value); year.pdrb_sektor_pembanding = year.pdrb_sektor_pembanding_fmt.replace(/\./g, '')" x-init="year.pdrb_sektor_pembanding_fmt = format(year.pdrb_sektor_pembanding)" class="op-input !py-1.5 !text-sm" placeholder="1.000.000" required>
                                        <input type="hidden" name="pdrb_sektor_pembanding[]" :value="year.pdrb_sektor_pembanding">
                                    </div>
                                    <div class="space-y-1">
                                        <label class="text-xs font-semibold text-slate-600">Total (Rp)</label>
                                        <input type="text" x-model="year.total_pdrb_pembanding_fmt" @input="year.total_pdrb_pembanding_fmt = format($event.target.value); year.total_pdrb_pembanding = year.total_pdrb_pembanding_fmt.replace(/\./g, '')" x-init="year.total_pdrb_pembanding_fmt = format(year.total_pdrb_pembanding)" class="op-input !py-1.5 !text-sm" placeholder="1.000.000" required>
                                        <input type="hidden" name="total_pdrb_pembanding[]" :value="year.total_pdrb_pembanding">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div class="flex justify-center mb-6">
                    <button type="button" @click="addYear()" class="flex items-center gap-2 bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-full text-sm font-bold transition-colors border border-slate-300">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Tahun</span>
                    </button>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk text-[#FFD54F]"></i>
                        <span>{{ $editItem ? 'Perbarui Data Simulasi' : 'Simpan Data Simulasi' }}</span>
                    </button>
                    @if($editItem)
                        <a href="{{ route('operator.klassen.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Batal Edit</a>
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

            <form action="{{ route('operator.klassen.empty') }}" method="POST" onsubmit="return confirmDeleteAll(event, this);" class="w-full sm:w-auto">
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

<x-import-modal action="{{ route('operator.klassen.import') }}" type="klassen" />

<script>
    function exportToExcel() {
        var table = document.getElementById("klassenTable");
        var clone = table.cloneNode(true);
        
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            if(rows[i].cells.length > 0) {
                rows[i].deleteCell(-1); 
            }
        }
        
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Ringkasan Klassen"});
        XLSX.writeFile(wb, "Hasil_Ringkasan_Analisis_Klassen.xlsx");
    }
</script>
@endsection

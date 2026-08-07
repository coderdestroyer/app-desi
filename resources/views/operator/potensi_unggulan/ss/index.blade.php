@extends('partials.layouts.operator')

@section('title', 'Analisis Shift-Share (SS)')

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
                <i class="fa-solid fa-chart-line text-[#D8A62A]"></i>
                <span>Analisis Pertumbuhan & Daya Saing</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">Analisis Shift-Share (SS)</h1>
            <p class="text-slate-500 text-xs md:text-sm mt-0.5">Analisis Komponen Pertumbuhan Nasional, Proproporsional & Keunggulan Kompetitif Sektor</p>
        </div>

        <!-- Pill Tabs Switcher (45% Width with Multi-Line Text Wrapping) -->
        <div class="w-full lg:w-[45%] flex justify-start lg:justify-end">
            <div class="inline-flex w-full p-1.5 bg-slate-100/90 rounded-xl border border-slate-200/90 shadow-inner">
                <button type="button" @click="activeTab = 'real'" 
                    :class="activeTab === 'real' ? 'bg-[#145239] text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                    class="flex-1 inline-flex flex-wrap items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg font-bold text-xs text-center leading-tight transition-all duration-200 cursor-pointer">
                    <i class="fa-solid fa-chart-column text-[#FFD54F] shrink-0"></i>
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
            :action="route('operator.ss.index')"
            :provinsis="$provinsis ?? []"
            :kabupatens="$kabupatens ?? []"
            :availableYears="$availableYears ?? []"
            searchPlaceholder="Cari Wilayah atau Tahun..."
        />

        <!-- Table Container -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-6">
            <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Ringkasan Analisis Shift-Share Per Wilayah & Periode</h2>
                    <p class="text-slate-500 text-xs mt-0.5">Prioritas Wilayah Provinsi Ditampilkan Teratas untuk Setiap Tahun</p>
                </div>
            </div>

            <div id="tableContainer" class="transition-opacity duration-200">
                @include('operator.potensi_unggulan.ss.partials.table')
            </div>
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
                    Gunakan form di bawah ini atau unggah berkas Excel untuk melakukan pengujian skenario Shift-Share custom tanpa mengubah data PDRB resmi.
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
                <h2 class="text-xl font-bold text-slate-800">{{ $editItem ? 'Edit Data Simulasi Shift-Share' : 'Tambah Simulasi Shift-Share Baru' }}</h2>
                <p class="text-slate-500 text-xs mt-0.5">Masukkan variabel nilai PDRB tahun awal & akhir custom untuk diuji</p>
            </div>

            <form action="{{ $editItem ? route('operator.ss.update', $editItem['id']) : route('operator.ss.store') }}" method="POST" x-data="{ 
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
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 md:gap-6 mb-6">
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
                            <select name="sektor" class="op-input op-input-icon op-select" required>
                                <option value="">Pilih Sektor</option>
                                @php
                                    $sectors = [
                                        "PERTANIAN, KEHUTANAN, DAN PERIKANAN", "PERTAMBANGAN DAN PENGGALIAN", "INDUSTRI PENGOLAHAN",
                                        "PENGADAAN LISTRIK DAN GAS", "PENGADAAN AIR, PENGELOLAAN SAMPAH, LIMBAH DAN DAUR ULANG",
                                        "KONSTRUKSI", "PERDAGANGAN BESAR DAN ECERAN; REPARASI MOBIL DAN SEPEDA MOTOR", "TRANSPORTASI DAN PERGUDANGAN",
                                        "PENYEDIAAN AKOMODASI DAN MAKAN MINUM", "INFORMASI DAN KOMUNIKASI", "JASA KEUANGAN DAN ASURANSI",
                                        "REAL ESTATE", "JASA PERUSAHAAN", "ADMINISTRASI PEMERINTAHAN, PERTAHANAN DAN JAMINAN SOSIAL WAJIB",
                                        "JASA PENDIDIKAN", "JASA KESEHATAN DAN KEGIATAN SOSIAL", "JASA LAINNYA"
                                    ];
                                @endphp
                                @foreach($sectors as $sector)
                                    <option value="{{ $sector }}" {{ old('sektor', $editItem['sektor'] ?? '') == $sector ? 'selected' : '' }}>{{ $sector }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4">
                                <svg class="w-4 h-4 text-slate-600 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Tahun Awal</label>
                        <input type="number" name="tahun_awal" value="{{ old('tahun_awal', $editItem['tahun_awal'] ?? '2021') }}" class="op-input" placeholder="Contoh: 2021" required>
                    </div>

                    <div class="space-y-2 col-span-1">
                        <label class="op-label">Tahun Akhir</label>
                        <input type="number" name="tahun_akhir" value="{{ old('tahun_akhir', $editItem['tahun_akhir'] ?? '2022') }}" class="op-input" placeholder="Contoh: 2022" required>
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

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-6">
                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_analisis_awal', $editItem['pdrb_sektor_analisis_awal'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">PDRB Sektor Analisis (Awal)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 100.000" required>
                        <input type="hidden" name="pdrb_sektor_analisis_awal" :value="val.replace(/\./g, '')">
                    </div>

                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_analisis_akhir', $editItem['pdrb_sektor_analisis_akhir'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">PDRB Sektor Analisis (Akhir)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 120.000" required>
                        <input type="hidden" name="pdrb_sektor_analisis_akhir" :value="val.replace(/\./g, '')">
                    </div>

                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_pembanding_awal', $editItem['pdrb_sektor_pembanding_awal'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">PDRB Sektor Pembanding (Awal)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 500.000" required>
                        <input type="hidden" name="pdrb_sektor_pembanding_awal" :value="val.replace(/\./g, '')">
                    </div>

                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('pdrb_sektor_pembanding_akhir', $editItem['pdrb_sektor_pembanding_akhir'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">PDRB Sektor Pembanding (Akhir)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 550.000" required>
                        <input type="hidden" name="total_pdrb_pembanding_akhir" :value="val.replace(/\./g, '')">
                    </div>

                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('total_pdrb_pembanding_awal', $editItem['total_pdrb_pembanding_awal'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">Total PDRB Pembanding (Awal)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 5.000.000" required>
                        <input type="hidden" name="total_pdrb_pembanding_awal" :value="val.replace(/\./g, '')">
                    </div>

                    <div class="space-y-2 col-span-1" x-data="{ val: '{{ old('total_pdrb_pembanding_akhir', $editItem['total_pdrb_pembanding_akhir'] ?? '') }}'.split('.')[0], format(v) { let raw = v.toString().replace(/[^0-9]/g, ''); return raw.replace(/\B(?=(\d{3})+(?!\d))/g, '.'); } }" x-init="val = format(val)">
                        <label class="op-label">Total PDRB Pembanding (Akhir)</label>
                        <input type="text" x-model="val" @input="val = format($event.target.value)" class="op-input" placeholder="Contoh: 5.500.000" required>
                        <input type="hidden" name="total_pdrb_pembanding_akhir" :value="val.replace(/\./g, '')">
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit" class="flex items-center gap-2 bg-[#145239] hover:bg-[#0F8A5F] text-white px-5 py-2.5 rounded-xl font-bold text-sm transition-all shadow-sm">
                        <i class="fa-solid fa-floppy-disk text-[#FFD54F]"></i>
                        <span>{{ $editItem ? 'Perbarui Data Simulasi' : 'Simpan Data Simulasi' }}</span>
                    </button>
                    @if($editItem)
                        <a href="{{ route('operator.ss.index') }}" class="px-5 py-2.5 rounded-xl text-sm font-bold bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">Batal Edit</a>
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

            <form action="{{ route('operator.ss.empty') }}" method="POST" onsubmit="return confirmDeleteAll(event, this);" class="w-full sm:w-auto">
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

<x-import-modal action="{{ route('operator.ss.import') }}" type="ss" />

<script>
    function exportToExcel() {
        var table = document.getElementById("ssTable");
        var clone = table.cloneNode(true);
        
        var rows = clone.rows;
        for (var i = 0; i < rows.length; i++) {
            if(rows[i].cells.length > 0) {
                rows[i].deleteCell(-1); 
            }
        }
        
        var wb = XLSX.utils.table_to_book(clone, {sheet: "Ringkasan Shift-Share"});
        XLSX.writeFile(wb, "Hasil_Ringkasan_Analisis_Shift_Share.xlsx");
    }
</script>

@include('partials.live-filter-script')
@endsection

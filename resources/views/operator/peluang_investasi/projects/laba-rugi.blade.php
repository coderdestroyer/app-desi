@extends('partials.layouts.operator')

@section('title', 'Proyeksi Laba Rugi - ' . $project->nama_proyek)
@section('page_heading', 'Proyeksi Laba Rugi Proyek')

@section('content')
<div x-data="labaRugiManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3.5 sm:gap-4">
        <nav class="flex flex-wrap text-xs sm:text-sm text-slate-500 gap-1.5 items-center">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-[#145239] font-medium truncate max-w-[150px] sm:max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Proyeksi Laba Rugi</span>
        </nav>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <!-- Indicator Autosave & Countdown -->
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all shadow-2xs" :class="{
                'bg-slate-100 text-slate-600 border border-slate-200': autoSaveStatus === 'saving',
                'bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5]': autoSaveStatus === 'saved',
                'bg-amber-50 text-amber-700 border border-amber-200': autoSaveStatus === 'draft',
                'bg-slate-50 text-slate-600 border border-slate-200': !autoSaveStatus
            }">
                <template x-if="autoSaveStatus === 'saving'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-spinner animate-spin text-[#145239]"></i>
                        <span>Menyimpan otomatis...</span>
                    </span>
                </template>
                <template x-if="autoSaveStatus === 'saved'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up text-[#145239]"></i>
                        <span x-text="'Tersimpan ' + (lastSavedTime ? lastSavedTime + ' • ' : '') + 'Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
                <template x-if="autoSaveStatus === 'draft'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-amber-600"></i>
                        <span x-text="'Draft lokal • Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
                <template x-if="!autoSaveStatus">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-clock text-slate-500"></i>
                        <span x-text="'Autosave DB (' + formattedCountdown + ')'"></span>
                    </span>
                </template>
            </div>

            <button @click="toggleMode()" class="flex-1 sm:flex-initial px-4 py-2 border border-[#CFE3D5] bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold shadow-xs transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid" :class="isPreviewMode ? 'fa-pen-to-square' : 'fa-eye'"></i>
                <span x-text="isPreviewMode ? 'Edit Data' : 'Pratinjau'"></span>
            </button>

            <button @click="saveData()" :disabled="isSaving" class="flex-1 sm:flex-initial bg-[#145239] hover:bg-[#0B5D3D] text-white px-5 py-2 rounded-xl text-xs sm:text-sm font-bold shadow-md transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed gap-2">
                <i x-show="isSaving" class="fa-solid fa-spinner animate-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span>Simpan</span>
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="toast.show" x-transition class="p-4 rounded-xl shadow-xs flex items-center justify-between border" :class="toast.isSuccess ? 'bg-[#E7F2EB] border-[#CFE3D5] text-[#145239]' : 'bg-rose-50 border-rose-200 text-rose-800'">
        <div class="flex items-center gap-3">
            <i class="fa-solid text-lg" :class="toast.isSuccess ? 'fa-circle-check text-[#145239]' : 'fa-triangle-exclamation text-rose-500'"></i>
            <span class="text-xs sm:text-sm font-semibold" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="bg-white rounded-2xl shadow-xs border border-[#CFE3D5] p-4 sm:p-5 md:p-6 overflow-hidden">
        
        <!-- Table Title and Desc -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Proyeksi Laba Rugi (Profit & Loss)</h2>
                <p class="text-xs text-slate-500 mt-1">Buat struktur kategori Anda sendiri secara dinamis hingga 3 level turunan.</p>
            </div>
            
            <button @click="isSettingsOpen = !isSettingsOpen" class="w-full md:w-auto inline-flex items-center justify-center text-xs font-semibold px-3.5 py-2 bg-[#E7F2EB] hover:bg-[#CFE3D5] text-[#145239] rounded-xl transition-colors border border-[#CFE3D5] shadow-xs gap-2">
                <i class="fa-solid fa-sliders text-xs"></i>
                <span x-text="isSettingsOpen ? 'Tutup Pengaturan Variabel' : 'Pengaturan Variabel'"></span>
            </button>
        </div>

        <!-- Pengaturan Variabel P&L Card -->
        <div x-show="isSettingsOpen" x-transition class="bg-[#F7FAF8] rounded-2xl border border-[#CFE3D5] p-4 sm:p-5 mb-6 shadow-xs">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-slate-100 pb-3 mb-4 gap-3">
                <div>
                    <h3 class="text-xs sm:text-sm font-bold text-slate-800">Variabel Pengaturan Laba Rugi</h3>
                    <p class="text-[11px] text-slate-500">Nilai statis di bawah ini digunakan khusus untuk kalkulasi EBIT, EBT, dan EAT di akhir tabel.</p>
                </div>
                <button @click="saveSettings()" :disabled="isSavingSettings" class="w-full sm:w-auto bg-[#145239] hover:bg-[#0B5D3D] text-white px-4 py-2 sm:py-1.5 rounded-xl text-xs font-bold shadow-md transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed gap-2 shrink-0">
                    <i x-show="isSavingSettings" class="fa-solid fa-spinner animate-spin"></i>
                    <i x-show="!isSavingSettings" class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Depresiasi (Nominal per Tahun)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 text-xs text-slate-500 font-bold">Rp</span>
                        <input type="text" 
                               :value="formatRupiah(settings.pl_nominal_depresiasi)" 
                               @input="let raw = $event.target.value.replace(/[^0-9]/g, ''); settings.pl_nominal_depresiasi = raw ? parseFloat(raw) : 0; $event.target.value = formatRupiah(settings.pl_nominal_depresiasi);" 
                               class="w-full bg-white border border-slate-200 rounded-lg pl-8 pr-2.5 py-1.5 text-xs focus:border-[#145239] focus:ring-[#145239] font-mono shadow-xs" 
                               placeholder="0">
                    </div>
                    <p class="text-[10px] text-[#145239] mt-1 font-medium"><i class="fa-solid fa-calculator mr-1"></i>Otomatis: (Total CAPEX - Persiapan - Fasilitas Pendukung) / {{ $project->jangka_waktu_tahun }} Tahun</p>                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Suku Bunga Pinjaman (% / Tahun)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" x-model.number="settings.suku_bunga_kredit" @input="if(settings.suku_bunga_kredit < 0) settings.suku_bunga_kredit = 0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-7 text-xs focus:border-[#145239] focus:ring-[#145239] font-mono shadow-xs" placeholder="8.05">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-xs text-slate-400 font-bold">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pajak PPh (% dari EBT)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" x-model.number="settings.pl_persentase_pajak_penghasilan" @input="if(settings.pl_persentase_pajak_penghasilan < 0) settings.pl_persentase_pajak_penghasilan = 0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-7 text-xs focus:border-[#145239] focus:ring-[#145239] font-mono shadow-xs" placeholder="22">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-xs text-slate-400 font-bold">%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Tambah Dinamis -->
        <div x-show="!isPreviewMode" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 mb-4">
            <button @click="addParent('PENDAPATAN')" class="w-full sm:w-auto px-4 py-2.5 bg-[#E7F2EB] border border-[#CFE3D5] hover:bg-[#CFE3D5] text-[#145239] rounded-xl text-xs font-bold transition-colors shadow-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Kategori Pendapatan</span>
            </button>
            <button @click="addParent('BIAYA_OPERASIONAL')" class="w-full sm:w-auto px-4 py-2.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-800 rounded-xl text-xs font-bold transition-colors shadow-xs flex items-center justify-center gap-2">
                <i class="fa-solid fa-plus text-xs"></i>
                <span>Kategori Biaya Operasional</span>
            </button>
        </div>

        <!-- Tabel Responsive -->
        <div class="w-full overflow-x-auto border border-[#CFE3D5] rounded-xl pb-10">
            <table class="w-full min-w-[900px] text-xs sm:text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[240px] sm:min-w-[320px] md:min-w-[380px] sm:sticky sm:left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] shadow-[2px_0_5px_rgba(0,0,0,0.15)]">Kategori / Komponen</th>
                        <template x-for="year in years" :key="year">
                            <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[160px] border-r border-[#0B5D3D] whitespace-nowrap" x-text="'Tahun Ke-' + year"></th>
                        </template>
                    </tr>
                </thead>
                
                <!-- ================= 1. BLOK PENDAPATAN ================= -->
                <tbody x-show="getParents('PENDAPATAN').length > 0">
                    <tr class="bg-[#E7F2EB] text-[#145239] font-bold">
                        <td class="px-4 py-2.5 sm:sticky sm:left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase tracking-wider text-xs">A. PENDAPATAN</td>
                        <td :colspan="years.length" class="bg-[#E7F2EB]"></td>
                    </tr>
                </tbody>

                <template x-for="parent in getParents('PENDAPATAN')" :key="parent.temp_id">
                    <tbody class="border-b border-slate-300">
                        <!-- Level 1 -->
                        <tr class="bg-slate-100">
                            <td class="px-4 py-2 text-slate-800 font-bold sm:sticky sm:left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                <div x-show="!isPreviewMode" class="flex items-center space-x-2">
                                    <input type="text" x-model="parent.nama_komponen" class="w-full min-w-[120px] bg-white border border-slate-300 rounded px-2 py-1 text-xs sm:text-sm font-bold focus:border-[#145239]" placeholder="Kategori Pendapatan...">
                                    <button @click="addChild(parent.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-[#E7F2EB] text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-lg text-[10px] sm:text-xs font-bold transition-colors shadow-xs">
                                        <i class="fa-solid fa-plus mr-1 text-[9px]"></i>
                                        Sub
                                    </button>
                                    <button @click="removeRow(parent.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                        <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                        Hapus
                                    </button>
                                </div>
                                <span x-show="isPreviewMode" class="uppercase" x-text="parent.nama_komponen || '(Tanpa Nama)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right border-r border-slate-200 whitespace-nowrap">
                                    <input x-show="!isPreviewMode && getChildren(parent.temp_id).length === 0" type="number" min="0" x-model.number="parent.yearly_data[year]" @input="if(parent.yearly_data[year] < 0) parent.yearly_data[year] = 0" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm text-right focus:border-[#145239] font-mono" placeholder="0">
                                    <span x-show="isPreviewMode || getChildren(parent.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))" class="font-bold text-slate-800 font-mono"></span>
                                </td>
                            </template>
                        </tr>

                        <!-- Level 2 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                            <template x-if="true">
                                <tr>
                                    <td class="px-4 py-2 pl-6 sm:pl-10 sm:sticky sm:left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-300 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="child.nama_komponen" class="w-full min-w-[100px] bg-white border border-slate-200 rounded px-2 py-1 text-xs sm:text-sm font-semibold focus:border-[#145239]" placeholder="Sub-kategori...">
                                                <button @click="addChild(child.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-[#E7F2EB] text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-lg text-[10px] sm:text-xs font-bold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-plus mr-1 text-[9px]"></i>
                                                    Sub
                                                </button>
                                                <button @click="removeRow(child.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                                    Hapus
                                                </button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="child.nama_komponen || '(Tanpa Nama)'" class="text-slate-700 font-semibold"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100 whitespace-nowrap">
                                            <input x-show="!isPreviewMode && getChildren(child.temp_id).length === 0" type="number" min="0" x-model.number="child.yearly_data[year]" @input="if(child.yearly_data[year] < 0) child.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm text-right focus:border-[#145239] font-mono" placeholder="0">
                                            <span x-show="isPreviewMode || getChildren(child.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(child.temp_id, year))" class="font-semibold text-slate-700 font-mono"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>

                        <!-- Level 3 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="'l3_'+child.temp_id">
                            <template x-for="subchild in getChildren(child.temp_id)" :key="subchild.temp_id">
                                <tr class="bg-slate-50 border-t border-slate-100">
                                    <td class="px-4 py-2 pl-10 sm:pl-16 sm:sticky sm:left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-400 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="subchild.nama_komponen" class="w-full min-w-[100px] bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:border-[#145239]" placeholder="Detail Komponen...">
                                                <button @click="removeRow(subchild.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                                    Hapus
                                                </button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="subchild.nama_komponen || '(Tanpa Nama)'" class="text-slate-600 text-xs"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100 whitespace-nowrap">
                                            <input x-show="!isPreviewMode" type="number" min="0" x-model.number="subchild.yearly_data[year]" @input="if(subchild.yearly_data[year] < 0) subchild.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs text-right focus:border-[#145239] font-mono" placeholder="0">
                                            <span x-show="isPreviewMode" x-text="formatRupiah(subchild.yearly_data[year] || 0)" class="font-mono text-slate-600 text-xs"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </template>


                <!-- ================= 2. BLOK BIAYA OPERASIONAL ================= -->
                <tbody x-show="getParents('BIAYA_OPERASIONAL').length > 0">
                    <tr class="bg-rose-100 text-rose-900 font-bold border-t border-b border-rose-200">
                        <td class="px-4 py-2.5 sm:sticky sm:left-0 bg-rose-100 z-20 border-r border-rose-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] uppercase tracking-wider text-xs">B. BIAYA OPERASIONAL</td>
                        <td :colspan="years.length" class="bg-rose-100"></td>
                    </tr>
                </tbody>

                <template x-for="parent in getParents('BIAYA_OPERASIONAL')" :key="parent.temp_id">
                    <tbody class="border-b border-slate-300">
                        <!-- Level 1 -->
                        <tr class="bg-slate-100">
                            <td class="px-4 py-2 text-slate-800 font-bold sm:sticky sm:left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                <div x-show="!isPreviewMode" class="flex items-center space-x-2">
                                    <input type="text" x-model="parent.nama_komponen" class="w-full min-w-[120px] bg-white border border-slate-300 rounded px-2 py-1 text-xs sm:text-sm font-bold focus:border-[#145239]" placeholder="Kategori Biaya...">
                                    <button @click="addChild(parent.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-[#E7F2EB] text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-lg text-[10px] sm:text-xs font-bold transition-colors shadow-xs">
                                        <i class="fa-solid fa-plus mr-1 text-[9px]"></i>
                                        Sub
                                    </button>
                                    <button @click="removeRow(parent.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                        <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                        Hapus
                                    </button>
                                </div>
                                <span x-show="isPreviewMode" class="uppercase" x-text="parent.nama_komponen || '(Tanpa Nama)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right border-r border-slate-200 whitespace-nowrap">
                                    <input x-show="!isPreviewMode && getChildren(parent.temp_id).length === 0" type="number" min="0" x-model.number="parent.yearly_data[year]" @input="if(parent.yearly_data[year] < 0) parent.yearly_data[year] = 0" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm text-right focus:border-[#145239] font-mono" placeholder="0">
                                    <span x-show="isPreviewMode || getChildren(parent.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))" class="font-bold text-slate-800 font-mono"></span>
                                </td>
                            </template>
                        </tr>

                        <!-- Level 2 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                            <template x-if="true">
                                <tr>
                                    <td class="px-4 py-2 pl-6 sm:pl-10 sm:sticky sm:left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-300 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="child.nama_komponen" class="w-full min-w-[100px] bg-white border border-slate-200 rounded px-2 py-1 text-xs sm:text-sm font-semibold focus:border-[#145239]" placeholder="Sub-kategori...">
                                                <button @click="addChild(child.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-[#E7F2EB] text-[#145239] hover:bg-[#CFE3D5] border border-[#CFE3D5] rounded-lg text-[10px] sm:text-xs font-bold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-plus mr-1 text-[9px]"></i>
                                                    Sub
                                                </button>
                                                <button @click="removeRow(child.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                                    Hapus
                                                </button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="child.nama_komponen || '(Tanpa Nama)'" class="text-slate-700 font-semibold"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100 whitespace-nowrap">
                                            <input x-show="!isPreviewMode && getChildren(child.temp_id).length === 0" type="number" min="0" x-model.number="child.yearly_data[year]" @input="if(child.yearly_data[year] < 0) child.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm text-right focus:border-[#145239] font-mono" placeholder="0">
                                            <span x-show="isPreviewMode || getChildren(child.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(child.temp_id, year))" class="font-semibold text-slate-700 font-mono"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>

                        <!-- Level 3 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="'l3_'+child.temp_id">
                            <template x-for="subchild in getChildren(child.temp_id)" :key="subchild.temp_id">
                                <tr class="bg-slate-50 border-t border-slate-100">
                                    <td class="px-4 py-2 pl-10 sm:pl-16 sm:sticky sm:left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-400 mr-1.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="subchild.nama_komponen" class="w-full min-w-[100px] bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:border-[#145239]" placeholder="Detail Komponen...">
                                                <button @click="removeRow(subchild.temp_id)" class="shrink-0 inline-flex items-center justify-center px-2 py-1 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 rounded-lg text-[10px] sm:text-xs font-semibold transition-colors shadow-xs">
                                                    <i class="fa-solid fa-trash-can mr-1 text-[9px]"></i>
                                                    Hapus
                                                </button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="subchild.nama_komponen || '(Tanpa Nama)'" class="text-slate-600 text-xs"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100 whitespace-nowrap">
                                            <input x-show="!isPreviewMode" type="number" min="0" x-model.number="subchild.yearly_data[year]" @input="if(subchild.yearly_data[year] < 0) subchild.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs text-right focus:border-[#145239] font-mono" placeholder="0">
                                            <span x-show="isPreviewMode" x-text="formatRupiah(subchild.yearly_data[year] || 0)" class="font-mono text-slate-600 text-xs"></span>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </template>
                    </tbody>
                </template>


                <!-- Empty State (Jika tidak ada data sama sekali) -->
                <tbody x-show="rows.length === 0">
                    <tr class="border-b border-slate-100">
                        <td :colspan="years.length + 1" class="text-center py-12 text-slate-400 text-sm">
                            Tabel saat ini kosong. Klik tombol tambah di atas untuk membuat struktur kategori Anda.
                        </td>
                    </tr>
                </tbody>


                <!-- ================= 3. RINGKASAN FINANSIAL ================= -->
                <tbody x-show="rows.length > 0">
                    <tr><td :colspan="years.length + 1" class="h-6 bg-white border-0"></td></tr>

                    <!-- Total Pendapatan & Biaya Murni -->
                    <tr class="bg-[#E7F2EB] border-t border-[#CFE3D5] font-bold">
                        <td class="px-4 py-2.5 sm:sticky sm:left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-[#145239] uppercase tracking-wider text-xs">TOTAL PENDAPATAN</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2.5 text-right font-mono text-[#145239] border-r border-[#CFE3D5] whitespace-nowrap" x-text="formatRupiah(calculateTotal('PENDAPATAN', year))"></td>
                        </template>
                    </tr>
                    <tr class="bg-rose-100 border-b border-rose-200 font-bold">
                        <td class="px-4 py-2.5 sm:sticky sm:left-0 bg-rose-100 z-20 border-r border-rose-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-rose-900 uppercase tracking-wider text-xs">TOTAL BIAYA OPERASIONAL</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2.5 text-right font-mono text-rose-900 border-r border-rose-200 whitespace-nowrap" x-text="formatRupiah(calculateTotal('BIAYA_OPERASIONAL', year))"></td>
                        </template>
                    </tr>

                    <!-- EBITDA -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sm:sticky sm:left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBITDA</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBITDA(year))"></td>
                        </template>
                    </tr>

                    <!-- Depresiasi -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sm:sticky sm:left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">Depresiasi (-)</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(settings.pl_nominal_depresiasi)"></td>
                        </template>
                    </tr>

                    <!-- EBIT -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sm:sticky sm:left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBIT (Laba Operasional)</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBIT(year))"></td>
                        </template>
                    </tr>

                    <!-- Bunga -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sm:sticky sm:left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">
                            Beban Bunga Pinjaman Bank (-)
                            <!-- <span class="text-xs text-[#145239] font-bold" x-text="'(' + (settings.suku_bunga_kredit || 8.05) + '%)'"></span> -->
                        </td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getBebanBunga(year))"></td>
                        </template>
                    </tr>

                    <!-- EBT -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sm:sticky sm:left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-900">EBT (Laba Sebelum Pajak)</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300 whitespace-nowrap" x-text="formatRupiah(getEBT(year))"></td>
                        </template>
                    </tr>

                    <!-- Pajak -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sm:sticky sm:left-0 bg-white z-20 border-r border-slate-200 shadow-[2px_0_5px_rgba(0,0,0,0.08)] text-slate-700">
                            Pajak PPh (-)
                            <!-- <span class="text-xs text-slate-500 italic" x-text="'(' + (settings.pl_persentase_pajak_penghasilan || 22) + '% dari EBT)'"></span> -->
                        </td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200 whitespace-nowrap" x-text="formatRupiah(getPajakPenghasilan(year))"></td>
                        </template>
                    </tr>

                    <!-- EAT / NET INCOME -->
                    <tr class="bg-[#145239] text-white font-bold border-y-2 border-[#0B5D3D]">
                        <td class="px-4 py-3.5 sm:sticky sm:left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] shadow-[2px_0_5px_rgba(0,0,0,0.15)] text-white text-base">EAT / LABA BERSIH (NET INCOME)</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3.5 text-right font-mono font-black text-[#FFD54F] border-r border-[#0B5D3D] text-base whitespace-nowrap" x-text="formatRupiah(getEAT(year))"></td>
                        </template>
                    </tr>
                </tbody>

            </table>
        </div>

    </div>

    <!-- MODAL KONFIRMASI PERUBAHAN BELUM DISIMPAN -->
    <x-confirm-unsaved-modal />
</div>

<script>
    function registerLabaRugi() {
        Alpine.data('labaRugiManager', () => ({
            jangkaWaktuTahun: {{ $project->jangka_waktu_tahun ?? 10 }},
            projectId: {{ $project->id }},
            years: [],
            
            isInitializing: false,
            isPreviewMode: false,
            isSaving: false,
            isSavingSettings: false,
            isSettingsOpen: false,

            toast: { show: false, message: '', isSuccess: true },

            totalCapex: {{ $totalCapex }},

            settings: {
                pl_persentase_pajak_penghasilan: 22,
                pl_nominal_depresiasi: 0,
                rasio_modal_sendiri: 60,
                rasio_pinjaman_kredit: 40,
                suku_bunga_kredit: 8.05,
                tenor_kredit_tahun: 5,
            },

            hasUnsavedChanges: false,
            showLeaveModal: false,
            pendingNavigationUrl: null,
            isGuardPushed: false,
            autoSaveStatus: '',
            lastSavedTime: '',
            countdownSeconds: 300,
            countdownInterval: null,
            localDraftTimeout: null,
            storageKey: 'labarugi_draft_' + {{ $project->id }},

            get formattedCountdown() {
                const m = Math.floor(this.countdownSeconds / 60);
                const s = this.countdownSeconds % 60;
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            },

            initData() {
                for (let i = 1; i <= this.jangkaWaktuTahun; i++) {
                    this.years.push(i);
                }
                
                const localDraft = localStorage.getItem(this.storageKey);
                if (localDraft) {
                    try {
                        const parsed = JSON.parse(localDraft);
                        if (parsed && Array.isArray(parsed.rows) && parsed.rows.length > 0) {
                            this.rows = parsed.rows;
                            if (parsed.settings) this.settings = Object.assign({}, this.settings, parsed.settings);
                            this.hasUnsavedChanges = true;
                            this.autoSaveStatus = 'draft';
                            this.lastSavedTime = parsed.time || '';
                        }
                    } catch(e) {}
                }

                if (this.hasUnsavedChanges) {
                    this.pushHistoryGuard();
                }

                this.loadData();

                this.$watch('rows', () => { this.triggerAutoSave(); });
                this.$watch('settings', () => { this.triggerAutoSave(); });

                this.startCountdownTimer();
                this.setupNavigationInterception();
            },

            startCountdownTimer() {
                if (this.countdownInterval) clearInterval(this.countdownInterval);
                this.countdownSeconds = 300;
                this.countdownInterval = setInterval(() => {
                    if (this.countdownSeconds > 0) {
                        this.countdownSeconds--;
                    } else {
                        if (this.hasUnsavedChanges) {
                            this.autoSaveToServer();
                        }
                        this.countdownSeconds = 300;
                    }
                }, 1000);
            },

            pushHistoryGuard() {
                if (!this.isGuardPushed) {
                    try {
                        history.pushState({ unsavedGuard: true }, '', window.location.href);
                        this.isGuardPushed = true;
                    } catch(e) {}
                }
            },

            setupNavigationInterception() {
                window.addEventListener('beforeunload', (e) => {
                    if (this.hasUnsavedChanges) {
                        e.preventDefault();
                        e.returnValue = '';
                    }
                });

                window.addEventListener('popstate', (e) => {
                    if (this.hasUnsavedChanges) {
                        try {
                            history.pushState({ unsavedGuard: true }, '', window.location.href);
                        } catch(err) {}
                        this.pendingNavigationUrl = 'BACK_NAVIGATION';
                        this.showLeaveModal = true;
                    }
                });

                document.addEventListener('click', (e) => {
                    if (!this.hasUnsavedChanges) return;

                    const link = e.target.closest('a');
                    if (!link) return;

                    const href = link.getAttribute('href');
                    const target = link.getAttribute('target');

                    if (!href || 
                        href.startsWith('#') || 
                        href.startsWith('javascript:') || 
                        href.startsWith('mailto:') || 
                        href.startsWith('tel:') || 
                        target === '_blank' || 
                        e.ctrlKey || 
                        e.metaKey || 
                        link.hasAttribute('download')) {
                        return;
                    }

                    if (link.href === window.location.href) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();
                    this.pendingNavigationUrl = link.href;
                    this.showLeaveModal = true;
                }, true);
            },

            triggerAutoSave() {
                if (this.isInitializing) return;
                this.hasUnsavedChanges = true;
                this.pushHistoryGuard();
                clearTimeout(this.localDraftTimeout);
                this.localDraftTimeout = setTimeout(() => {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    localStorage.setItem(this.storageKey, JSON.stringify({
                        rows: this.rows,
                        settings: this.settings,
                        time: timeStr
                    }));
                    if (this.autoSaveStatus !== 'saving') {
                        this.autoSaveStatus = 'draft';
                    }
                }, 400);
            },

            async autoSaveToServer(force = false) {
                if (!force && !this.hasUnsavedChanges) return true;
                this.autoSaveStatus = 'saving';
                try {
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const response = await fetch(`/operator/projects/${this.projectId}/laba-rugi/data`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ components: this.rows, settings: this.settings })
                    });
                    if (response.ok) {
                        const now = new Date();
                        this.lastSavedTime = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                        this.autoSaveStatus = 'saved';
                        this.hasUnsavedChanges = false;
                        this.isGuardPushed = false;
                        this.countdownSeconds = 300;
                        localStorage.removeItem(this.storageKey);
                        return true;
                    } else {
                        const errData = await response.json().catch(() => ({}));
                        console.error('Server Save Failed:', errData);
                        this.autoSaveStatus = 'draft';
                        return false;
                    }
                } catch (error) {
                    console.error('Network/Server Error during save:', error);
                    this.autoSaveStatus = 'draft';
                    return false;
                }
            },

            async saveAndLeave() {
                const isBackNav = (this.pendingNavigationUrl === 'BACK_NAVIGATION');
                const targetUrl = this.pendingNavigationUrl;

                const success = await this.autoSaveToServer(true);
                if (success) {
                    this.hasUnsavedChanges = false;
                    this.isGuardPushed = false;
                    localStorage.removeItem(this.storageKey);
                    this.showLeaveModal = false;

                    if (isBackNav) {
                        window.history.go(-2);
                    } else if (targetUrl) {
                        window.location.href = targetUrl;
                    }
                } else {
                    alert('Gagal menyimpan data ke database. Silakan coba lagi.');
                }
            },

            discardAndLeave() {
                const isBackNav = (this.pendingNavigationUrl === 'BACK_NAVIGATION');
                const targetUrl = this.pendingNavigationUrl;

                this.hasUnsavedChanges = false;
                this.isGuardPushed = false;
                localStorage.removeItem(this.storageKey);
                this.showLeaveModal = false;

                if (isBackNav) {
                    window.history.go(-2);
                } else if (targetUrl) {
                    window.location.href = targetUrl;
                }
            },

            async loadData() {
                this.isInitializing = true;
                try {
                    const response = await fetch(`/operator/projects/${this.projectId}/laba-rugi`, {
                        headers: { 'Accept': 'application/json' }
                    });
                    
                    if (response.ok) {
                        const data = await response.json();
                        
                        if (data.settings) {
                            this.settings = data.settings;
                            if (!this.settings.pl_persentase_pajak_penghasilan || parseFloat(this.settings.pl_persentase_pajak_penghasilan) <= 0) {
                                this.settings.pl_persentase_pajak_penghasilan = 22;
                            }
                            if (!this.settings.suku_bunga_kredit || parseFloat(this.settings.suku_bunga_kredit) <= 0) {
                                this.settings.suku_bunga_kredit = 8.05;
                            }
                        }
                        if (data.total_capex !== undefined) {
                            this.totalCapex = parseFloat(data.total_capex) || 0;
                        }
                        
                        const components = data.components || [];
                        let newRows = [];
                        
                        components.forEach(comp => {
                            let yearly_data = {};
                            this.years.forEach(y => yearly_data[y] = 0);
                            
                            if (comp.yearly_data) {
                                comp.yearly_data.forEach(yd => {
                                    yearly_data[yd.tahun_ke] = parseFloat(yd.nilai);
                                });
                            }
                            
                            newRows.push({
                                id: comp.id,
                                temp_id: 'db_' + comp.id,
                                parent_temp_id: comp.parent_id ? 'db_' + comp.parent_id : null,
                                nama_komponen: comp.nama_komponen,
                                tipe_kategori: comp.tipe_kategori,
                                yearly_data: yearly_data
                            });
                        });
                        
                        const localDraft = localStorage.getItem(this.storageKey);
                        if (!localDraft) {
                            this.rows = newRows;
                        }
                    }
                } catch (error) {
                    console.error('Gagal memuat data', error);
                } finally {
                    this.$nextTick(() => {
                        this.isInitializing = false;
                        const localDraft = localStorage.getItem(this.storageKey);
                        if (!localDraft) {
                            this.hasUnsavedChanges = false;
                            this.autoSaveStatus = 'saved';
                        }
                    });
                }
            },

            generateId() {
                return 'temp_' + Date.now() + Math.random().toString(36).substr(2, 5);
            },

            getEmptyYearlyData() {
                let obj = {};
                this.years.forEach(y => obj[y] = 0);
                return obj;
            },

            toggleMode() {
                this.isPreviewMode = !this.isPreviewMode;
            },

            // Tambah Level 1 (Pendapatan / Biaya)
            addParent(tipe_kategori) {
                this.rows.push({
                    id: null,
                    temp_id: this.generateId(),
                    parent_temp_id: null,
                    nama_komponen: '',
                    tipe_kategori: tipe_kategori,
                    yearly_data: this.getEmptyYearlyData()
                });
            },

            // Tambah Sub-Level (Level 2 & Level 3)
            addChild(parentTempId) {
                let parentIdx = this.rows.findIndex(r => r.temp_id === parentTempId);
                let parentType = 'PENDAPATAN';
                
                if (parentIdx !== -1) {
                    // Karena ini sekarang punya sub-item, data inputannya kita paksa ke 0 agar dijumlahkan dari bawah
                    this.years.forEach(y => this.rows[parentIdx].yearly_data[y] = 0);
                    parentType = this.rows[parentIdx].tipe_kategori;
                }
                
                this.rows.push({
                    id: null,
                    temp_id: this.generateId(),
                    parent_temp_id: parentTempId,
                    nama_komponen: '',
                    tipe_kategori: parentType,
                    yearly_data: this.getEmptyYearlyData()
                });
            },

            // Hapus baris sekaligus sub-komponen di bawahnya (Rekursif)
            removeRow(tempId) {
                const removeRecursive = (parentId) => {
                    let children = this.getChildren(parentId);
                    children.forEach(c => {
                        removeRecursive(c.temp_id);
                    });
                    this.rows = this.rows.filter(r => r.temp_id !== parentId);
                };
                removeRecursive(tempId);
            },

            getParents(tipe_kategori = null) {
                if (tipe_kategori) {
                    return this.rows.filter(r => r.parent_temp_id === null && r.tipe_kategori === tipe_kategori);
                }
                return this.rows.filter(r => r.parent_temp_id === null);
            },

            getChildren(parentTempId) {
                return this.rows.filter(r => r.parent_temp_id === parentTempId);
            },

            // Menghitung total dari level anak (Otomatis mendeteksi hingga Level 3)
            getRowYearlyTotal(tempId, year) {
                let children = this.getChildren(tempId);
                if (children.length === 0) {
                    let row = this.rows.find(r => r.temp_id === tempId);
                    return row ? (parseFloat(row.yearly_data[year]) || 0) : 0;
                }
                return children.reduce((sum, child) => sum + this.getRowYearlyTotal(child.temp_id, year), 0);
            },

            calculateTotal(tipe_kategori, year) {
                let parents = this.getParents(tipe_kategori);
                return parents.reduce((sum, parent) => sum + this.getRowYearlyTotal(parent.temp_id, year), 0);
            },

            getEBITDA(year) {
                let pendapatan = this.calculateTotal('PENDAPATAN', year);
                let biayaOps = this.calculateTotal('BIAYA_OPERASIONAL', year);
                return pendapatan - biayaOps;
            },

            getEBIT(year) {
                return this.getEBITDA(year) - (parseFloat(this.settings.pl_nominal_depresiasi) || 0);
            },

            getDebtAmount() {
                const debtRatio = (parseFloat(this.settings.rasio_pinjaman_kredit) || 40) / 100;
                return debtRatio * this.totalCapex;
            },

            getBebanBunga(year) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                const rate = (parseFloat(this.settings.suku_bunga_kredit) || 8.05) / 100;
                
                if (year <= tenor && debt > 0) {
                    return debt * rate;
                }
                return 0;
            },

            getEBT(year) {
                return this.getEBIT(year) - this.getBebanBunga(year);
            },

            getPajakPenghasilan(year) {
                let ebt = this.getEBT(year);
                if (ebt <= 0) return 0; 
                let persentase = parseFloat(this.settings.pl_persentase_pajak_penghasilan) || 22;
                return ebt * (persentase / 100);
            },

            getEAT(year) {
                return this.getEBT(year) - this.getPajakPenghasilan(year);
            },

            formatRupiah(value) {
                if (value === null || value === undefined || isNaN(value)) return '0';
                let isNegative = value < 0;
                let formatted = new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Math.abs(value));
                return isNegative ? `-${formatted}` : formatted;
            },

            showToast(message, isSuccess = true) {
                this.toast.message = message;
                this.toast.isSuccess = isSuccess;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            async saveSettings() {
                this.isSavingSettings = true;
                try {
                    const response = await fetch(`/operator/projects/${this.projectId}/laba-rugi/settings`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.settings)
                    });
                    const result = await response.json();
                    this.showToast(result.message, response.ok);
                } catch (error) {
                    this.showToast('Terjadi kesalahan pada server.', false);
                }
                this.isSavingSettings = false;
            },

            async saveData() {
                this.isSaving = true;
                const success = await this.autoSaveToServer(true);
                this.isSaving = false;
                if (success) {
                    this.showToast('Data Laba Rugi berhasil disimpan ke database.', true);
                    this.isInitializing = true;
                    await this.loadData();
                    this.hasUnsavedChanges = false;
                    this.autoSaveStatus = 'saved';
                    localStorage.removeItem(this.storageKey);
                    this.isPreviewMode = true; 
                } else {
                    this.showToast('Gagal menyimpan data.', false);
                }
            }
        }));
    }

    if (window.Alpine) { registerLabaRugi(); } 
    else { document.addEventListener('alpine:init', registerLabaRugi); }
</script>
@endsection
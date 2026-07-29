@extends('partials.layouts.operator')

@section('title', 'Proyeksi Laba Rugi - ' . $project->nama_proyek)
@section('page_heading', 'Proyeksi Laba Rugi Proyek')

@section('content')
<div x-data="labaRugiManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <nav class="flex text-sm text-slate-500 space-x-2">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-primary font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-primary font-medium truncate max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Proyeksi Laba Rugi</span>
        </nav>

        <div class="flex items-center gap-2">
            <button @click="toggleMode()" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path x-show="!isPreviewMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path x-show="!isPreviewMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    <path x-show="isPreviewMode" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                <span x-text="isPreviewMode ? 'Edit Data' : 'Pratinjau (Preview)'"></span>
            </button>

            <button @click="saveData()" :disabled="isSaving" class="bg-primary hover:bg-primary-container text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center disabled:opacity-50 disabled:cursor-not-allowed">
                <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg x-show="!isSaving" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                Simpan Perubahan
            </button>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="toast.show" x-transition class="p-4 rounded-lg shadow-md flex items-center justify-between border" :class="toast.isSuccess ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-rose-50 border-rose-200 text-rose-800'">
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-3" :class="toast.isSuccess ? 'text-emerald-500' : 'text-rose-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="toast.isSuccess ? 'M5 13l4 4L19 7' : 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'"></path></svg>
            <span class="text-sm font-medium" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>

    <!-- Main Workspace -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        
        <!-- Table Title and Desc -->
        <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Proyeksi Laba Rugi (Profit & Loss)</h2>
                <p class="text-xs text-slate-500 mt-1">Buat struktur kategori Anda sendiri secara dinamis hingga 3 level turunan.</p>
            </div>
            
            <button @click="isSettingsOpen = !isSettingsOpen" class="inline-flex items-center text-xs font-semibold px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition-colors border border-slate-200 shadow-sm">
                <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                <span x-text="isSettingsOpen ? 'Tutup Pengaturan Variabel' : 'Pengaturan Variabel'"></span>
            </button>
        </div>

        <!-- Pengaturan Variabel P&L Card -->
        <div x-show="isSettingsOpen" x-transition class="bg-slate-50 rounded-xl border border-slate-200 p-5 mb-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-200 pb-3 mb-4">
                <div>
                    <h3 class="text-sm font-bold text-slate-800">Variabel Pengaturan Laba Rugi</h3>
                    <p class="text-[11px] text-slate-500">Nilai statis di bawah ini digunakan khusus untuk kalkulasi EBIT, EBT, dan EAT di akhir tabel.</p>
                </div>
                <button @click="saveSettings()" :disabled="isSavingSettings" class="bg-primary hover:bg-primary-container text-white px-4 py-1.5 rounded-lg text-xs font-semibold shadow-sm transition-colors flex items-center disabled:opacity-50">
                    <svg x-show="isSavingSettings" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Simpan Pengaturan
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Depresiasi (Nominal per Tahun)</label>
                    <input type="number" min="0" x-model.number="settings.pl_nominal_depresiasi" @input="if(settings.pl_nominal_depresiasi < 0) settings.pl_nominal_depresiasi = 0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 text-xs focus:border-primary focus:ring-primary font-mono shadow-sm" placeholder="0">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Suku Bunga Pinjaman (% / Thn)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" x-model.number="settings.suku_bunga_kredit" @input="if(settings.suku_bunga_kredit < 0) settings.suku_bunga_kredit = 0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-7 text-xs focus:border-primary focus:ring-primary font-mono shadow-sm" placeholder="8.05">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-xs text-slate-400 font-bold">%</span>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Pajak PPh (% dari EBT)</label>
                    <div class="relative">
                        <input type="number" step="0.01" min="0" max="100" x-model.number="settings.pl_persentase_pajak_penghasilan" @input="if(settings.pl_persentase_pajak_penghasilan < 0) settings.pl_persentase_pajak_penghasilan = 0" class="w-full bg-white border border-slate-200 rounded-lg px-2.5 py-1.5 pr-7 text-xs focus:border-primary focus:ring-primary font-mono shadow-sm" placeholder="22">
                        <span class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-xs text-slate-400 font-bold">%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Tambah Dinamis -->
        <div x-show="!isPreviewMode" class="flex gap-3 mb-4">
            <button @click="addParent('PENDAPATAN')" class="px-4 py-2 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold transition-colors shadow-sm">
                + Kategori Pendapatan
            </button>
            <button @click="addParent('BIAYA_OPERASIONAL')" class="px-4 py-2 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-bold transition-colors shadow-sm">
                + Kategori Biaya Operasional
            </button>
        </div>

        <!-- Tabel Responsive -->
        <div class="overflow-x-auto border border-slate-200 rounded-lg pb-10">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-blue-600 text-white border-b border-blue-700">
                        <th class="px-4 py-3 font-semibold text-xs tracking-wider min-w-[400px] sticky left-0 bg-blue-600 z-20 border-r border-blue-500 shadow-[1px_0_0_0_#3b82f6]">Kategori / Komponen</th>
                        <template x-for="year in years" :key="year">
                            <th class="px-4 py-3 font-semibold text-xs tracking-wider text-right min-w-[160px] border-r border-blue-500" x-text="'Tahun Ke-' + year"></th>
                        </template>
                    </tr>
                </thead>
                
                <!-- ================= 1. BLOK PENDAPATAN ================= -->
                <tbody x-show="getParents('PENDAPATAN').length > 0">
                    <tr class="bg-emerald-500 text-white font-bold">
                        <td class="px-4 py-2 sticky left-0 bg-emerald-500 z-20 border-r border-emerald-600 shadow-[1px_0_0_0_#10b981] uppercase tracking-wider text-xs">A. PENDAPATAN</td>
                        <td :colspan="years.length" class="bg-emerald-500"></td>
                    </tr>
                </tbody>

                <template x-for="parent in getParents('PENDAPATAN')" :key="parent.temp_id">
                    <tbody class="border-b border-slate-300">
                        <!-- Level 1 -->
                        <tr class="bg-slate-100">
                            <td class="px-4 py-2 text-slate-800 font-bold sticky left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[1px_0_0_0_#cbd5e1]">
                                <div x-show="!isPreviewMode" class="flex items-center space-x-2">
                                    <input type="text" x-model="parent.nama_komponen" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm font-bold focus:border-primary" placeholder="Kategori Pendapatan...">
                                    <button @click="addChild(parent.temp_id)" class="shrink-0 px-2 py-1 bg-primary/10 text-primary hover:bg-primary/20 rounded font-semibold text-xs transition-colors">+ Sub</button>
                                    <button @click="removeRow(parent.temp_id)" class="shrink-0 px-2 py-1 bg-error/10 text-error hover:bg-error/20 rounded font-semibold text-xs transition-colors">Hapus</button>
                                </div>
                                <span x-show="isPreviewMode" class="uppercase" x-text="parent.nama_komponen || '(Tanpa Nama)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right border-r border-slate-200">
                                    <input x-show="!isPreviewMode && getChildren(parent.temp_id).length === 0" type="number" min="0" x-model.number="parent.yearly_data[year]" @input="if(parent.yearly_data[year] < 0) parent.yearly_data[year] = 0" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm text-right focus:border-primary font-mono" placeholder="0">
                                    <span x-show="isPreviewMode || getChildren(parent.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))" class="font-bold text-slate-800 font-mono"></span>
                                </td>
                            </template>
                        </tr>

                        <!-- Level 2 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                            <template x-if="true">
                                <tr>
                                    <td class="px-4 py-2 pl-10 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-300 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="child.nama_komponen" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm font-semibold focus:border-primary" placeholder="Sub-kategori...">
                                                <button @click="addChild(child.temp_id)" class="shrink-0 text-primary hover:text-primary/80 font-semibold text-xs transition-colors">+ Sub</button>
                                                <button @click="removeRow(child.temp_id)" class="shrink-0 text-error hover:text-error/80 text-xs font-semibold transition-colors">Hapus</button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="child.nama_komponen || '(Tanpa Nama)'" class="text-slate-700 font-semibold"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100">
                                            <input x-show="!isPreviewMode && getChildren(child.temp_id).length === 0" type="number" min="0" x-model.number="child.yearly_data[year]" @input="if(child.yearly_data[year] < 0) child.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm text-right focus:border-primary font-mono" placeholder="0">
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
                                    <td class="px-4 py-2 pl-16 sticky left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-400 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="subchild.nama_komponen" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:border-primary" placeholder="Detail Komponen...">
                                                <button @click="removeRow(subchild.temp_id)" class="shrink-0 text-error hover:text-error/80 text-xs transition-colors">Hapus</button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="subchild.nama_komponen || '(Tanpa Nama)'" class="text-slate-600 text-xs"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100">
                                            <input x-show="!isPreviewMode" type="number" min="0" x-model.number="subchild.yearly_data[year]" @input="if(subchild.yearly_data[year] < 0) subchild.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs text-right focus:border-primary font-mono" placeholder="0">
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
                    <tr class="bg-rose-500 text-white font-bold">
                        <td class="px-4 py-2 sticky left-0 bg-rose-500 z-20 border-r border-rose-600 shadow-[1px_0_0_0_#f43f5e] uppercase tracking-wider text-xs">B. BIAYA OPERASIONAL</td>
                        <td :colspan="years.length" class="bg-rose-500"></td>
                    </tr>
                </tbody>

                <template x-for="parent in getParents('BIAYA_OPERASIONAL')" :key="parent.temp_id">
                    <tbody class="border-b border-slate-300">
                        <!-- Level 1 -->
                        <tr class="bg-slate-100">
                            <td class="px-4 py-2 text-slate-800 font-bold sticky left-0 bg-slate-100 z-20 border-r border-slate-300 shadow-[1px_0_0_0_#cbd5e1]">
                                <div x-show="!isPreviewMode" class="flex items-center space-x-2">
                                    <input type="text" x-model="parent.nama_komponen" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm font-bold focus:border-primary" placeholder="Kategori Biaya...">
                                    <button @click="addChild(parent.temp_id)" class="shrink-0 px-2 py-1 bg-primary/10 text-primary hover:bg-primary/20 rounded font-semibold text-xs transition-colors">+ Sub</button>
                                    <button @click="removeRow(parent.temp_id)" class="shrink-0 px-2 py-1 bg-error/10 text-error hover:bg-error/20 rounded font-semibold text-xs transition-colors">Hapus</button>
                                </div>
                                <span x-show="isPreviewMode" class="uppercase" x-text="parent.nama_komponen || '(Tanpa Nama)'"></span>
                            </td>
                            <template x-for="year in years" :key="year">
                                <td class="px-4 py-2 text-right border-r border-slate-200">
                                    <input x-show="!isPreviewMode && getChildren(parent.temp_id).length === 0" type="number" min="0" x-model.number="parent.yearly_data[year]" @input="if(parent.yearly_data[year] < 0) parent.yearly_data[year] = 0" class="w-full bg-white border border-slate-300 rounded px-2 py-1 text-sm text-right focus:border-primary font-mono" placeholder="0">
                                    <span x-show="isPreviewMode || getChildren(parent.temp_id).length > 0" x-text="formatRupiah(getRowYearlyTotal(parent.temp_id, year))" class="font-bold text-slate-800 font-mono"></span>
                                </td>
                            </template>
                        </tr>

                        <!-- Level 2 -->
                        <template x-for="child in getChildren(parent.temp_id)" :key="child.temp_id">
                            <template x-if="true">
                                <tr>
                                    <td class="px-4 py-2 pl-10 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-300 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="child.nama_komponen" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm font-semibold focus:border-primary" placeholder="Sub-kategori...">
                                                <button @click="addChild(child.temp_id)" class="shrink-0 text-primary hover:text-primary/80 font-semibold text-xs transition-colors">+ Sub</button>
                                                <button @click="removeRow(child.temp_id)" class="shrink-0 text-error hover:text-error/80 text-xs font-semibold transition-colors">Hapus</button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="child.nama_komponen || '(Tanpa Nama)'" class="text-slate-700 font-semibold"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100">
                                            <input x-show="!isPreviewMode && getChildren(child.temp_id).length === 0" type="number" min="0" x-model.number="child.yearly_data[year]" @input="if(child.yearly_data[year] < 0) child.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-sm text-right focus:border-primary font-mono" placeholder="0">
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
                                    <td class="px-4 py-2 pl-16 sticky left-0 bg-slate-50 z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0]">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-slate-400 mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            <div x-show="!isPreviewMode" class="flex items-center space-x-2 w-full">
                                                <input type="text" x-model="subchild.nama_komponen" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs focus:border-primary" placeholder="Detail Komponen...">
                                                <button @click="removeRow(subchild.temp_id)" class="shrink-0 text-error hover:text-error/80 text-xs transition-colors">Hapus</button>
                                            </div>
                                            <span x-show="isPreviewMode" x-text="subchild.nama_komponen || '(Tanpa Nama)'" class="text-slate-600 text-xs"></span>
                                        </div>
                                    </td>
                                    <template x-for="year in years" :key="year">
                                        <td class="px-4 py-2 text-right border-r border-slate-100">
                                            <input x-show="!isPreviewMode" type="number" min="0" x-model.number="subchild.yearly_data[year]" @input="if(subchild.yearly_data[year] < 0) subchild.yearly_data[year] = 0" class="w-full bg-white border border-slate-200 rounded px-2 py-1 text-xs text-right focus:border-primary font-mono" placeholder="0">
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
                    <tr><td :colspan="years.length + 1" class="h-8 bg-white border-0"></td></tr>

                    <!-- Total Pendapatan & Biaya Murni -->
                    <tr class="bg-emerald-50/50 border-t border-emerald-200 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-emerald-50 z-20 border-r border-emerald-200 shadow-[1px_0_0_0_#a7f3d0] text-emerald-800 uppercase tracking-wider text-xs">TOTAL PENDAPATAN</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono text-emerald-800 border-r border-emerald-200" x-text="formatRupiah(calculateTotal('PENDAPATAN', year))"></td>
                        </template>
                    </tr>
                    <tr class="bg-rose-50/50 border-b border-rose-200 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 shadow-[1px_0_0_0_#fecdd3] text-rose-800 uppercase tracking-wider text-xs">TOTAL BIAYA OPERASIONAL</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono text-rose-800 border-r border-rose-200" x-text="formatRupiah(calculateTotal('BIAYA_OPERASIONAL', year))"></td>
                        </template>
                    </tr>

                    <tr><td :colspan="years.length + 1" class="h-8 bg-white border-0"></td></tr>

                    <!-- EBITDA -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[1px_0_0_0_#cbd5e1] text-slate-900">EBITDA</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300" x-text="formatRupiah(getEBITDA(year))"></td>
                        </template>
                    </tr>

                    <!-- Depresiasi -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0] text-slate-700">Depresiasi</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200" x-text="formatRupiah(settings.pl_nominal_depresiasi)"></td>
                        </template>
                    </tr>

                    <!-- EBIT -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[1px_0_0_0_#cbd5e1] text-slate-900">EBIT</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300" x-text="formatRupiah(getEBIT(year))"></td>
                        </template>
                    </tr>

                    <!-- Bunga -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0] text-slate-700">
                            Beban Bunga Pinjaman Bank <span class="text-xs text-emerald-600 font-semibold" x-text="'(' + (settings.suku_bunga_kredit || 8.05) + '%)'"></span>
                        </td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200" x-text="formatRupiah(getBebanBunga(year))"></td>
                        </template>
                    </tr>

                    <!-- EBT -->
                    <tr class="bg-slate-200 border-y border-slate-300 font-bold">
                        <td class="px-4 py-3 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 shadow-[1px_0_0_0_#cbd5e1] text-slate-900">EBT (Laba Sebelum Pajak)</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-3 text-right font-mono font-bold text-slate-900 border-r border-slate-300" x-text="formatRupiah(getEBT(year))"></td>
                        </template>
                    </tr>

                    <!-- Pajak -->
                    <tr class="bg-white border-b border-slate-200">
                        <td class="px-4 py-2 sticky left-0 bg-white z-20 border-r border-slate-200 shadow-[1px_0_0_0_#e2e8f0] text-slate-700">
                            Pajak PPh <span class="text-xs text-slate-500 italic" x-text="'(' + (settings.pl_persentase_pajak_penghasilan || 22) + '% dari EBT)'"></span>
                        </td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-2 text-right font-mono text-slate-700 border-r border-slate-200" x-text="formatRupiah(getPajakPenghasilan(year))"></td>
                        </template>
                    </tr>

                    <!-- EAT -->
                    <tr class="bg-slate-300 border-y-2 border-slate-400 font-bold">
                        <td class="px-4 py-4 sticky left-0 bg-slate-300 z-20 border-r border-slate-400 shadow-[1px_0_0_0_#94a3b8] text-slate-900 text-base">EAT</td>
                        <template x-for="year in years" :key="year">
                            <td class="px-4 py-4 text-right font-mono font-black text-slate-900 border-r border-slate-400 text-base" x-text="formatRupiah(getEAT(year))"></td>
                        </template>
                    </tr>
                </tbody>

            </table>
        </div>

    </div>
</div>

<script>
    function registerLabaRugi() {
        Alpine.data('labaRugiManager', () => ({
            jangkaWaktuTahun: {{ $project->jangka_waktu_tahun ?? 10 }},
            projectId: {{ $project->id }},
            years: [],
            
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

            rows: [], 

            initData() {
                for (let i = 1; i <= this.jangkaWaktuTahun; i++) {
                    this.years.push(i);
                }
                this.loadData();
            },

            async loadData() {
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
                        
                        this.rows = newRows;
                    }
                } catch (error) {
                    console.error('Gagal memuat data', error);
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
                try {
                    const response = await fetch(`/operator/projects/${this.projectId}/laba-rugi/data`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ components: this.rows })
                    });
                    const result = await response.json();
                    
                    if (response.ok) {
                        this.showToast(result.message, true);
                        await this.loadData();
                        this.isPreviewMode = true; 
                    } else {
                        this.showToast(result.message || 'Validasi gagal.', false);
                    }
                } catch (error) {
                    this.showToast('Gagal menyimpan data.', false);
                }
                this.isSaving = false;
            }
        }));
    }

    if (window.Alpine) { registerLabaRugi(); } 
    else { document.addEventListener('alpine:init', registerLabaRugi); }
</script>
@endsection
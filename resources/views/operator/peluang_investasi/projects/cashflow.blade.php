@extends('partials.layouts.operator')

@section('title', 'Tabel Arus Kas (Cash Flow) - ' . $project->nama_proyek)
@section('page_heading', 'Tabel Arus Kas (Cash Flow)')

@section('content')
<div x-data="cashflowManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3.5 sm:gap-4">
        <nav class="flex flex-wrap text-xs sm:text-sm text-slate-500 gap-1.5 items-center">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-[#145239] font-medium truncate max-w-[150px] sm:max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Tabel Arus Kas</span>
        </nav>

        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <!-- Indicator Autosave -->
            <div x-show="autoSaveStatus" x-transition class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold transition-all" :class="{
                'bg-slate-100 text-slate-600 border border-slate-200': autoSaveStatus === 'saving',
                'bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5]': autoSaveStatus === 'saved',
                'bg-amber-50 text-amber-700 border border-amber-200': autoSaveStatus === 'draft'
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
                        <span x-text="'Tersimpan ' + lastSavedTime"></span>
                    </span>
                </template>
                <template x-if="autoSaveStatus === 'draft'">
                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-floppy-disk text-amber-600"></i>
                        <span>Draft lokal (Autosave DB per 5 mnt)</span>
                    </span>
                </template>
            </div>

            <a href="{{ route('operator.projects.show', $project->id) }}" class="w-full sm:w-auto px-4 py-2 border border-[#CFE3D5] bg-white hover:bg-slate-50 text-slate-700 rounded-xl text-xs sm:text-sm font-semibold shadow-sm transition-colors flex items-center justify-center gap-2">
                <i class="fa-solid fa-arrow-left text-xs"></i>
                <span>Kembali ke Detail</span>
            </a>
        </div>
    </div>

    <!-- Alert / Toast -->
    <div x-show="toast.show" x-transition class="p-4 rounded-xl shadow-sm flex items-center justify-between border" :class="toast.isSuccess ? 'bg-[#EEF8F2] border-[#CFE3D5] text-[#145239]' : 'bg-rose-50 border-rose-200 text-rose-800'">
        <div class="flex items-center gap-3">
            <i class="fa-solid text-lg" :class="toast.isSuccess ? 'fa-circle-check text-[#145239]' : 'fa-triangle-exclamation text-rose-500'"></i>
            <span class="text-xs sm:text-sm font-semibold" x-text="toast.message"></span>
        </div>
        <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Panel Pengaturan Parameter Pembiayaan (Quick Simulation Bar) -->
    <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-4 sm:p-5 md:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 pb-4 border-b border-[#EEF8F2]">
            <div>
                <h3 class="text-xs sm:text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-[#145239]"></i>
                    <span>Simulasi Parameter Pembiayaan & Kredit Bank</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ubah rasio modal, suku bunga, dan tenor di bawah ini untuk menghitung ulang laporan Arus Kas secara otomatis.</p>
            </div>

            <button @click="saveSettings()" :disabled="isSaving" class="w-full sm:w-auto bg-[#145239] hover:bg-[#0B5D3D] text-white px-5 py-2.5 sm:py-2 rounded-xl text-xs sm:text-sm font-bold shadow-md transition-colors flex items-center justify-center disabled:opacity-50 disabled:cursor-not-allowed gap-2 shrink-0">
                <i x-show="isSaving" class="fa-solid fa-spinner animate-spin"></i>
                <i x-show="!isSaving" class="fa-solid fa-floppy-disk"></i>
                <span>Simpan & Hitung Ulang</span>
            </button>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Rasio Equity (Modal Sendiri %)</label>
                <input type="number" min="0" max="100" x-model.number="settings.rasio_modal_sendiri" @input="settings.rasio_pinjaman_kredit = 100 - settings.rasio_modal_sendiri" class="w-full bg-[#F7FAF8] rounded-xl border border-[#CFE3D5] px-3 py-1.5 text-xs sm:text-sm font-mono font-bold text-slate-800 focus:border-[#145239]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Rasio Debt (Pinjaman Kredit %)</label>
                <input type="number" min="0" max="100" x-model.number="settings.rasio_pinjaman_kredit" @input="settings.rasio_modal_sendiri = 100 - settings.rasio_pinjaman_kredit" class="w-full bg-[#F7FAF8] rounded-xl border border-[#CFE3D5] px-3 py-1.5 text-xs sm:text-sm font-mono font-bold text-slate-800 focus:border-[#145239]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Suku Bunga Kredit (% / Thn)</label>
                <input type="number" step="0.01" min="0" max="100" x-model.number="settings.suku_bunga_kredit" class="w-full bg-[#F7FAF8] rounded-xl border border-[#CFE3D5] px-3 py-1.5 text-xs sm:text-sm font-mono font-bold text-[#145239] focus:border-[#145239]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Tenor Kredit (Tahun)</label>
                <input type="number" min="1" max="50" x-model.number="settings.tenor_kredit_tahun" class="w-full bg-[#F7FAF8] rounded-xl border border-[#CFE3D5] px-3 py-1.5 text-xs sm:text-sm font-mono font-bold text-purple-700 focus:border-[#145239]">
            </div>
        </div>
    </div>

    <!-- Main Workspace Tabel Arus Kas -->
    <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-4 sm:p-5 md:p-6 overflow-hidden">

        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-[#EEF8F2] pb-4">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800">Laporan Proyeksi Arus Kas (Cash Flow)</h2>
                <p class="text-xs text-slate-500 mt-1">Hasil kalkulasi otomatis aliran kas operasional, non-operasional, dan saldo kas dari Tahun 0 s/d Tahun {{ $project->jangka_waktu_tahun }}.</p>
            </div>
            <div class="bg-[#EEF8F2] border border-[#CFE3D5] px-4 py-2 rounded-xl text-left sm:text-right w-full sm:w-auto">
                <span class="block text-[11px] font-bold text-[#145239] uppercase tracking-wider">TOTAL CAPEX TERHITUNG</span>
                <span class="text-base sm:text-lg font-black text-slate-800 font-mono" x-text="formatRupiah(totalCapex)"></span>
            </div>
        </div>

        <!-- Tabel Responsive dengan Sticky Column -->
        <div class="w-full overflow-x-auto border border-[#CFE3D5] rounded-xl">
            <table class="w-full min-w-[900px] text-xs sm:text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-[#145239] text-white border-b border-[#0B5D3D]">
                        <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wider min-w-[280px] sticky left-0 bg-[#145239] z-20 border-r border-[#0B5D3D]">
                            Tahun
                        </th>
                        <th class="px-4 py-3 font-semibold text-xs uppercase tracking-wider text-right min-w-[160px] border-r border-[#0B5D3D] bg-[#0E422D]">
                            0
                        </th>
                        <template x-for="t in jangkaWaktu" :key="t">
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
                        <td :colspan="jangkaWaktu.length + 1" class="bg-[#E7F2EB]"></td>
                    </tr>

                    <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-semibold">
                            Kas Masuk
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="pendapatanPerTahun[t] ? formatRupiah(pendapatanPerTahun[t]) : '-'"></td>
                        </template>
                    </tr>

                    <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-semibold">
                            Kas Keluar
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="opexPerTahun[t] ? formatRupiah(-opexPerTahun[t]) : '-'"></td>
                        </template>
                    </tr>

                    <!-- ================= ARUS KAS NON-OPERASIONAL ================= -->
                    <tr class="bg-blue-100 text-blue-900 font-bold border-y border-blue-200">
                        <td class="px-4 py-2.5 sticky left-0 bg-blue-100 z-20 border-r border-blue-200 font-bold uppercase tracking-wider text-xs">
                            Arus Kas Non-Operasional
                        </td>
                        <td :colspan="jangkaWaktu.length + 1" class="bg-blue-100"></td>
                    </tr>

                    <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-medium">
                            Setoran Modal
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="formatRupiah(getEquityAmount())"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                        </template>
                    </tr>

                    <tr class="bg-[#EEF8F2] border-b border-[#CFE3D5] hover:bg-[#E7F2EB] transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-[#EEF8F2] z-20 border-r border-[#CFE3D5] text-[#145239] font-medium">
                            Penarikan Kredit
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-[#145239] font-semibold border-r border-[#CFE3D5]" x-text="formatRupiah(getDebtAmount())"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                        </template>
                    </tr>

                    <tr class="bg-[#E7F2EB] font-bold text-[#145239] border-b border-[#CFE3D5]">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-[#E7F2EB] z-20 border-r border-[#CFE3D5] font-bold uppercase text-xs tracking-wider">
                            Kas Masuk Non-operasional
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-[#145239] font-bold border-r border-[#CFE3D5]" x-text="formatRupiah(getTotalKasMasukNonOps(0))"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-[#CFE3D5]">-</td>
                        </template>
                    </tr>

                    <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                            Investasi
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="formatRupiah(-totalCapex)"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                        </template>
                    </tr>

                    <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                            Pokok
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="getAngsuranPokok(t) > 0 ? formatRupiah(-getAngsuranPokok(t)) : '-'"></td>
                        </template>
                    </tr>

                    <tr class="bg-rose-50/70 border-b border-rose-200 hover:bg-rose-100/60 transition-colors">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-50 z-20 border-r border-rose-200 text-rose-900 font-medium">
                            Bunga
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-slate-400 border-r border-rose-200">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-rose-900 font-semibold border-r border-rose-200" x-text="getBebanBunga(t) > 0 ? formatRupiah(-getBebanBunga(t)) : '-'"></td>
                        </template>
                    </tr>

                    <tr class="bg-rose-100 font-bold text-rose-900 border-b border-rose-200">
                        <td class="px-4 py-2 pl-8 sticky left-0 bg-rose-100 z-20 border-r border-rose-200 font-bold uppercase text-xs tracking-wider">
                            Kas Keluar Non-Operasional
                        </td>
                        <td class="px-4 py-2 text-right font-mono text-rose-900 font-bold border-r border-rose-200" x-text="formatRupiah(-totalCapex)"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2 text-right font-mono text-rose-900 font-bold border-r border-rose-200" x-text="(getAngsuranPokok(t) + getBebanBunga(t)) > 0 ? formatRupiah(-(getAngsuranPokok(t) + getBebanBunga(t))) : '-'"></td>
                        </template>
                    </tr>

                    <!-- ================= RINGKASAN SALDO ================= -->
                    <tr class="bg-slate-200 font-bold text-slate-900 border-b border-slate-300">
                        <td class="px-4 py-2.5 sticky left-0 bg-slate-200 z-20 border-r border-slate-300 font-bold uppercase text-xs tracking-wider">
                            Saldo
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-900 font-bold border-r border-slate-300" x-text="formatRupiah(getNetCashflow(0))"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-900 font-bold border-r border-slate-300" x-text="formatRupiah(getNetCashflow(t))"></td>
                        </template>
                    </tr>

                    <tr class="bg-[#145239] border-y-2 border-[#0B5D3D] font-bold text-white">
                        <td class="px-4 py-3 sticky left-0 bg-[#145239] z-20 border-r border-[#0B5D3D] font-bold text-white text-base">
                            Akumulasi Saldo
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-[#FFD54F] font-black border-r border-[#0B5D3D] text-base" x-text="formatRupiah(getAkumulasiSaldo(0))"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-3 text-right font-mono text-[#FFD54F] font-black border-r border-[#0B5D3D] text-base" x-text="formatRupiah(getAkumulasiSaldo(t))"></td>
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
    function registerCashflow() {
        Alpine.data('cashflowManager', () => ({
            totalCapex: {{ $totalCapex }},
            jangkaWaktu: {{ $project->jangka_waktu_tahun }},
            pendapatanPerTahun: @json($pendapatanPerTahun),
            opexPerTahun: @json($opexPerTahun),
            settings: {
                rasio_modal_sendiri: {{ (float) $project->rasio_modal_sendiri }},
                rasio_pinjaman_kredit: {{ (float) $project->rasio_pinjaman_kredit }},
                suku_bunga_kredit: {{ (float) $project->suku_bunga_kredit }},
                tenor_kredit_tahun: {{ (int) $project->tenor_kredit_tahun }},
            },
            isSaving: false,
            hasUnsavedChanges: false,
            showLeaveModal: false,
            pendingNavigationUrl: null,
            isGuardPushed: false,
            autoSaveStatus: '',
            lastSavedTime: '',
            autoSaveTimeout: null,
            localDraftTimeout: null,
            fiveMinIntervalTimer: null,
            storageKey: 'cashflow_settings_draft_' + {{ $project->id }},

            initData() {
                const localDraft = localStorage.getItem(this.storageKey);
                if (localDraft) {
                    try {
                        const parsed = JSON.parse(localDraft);
                        if (parsed && parsed.settings) {
                            this.settings = Object.assign({}, this.settings, parsed.settings);
                            this.hasUnsavedChanges = true;
                            this.autoSaveStatus = 'draft';
                            this.lastSavedTime = parsed.time || '';
                        }
                    } catch (e) {}
                }

                if (this.hasUnsavedChanges) {
                    this.pushHistoryGuard();
                }

                this.$watch('settings', () => {
                    this.triggerAutoSave();
                });

                // Autosave to DB interval 5 minutes (300.000 ms)
                this.fiveMinIntervalTimer = setInterval(() => {
                    if (this.hasUnsavedChanges) {
                        this.autoSaveSettings();
                    }
                }, 300000);

                this.setupNavigationInterception();
            },

            pushHistoryGuard() {
                if (!this.isGuardPushed) {
                    try {
                        history.pushState({
                            unsavedGuard: true
                        }, '', window.location.href);
                        this.isGuardPushed = true;
                    } catch (e) {}
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
                            history.pushState({
                                unsavedGuard: true
                            }, '', window.location.href);
                        } catch (err) {}
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
                this.hasUnsavedChanges = true;
                this.pushHistoryGuard();
                clearTimeout(this.localDraftTimeout);
                this.localDraftTimeout = setTimeout(() => {
                    const now = new Date();
                    const timeStr = now.toLocaleTimeString('id-ID', {
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    localStorage.setItem(this.storageKey, JSON.stringify({
                        settings: this.settings,
                        time: timeStr
                    }));
                    if (this.autoSaveStatus !== 'saving') {
                        this.autoSaveStatus = 'draft';
                    }
                }, 400);
            },

            async autoSaveSettings(force = false) {
                if (!force && !this.hasUnsavedChanges) return true;
                this.autoSaveStatus = 'saving';

                try {
                    const res = await fetch("{{ route('operator.projects.cashflow.settings', $project->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.settings)
                    });
                    const data = await res.json();
                    if (data.success) {
                        const now = new Date();
                        this.lastSavedTime = now.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                        this.autoSaveStatus = 'saved';
                        this.hasUnsavedChanges = false;
                        this.isGuardPushed = false;
                        localStorage.removeItem(this.storageKey);
                        return true;
                    } else {
                        this.autoSaveStatus = 'draft';
                        return false;
                    }
                } catch (e) {
                    this.autoSaveStatus = 'draft';
                    return false;
                }
            },

            async saveAndLeave() {
                const isBackNav = (this.pendingNavigationUrl === 'BACK_NAVIGATION');
                const targetUrl = this.pendingNavigationUrl;

                const success = await this.autoSaveSettings(true);
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
                    alert('Gagal menyimpan pengaturan ke database. Silakan coba lagi.');
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

            getEquityAmount() {
                return (this.settings.rasio_modal_sendiri / 100) * this.totalCapex;
            },

            getDebtAmount() {
                return (this.settings.rasio_pinjaman_kredit / 100) * this.totalCapex;
            },

            getTotalKasMasukNonOps(t) {
                if (t === 0) {
                    return this.getEquityAmount() + this.getDebtAmount();
                }
                return 0;
            },

            getPMT() {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                const rate = (parseFloat(this.settings.suku_bunga_kredit) || 0) / 100;

                if (debt <= 0 || tenor <= 0) return 0;
                if (rate <= 0) return debt / tenor;

                const factor = Math.pow(1 + rate, tenor);
                return debt * (rate * factor) / (factor - 1);
            },

            getAngsuranPokok(t) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                if (t >= 1 && t <= tenor && debt > 0) {
                    const pmt = this.getPMT();
                    const bunga = this.getBebanBunga(t);
                    return Math.max(0, pmt - bunga);
                }
                return 0;
            },

            getBebanBunga(t) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                const rate = (parseFloat(this.settings.suku_bunga_kredit) || 0) / 100;

                if (t >= 1 && t <= tenor && debt > 0) {
                    return debt * rate;
                }
                return 0;
            },

            getOperasionalBersih(t) {
                const pend = parseFloat(this.pendapatanPerTahun[t]) || 0;
                const opex = parseFloat(this.opexPerTahun[t]) || 0;
                return pend - opex;
            },

            getNetCashflow(t) {
                if (t === 0) {
                    return -this.totalCapex;
                }
                const opBersih = this.getOperasionalBersih(t);
                const pokok = this.getAngsuranPokok(t);
                const bunga = this.getBebanBunga(t);
                return opBersih - pokok - bunga;
            },

            getAkumulasiSaldo(t) {
                let cumulative = this.getNetCashflow(0);
                for (let i = 1; i <= t; i++) {
                    cumulative += this.getNetCashflow(i);
                }
                return cumulative;
            },

            formatRupiah(val) {
                if (val === null || val === undefined || isNaN(val)) return '0';
                const isNeg = val < 0;
                const absVal = Math.abs(val);
                const formatted = new Intl.NumberFormat('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(absVal);
                return isNeg ? `-${formatted}` : formatted;
            },

            async saveSettings() {
                this.isSaving = true;
                this.toast.show = false;
                const success = await this.autoSaveSettings(true);
                this.isSaving = false;
                if (success) {
                    this.toast.isSuccess = true;
                    this.toast.message = 'Pengaturan pembiayaan berhasil disimpan ke database.';
                    this.toast.show = true;
                } else {
                    this.toast.isSuccess = false;
                    this.toast.message = 'Gagal menyimpan pengaturan.';
                    this.toast.show = true;
                }
            }
        }));
    }

    if (window.Alpine) {
        registerCashflow();
    } else {
        document.addEventListener('alpine:init', registerCashflow);
    }
</script>
@endsection
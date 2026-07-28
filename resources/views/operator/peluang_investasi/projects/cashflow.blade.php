@extends('partials.layouts.operator')

@section('title', 'Tabel Arus Kas (Cash Flow) - ' . $project->nama_proyek)
@section('page_heading', 'Tabel Arus Kas (Cash Flow)')

@section('content')
<div x-data="cashflowManager()" x-init="initData()" class="space-y-6">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <nav class="flex text-sm text-slate-500 space-x-2">
            <a href="{{ route('operator.projects.index') }}" class="hover:text-primary font-medium">Proyek</a>
            <span>/</span>
            <a href="{{ route('operator.projects.show', $project->id) }}" class="hover:text-primary font-medium truncate max-w-xs">{{ $project->nama_proyek }}</a>
            <span>/</span>
            <span class="text-slate-800 font-semibold">Tabel Arus Kas</span>
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('operator.projects.show', $project->id) }}" class="px-4 py-2 border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center">
                <svg class="w-4 h-4 mr-2 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Detail
            </a>
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

    <!-- Panel Pengaturan Parameter Pembiayaan (Quick Simulation Bar) -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-4 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    Simulasi Parameter Pembiayaan & Kredit Bank
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Ubah rasio modal, suku bunga, dan tenor di bawah ini untuk menghitung ulang laporan Arus Kas secara otomatis.</p>
            </div>

            <button @click="saveSettings()" :disabled="isSaving" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition-colors flex items-center flex-shrink-0">
                <svg x-show="isSaving" class="animate-spin -ml-1 mr-2 h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Simpan & Hitung Ulang
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Rasio Equity (Modal Sendiri %)</label>
                <input type="number" min="0" max="100" x-model.number="settings.rasio_modal_sendiri" @input="settings.rasio_pinjaman_kredit = 100 - settings.rasio_modal_sendiri" class="w-full bg-slate-50 rounded border border-slate-200 px-3 py-1.5 text-sm font-mono font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Rasio Debt (Pinjaman Kredit %)</label>
                <input type="number" min="0" max="100" x-model.number="settings.rasio_pinjaman_kredit" @input="settings.rasio_modal_sendiri = 100 - settings.rasio_pinjaman_kredit" class="w-full bg-slate-50 rounded border border-slate-200 px-3 py-1.5 text-sm font-mono font-bold text-slate-800">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Suku Bunga Kredit (% / Thn)</label>
                <input type="number" step="0.01" min="0" max="100" x-model.number="settings.suku_bunga_kredit" class="w-full bg-slate-50 rounded border border-slate-200 px-3 py-1.5 text-sm font-mono font-bold text-emerald-700">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Tenor Kredit (Tahun)</label>
                <input type="number" min="1" max="50" x-model.number="settings.tenor_kredit_tahun" class="w-full bg-slate-50 rounded border border-slate-200 px-3 py-1.5 text-sm font-mono font-bold text-purple-700">
            </div>
        </div>
    </div>

    <!-- Main Workspace Tabel Arus Kas -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 overflow-hidden">
        
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 border-b border-slate-100 pb-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Laporan Proyeksi Arus Kas (Cash Flow)</h2>
                <p class="text-xs text-slate-500 mt-1">Hasil kalkulasi otomatis aliran kas masuk, kas keluar, debt service, dan saldo kas dari Tahun 0 s/d Tahun {{ $project->jangka_waktu_tahun }}.</p>
            </div>
            <div class="bg-primary/5 border border-primary/20 px-4 py-2 rounded-lg text-right">
                <span class="block text-[11px] font-bold text-primary uppercase tracking-wider">TOTAL CAPEX TERHITUNG</span>
                <span class="text-lg font-black text-slate-800 font-mono" x-text="formatRupiah(totalCapex)"></span>
            </div>
        </div>

        <!-- Tabel Responsive dengan Sticky Column -->
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200">
                        <th class="px-4 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider sticky left-0 bg-slate-50 z-10 min-w-[280px] shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                            Indikator Arus Kas
                        </th>
                        <th class="px-4 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider text-right bg-amber-50/70 text-amber-900 border-x border-amber-200/60 min-w-[170px]">
                            Tahun 0 (Persiapan)
                        </th>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <th class="px-4 py-3 font-semibold text-slate-600 text-xs uppercase tracking-wider text-right min-w-[170px]" x-text="'Tahun ' + t"></th>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">

                    <!-- SEKSI 1: ARUS KAS INVESTASI AWAL (TAHUN 0) -->
                    <tr class="bg-slate-50/90 font-bold text-slate-800 border-t border-slate-200">
                        <td class="px-4 py-2 text-xs uppercase tracking-wider text-primary sticky left-0 bg-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] z-10">
                            1. ARUS KAS INVESTASI AWAL & PEMBIAYAAN (TAHUN 0)
                        </td>
                        <td colspan="100%" class="bg-slate-50/90"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Pengeluaran Investasi (CAPEX) [-]
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-rose-600 bg-amber-50/30 border-x border-amber-200/40" x-text="'- ' + formatRupiah(totalCapex)"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-400">-</td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]" x-text="'Penerimaan Modal Sendiri (Equity ' + settings.rasio_modal_sendiri + '%) [+]'"></td>
                        <td class="px-4 py-2.5 text-right font-mono text-emerald-600 bg-amber-50/30 border-x border-amber-200/40" x-text="formatRupiah(getEquityAmount())"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-400">-</td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]" x-text="'Penerimaan Kredit Bank (Debt ' + settings.rasio_pinjaman_kredit + '%) [+]'"></td>
                        <td class="px-4 py-2.5 text-right font-mono text-emerald-600 bg-amber-50/30 border-x border-amber-200/40" x-text="formatRupiah(getDebtAmount())"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-400">-</td>
                        </template>
                    </tr>

                    <!-- SEKSI 2: ARUS KAS OPERASIONAL -->
                    <tr class="bg-slate-50/90 font-bold text-slate-800 border-t border-slate-200">
                        <td class="px-4 py-2 text-xs uppercase tracking-wider text-emerald-700 sticky left-0 bg-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] z-10">
                            2. ARUS KAS OPERASIONAL (TAHUN OPERASI)
                        </td>
                        <td colspan="100%" class="bg-slate-50/90"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Kas Masuk Operasional (Pendapatan P&L) [+]
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-400 bg-amber-50/30 border-x border-amber-200/40">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-emerald-600 font-medium" x-text="formatRupiah(pendapatanPerTahun[t] || 0)"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Kas Keluar Operasional (OPEX P&L) [-]
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-400 bg-amber-50/30 border-x border-amber-200/40">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-rose-600" x-text="'- ' + formatRupiah(opexPerTahun[t] || 0)"></td>
                        </template>
                    </tr>
                    <tr class="bg-slate-50/40 font-semibold">
                        <td class="px-4 py-2.5 text-slate-800 sticky left-0 bg-slate-50/90 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Sub-Total Kas Operasional Bersih (EBITDA Kas)
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-400 bg-amber-50/30 border-x border-amber-200/40">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-900 font-bold" x-text="formatRupiah(getOperasionalBersih(t))"></td>
                        </template>
                    </tr>

                    <!-- SEKSI 3: KEWAJIBAN PEMBAYARAN KREDIT (DEBT SERVICE) -->
                    <tr class="bg-slate-50/90 font-bold text-slate-800 border-t border-slate-200">
                        <td class="px-4 py-2 text-xs uppercase tracking-wider text-purple-700 sticky left-0 bg-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] z-10">
                            3. KEWAJIBAN PEMBAYARAN KREDIT BANK (DEBT SERVICE)
                        </td>
                        <td colspan="100%" class="bg-slate-50/90"></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Angsuran Pokok Pinjaman [-]
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-400 bg-amber-50/30 border-x border-amber-200/40">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-700" x-text="getAngsuranPokok(t) > 0 ? '- ' + formatRupiah(getAngsuranPokok(t)) : '-'"></td>
                        </template>
                    </tr>
                    <tr>
                        <td class="px-4 py-2.5 text-slate-700 font-medium sticky left-0 bg-white shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]" x-text="'Beban Bunga Pinjaman (' + settings.suku_bunga_kredit + '% / Thn) [-]'"></td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-400 bg-amber-50/30 border-x border-amber-200/40">-</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-700" x-text="getBebanBunga(t) > 0 ? '- ' + formatRupiah(getBebanBunga(t)) : '-'"></td>
                        </template>
                    </tr>

                    <!-- SEKSI 4: REKAPITULASI KAS FINAL -->
                    <tr class="bg-slate-100 font-bold text-slate-900 border-t-2 border-slate-300">
                        <td class="px-4 py-3 sticky left-0 bg-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                            NET CASH FLOW (ARUS KAS BERSIH)
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-800 bg-amber-100/50 border-x border-amber-300/60" x-text="formatRupiah(getNetCashflow(0))"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-3 text-right font-mono font-extrabold" :class="getNetCashflow(t) >= 0 ? 'text-emerald-700' : 'text-rose-600'" x-text="formatRupiah(getNetCashflow(t))"></td>
                        </template>
                    </tr>

                    <tr class="bg-slate-50 font-semibold">
                        <td class="px-4 py-2.5 text-slate-700 sticky left-0 bg-slate-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                            Saldo Kas Awal
                        </td>
                        <td class="px-4 py-2.5 text-right font-mono text-slate-500 bg-amber-50/30 border-x border-amber-200/40">Rp 0,00</td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-2.5 text-right font-mono text-slate-600" x-text="formatRupiah(getSaldoAwalKas(t))"></td>
                        </template>
                    </tr>

                    <tr class="bg-primary/10 font-extrabold text-primary border-t-2 border-primary/30">
                        <td class="px-4 py-3.5 text-sm uppercase tracking-wider sticky left-0 bg-slate-100 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.1)]">
                            SALDO AKHIR KAS (KUMULATIF)
                        </td>
                        <td class="px-4 py-3.5 text-right font-mono text-slate-800 bg-amber-100/80 border-x border-amber-300" x-text="formatRupiah(getSaldoAkhirKas(0))"></td>
                        <template x-for="t in jangkaWaktu" :key="t">
                            <td class="px-4 py-3.5 text-right font-mono text-base font-black" :class="getSaldoAkhirKas(t) >= 0 ? 'text-primary' : 'text-rose-600'" x-text="formatRupiah(getSaldoAkhirKas(t))"></td>
                        </template>
                    </tr>

                </tbody>
            </table>
        </div>

    </div>
</div>

<script>
    function registerCashflow() {
        Alpine.data('cashflowManager', () => ({
            totalCapex: {{ $totalCapex }},
            jangkaWaktu: {{ $project->jangka_waktu_tahun }},
            pendapatanPerTahun: @json($pendapatanPerTahun),
            opexPerTahun: @json($opexPerTahun),
            isSaving: false,
            toast: { show: false, message: '', isSuccess: true },
            settings: {
                rasio_modal_sendiri: {{ $project->rasio_modal_sendiri }},
                rasio_pinjaman_kredit: {{ $project->rasio_pinjaman_kredit }},
                suku_bunga_kredit: {{ $project->suku_bunga_kredit }},
                tenor_kredit_tahun: {{ $project->tenor_kredit_tahun }},
            },

            initData() {
                // Initial calculation
            },

            getEquityAmount() {
                return (this.settings.rasio_modal_sendiri / 100) * this.totalCapex;
            },

            getDebtAmount() {
                return (this.settings.rasio_pinjaman_kredit / 100) * this.totalCapex;
            },

            getAngsuranPokok(t) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 1;
                if (t <= tenor && debt > 0) {
                    return debt / tenor;
                }
                return 0;
            },

            getBebanBunga(t) {
                const debt = this.getDebtAmount();
                const tenor = parseInt(this.settings.tenor_kredit_tahun) || 5;
                const rate = (parseFloat(this.settings.suku_bunga_kredit) || 0) / 100;
                
                if (t <= tenor && debt > 0) {
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
                    return (this.getEquityAmount() + this.getDebtAmount()) - this.totalCapex;
                }
                const opBersih = this.getOperasionalBersih(t);
                const pokok = this.getAngsuranPokok(t);
                const bunga = this.getBebanBunga(t);
                return opBersih - pokok - bunga;
            },

            getSaldoAwalKas(t) {
                if (t <= 1) {
                    return this.getNetCashflow(0);
                }
                return this.getSaldoAkhirKas(t - 1);
            },

            getSaldoAkhirKas(t) {
                if (t === 0) {
                    return this.getNetCashflow(0);
                }
                let saldo = this.getNetCashflow(0);
                for (let i = 1; i <= t; i++) {
                    saldo += this.getNetCashflow(i);
                }
                return saldo;
            },

            formatRupiah(val) {
                if (val === null || isNaN(val)) return 'Rp 0,00';
                const isNeg = val < 0;
                const absVal = Math.abs(val);
                const formatted = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 2
                }).format(absVal);
                return isNeg ? '- ' + formatted : formatted;
            },

            saveSettings() {
                this.isSaving = true;
                this.toast.show = false;

                fetch("{{ route('operator.projects.cashflow.settings', $project->id) }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(this.settings)
                })
                .then(res => res.json())
                .then(data => {
                    this.isSaving = false;
                    if (data.success) {
                        this.toast.isSuccess = true;
                        this.toast.message = data.message;
                        this.toast.show = true;
                    } else {
                        this.toast.isSuccess = false;
                        this.toast.message = data.message || 'Gagal menyimpan pengaturan.';
                        this.toast.show = true;
                    }
                })
                .catch(() => {
                    this.isSaving = false;
                    this.toast.isSuccess = false;
                    this.toast.message = 'Koneksi server terputus.';
                    this.toast.show = true;
                });
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

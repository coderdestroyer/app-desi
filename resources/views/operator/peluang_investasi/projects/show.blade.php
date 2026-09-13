@extends('partials.layouts.operator')

@section('title', $project->nama_proyek . ' - Detail Proyek IPRO')
@section('page_heading', 'Detail Proyek Investasi')

@section('content')
    <div x-data="{
        showModal: false,
        isSaving: false,
        form: {
            rasio_modal_sendiri: {{ $project->rasio_modal_sendiri ?: 60 }},
            rasio_pinjaman_kredit: {{ $project->rasio_pinjaman_kredit ?: 40 }},
            suku_bunga_kredit: {{ $project->suku_bunga_kredit ?: 8.05 }},
            tenor_kredit_tahun: {{ $project->tenor_kredit_tahun ?: 5 }}
        },
        toast: { show: false, message: '', isSuccess: true },
        saveFinancing() {
            this.isSaving = true;
            fetch('{{ route('operator.projects.cashflow.settings', $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(this.form)
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                if(data.success) {
                    this.showModal = false;
                    this.toast.isSuccess = true;
                    this.toast.message = data.message;
                    this.toast.show = true;
                    setTimeout(() => window.location.reload(), 800);
                } else {
                    alert(data.message || 'Gagal menyimpan parameter.');
                }
            })
            .catch(() => {
                this.isSaving = false;
                alert('Terjadi kesalahan koneksi.');
            });
        }
    }">

        <!-- Toast Alert -->
        <div x-show="toast.show" x-transition class="mb-6 p-4 rounded-xl shadow-sm flex items-center justify-between border bg-[#EEF8F2] border-[#CFE3D5] text-[#145239]">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check text-emerald-600 text-lg mr-3"></i>
                <span class="text-sm font-semibold" x-text="toast.message"></span>
            </div>
            <button @click="toast.show = false" class="text-slate-400 hover:text-slate-600">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <!-- Breadcrumb & Navigasi Kembali -->
        <div class="mb-6 flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-3">
            <nav class="flex text-xs sm:text-sm text-slate-500 space-x-2">
                <a href="{{ route('operator.projects.index') }}" class="hover:text-[#145239] font-medium">Proyek Investasi</a>
                <span>/</span>
                <span class="text-slate-800 font-semibold truncate max-w-[200px] sm:max-w-xs">{{ $project->nama_proyek }}</span>
            </nav>
            <a href="{{ route('operator.projects.index') }}" class="inline-flex items-center justify-center text-xs font-bold text-slate-700 hover:text-[#145239] bg-white px-4 py-2.5 rounded-xl border border-[#CFE3D5] shadow-xs transition-colors w-full sm:w-auto">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                <span>Kembali ke Daftar Proyek</span>
            </a>
        </div>

        <!-- Header Card Proyek -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-5 sm:p-6 mb-6">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-[#EEF8F2] pb-5 sm:pb-6 mb-5 sm:mb-6">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 leading-tight">{{ $project->nama_proyek }}</h1>
                        <span class="px-3 py-1 bg-[#EEF8F2] text-[#145239] font-bold text-xs rounded-full border border-[#CFE3D5] whitespace-nowrap">Proyek Aktif</span>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">{{ $project->deskripsi ?: 'Tidak ada deskripsi rincian proyek.' }}</p>
                </div>
            </div>

            <!-- Stats Metadata -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4">
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Tahun Awal</span>
                    <span class="text-base sm:text-lg font-bold text-slate-800 font-mono mt-1 block">{{ $project->tahun_awal }}</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Jangka Waktu</span>
                    <span class="text-base sm:text-lg font-bold text-[#145239] mt-1 block">{{ $project->jangka_waktu_tahun }} Tahun</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Rentang Proyeksi</span>
                    <span class="text-base sm:text-lg font-bold text-slate-800 font-mono mt-1 block">{{ $project->tahun_awal }} - {{ $project->tahun_awal + $project->jangka_waktu_tahun - 1 }}</span>
                </div>
                <div class="bg-[#F7FAF8] rounded-xl p-3.5 sm:p-4 border border-[#EEF8F2]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-slate-400 uppercase tracking-wider">Kabupaten / Kota</span>
                    <span class="text-xs sm:text-sm font-bold text-slate-700 mt-1 block truncate">{{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut' }}</span>
                </div>
            </div>
        </div>

        <!-- Parameter Skema Pembiayaan & Kredit -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-5 sm:p-6 mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4 mb-4">
                <div>
                    <h2 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <i class="fa-solid fa-scale-balanced text-[#145239]"></i>
                        <span>Parameter Pembiayaan & Kredit Bank</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Skema rasio modal awal dan perhitungan suku bunga pinjaman untuk proyeksi finansial IPRO.</p>
                </div>
                <button @click="showModal = true" class="w-full sm:w-auto px-4 py-2.5 bg-[#EEF8F2] hover:bg-[#E7F2EB] text-[#145239] text-xs font-bold rounded-xl border border-[#CFE3D5] transition-colors flex items-center justify-center gap-1.5 shadow-sm shrink-0">
                    <i class="fa-solid fa-sliders"></i>
                    <span>Ubah Parameter</span>
                </button>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 pt-2">
                <div class="bg-[#EEF8F2] rounded-xl p-3.5 sm:p-4 border border-[#CFE3D5]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#145239] uppercase tracking-wider">Modal Sendiri (Equity)</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->rasio_modal_sendiri ?: 60, 0) }}%</span>
                </div>
                <div class="bg-[#EEF8F2] rounded-xl p-3.5 sm:p-4 border border-[#CFE3D5]">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#145239] uppercase tracking-wider">Pinjaman Kredit (Debt)</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->rasio_pinjaman_kredit ?: 40, 0) }}%</span>
                </div>
                <div class="bg-amber-50 rounded-xl p-3.5 sm:p-4 border border-amber-200">
                    <span class="block text-[11px] sm:text-xs font-semibold text-[#D4A017] uppercase tracking-wider">Suku Bunga Kredit</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ number_format($project->suku_bunga_kredit ?: 8.05, 2) }}% / tahun</span>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3.5 sm:p-4 border border-emerald-200">
                    <span class="block text-[11px] sm:text-xs font-semibold text-emerald-800 uppercase tracking-wider">Tenor Kredit</span>
                    <span class="text-lg sm:text-xl font-black text-slate-800 font-mono mt-1 block">{{ $project->tenor_kredit_tahun ?: 5 }} Tahun</span>
                </div>
            </div>
        </div>

        <!-- Modul Navigasi Kalkulasi (CAPEX, Laba Rugi, Arus Kas) -->
        <div class="mb-6">
            <h2 class="text-base sm:text-lg font-bold text-slate-800 mb-4">Modul Dokumen Finansial (IPRO Engine)</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 sm:gap-6">
                
                <!-- Modul 1: CAPEX -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-[#EEF8F2] text-[#145239] rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-[#CFE3D5]">
                            01
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-[#145239] transition-colors">Tabel Estimasi CAPEX</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Pencatatan & kalkulasi pengeluaran modal awal (biaya persiapan, pembersihan lahan, pembangunan fasilitas) dengan hirarki komponen dinamis.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.capex.index', $project->id) }}" class="w-full py-2.5 px-4 bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-table-list"></i>
                            <span>Kelola Estimasi CAPEX</span>
                        </a>
                    </div>
                </div>

                <!-- Modul 2: Laba Rugi -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-amber-50 text-[#D4A017] rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-amber-200">
                            02
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-[#D4A017] transition-colors">Proyeksi Laba Rugi (P&L)</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Proyeksi pendapatan dan biaya operasional per tahun selama {{ $project->jangka_waktu_tahun }} tahun proyeksi operasi.
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.pl.index', $project->id) }}" class="w-full py-2.5 px-4 bg-[#D4A017] hover:bg-[#b8890f] text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Kelola Laba Rugi (P&L)</span>
                        </a>
                    </div>
                </div>

                <!-- Modul 3: Arus Kas -->
                <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm flex flex-col justify-between hover:border-[#145239] transition-all duration-300 group">
                    <div>
                        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-emerald-50 text-emerald-800 rounded-xl flex items-center justify-center mb-3 sm:mb-4 font-black text-base sm:text-lg border border-emerald-200">
                            03
                        </div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 mb-2 group-hover:text-emerald-800 transition-colors">Tabel Arus Kas (Cash Flow)</h3>
                        <p class="text-xs text-slate-500 leading-relaxed mb-4">
                            Pelacakan aliran kas masuk, kas keluar operasional, non-operasional, dan modal awal (Tahun 0 s/d Tahun {{ $project->jangka_waktu_tahun }}).
                        </p>
                    </div>
                    <div class="pt-4 border-t border-[#EEF8F2]">
                        <a href="{{ route('operator.projects.cashflow.index', $project->id) }}" class="w-full py-2.5 px-4 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-xl flex items-center justify-center transition-colors shadow-md text-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i>
                            <span>Lihat Tabel Arus Kas</span>
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- 2.5.5. Analisis Kelayakan Investasi (NPV, IRR, BCR, Payback Period) -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] p-5 sm:p-6 mb-6 sm:mb-8" x-data="{ showAllYears: false }">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-5 mb-6 border-b border-[#EEF8F2]">
                <div>
                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-1.5">
                        <span class="px-2.5 py-0.5 bg-[#145239] text-white font-bold text-xs rounded-md">2.5.5</span>
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Analisis Kelayakan Investasi</h2>
                    </div>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Kelayakan investasi dengan periode {{ $project->jangka_waktu_tahun }} tahun diukur dengan indikator <strong>NPV</strong>, <strong>BCR</strong>, <strong>IRR</strong>, dan <strong>PP</strong> untuk mengetahui profitabilitas proyek investasi <strong>{{ $project->nama_proyek }}</strong>.
                    </p>
                </div>
                <div>
                    @if($feasibility['is_feasible'])
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-xs">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i>
                            <span>Proyek Layak Investasi (Feasible)</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300 shadow-xs">
                            <i class="fa-solid fa-triangle-exclamation text-[#D4A017]"></i>
                            <span>Perlu Peninjauan Data Finansial</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- 4 Indikator Utama (NPV, IRR, BCR, Payback Period) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                
                <!-- 1. Net Present Value (NPV) -->
                <div class="bg-gradient-to-br from-[#F7FAF8] to-[#EEF8F2] rounded-2xl p-4 sm:p-5 border border-[#CFE3D5] flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-[#145239]/5 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-[#145239] uppercase tracking-wider">Net Present Value</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $feasibility['npv'] >= 0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                {{ $feasibility['npv'] >= 0 ? 'NPV > 0 (Layak)' : 'NPV < 0' }}
                            </span>
                        </div>
                        <div class="text-lg sm:text-xl font-black text-slate-900 font-mono tracking-tight mt-1">
                            Rp {{ number_format($feasibility['npv'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-[#CFE3D5]/60 text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Diskonto (i): <strong class="text-slate-700 font-mono">{{ number_format($feasibility['discount_rate_pct'], 2) }}%</strong></span>
                        <span class="font-medium text-slate-400">Periode {{ $project->jangka_waktu_tahun }} Thn</span>
                    </div>
                </div>

                <!-- 2. Internal Rate of Return (IRR) -->
                <div class="bg-gradient-to-br from-amber-50/50 to-amber-100/40 rounded-2xl p-4 sm:p-5 border border-amber-200 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-amber-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-[#D4A017] uppercase tracking-wider">Internal Rate of Return</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ ($feasibility['irr_pct'] !== null && $feasibility['irr_pct'] >= $feasibility['discount_rate_pct']) ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-amber-100 text-amber-800 border border-amber-300' }}">
                                {{ ($feasibility['irr_pct'] !== null && $feasibility['irr_pct'] >= $feasibility['discount_rate_pct']) ? 'IRR > Diskonto' : 'Hasil Proyeksi' }}
                            </span>
                        </div>
                        <div class="text-lg sm:text-xl font-black text-slate-900 font-mono tracking-tight mt-1">
                            {{ $feasibility['irr_pct'] !== null ? number_format($feasibility['irr_pct'], 2) . '%' : 'N/A' }}
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-amber-200/60 text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Tingkat Pengembalian</span>
                        <span class="font-medium text-slate-500 font-mono">Tahun ke-{{ $project->jangka_waktu_tahun }}</span>
                    </div>
                </div>

                <!-- 3. Benefit Cost Ratio (BCR) -->
                <div class="bg-gradient-to-br from-blue-50/50 to-blue-100/40 rounded-2xl p-4 sm:p-5 border border-blue-200 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-blue-500/5 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-blue-800 uppercase tracking-wider">Benefit Cost Ratio (BCR)</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md {{ $feasibility['bcr'] >= 1.0 ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300' }}">
                                {{ $feasibility['bcr'] >= 1.0 ? 'BCR ≥ 1.0 (Untung)' : 'BCR < 1.0' }}
                            </span>
                        </div>
                        <div class="text-lg sm:text-xl font-black text-slate-900 font-mono tracking-tight mt-1">
                            {{ number_format($feasibility['bcr'], 2, ',', '.') }}
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-blue-200/60 text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Rasio Manfaat / Biaya</span>
                        <span class="font-mono text-slate-600 font-medium">B / C</span>
                    </div>
                </div>

                <!-- 4. Payback Period (PP) -->
                <div class="bg-gradient-to-br from-emerald-50/60 to-emerald-100/50 rounded-2xl p-4 sm:p-5 border border-emerald-200 flex flex-col justify-between relative overflow-hidden group hover:shadow-md transition-all">
                    <div class="absolute -right-4 -top-4 w-16 h-16 bg-emerald-600/5 rounded-full blur-xl group-hover:scale-150 transition-transform"></div>
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Payback Period (PP)</span>
                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-300">
                                {{ $feasibility['payback_tahun'] !== null ? '< Umur Proyek' : 'Proyeksi' }}
                            </span>
                        </div>
                        <div class="text-base sm:text-lg font-black text-slate-900 font-mono tracking-tight mt-1">
                            {{ $feasibility['payback_text'] }}
                        </div>
                    </div>
                    <div class="mt-3 pt-3 border-t border-emerald-200/60 text-[11px] text-slate-500 flex items-center justify-between">
                        <span>Balik Modal Awal</span>
                        <span class="font-mono text-emerald-700 font-bold">Thn ke-{{ $feasibility['payback_tahun'] !== null ? $feasibility['payback_tahun'] + 1 : '-' }}</span>
                    </div>
                </div>

            </div>

            <!-- Detail Narasi & Tabel Benefit Cost Ratio (BC) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-8">
                
                <!-- Narasi Indikator -->
                <div class="lg:col-span-7 space-y-4">
                    <div class="bg-[#F7FAF8] rounded-xl p-4 sm:p-5 border border-[#CFE3D5]/80">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-[#145239] mb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Keterangan Indikator Finansial</span>
                        </h4>
                        <ul class="text-xs text-slate-600 space-y-2.5 leading-relaxed">
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-[#145239] mt-1.5 shrink-0"></span>
                                <div>
                                    <strong class="text-slate-800">Net Present Value (NPV):</strong> Metode yang menghitung selisih antara manfaat atau penerimaan dengan biaya atau pengeluaran yang didiskontokan ke nilai sekarang.
                                    <div class="mt-1 font-mono text-[11px] text-slate-500 bg-white px-2.5 py-1 rounded-md border border-[#CFE3D5] inline-block">
                                        Rumus NPV = ∑ [Arus Kas_t / (1 + i)^t] - Investasi Awal
                                    </div>
                                </div>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 mt-1.5 shrink-0"></span>
                                <div>
                                    <strong class="text-slate-800">Internal Rate of Return (IRR):</strong> Tingkat pengembalian tahunan internal di mana nilai sekarang bersih (NPV) dari seluruh arus kas bernilai nol.
                                </div>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 mt-1.5 shrink-0"></span>
                                <div>
                                    <strong class="text-slate-800">Benefit Cost Ratio (BCR):</strong> Perbandingan antara akumulasi penerimaan (benefit) dengan total biaya modal (cost). Nilai BCR > 1 menunjukkan proyek menguntungkan secara finansial.
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Tabel Benefit Cost Ratio (BC) -->
                <div class="lg:col-span-5">
                    <div class="bg-white rounded-xl border border-[#CFE3D5] shadow-xs overflow-hidden h-full flex flex-col justify-between">
                        <div>
                            <div class="bg-[#145239] px-4 py-3 text-white flex items-center justify-between">
                                <h4 class="text-xs sm:text-sm font-bold tracking-wide">Tabel Benefit Cost Ratio (BC)</h4>
                                <span class="text-[11px] font-medium text-emerald-200">B/C Analysis</span>
                            </div>
                            <div class="divide-y divide-[#EEF8F2] text-xs">
                                <div class="flex items-center justify-between p-3.5 bg-slate-50/50">
                                    <span class="font-semibold text-slate-700">Total Benefit (Rp)</span>
                                    <span class="font-mono font-bold text-slate-900">Rp {{ number_format($feasibility['total_benefit'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3.5">
                                    <span class="font-semibold text-slate-700">Total Cost (Rp)</span>
                                    <span class="font-mono font-bold text-slate-900">Rp {{ number_format($feasibility['total_cost'], 0, ',', '.') }}</span>
                                </div>
                                <div class="flex items-center justify-between p-3.5 bg-[#EEF8F2] font-bold text-[#145239]">
                                    <span class="text-xs uppercase tracking-wider">Benefit Cost Ratio (BCR)</span>
                                    <span class="font-mono text-sm sm:text-base font-black">{{ number_format($feasibility['bcr'], 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="px-4 py-2 bg-slate-50 border-t border-[#EEF8F2] text-[11px] text-slate-400 italic">
                            Sumber: Konsultan / Analisis Finansial Proyek, {{ date('Y') }}
                        </div>
                    </div>
                </div>

            </div>

            <!-- Tabel Perhitungan Payback Period (PP) -->
            <div class="bg-white rounded-xl border border-[#CFE3D5] shadow-xs overflow-hidden">
                <div class="bg-[#1F497D] px-4 sm:px-5 py-3.5 text-white flex flex-col sm:flex-row justify-between sm:items-center gap-2">
                    <div>
                        <h4 class="text-sm font-bold tracking-wide flex items-center gap-2">
                            <i class="fa-solid fa-clock-rotate-left text-blue-200"></i>
                            <span>Tabel Perhitungan Payback Period (PP)</span>
                        </h4>
                        <p class="text-[11px] text-blue-100 mt-0.5">Jadwal saldo kas masuk operasional dan pelacakan akumulasi pengembalian modal investasi.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" @click="showAllYears = !showAllYears" class="px-3 py-1 bg-white/10 hover:bg-white/20 text-white rounded-lg text-xs font-semibold border border-white/20 transition-colors flex items-center gap-1.5">
                            <span x-text="showAllYears ? 'Tampilkan Ringkas' : 'Lihat Seluruh Tahun ({{ count($feasibility['payback_schedule']) }})'"></span>
                            <i class="fa-solid" :class="showAllYears ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto max-h-[440px] overflow-y-auto">
                    <table class="w-full text-left text-xs border-collapse">
                        <thead class="bg-[#2E5B82] text-white uppercase text-[11px] font-bold sticky top-0 z-10">
                            <tr>
                                <th class="py-2.5 px-4 w-24 text-center border-r border-blue-900/30">Tahun</th>
                                <th class="py-2.5 px-4 text-right border-r border-blue-900/30">Saldo (Arus Kas Bersih)</th>
                                <th class="py-2.5 px-4 text-right">Akumulasi Saldo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @foreach($feasibility['payback_schedule'] as $index => $row)
                                @php
                                    $isBreakeven = $row['is_breakeven'];
                                    $isTahun0 = ($row['tahun'] === 0);
                                @endphp
                                <tr 
                                    x-show="showAllYears || {{ $row['tahun'] <= 12 || $isBreakeven ? 'true' : 'false' }}"
                                    class="{{ $isBreakeven ? 'bg-emerald-100/70 font-bold text-emerald-950 hover:bg-emerald-100' : ($isTahun0 ? 'bg-slate-50 font-semibold' : ($index % 2 === 0 ? 'bg-white' : 'bg-[#F9FBFA]')) }} hover:bg-emerald-50/50 transition-colors"
                                >
                                    <td class="py-2 px-4 text-center font-mono border-r border-slate-100">
                                        @if($isBreakeven)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-600 text-white text-[10px] font-bold">
                                                Thn {{ $row['tahun'] }} <i class="fa-solid fa-check"></i>
                                            </span>
                                        @else
                                            <span>{{ $row['tahun'] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono border-r border-slate-100">
                                        @if($row['saldo'] < 0)
                                            <span class="text-rose-600 font-semibold">({{ number_format(abs($row['saldo']), 0, ',', '.') }})</span>
                                        @else
                                            <span class="text-slate-800">{{ number_format($row['saldo'], 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-4 text-right font-mono">
                                        @if($row['akumulasi'] < 0)
                                            <span class="text-rose-600 font-semibold">({{ number_format(abs($row['akumulasi']), 0, ',', '.') }})</span>
                                        @else
                                            <span class="text-emerald-700 font-bold">{{ number_format($row['akumulasi'], 0, ',', '.') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Footer Highlight Bar Payback Period -->
                <div class="bg-[#FBE4D5] px-4 sm:px-6 py-3 border-t border-[#F8CBAD] flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div class="flex items-center gap-2 text-slate-800 font-bold text-xs sm:text-sm">
                        <i class="fa-solid fa-flag-checkered text-[#C55A11]"></i>
                        <span>Payback Period</span>
                    </div>
                    <div class="font-mono text-sm sm:text-base font-black text-slate-900 bg-white/70 px-3 py-1 rounded-lg border border-[#F8CBAD]">
                        {{ $feasibility['payback_text'] }}
                    </div>
                </div>

                <div class="px-4 py-2 bg-slate-50 border-t border-slate-200 text-[11px] text-slate-400 italic">
                    Sumber: Konsultan / Analisis Finansial IPRO, {{ date('Y') }}
                </div>
            </div>
        </div>

        <!-- Modal Edit Parameter Pembiayaan -->
        <template x-teleport="body">
            <div x-show="showModal" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto overflow-x-hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-transition>
                <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-[#CFE3D5] relative my-auto max-h-[90vh] flex flex-col overflow-hidden box-border" @click.outside="showModal = false">
                    <div class="flex justify-between items-center mb-4 pb-3 border-b border-[#EEF8F2] shrink-0">
                        <h3 class="font-bold text-slate-800 text-base">Ubah Parameter Pembiayaan</h3>
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 shrink-0">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>

                    <div class="space-y-4 text-sm flex-1 overflow-y-auto pr-1">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Rasio Modal Sendiri (Equity %)</label>
                            <input type="number" min="0" max="100" x-model.number="form.rasio_modal_sendiri" @input="form.rasio_pinjaman_kredit = 100 - form.rasio_modal_sendiri" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Rasio Pinjaman Kredit (Debt %)</label>
                            <input type="number" min="0" max="100" x-model.number="form.rasio_pinjaman_kredit" @input="form.rasio_modal_sendiri = 100 - form.rasio_pinjaman_kredit" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm bg-slate-50 box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Suku Bunga Kredit (% per Tahun)</label>
                            <input type="number" step="0.01" min="0" max="100" x-model.number="form.suku_bunga_kredit" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tenor Kredit (Tahun)</label>
                            <input type="number" min="1" max="50" x-model.number="form.tenor_kredit_tahun" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] font-mono text-sm box-border">
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col-reverse sm:flex-row justify-end gap-2 pt-4 border-t border-[#EEF8F2] shrink-0">
                        <button @click="showModal = false" class="w-full sm:w-auto px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button @click="saveFinancing()" :disabled="isSaving" class="w-full sm:w-auto px-5 py-2.5 bg-[#145239] hover:bg-[#0B5D3D] text-white rounded-xl text-xs font-bold transition-colors flex items-center justify-center">
                            <span x-text="isSaving ? 'Menyimpan...' : 'Simpan Parameter'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
@endsection

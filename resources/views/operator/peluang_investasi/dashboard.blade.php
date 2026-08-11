@extends('partials.layouts.operator')

@section('title', 'Dashboard Peluang Investasi')
@section('page_heading', 'Dashboard Peluang Investasi')

@section('content')

    <!-- Welcome Header Banner -->
    <div class="bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] rounded-2xl p-6 sm:p-8 md:p-10 pb-16 sm:pb-20 md:pb-24 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-5">
        <!-- Background Ornaments -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-400 rounded-full mix-blend-overlay filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 right-32 w-48 h-48 bg-[#FFD54F] rounded-full mix-blend-overlay filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute top-10 left-1/2 w-72 h-72 bg-emerald-500 rounded-full mix-blend-overlay filter blur-3xl opacity-10 animate-blob animation-delay-4000"></div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>

        <div class="relative z-10 text-white flex-1 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/50 border border-emerald-700/50 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-chart-line text-xs text-[#FFD54F]"></i>
                <span>Dashboard Peluang Investasi</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight">
                Selamat Datang, <span class="text-[#FFD54F]">{{ Auth::user()->name }}</span>
            </h1>
            <p class="text-emerald-100/90 font-medium max-w-2xl text-xs sm:text-sm leading-relaxed">
                Kelola dan susun studi kelayakan finansial proyek investasi *Ready to Offer* daerah meliputi Komputasi CAPEX, Proyeksi Laba Rugi (P&L), dan Arus Kas (Dynamic Real-Time Cash Flow).
            </p>
        </div>

        <!-- Right Side Date Info -->
        <div class="relative z-10 flex flex-col items-start md:items-end justify-center shrink-0">
            <span class="text-xl sm:text-2xl md:text-3xl font-black text-white tracking-tight drop-shadow-md">{{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}</span>
            <span class="text-[10px] sm:text-xs font-semibold text-[#FFD54F] uppercase tracking-wider mt-0.5">Tanggal Sistem</span>
        </div>
    </div>

    <!-- Executive Stats Row (Overlapping Header) -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 sm:gap-4 !-mt-10 sm:!-mt-12 md:!-mt-16 relative z-20 px-1 sm:px-2 md:px-4 mb-8">
        
        <!-- Stat Card 1: Total Proyek -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#CFE3D5] shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-[#EEF8F2] rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500"></div>
            <p class="text-[11px] sm:text-xs font-bold text-slate-500 mb-1.5 sm:mb-2 relative z-10 truncate">Total Proyek IPRO</p>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-2xl sm:text-3xl font-black text-slate-800 font-mono">{{ $projects->count() }}</span>
                <div class="bg-[#145239] text-white p-2 sm:p-2.5 rounded-xl shadow-xs shrink-0">
                    <i class="fa-solid fa-briefcase text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Stat Card 2: Kab/Kota -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#CFE3D5] shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-blue-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500"></div>
            <p class="text-[11px] sm:text-xs font-bold text-slate-500 mb-1.5 sm:mb-2 relative z-10 truncate">Kab/Kota Terjangkau</p>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-2xl sm:text-3xl font-black text-slate-800 font-mono">{{ $projects->pluck('kabupaten_id')->unique()->filter()->count() }}</span>
                <div class="bg-blue-600 text-white p-2 sm:p-2.5 rounded-xl shadow-xs shrink-0">
                    <i class="fa-solid fa-location-dot text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Stat Card 3: Rata Tenor Kredit -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#CFE3D5] shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-amber-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500"></div>
            <p class="text-[11px] sm:text-xs font-bold text-slate-500 mb-1.5 sm:mb-2 relative z-10 truncate">Rata Tenor Kredit</p>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-2xl sm:text-3xl font-black text-slate-800 font-mono">{{ number_format($projects->avg('tenor_kredit_tahun') ?: 5, 0) }} <span class="text-xs sm:text-sm font-normal text-slate-500">Thn</span></span>
                <div class="bg-[#D4A017] text-white p-2 sm:p-2.5 rounded-xl shadow-xs shrink-0">
                    <i class="fa-solid fa-clock-rotate-left text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

        <!-- Stat Card 4: Status Terbit -->
        <div class="bg-white rounded-2xl p-4 sm:p-5 border border-[#CFE3D5] shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500"></div>
            <p class="text-[11px] sm:text-xs font-bold text-slate-500 mb-1.5 sm:mb-2 relative z-10 truncate">Proyek Published</p>
            <div class="flex items-center justify-between relative z-10">
                <span class="text-2xl sm:text-3xl font-black text-[#145239] font-mono">{{ $projects->where('status_publikasi', 'published')->count() }}</span>
                <div class="bg-emerald-600 text-white p-2 sm:p-2.5 rounded-xl shadow-xs shrink-0">
                    <i class="fa-solid fa-circle-check text-base sm:text-lg"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Navigation Banner Card to Kalkulasi & Daftar Proyek -->
    <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#145239] rounded-2xl p-5 sm:p-6 md:p-8 text-white shadow-md mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-5">
        <div class="space-y-1">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-amber-300 text-xs font-semibold">
                <i class="fa-solid fa-calculator"></i>
                <span>Modul Kalkulasi Proyek</span>
            </div>
            <h2 class="text-lg sm:text-xl font-bold">Kelola Kalkulasi & Daftar Proyek Investasi</h2>
            <p class="text-xs text-slate-300 max-w-xl leading-relaxed">
                Buka halaman daftar proyek untuk menambah proyek baru, mengedit rincian CAPEX, mengatur parameter rasio pembiayaan (Debt:Equity), dan mencetak tabel arus kas.
            </p>
        </div>
        <a href="{{ route('operator.projects.index') }}" class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-[#FFC93B] text-[#17201C] font-extrabold text-xs sm:text-sm shadow-lg transition-all duration-300 flex items-center justify-center gap-2 shrink-0 transform hover:-translate-y-0.5">
            <i class="fa-solid fa-folder-open text-xs"></i>
            <span>Buka Kalkulasi & Daftar Proyek</span>
        </a>
    </div>

    <!-- Status Sub-Modul Financial IPRO -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6 mb-8">
        
        <!-- Modul 1: CAPEX -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5] flex items-center justify-center text-lg sm:text-xl mb-3 sm:mb-4 font-black">
                01
            </div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-1">Estimasi Biaya Awal (CAPEX)</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Pencatatan & komputasi biaya modal persiapan lahan, infrastruktur, peralatan, dan perizinan proyek secara dinamis.
            </p>
            <div class="flex items-center justify-between pt-3 border-t border-[#EEF8F2] text-xs">
                <span class="text-slate-400 font-medium">Status Fitur</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-emerald-100 text-emerald-800">Aktif & Siap</span>
            </div>
        </div>

        <!-- Modul 2: P&L -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 text-[#D4A017] border border-amber-200 flex items-center justify-center text-lg sm:text-xl mb-3 sm:mb-4 font-black">
                02
            </div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-1">Proyeksi Laba Rugi (P&L)</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Perhitungan proyeksi pendapatan operasional, biaya OPEX, Pajak Penghasilan (PPh 22%), dan nominal depresiasi.
            </p>
            <div class="flex items-center justify-between pt-3 border-t border-[#EEF8F2] text-xs">
                <span class="text-slate-400 font-medium">Status Fitur</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-amber-100 text-[#D4A017]">Aktif & Siap</span>
            </div>
        </div>

        <!-- Modul 3: Arus Kas -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm hover:shadow-md transition-all duration-300">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 flex items-center justify-center text-lg sm:text-xl mb-3 sm:mb-4 font-black">
                03
            </div>
            <h3 class="text-sm sm:text-base font-bold text-slate-900 mb-1">Tabel Arus Kas (Cash Flow)</h3>
            <p class="text-xs text-slate-500 leading-relaxed mb-4">
                Komputasi otomatis aliran kas masuk/keluar, skema rasio hutang/modal (40% Debt : 60% Equity), dan angsuran pinjaman bank.
            </p>
            <div class="flex items-center justify-between pt-3 border-t border-[#EEF8F2] text-xs">
                <span class="text-slate-400 font-medium">Status Fitur</span>
                <span class="px-2.5 py-0.5 rounded-full font-bold bg-blue-100 text-blue-800">Aktif & Siap</span>
            </div>
        </div>

    </div>

    <!-- Ringkasan Proyek Terbaru -->
    <div class="bg-white rounded-2xl border border-[#CFE3D5] p-5 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-4 border-b border-[#EEF8F2] mb-5 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#145239]"></i>
                    <span>Ringkasan Proyek Terbaru</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Daftar proyek investasi yang baru saja diperbarui di sistem</p>
            </div>
            <a href="{{ route('operator.projects.index') }}" class="text-xs font-bold text-[#145239] hover:underline flex items-center gap-1">
                <span>Lihat Semua Proyek</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        <div class="space-y-3">
            @forelse($recentProjects as $project)
                <div class="p-3.5 sm:p-4 rounded-xl bg-[#F7FAF8] border border-[#EEF8F2] hover:border-[#CFE3D5] transition-all flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5] flex items-center justify-center font-bold text-sm shrink-0">
                            <i class="fa-solid fa-briefcase"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900 hover:text-[#145239] transition-colors">
                                <a href="{{ route('operator.projects.show', $project->id) }}">
                                    {{ $project->nama_proyek }}
                                </a>
                            </h4>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs text-slate-500 mt-1">
                                <span><i class="fa-solid fa-location-dot text-[#145239] mr-1"></i> {{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut' }}</span>
                                <span class="hidden sm:inline">•</span>
                                <span><i class="fa-solid fa-calendar text-slate-400 mr-1"></i> {{ $project->tahun_awal }} ({{ $project->jangka_waktu_tahun }} Thn)</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 w-full sm:w-auto justify-end">
                        <a href="{{ route('operator.projects.show', $project->id) }}" class="w-full sm:w-auto px-3.5 py-1.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold transition-colors text-center">
                            Kelola Kalkulasi
                        </a>
                    </div>
                </div>
            @empty
                <div class="text-center py-8 text-slate-400 text-xs">
                    Belum ada proyek investasi yang tercatat. Buka menu 'Kalkulasi & Daftar Proyek' untuk membuat proyek baru.
                </div>
            @endforelse
        </div>
    </div>

@endsection

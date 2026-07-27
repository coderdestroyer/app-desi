@extends('partials.layouts.operator')

@section('content')
<div class="max-w-6xl mx-auto py-6 px-4">
    <!-- Welcome Header -->
    <div class="bg-gradient-to-r from-[#145239] via-[#0B5D3D] to-[#1E5D41] rounded-2xl p-8 md:p-10 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6 mb-8">
        <!-- Background Ornaments -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-400 rounded-full mix-blend-overlay filter blur-3xl opacity-20"></div>
        <div class="absolute bottom-0 right-32 w-48 h-48 bg-[#FFD54F] rounded-full mix-blend-overlay filter blur-3xl opacity-20"></div>
        
        <div class="relative z-10 text-white flex-1">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-emerald-900/60 border border-emerald-500/30 text-emerald-100 text-xs font-semibold mb-4 backdrop-blur-sm">
                <svg class="w-4 h-4 text-[#FFD54F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                Pusat Kerja Operator DPMPTSP
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-2">Selamat Datang, <span class="text-[#FFD54F]">{{ Auth::user()->name }}</span></h1>
            <p class="text-emerald-100/90 font-normal text-sm md:text-base leading-relaxed max-w-2xl">
                Silakan pilih ruang kerja yang ingin Anda kelola hari ini. Anda dapat mengolah data makroekonomi daerah atau menyusun kalkulasi finansial proyek investasi.
            </p>
        </div>

        <div class="relative z-10 text-right hidden md:block">
            <span class="text-xs uppercase font-bold tracking-wider text-[#FFD54F] block mb-1">Tanggal Sistem</span>
            <span class="text-2xl font-black text-white">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
        </div>
    </div>

    <!-- Choice Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- OPTION 1: POTENSI UNGGULAN DAERAH (MAKRO) -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col justify-between group relative">
            <div class="h-3 bg-gradient-to-r from-[#145239] to-[#0F8A5F]"></div>
            
            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-[#E7F2EB] flex items-center justify-center text-[#145239] group-hover:bg-[#145239] group-hover:text-white transition-colors duration-300 shadow-sm">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-[#145239] border border-emerald-200">
                        Makroekonomi
                    </span>
                </div>

                <h2 class="text-2xl font-bold text-[#17201C] mb-3 group-hover:text-[#145239] transition-colors">
                    Potensi Unggulan Daerah
                </h2>
                <p class="text-[#667069] text-sm leading-relaxed mb-6">
                    Kelola data PDRB daerah, lakukan perhitungan otomatis metode LQ, Shift-Share, Tipologi Sektor, dan Tipologi Klassen untuk pemetaan sektor basis di Sumatera.
                </p>

                <!-- Feature List -->
                <ul class="space-y-3 mb-8 text-sm text-[#17201C]">
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-[#145239] flex items-center justify-center text-xs font-bold">✓</span>
                        Analisis Location Quotient (LQ Sektor Basis)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-[#145239] flex items-center justify-center text-xs font-bold">✓</span>
                        Shift-Share Analysis (Komponen N, P, D)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-[#145239] flex items-center justify-center text-xs font-bold">✓</span>
                        Matriks Tipologi Sektor & Klassen (Kuadran I-IV)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-[#145239] flex items-center justify-center text-xs font-bold">✓</span>
                        Impor Massal Data via Excel (.xlsx) & Sync DB
                    </li>
                </ul>
            </div>

            <div class="p-8 pt-0">
                <a href="{{ route('operator.potensi-unggulan') }}" class="w-full py-3.5 px-6 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-300 shadow-md group-hover:shadow-lg">
                    <span>Masuk Modul Potensi Unggulan</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>

        <!-- OPTION 2: PELUANG INVESTASI DAERAH (MIKRO / IPRO) -->
        <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-lg hover:shadow-2xl transition-all duration-300 overflow-hidden flex flex-col justify-between group relative">
            <div class="h-3 bg-gradient-to-r from-[#D4A017] via-[#FFD54F] to-[#001E6C]"></div>

            <div class="p-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center text-[#D4A017] group-hover:bg-[#D4A017] group-hover:text-white transition-colors duration-300 shadow-sm">
                        <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-amber-100 text-[#D4A017] border border-amber-200">
                        Mikro Finansial (IPRO)
                    </span>
                </div>

                <h2 class="text-2xl font-bold text-[#17201C] mb-3 group-hover:text-[#D4A017] transition-colors">
                    Peluang Investasi (Dokumen IPRO)
                </h2>
                <p class="text-[#667069] text-sm leading-relaxed mb-6">
                    Digitalisasi penyusunan dokumen kelayakan proyek *Ready to Offer* meliputi rincian CAPEX berjenjang, proyeksi Laba Rugi (P&L), dan Arus Kas otomatis.
                </p>

                <!-- Feature List -->
                <ul class="space-y-3 mb-8 text-sm text-[#17201C]">
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-[#D4A017] flex items-center justify-center text-xs font-bold">✓</span>
                        Pencatatan Hierarki CAPEX (Auto Roll-Up)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-[#D4A017] flex items-center justify-center text-xs font-bold">✓</span>
                        Proyeksi Laba Rugi Tahunan (P&L, PPh %, Fee)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-[#D4A017] flex items-center justify-center text-xs font-bold">✓</span>
                        Automated Cash Flow Engine (Equity/Debt 60:40)
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full bg-amber-100 text-[#D4A017] flex items-center justify-center text-xs font-bold">✓</span>
                        Integrasi Titik Koordinat GIS & Kabupaten
                    </li>
                </ul>
            </div>

            <div class="p-8 pt-0">
                <a href="{{ route('operator.peluang-investasi') }}" class="w-full py-3.5 px-6 rounded-xl bg-[#D4A017] hover:bg-[#b8890f] text-white font-semibold text-sm flex items-center justify-center gap-2 transition-all duration-300 shadow-md group-hover:shadow-lg">
                    <span>Masuk Modul Peluang Investasi</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                    </svg>
                </a>
            </div>
        </div>

    </div>
</div>
@endsection

@extends('partials.layouts.operator')

@section('title', 'Data PDB Nasional')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-sky-50 border border-sky-200 text-sky-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-sky-600 text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-sky-500 hover:text-sky-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Header Banner (Read-Only Mode) -->
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-globe text-[#FFD54F]"></i>
                <span>Referensi Makroekonomi Nasional (Read Only)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Data PDB Nasional
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Informasi Produk Domestik Bruto (PDB) Nasional per 17 Sektor Lapangan Usaha. Data ini digunakan sebagai acuan pembanding dalam perhitungan Analisis Makroekonomi (LQ, Shift-Share, Tipologi Sektor, & Klassen).
            </p>
        </div>

        <div class="relative z-10 w-full sm:w-auto shrink-0">
            <div class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-semibold flex items-center justify-center gap-2 backdrop-blur-sm shadow-xs">
                <i class="fa-solid fa-lock text-[#FFD54F]"></i>
                <span>Akses Mode: Read Only</span>
            </div>
        </div>
    </section>

    <!-- Filter Section Component -->
    <x-pdb-filter-bar
        :action="route('operator.pdb-nasional.index')"
        :available-years="$availableYears"
    />

    <!-- Data Table Card (Read Only) -->
    <div class="bg-white rounded-2xl border border-[#CFE3D5] shadow-xs p-4 sm:p-5 md:p-6">
        <div class="mb-5 sm:mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 sm:gap-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-globe text-[#145239]"></i>
                    <span>Daftar Record PDB Nasional</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Daftar PDB Nasional 17 Sektor Lapangan Usaha yang terdaftar di sistem.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <i class="fa-solid fa-globe"></i>
                {{ number_format($pdbGroups->total(), 0, ',', '.') }} Data
            </div>
        </div>

        <div id="tableContainer" class="transition-opacity duration-200">
            @include('operator.potensi_unggulan.pdb_nasional.partials.table')
        </div>
    </div>

</div>
@endsection

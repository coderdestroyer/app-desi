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
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:p-5 sm:flex-row sm:items-center">
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
        </header>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[700px] border-collapse text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5 text-center w-36">Tahun PDB Nasional</th>
                        <th class="px-4 py-3.5 text-center min-w-[160px]">Sektor Terisi</th>
                        <th class="px-5 py-3.5 text-right min-w-[200px]">Total PDB Nasional (Rp Juta)</th>
                        <th class="px-4 py-3.5 text-center w-36">Akses Otorisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pdbGroups as $index => $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-4 text-center text-slate-400 font-mono">
                                {{ $pdbGroups->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-semibold whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ $group->tahun }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                @if($group->total_sektor >= 17)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#145239] border border-emerald-200 leading-snug">
                                        <i class="fa-solid fa-circle-check text-[11px] text-emerald-600"></i>
                                        <span>{{ $group->total_sektor }}/17 Sektor</span>
                                    </span>
                                @elseif($group->total_sektor > 0)
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200 leading-snug">
                                        <i class="fa-solid fa-clock text-[11px] text-amber-600"></i>
                                        <span>{{ $group->total_sektor }}/17 Sektor</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200 leading-snug">
                                        <i class="fa-solid fa-circle-minus text-[11px] text-slate-400"></i>
                                        <span>0 Sektor</span>
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900 text-sm whitespace-nowrap">
                                Rp {{ number_format($group->total_pdb, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 text-slate-600 font-semibold text-xs border border-slate-200 whitespace-nowrap">
                                    <i class="fa-solid fa-lock text-[10px] text-slate-400"></i>
                                    Read Only
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                                Belum ada data PDB Nasional yang terdaftar di sistem.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Component -->
        <x-pagination :paginator="$pdbGroups" />
    </div>

</div>
@endsection

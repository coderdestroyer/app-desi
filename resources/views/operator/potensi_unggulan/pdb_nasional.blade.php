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
    <div class="bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="relative z-10 text-white flex-1 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/50 border border-emerald-700/50 text-emerald-100 text-xs font-bold backdrop-blur-sm">
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

        <div class="relative z-10">
            <div class="px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-emerald-100 text-xs font-semibold flex items-center gap-2">
                <i class="fa-solid fa-lock text-[#FFD54F]"></i>
                <span>Akses Mode: Read Only</span>
            </div>
        </div>
    </div>

    <!-- Filter Section (Proporsi 50:50 Tanpa Search Bar) -->
    <section class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <form
            action="{{ route('operator.pdb-nasional.index') }}"
            method="GET"
            class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12"
        >
            {{-- Filter Tahun PDB (50%) --}}
            <div class="relative min-w-0 md:col-span-1 xl:col-span-6">
                <select
                    name="tahun"
                    id="filterTahun"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium font-mono text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">Semua Tahun PDB</option>
                    @foreach ($availableYears as $yr)
                        <option
                            value="{{ $yr }}"
                            @selected(request('tahun') == $yr)
                        >
                            {{ $yr }}
                        </option>
                    @endforeach
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            {{-- Action Buttons (50%) --}}
            <div class="flex min-w-0 gap-2 md:col-span-1 xl:col-span-6">
                <button
                    type="submit"
                    class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span class="truncate">Terapkan</span>
                </button>

                <a
                    href="{{ route('operator.pdb-nasional.index') }}"
                    title="Reset filter"
                    class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600 shadow-sm"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </section>

    <!-- Data Table Card (Read Only) -->
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Daftar Record PDB Nasional
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

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5 text-center">Tahun PDB Nasional</th>
                        <th class="px-5 py-3.5 text-center">Sektor Terisi</th>
                        <th class="px-5 py-3.5 text-right">Total PDB Nasional (Rp Juta)</th>
                        <th class="px-5 py-3.5 text-center w-36">Akses Otorisasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pdbGroups as $index => $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 text-center text-slate-400">
                                {{ $pdbGroups->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4 text-center font-mono font-semibold">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-100 border border-slate-200 text-slate-800">
                                    {{ $group->tahun }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-[#145239] border border-emerald-200">
                                    <i class="fa-solid fa-check text-[10px]"></i>
                                    {{ $group->total_sektor }} Sektor Terisi
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-mono font-bold text-slate-900 text-sm">
                                Rp {{ number_format($group->total_pdb, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold text-xs border border-slate-200">
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

        @if ($pdbGroups->total() > 0)
            <footer class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="m-0 text-xs text-slate-500">
                    Menampilkan <strong class="text-slate-700">{{ $pdbGroups->firstItem() }}</strong>–<strong class="text-slate-700">{{ $pdbGroups->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($pdbGroups->total(), 0, ',', '.') }}</strong> data
                </p>

                @if ($pdbGroups->hasPages())
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($pdbGroups->onFirstPage())
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </span>
                        @else
                            <a href="{{ $pdbGroups->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </a>
                        @endif

                        @php
                            $currentPage = $pdbGroups->currentPage();
                            $lastPage = $pdbGroups->lastPage();
                            $pages = collect([1, 2, $currentPage - 1, $currentPage, $currentPage + 1, $lastPage - 1, $lastPage])
                                ->filter(fn ($page) => $page >= 1 && $page <= $lastPage)
                                ->unique()
                                ->sort()
                                ->values();
                            $previousPageNumber = null;
                        @endphp

                        @foreach ($pages as $page)
                            @if ($previousPageNumber && $page - $previousPageNumber > 1)
                                <span class="inline-flex h-9 min-w-9 items-center justify-center text-xs text-slate-400">…</span>
                            @endif

                            @if ($page === $currentPage)
                                <span class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-emerald-600 bg-emerald-600 px-3 text-xs font-bold text-white">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $pdbGroups->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                                    {{ $page }}
                                </a>
                            @endif

                            @php
                                $previousPageNumber = $page;
                            @endphp
                        @endforeach

                        @if ($pdbGroups->hasMorePages())
                            <a href="{{ $pdbGroups->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </a>
                        @else
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-chevron-right text-xs"></i>
                            </span>
                        @endif
                    </div>
                @endif
            </footer>
        @endif
    </div>

</div>
@endsection

@extends('partials.layouts.operator')

@section('title', 'Potensi Unggulan Daerah')

@section('content')
<div x-data="{ isPdrbModalOpen: false, inputMode: 'batch' }">
    @if(session('success'))
        <div class="mb-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    <!-- Welcome Header -->
    <div
        class="bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] rounded-2xl p-8 md:p-10 pb-16 md:pb-24 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <!-- Background Ornaments -->
        <div
            class="absolute top-0 right-0 w-64 h-64 bg-emerald-400 rounded-full mix-blend-overlay filter blur-3xl opacity-20 animate-blob">
        </div>
        <div
            class="absolute bottom-0 right-32 w-48 h-48 bg-yellow-400 rounded-full mix-blend-overlay filter blur-3xl opacity-20 animate-blob animation-delay-2000">
        </div>
        <div
            class="absolute top-10 left-1/2 w-72 h-72 bg-emerald-500 rounded-full mix-blend-overlay filter blur-3xl opacity-10 animate-blob animation-delay-4000">
        </div>
        <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>

        <div class="relative z-10 text-white flex-1">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/50 border border-emerald-700/50 text-emerald-100 text-xs font-bold mb-4 backdrop-blur-sm">
                <svg class="w-4 h-4 text-[#FFD54F]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                Dashboard Utama Potensi Unggulan
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight mb-3">Selamat Datang, <span
                    class="text-[#FFD54F]">{{ Auth::user()->name }}</span></h1>
            <p class="text-emerald-100/90 font-medium max-w-xl text-sm leading-relaxed">
                Kelola dan pantau data serta hasil perhitungan analisis investasi dan ekonomi daerah Provinsi Sumatera Utara.
            </p>
        </div>

        <!-- Right Side Action & Date Info -->
        <div class="relative z-10 flex flex-col md:flex-row items-end md:items-center gap-4 mt-4 md:mt-0">
            <div class="flex flex-col items-end justify-center hidden sm:flex">
                <span
                    class="text-xl md:text-2xl font-black text-white tracking-tight drop-shadow-md">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
                <span class="text-xs font-semibold text-[#FFD54F] uppercase tracking-wider mt-0.5">Tanggal Hari Ini</span>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-5 gap-4 !-mt-12 md:!-mt-16 relative z-20 px-2 md:px-4">
        <!-- Stat Card 1 -->
        <div
            class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500">
            </div>
            <p class="text-xs font-bold text-slate-600 mb-2 relative z-10 text-center">Total Analisa</p>
            <div class="flex items-center justify-center gap-3 relative z-10">
                <div class="bg-emerald-600 text-white p-2 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
                <span class="text-3xl font-black text-slate-800">{{ $totalAnalisa ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 2 -->
        <div
            class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-16 h-16 bg-indigo-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500">
            </div>
            <p class="text-xs font-bold text-slate-600 mb-2 relative z-10 text-center">Analisis LQ</p>
            <div class="flex items-center justify-center gap-3 relative z-10">
                <div class="bg-indigo-600 text-white p-2 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </div>
                <span class="text-3xl font-black text-slate-800">{{ $countLq ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 3 -->
        <div
            class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-16 h-16 bg-sky-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500">
            </div>
            <p class="text-xs font-bold text-slate-600 mb-2 relative z-10 text-center">Analisis SSA</p>
            <div class="flex items-center justify-center gap-3 relative z-10">
                <div class="bg-sky-600 text-white p-2 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                </div>
                <span class="text-3xl font-black text-slate-800">{{ $countSs ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 4 -->
        <div
            class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-16 h-16 bg-emerald-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500">
            </div>
            <p class="text-xs font-bold text-slate-600 mb-2 relative z-10 text-center">Analisis TS</p>
            <div class="flex items-center justify-center gap-3 relative z-10">
                <div class="bg-emerald-600 text-white p-2 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                    </svg>
                </div>
                <span class="text-3xl font-black text-slate-800">{{ $countTipologi ?? 0 }}</span>
            </div>
        </div>

        <!-- Stat Card 5 -->
        <div
            class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
            <div
                class="absolute top-0 right-0 w-16 h-16 bg-cyan-50 rounded-bl-full -mr-8 -mt-8 transition-transform group-hover:scale-150 duration-500">
            </div>
            <p class="text-xs font-bold text-slate-600 mb-2 relative z-10 text-center">Analisis Klassen</p>
            <div class="flex items-center justify-center gap-3 relative z-10">
                <div class="bg-cyan-600 text-white p-2 rounded-lg shadow-sm">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </div>
                <span class="text-3xl font-black text-slate-800">{{ $countKlassen ?? 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column (Wider) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Table Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50">
                    <h3 class="font-bold text-slate-800 text-lg">Ringkasan Analisis</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr
                                class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                                <th class="px-5 py-3">Jenis Analisis</th>
                                <th class="px-5 py-3">Data Terakhir</th>
                                <th class="px-5 py-3 text-center">Total Data</th>
                                <th class="px-5 py-3 text-center">Riwayat</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm divide-y divide-slate-100">
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-700">Analisis LQ</td>
                                <td class="px-5 py-3.5 text-slate-500">{{ $statusLq['date'] }}</td>
                                <td class="px-5 py-3.5 text-center text-slate-600 font-semibold">{{ $countLq }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm {{ $statusLq['color'] }}">
                                        {{ $statusLq['action'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-700">Analisis SSA</td>
                                <td class="px-5 py-3.5 text-slate-500">{{ $statusSs['date'] }}</td>
                                <td class="px-5 py-3.5 text-center text-slate-600 font-semibold">{{ $countSs }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm {{ $statusSs['color'] }}">
                                        {{ $statusSs['action'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-700">Analisis Tipologi Sektor</td>
                                <td class="px-5 py-3.5 text-slate-500">{{ $statusTipologi['date'] }}</td>
                                <td class="px-5 py-3.5 text-center text-slate-600 font-semibold">{{ $countTipologi }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm {{ $statusTipologi['color'] }}">
                                        {{ $statusTipologi['action'] }}
                                    </span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-5 py-3.5 font-medium text-slate-700">Analisis Klassen</td>
                                <td class="px-5 py-3.5 text-slate-500">{{ $statusKlassen['date'] }}</td>
                                <td class="px-5 py-3.5 text-center text-slate-600 font-semibold">{{ $countKlassen }}</td>
                                <td class="px-5 py-3.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm {{ $statusKlassen['color'] }}">
                                        {{ $statusKlassen['action'] }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>



        </div>

        <!-- Right Column (Narrower) -->
        <div class="space-y-6">
            <!-- Activity Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden sticky top-6">
                <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-slate-800 text-lg">Aktivitas Terbaru</h3>
                    <span class="flex h-3 w-3 relative">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                    </span>
                </div>
                <div class="p-5 space-y-5">
                    @forelse($activityLogs->take(5) as $log)
                        @php
                            $iconClass = match (strtolower($log->action)) {
                                'ditambah' => 'bg-green-100 text-green-600',
                                'diperbarui', 'diubah' => 'bg-emerald-100 text-emerald-600',
                                'diimpor' => 'bg-purple-100 text-purple-600',
                                'dihapus' => 'bg-red-100 text-red-600',
                                default => 'bg-slate-100 text-slate-600'
                            };
                            $iconSvg = match (strtolower($log->action)) {
                                'ditambah' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />',
                                'diperbarui', 'diubah' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />',
                                'diimpor' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />',
                                'dihapus' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />',
                                default => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                            };
                        @endphp
                        <!-- Activity Item -->
                        <div class="flex items-start gap-4 group cursor-default">
                            <div
                                class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5 {{ $iconClass }} transition-transform group-hover:scale-110">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">{!! $iconSvg !!}</svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-700 leading-tight">{{ $log->desc }}</p>
                                <p class="text-xs text-slate-400 mt-1.5">{{ $log->created_at->format('d M Y H:i') }} &bull;
                                    {{ $log->module }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6 text-slate-500 text-sm">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-2" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            Belum ada aktivitas terbaru saat ini.
                        </div>
                    @endforelse
                </div>
                <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-sm">
                    <a href="{{ route('operator.aktivitas') }}"
                        class="text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1 group">
                        Lihat Semua Aktivitas
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M14 5l7 7m0 0l-7 7m7-7H3" />
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL INISIASI PDRB BARU (KABUPATEN & TAHUN ONLY) -->
    <div x-show="isPdrbModalOpen" x-cloak class="fixed inset-0 z-[1000] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 relative" @click.outside="isPdrbModalOpen = false">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold">
                        <i class="fa-solid fa-plus-circle text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDRB Baru</h3>
                        <p class="text-xs text-slate-500">Pilih Daerah & Tahun yang belum terdaftar</p>
                    </div>
                </div>
                <button type="button" @click="isPdrbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <form action="{{ route('operator.pdrb.init') }}" method="POST" class="space-y-4 pt-4 text-sm">
                @csrf

                {{-- Kabupaten / Kota Dropdown --}}
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '{{ old('kabupaten_id', $kabupatens->first()->kab_id ?? '') }}',
                    selectedName: '{{ old('kabupaten_id') ? ($kabupatens->firstWhere('kab_id', old('kabupaten_id'))->nama_kabupaten ?? '') : ($kabupatens->first()->nama_kabupaten ?? '') }}',
                    items: [
                        @foreach($kabupatens as $kab)
                            {
                                id: '{{ $kab->kab_id }}',
                                name: '{{ addslashes($kab->nama_kabupaten) }}'
                            },
                        @endforeach
                    ],
                    get filteredItems() {
                        if (!this.search) return this.items;
                        const q = this.search.toLowerCase();
                        return this.items.filter(i => i.name.toLowerCase().includes(q));
                    },
                    selectItem(item) {
                        this.selectedId = item.id;
                        this.selectedName = item.name;
                        this.open = false;
                        this.search = '';
                    }
                }" class="relative">
                    <label class="block font-semibold text-slate-700 mb-1">
                        Kabupaten / Kota <span class="text-rose-500">*</span>
                        <span class="text-[10px] text-[#145239] font-normal block">(Tersaring Sesuai Scope Otorisasi Operator)</span>
                    </label>
                    <input type="hidden" name="kabupaten_id" :value="selectedId" required>

                    <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                        class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                        <span x-text="selectedName || 'Pilih Kabupaten/Kota...'" :class="selectedName ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                        <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                    </div>

                    <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                        class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            <input type="text" x-model="search" x-ref="searchInput" placeholder="Cari kabupaten/kota..."
                                class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                        </div>
                        <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="selectItem(item)"
                                    :class="selectedId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                    class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                    <span x-text="item.name"></span>
                                    <i x-show="selectedId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Tahun PDRB --}}
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">
                        Tahun PDRB <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="tahun" required value="{{ date('Y') - 1 }}" min="2000" max="2100" class="w-full h-[42px] rounded-xl border border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm font-mono px-3.5">
                    <p class="text-[11px] text-slate-400 mt-1">
                        Sistem akan mengecek apakah kombinasi Kabupaten & Tahun ini sudah pernah dibuat sebelumnya.
                    </p>
                </div>

                {{-- Modal Footer --}}
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="isPdrbModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center gap-2">
                        <span>Lanjut ke Input Nilai</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
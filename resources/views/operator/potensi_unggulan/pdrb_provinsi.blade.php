@extends('partials.layouts.operator')

@section('title', 'Data PDRB Provinsi')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="{ 
    isPdrbModalOpen: false,
    isDeleteModalOpen: false,
    deleteActionUrl: '',
    deleteTargetName: '',
    deleteTargetYear: '',
    openDeleteModal(url, name, year) {
        this.deleteActionUrl = url;
        this.deleteTargetName = name;
        this.deleteTargetYear = year;
        this.isDeleteModalOpen = true;
    }
}">

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

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm font-semibold flex items-center justify-between shadow-sm">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                <span>{{ session('error') }}</span>
            </div>
            <button @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-700">
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

    <!-- Header Banner -->
    <div class="bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="relative z-10 text-white flex-1 space-y-2">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-800/50 border border-emerald-700/50 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-[#FFD54F] {{ ($tab ?? 'own') === 'own' ? 'fa-building-columns' : 'fa-city' }}"></i>
                <span>{{ ($tab ?? 'own') === 'own' ? 'Pengelolaan PDRB Provinsi Scope Otorisasi' : 'Mode Lihat Data Makroekonomi' }}</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                {{ ($tab ?? 'own') === 'own' ? 'Kelola Data PDRB Provinsi (Scope)' : 'Lihat Data PDRB Daerah (Seluruh Sumatera)' }}
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                {{ ($tab ?? 'own') === 'own' ? 'Kelola data PDRB provinsi otorisasi Anda. Setiap tahun & sektor dapat diisi dan diedit.' : 'Melihat rincian nilai 17 sektor PDRB Kabupaten/Kota dan Provinsi di Sumatera (Read-Only Mode).' }}
            </p>
        </div>

        <div class="relative z-10">
            @if(($tab ?? 'own') === 'own' && Auth::user()->hasProvinsiScope())
                <button type="button" @click="isPdrbModalOpen = true"
                    class="px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Inisiasi Data PDRB Provinsi Baru</span>
                </button>
            @else
                <div class="px-4 py-2.5 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-emerald-100 text-xs font-semibold flex items-center gap-2">
                    <i class="fa-solid fa-eye text-[#FFD54F]"></i>
                    <span>Mode: Lihat Seluruh Data</span>
                </div>
            @endif
        </div>
    </div>

    @if(($tab ?? 'own') === 'all')
        {{-- Level Sub-Tabs for Lihat Mode --}}
        <div class="flex items-center gap-3 border-b border-slate-200 mt-6 pt-1">
            <a href="{{ route('operator.pdrb.index', ['tab' => 'all']) }}"
                class="px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2.5 border-b-2 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-50">
                <i class="fa-solid fa-city text-sm"></i>
                <span>Tingkat Kabupaten / Kota</span>
            </a>

            <a href="{{ route('operator.pdrb-provinsi.index', ['tab' => 'all']) }}"
                class="px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2.5 border-b-2 border-[#145239] text-[#145239] bg-white shadow-xs">
                <i class="fa-solid fa-building-columns text-sm"></i>
                <span>Tingkat Provinsi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-[#145239] font-bold border border-emerald-200">
                    {{ number_format($allCount ?? 0) }}
                </span>
            </a>
        </div>
    @endif

    <!-- Filter Section -->
    <section class="{{ ($tab ?? 'own') === 'all' ? 'mt-4' : 'mt-6' }} rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <form
            action="{{ route('operator.pdrb-provinsi.index') }}"
            method="GET"
            class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12"
        >
            <input type="hidden" name="tab" value="{{ $tab ?? 'own' }}">

            {{-- Filter Provinsi --}}
            <div class="relative min-w-0 xl:col-span-4">
                <select
                    name="provinsi_id"
                    id="filterProvinsi"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >
                    @if($provinsis->count() > 1)
                        <option value="">Semua Provinsi</option>
                    @endif
                    @foreach ($provinsis as $prov)
                        <option
                            value="{{ $prov->provinsi_id }}"
                            @selected(($selectedProvinsiId ?? request('provinsi_id')) == $prov->provinsi_id)
                        >
                            {{ $prov->nama_provinsi }}
                        </option>
                    @endforeach
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            {{-- Filter Tahun PDRB --}}
            <div class="relative min-w-0 xl:col-span-3">
                <select
                    name="tahun"
                    id="filterTahun"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium font-mono text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >
                    <option value="">Semua Tahun</option>
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

            {{-- Input Search --}}
            <div class="relative min-w-0 xl:col-span-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari provinsi / tahun..."
                    class="h-11 w-full min-w-0 rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                >

                <button
                    type="submit"
                    aria-label="Cari PDRB"
                    class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100"
                >
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            {{-- Action Buttons --}}
            <div class="flex min-w-0 gap-2 md:col-span-2 xl:col-span-2">
                <button
                    type="submit"
                    class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white transition hover:bg-emerald-700 shadow-sm"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span class="truncate">Terapkan</span>
                </button>

                <a
                    href="{{ route('operator.pdrb-provinsi.index', ['tab' => $tab ?? 'own']) }}"
                    title="Reset filter"
                    class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600 shadow-sm"
                >
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </section>

    <!-- Data Table Card (Grouped by Provinsi & Tahun) -->
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Daftar Record PDRB Provinsi Terdaftar
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    @if(($tab ?? 'own') === 'own')
                        Menampilkan data PDRB provinsi otorisasi Anda (Dapat dikelola & di-edit).
                    @else
                        Menampilkan seluruh data PDRB Provinsi di Sumatera (Mode Lihat & Referensi).
                    @endif
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <i class="fa-solid fa-building-columns"></i>
                {{ number_format($pdrbGroups->total(), 0, ',', '.') }} Data
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Provinsi</th>
                        <th class="px-5 py-3.5 text-center">Tahun PDRB</th>
                        <th class="px-5 py-3.5 text-center">Sektor Terisi</th>
                        <th class="px-5 py-3.5 text-right">Total PDRB (Rp Juta)</th>
                        <th class="px-5 py-3.5 text-center w-36">Aksi & Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pdrbGroups as $index => $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-5 py-4 text-center text-slate-400">
                                {{ $pdrbGroups->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4 text-slate-900 font-bold">
                                {{ $group->provinsi->nama_provinsi ?? '-' }}
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
                                Rp {{ number_format($group->total_pdrb, 2, ',', '.') }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if(Auth::user()->canManageProvinsi($group->provinsi_id))
                                    <div class="flex items-center justify-center gap-1.5">
                                        {{-- Edit Button --}}
                                        <a href="{{ route('operator.pdrb-provinsi.entry', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                            title="Edit Data PDRB ({{ $group->provinsi->nama_provinsi ?? '' }} {{ $group->tahun }})">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </a>

                                        {{-- Delete Group Button --}}
                                        <button type="button"
                                            @click="openDeleteModal('{{ route('operator.pdrb-provinsi.destroy-group', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}', '{{ $group->provinsi->nama_provinsi ?? 'Provinsi' }}', '{{ $group->tahun }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors shadow-2xs"
                                            title="Hapus Data PDRB {{ $group->provinsi->nama_provinsi ?? '' }} {{ $group->tahun }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </div>
                                @else
                                    <a href="{{ route('operator.pdrb-provinsi.entry', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}"
                                        class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 transition-colors text-xs font-bold"
                                        title="Lihat Rincian Sektor PDRB Provinsi">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                        <span>Lihat Nilai</span>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-3xl mb-2 block text-slate-300"></i>
                                @if(request()->filled('search'))
                                    Data PDRB Provinsi dengan kata kunci <strong>"{{ request('search') }}"</strong> tidak ditemukan.
                                    <div class="mt-2">
                                        <a href="{{ route('operator.pdrb-provinsi.index', ['tab' => $tab ?? 'own']) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                                            <i class="fa-solid fa-rotate-left mr-1"></i> Reset Pencarian
                                        </a>
                                    </div>
                                @else
                                    Belum ada data PDRB Provinsi yang terdaftar. Klik <strong>"+ Inisiasi Data PDRB Provinsi Baru"</strong> untuk menambah data baru.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pdrbGroups->total() > 0)
            <footer class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="m-0 text-xs text-slate-500">
                    Menampilkan <strong class="text-slate-700">{{ $pdrbGroups->firstItem() }}</strong>–<strong class="text-slate-700">{{ $pdrbGroups->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($pdrbGroups->total(), 0, ',', '.') }}</strong> data
                </p>

                @if ($pdrbGroups->hasPages())
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($pdrbGroups->onFirstPage())
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </span>
                        @else
                            <a href="{{ $pdrbGroups->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                                <i class="fa-solid fa-chevron-left text-xs"></i>
                            </a>
                        @endif

                        @php
                            $currentPage = $pdrbGroups->currentPage();
                            $lastPage = $pdrbGroups->lastPage();
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
                                <a href="{{ $pdrbGroups->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                                    {{ $page }}
                                </a>
                            @endif

                            @php
                                $previousPageNumber = $page;
                            @endphp
                        @endforeach

                        @if ($pdrbGroups->hasMorePages())
                            <a href="{{ $pdrbGroups->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
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

    <!-- MODAL INISIASI PDRB PROVINSI BARU -->
    <template x-teleport="body">
        <div x-show="isPdrbModalOpen" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 relative" @click.outside="isPdrbModalOpen = false">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold">
                            <i class="fa-solid fa-building-columns text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDRB Provinsi</h3>
                            <p class="text-xs text-slate-500">Pilih Provinsi & Tahun yang belum terdaftar</p>
                        </div>
                    </div>
                    <button type="button" @click="isPdrbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form action="{{ route('operator.pdrb-provinsi.init') }}" method="POST" class="space-y-4 pt-4 text-sm">
                    @csrf

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Provinsi <span class="text-rose-500">*</span>
                        </label>
                        <select name="provinsi_id" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm bg-white h-11" required>
                            @foreach($provinsis as $prov)
                                <option value="{{ $prov->provinsi_id }}">{{ $prov->nama_provinsi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Tahun PDRB <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tahun" value="{{ date('Y') }}" min="2000" max="2100" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm h-11 font-mono" required>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <button type="button" @click="isPdrbModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors">
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
    </template>

    <!-- MODAL KONFIRMASI HAPUS DATA PDRB PROVINSI -->
    <x-confirm-delete-modal title="Konfirmasi Hapus Data PDRB Provinsi" />

</div>
@endsection

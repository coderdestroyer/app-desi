@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8 space-y-6" x-data="{ isPdrbModalOpen: false }">

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
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-shield-halved text-[#FFD54F]"></i>
                <span>Administrator Central Access (33 Kab/Kota Sumut)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Kelola Data PDRB Daerah Sumatera Utara
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Akses penuh Admin untuk mengelola, menginputkan, mengedit, dan menghapus seluruh basis data PDRB 33 Kabupaten/Kota di Sumatera Utara yang diinput oleh Operator maupun Administrator.
            </p>
        </div>

        <div class="relative z-10">
            <button type="button" @click="isPdrbModalOpen = true"
                class="px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Inisiasi Data PDRB Baru</span>
            </button>
        </div>
    </section>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs">
        <form action="{{ route('admin.pdrb.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Filter Kabupaten / Kota</label>
                <select name="kabupaten_id" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 focus:border-[#145239] text-xs bg-white h-10">
                    <option value="">-- Semua 33 Kab/Kota Sumut --</option>
                    @foreach($kabupatens as $kab)
                        <option value="{{ $kab->kab_id }}" @selected(request('kabupaten_id') == $kab->kab_id)>{{ $kab->nama_kabupaten }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-semibold text-slate-700 mb-1">Filter Tahun PDRB</label>
                <select name="tahun" onchange="this.form.submit()" class="w-full rounded-xl border-slate-200 focus:border-[#145239] text-xs bg-white h-10 font-mono">
                    <option value="">-- Semua Tahun --</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" @selected(request('tahun') == $yr)>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="w-full h-10 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white font-bold transition-colors flex items-center justify-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-filter"></i>
                    <span>Terapkan Filter</span>
                </button>
                @if(request()->anyFilled(['kabupaten_id', 'tahun']))
                    <a href="{{ route('admin.pdrb.index') }}" class="h-10 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 flex items-center justify-center transition-colors" title="Reset Filter">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fa-solid fa-coins text-[#145239]"></i>
                Daftar Record PDRB Daerah Terdaftar (Seluruh Sumut)
            </h3>
            <span class="text-xs font-semibold text-[#145239] bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
                Total: {{ $pdrbGroups->total() }} Data Daerah/Tahun
            </span> 
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-5 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5">Kabupaten / Kota</th>
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
                                {{ $group->kabupaten->nama_kabupaten ?? '-' }}
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
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Edit Button (Icon Only) --}}
                                    <a href="{{ route('admin.pdrb.entry', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                        title="Edit Data PDRB ({{ $group->kabupaten->nama_kabupaten ?? '' }} {{ $group->tahun }})">
                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                    </a>

                                    {{-- Delete Group Button (Icon Only) --}}
                                    <form action="{{ route('admin.pdrb.destroy-group', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}" method="POST" onsubmit="return confirm('Hapus seluruh data PDRB {{ $group->kabupaten->nama_kabupaten ?? '' }} Tahun {{ $group->tahun }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors" title="Hapus Data Daerah & Tahun Ini">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2 block text-slate-300"></i>
                                Belum ada data PDRB yang terdaftar. Klik <strong>"Inisiasi Data PDRB Baru"</strong> untuk menginputkan data daerah baru.
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

    <!-- MODAL INISIASI PDRB BARU FOR ADMIN -->
    <template x-teleport="body">
        <div x-show="isPdrbModalOpen" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 relative" @click.outside="isPdrbModalOpen = false">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold">
                            <i class="fa-solid fa-plus-circle text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDRB Baru</h3>
                            <p class="text-xs text-slate-500">Pilih Kabupaten/Kota (33 Sumut) & Tahun</p>
                        </div>
                    </div>
                    <button type="button" @click="isPdrbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form action="{{ route('admin.pdrb.init') }}" method="POST" class="space-y-4 pt-4 text-sm">
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
                    }" class="relative z-50">
                        <label class="block font-semibold text-slate-700 mb-1">
                            Kabupaten / Kota <span class="text-rose-500">*</span>
                            <span class="text-[10px] text-[#145239] font-normal block">(Seluruh 33 Kabupaten/Kota di Sumut)</span>
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
    </template>

</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-slate-50 p-4 sm:p-6 md:p-7 lg:p-8 space-y-6" x-data="{ 
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
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-shield-halved text-[#FFD54F]"></i>
                <span>Administrator Central Access (Seluruh Provinsi & Kab/Kota Pulau Sumatera)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Kelola Data PDRB Daerah & Provinsi Sumatera
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Akses penuh Admin untuk mengelola, menginputkan, mengedit, dan menghapus seluruh basis data PDRB Kabupaten/Kota dan Provinsi se-Pulau Sumatera.
            </p>
        </div>

        <div class="relative z-10 w-full sm:w-auto shrink-0">
            <button type="button" @click="isPdrbModalOpen = true"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Inisiasi Data PDRB Baru</span>
            </button>
        </div>
    </section>

    <!-- Sub-Tabs Level Wilayah -->
    <div class="flex items-center gap-2 sm:gap-3 border-b border-slate-200 pt-1 overflow-x-auto">
        <a href="{{ route('admin.pdrb.index', array_merge(request()->except(['page']), ['type' => 'kabupaten'])) }}"
            class="px-4 sm:px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2 sm:gap-2.5 border-b-2 whitespace-nowrap shrink-0 {{ ($type ?? 'kabupaten') === 'kabupaten' ? 'border-[#145239] text-[#145239] bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100/60' }}">
            <i class="fa-solid fa-city text-sm"></i>
            <span>Tingkat Kabupaten / Kota</span>
        </a>

        <a href="{{ route('admin.pdrb.index', array_merge(request()->except(['page']), ['type' => 'provinsi'])) }}"
            class="px-4 sm:px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2 sm:gap-2.5 border-b-2 whitespace-nowrap shrink-0 {{ ($type ?? 'kabupaten') === 'provinsi' ? 'border-[#145239] text-[#145239] bg-white shadow-xs' : 'border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100/60' }}">
            <i class="fa-solid fa-building-columns text-sm"></i>
            <span>Tingkat Provinsi</span>
        </a>
    </div>

    <!-- Filter Section Component -->
    <x-pdrb-filter-bar
        :action="route('admin.pdrb.index')"
        :provinsis="$provinsis"
        :kabupatens="($type ?? 'kabupaten') === 'kabupaten' ? $kabupatens : null"
        :available-years="$availableYears"
        :selected-provinsi-id="$selectedProvinsiId"
    />

    <!-- Data Table Card -->
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid {{ ($type ?? 'kabupaten') === 'provinsi' ? 'fa-building-columns' : 'fa-city' }} text-[#145239]"></i>
                    <span>Daftar Record PDRB {{ ($type ?? 'kabupaten') === 'provinsi' ? 'Provinsi' : 'Kabupaten / Kota' }} Terdaftar</span>
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Kelola data PDRB per {{ ($type ?? 'kabupaten') === 'provinsi' ? 'Provinsi' : 'Kabupaten/Kota' }} dan Tahun untuk seluruh wilayah se-Pulau Sumatera.
                </p>
            </div>

            <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                <i class="fa-solid fa-coins"></i>
                {{ number_format($pdrbGroups->total(), 0, ',', '.') }} Data
            </div>
        </header>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs min-w-[780px]">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        @if(($type ?? 'kabupaten') === 'provinsi')
                            <th class="px-5 py-3.5 min-w-[180px]">Provinsi</th>
                        @else
                            <th class="px-5 py-3.5 min-w-[160px]">Provinsi</th>
                            <th class="px-5 py-3.5 min-w-[180px]">Kabupaten / Kota</th>
                        @endif
                        <th class="px-4 py-3.5 text-center w-28">Tahun PDRB</th>
                        <th class="px-4 py-3.5 text-center min-w-[150px]">Sektor Terisi</th>
                        <th class="px-5 py-3.5 text-right w-44">Total PDRB (Rp Juta)</th>
                        <th class="px-4 py-3.5 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pdrbGroups as $index => $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-4 text-center text-slate-400">
                                {{ $pdrbGroups->firstItem() + $index }}
                            </td>

                            @if(($type ?? 'kabupaten') === 'provinsi')
                                <td class="px-5 py-4 text-slate-900 font-bold">
                                    {{ $group->provinsi->nama_provinsi ?? '-' }}
                                </td>
                            @else
                                <td class="px-5 py-4 text-slate-700 font-semibold">
                                    {{ $group->kabupaten->provinsi->nama_provinsi ?? '-' }}
                                </td>
                                <td class="px-5 py-4 text-slate-900 font-bold">
                                    {{ $group->kabupaten->nama_kabupaten ?? '-' }}
                                </td>
                            @endif

                            <td class="px-4 py-4 text-center font-mono font-semibold">
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
                                Rp {{ number_format($group->total_pdrb, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-1.5">
                                    @if(($type ?? 'kabupaten') === 'provinsi')
                                        {{-- Edit Button Provinsi --}}
                                        <a href="{{ route('admin.pdrb.provinsi-entry', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                            title="Edit Data PDRB Provinsi ({{ $group->provinsi->nama_provinsi ?? '' }} {{ $group->tahun }})">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </a>

                                        {{-- Delete Group Button Provinsi --}}
                                        <button type="button"
                                            @click="openDeleteModal('{{ route('admin.pdrb.destroy-provinsi-group', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}', 'Provinsi {{ $group->provinsi->nama_provinsi ?? '' }}', '{{ $group->tahun }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors shadow-2xs"
                                            title="Hapus Data PDRB Provinsi {{ $group->provinsi->nama_provinsi ?? '' }} {{ $group->tahun }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    @else
                                        {{-- Edit Button Kabupaten --}}
                                        <a href="{{ route('admin.pdrb.entry', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                            title="Edit Data PDRB ({{ $group->kabupaten->nama_kabupaten ?? '' }} {{ $group->tahun }})">
                                            <i class="fa-regular fa-pen-to-square text-xs"></i>
                                        </a>

                                        {{-- Delete Group Button Kabupaten --}}
                                        <button type="button"
                                            @click="openDeleteModal('{{ route('admin.pdrb.destroy-group', ['kabupaten_id' => $group->kabupaten_id, 'tahun' => $group->tahun]) }}', '{{ $group->kabupaten->nama_kabupaten ?? 'Kabupaten/Kota' }}', '{{ $group->tahun }}')"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors shadow-2xs"
                                            title="Hapus Data PDRB {{ $group->kabupaten->nama_kabupaten ?? '' }} {{ $group->tahun }}">
                                            <i class="fa-solid fa-trash-can text-xs"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ ($type ?? 'kabupaten') === 'provinsi' ? 6 : 7 }}" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-1 block text-slate-300"></i>
                                Belum ada data PDRB {{ ($type ?? 'kabupaten') === 'provinsi' ? 'Provinsi' : 'Kabupaten/Kota' }} yang terdaftar. Klik <strong>"Inisiasi Data PDRB Baru"</strong> untuk menginputkan data baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Component -->
        <x-pagination :paginator="$pdrbGroups" />
    </div>

    <!-- MODAL INISIASI PDRB BARU FOR ADMIN -->
    <template x-teleport="body">
        <div x-show="isPdrbModalOpen" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-transition>
            <div x-data="{
                tingkat: '{{ ($type ?? 'kabupaten') === 'provinsi' ? 'provinsi' : 'kabupaten' }}',
                
                // State Dropdown Kabupaten
                openKab: false,
                searchKab: '',
                selectedKabId: '{{ $allKabupatenList->first()->kab_id ?? '' }}',
                selectedKabName: '{{ addslashes($allKabupatenList->first()->nama_kabupaten ?? '') }}',
                kabItems: [
                    @foreach($allKabupatenList as $kab)
                        {
                            id: '{{ $kab->kab_id }}',
                            name: '{{ addslashes($kab->nama_kabupaten) }}'
                        },
                    @endforeach
                ],
                get filteredKabItems() {
                    if (!this.searchKab) return this.kabItems;
                    const q = this.searchKab.toLowerCase();
                    return this.kabItems.filter(i => i.name.toLowerCase().includes(q));
                },
                selectKabItem(item) {
                    this.selectedKabId = item.id;
                    this.selectedKabName = item.name;
                    this.openKab = false;
                    this.searchKab = '';
                },

                // State Dropdown Provinsi
                openProv: false,
                searchProv: '',
                selectedProvId: '{{ $provinsis->first()->provinsi_id ?? '' }}',
                selectedProvName: '{{ addslashes($provinsis->first()->nama_provinsi ?? '') }}',
                provItems: [
                    @foreach($provinsis as $prov)
                        {
                            id: '{{ $prov->provinsi_id }}',
                            name: '{{ addslashes($prov->nama_provinsi) }}'
                        },
                    @endforeach
                ],
                get filteredProvItems() {
                    if (!this.searchProv) return this.provItems;
                    const q = this.searchProv.toLowerCase();
                    return this.provItems.filter(i => i.name.toLowerCase().includes(q));
                },
                selectProvItem(item) {
                    this.selectedProvId = item.id;
                    this.selectedProvName = item.name;
                    this.openProv = false;
                    this.searchProv = '';
                }
            }" class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="isPdrbModalOpen = false">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                            <i class="fa-solid fa-plus-circle text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDRB Baru</h3>
                            <p class="text-xs text-slate-500">Pilih Tingkat Wilayah & Tahun Data PDRB</p>
                        </div>
                    </div>
                    <button type="button" @click="isPdrbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 pt-3 pr-0.5 space-y-4">
                    {{-- Segmented Controller / Radio Pilihan Tingkat Wilayah --}}
                    <div>
                        <label class="block font-semibold text-slate-700 text-xs mb-1.5">Tingkat Wilayah Data PDRB <span class="text-rose-500">*</span></label>
                        <div class="grid grid-cols-2 gap-2 bg-slate-100 p-1 rounded-xl">
                            <button type="button" @click="tingkat = 'kabupaten'"
                                :class="tingkat === 'kabupaten' ? 'bg-white text-[#145239] font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-800 font-medium'"
                                class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-city"></i>
                                <span>Kabupaten / Kota</span>
                            </button>

                            <button type="button" @click="tingkat = 'provinsi'"
                                :class="tingkat === 'provinsi' ? 'bg-white text-[#145239] font-extrabold shadow-xs' : 'text-slate-600 hover:text-slate-800 font-medium'"
                                class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-building-columns"></i>
                                <span>Tingkat Provinsi</span>
                            </button>
                        </div>
                    </div>

                    <form :action="tingkat === 'kabupaten' ? '{{ route('admin.pdrb.init') }}' : '{{ route('admin.pdrb.init-provinsi') }}'" method="POST" class="space-y-4 text-sm">
                        @csrf

                        {{-- Kabupaten / Kota Dropdown (Tampil saat tingkat == kabupaten) --}}
                        <div x-show="tingkat === 'kabupaten'" class="relative z-50">
                            <label class="block font-semibold text-slate-700 mb-1">
                                Kabupaten / Kota <span class="text-rose-500">*</span>
                                <span class="text-[10px] text-[#145239] font-normal block">(Pilih Kabupaten/Kota di Pulau Sumatera)</span>
                            </label>
                            <input type="hidden" name="kabupaten_id" :value="selectedKabId" :disabled="tingkat !== 'kabupaten'" required>

                            <div @click="openKab = !openKab; if(openKab) $nextTick(() => $refs.searchKabInput.focus())"
                                class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                <span x-text="selectedKabName || 'Pilih Kabupaten/Kota...'" :class="selectedKabName ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="openKab ? 'rotate-180' : ''"></i>
                            </div>

                            <div x-show="openKab" @click.outside="openKab = false" x-transition.origin.top.duration.150ms
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                    <input type="text" x-model="searchKab" x-ref="searchKabInput" placeholder="Cari kabupaten/kota..."
                                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                    <template x-for="item in filteredKabItems" :key="item.id">
                                        <div @click="selectKabItem(item)"
                                            :class="selectedKabId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                            class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                            <span x-text="item.name"></span>
                                            <i x-show="selectedKabId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        {{-- Provinsi Dropdown (Tampil saat tingkat == provinsi) --}}
                        <div x-show="tingkat === 'provinsi'" class="relative z-50">
                            <label class="block font-semibold text-slate-700 mb-1">
                                Provinsi <span class="text-rose-500">*</span>
                                <span class="text-[10px] text-[#145239] font-normal block">(Pilih Provinsi di Pulau Sumatera)</span>
                            </label>
                            <input type="hidden" name="provinsi_id" :value="selectedProvId" :disabled="tingkat !== 'provinsi'" required>

                            <div @click="openProv = !openProv; if(openProv) $nextTick(() => $refs.searchProvInput.focus())"
                                class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                <span x-text="selectedProvName || 'Pilih Provinsi...'" :class="selectedProvName ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="openProv ? 'rotate-180' : ''"></i>
                            </div>

                            <div x-show="openProv" @click.outside="openProv = false" x-transition.origin.top.duration.150ms
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                    <input type="text" x-model="searchProv" x-ref="searchProvInput" placeholder="Cari provinsi..."
                                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                </div>
                                <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                    <template x-for="item in filteredProvItems" :key="item.id">
                                        <div @click="selectProvItem(item)"
                                            :class="selectedProvId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                            class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                            <span x-text="item.name"></span>
                                            <i x-show="selectedProvId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
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
                            <input type="number" name="tahun" required value="{{ date('Y') - 1 }}" min="2000" max="2100" class="w-full h-[42px] rounded-xl border border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm font-medium text-slate-700 px-3.5">
                            <p class="text-[11px] text-slate-400 mt-1">
                                Sistem akan mengecek apakah kombinasi Wilayah & Tahun ini sudah pernah dibuat sebelumnya.
                            </p>
                        </div>

                        {{-- Modal Footer --}}
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
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
    </template>

    <!-- MODAL KONFIRMASI HAPUS DATA PDRB (ADMIN) -->
    <x-confirm-delete-modal title="Konfirmasi Hapus Data PDRB" />
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterKabupaten = document.getElementById('filterKabupaten');
        const filterProvinsi = document.getElementById('filterProvinsi');

        if (filterKabupaten && filterProvinsi) {
            filterKabupaten.addEventListener('change', function () {
                const selectedOption = filterKabupaten.options[filterKabupaten.selectedIndex];
                const provId = selectedOption ? selectedOption.getAttribute('data-provinsi') : '';
                if (provId) {
                    filterProvinsi.value = provId;
                }
            });
        }
    });
</script>
@endsection

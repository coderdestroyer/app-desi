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
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid text-[#FFD54F] {{ ($tab ?? 'own') === 'own' ? 'fa-building-columns' : 'fa-city' }}"></i>
                <span>{{ ($tab ?? 'own') === 'own' ? 'Pengelolaan PDRB Provinsi Scope Otorisasi' : 'Mode Lihat Data Makroekonomi' }}</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                {{ ($tab ?? 'own') === 'own' ? 'Kelola Data PDRB Provinsi (Scope)' : 'Lihat Data PDRB Daerah (Seluruh Sumatera)' }}
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                {{ ($tab ?? 'own') === 'own' ? 'Kelola data PDRB provinsi otorisasi Anda. Setiap tahun & sektor dapat diisi dan diedit.' : 'Melihat rincian nilai 17 sektor PDRB Kabupaten/Kota dan Provinsi di Sumatera (Read-Only Mode).' }}
            </p>
        </div>

        <div class="relative z-10 w-full sm:w-auto shrink-0">
            @if(($tab ?? 'own') === 'own' && Auth::user()->hasProvinsiScope())
                <button type="button" @click="isPdrbModalOpen = true"
                    class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Inisiasi Data PDRB Provinsi Baru</span>
                </button>
            @else
                <div class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-white text-xs font-semibold flex items-center justify-center gap-2 backdrop-blur-sm">
                    <i class="fa-solid fa-eye text-[#FFD54F]"></i>
                    <span>Mode: Lihat Seluruh Data</span>
                </div>
            @endif
        </div>
    </section>

    @if(($tab ?? 'own') === 'all')
        {{-- Level Sub-Tabs for Lihat Mode --}}
        <div class="flex items-center gap-2 sm:gap-3 border-b border-slate-200 pt-1 overflow-x-auto">
            <a href="{{ route('operator.pdrb.index', ['tab' => 'all']) }}"
                class="px-4 sm:px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2 sm:gap-2.5 border-b-2 whitespace-nowrap shrink-0 border-transparent text-slate-500 hover:text-slate-700 hover:bg-slate-100/60">
                <i class="fa-solid fa-city text-sm"></i>
                <span>Tingkat Kabupaten / Kota</span>
            </a>

            <a href="{{ route('operator.pdrb-provinsi.index', ['tab' => 'all']) }}"
                class="px-4 sm:px-5 py-3 rounded-t-xl text-xs md:text-sm font-extrabold transition-all flex items-center gap-2 sm:gap-2.5 border-b-2 whitespace-nowrap shrink-0 border-[#145239] text-[#145239] bg-white shadow-xs">
                <i class="fa-solid fa-building-columns text-sm"></i>
                <span>Tingkat Provinsi</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] bg-emerald-100 text-[#145239] font-bold border border-emerald-200">
                    {{ number_format($allCount ?? 0) }}
                </span>
            </a>
        </div>
    @endif

    <!-- Filter Section Component -->
    <x-pdrb-filter-bar
        :action="route('operator.pdrb-provinsi.index')"
        :provinsis="$provinsis"
        :available-years="$availableYears"
        :selected-provinsi-id="$selectedProvinsiId"
        :tab="$tab"
        search-placeholder="Cari provinsi / tahun..."
    />

    <!-- Data Table Card (Grouped by Provinsi & Tahun) -->
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-building-columns text-[#145239]"></i>
                    <span>Daftar Record PDRB Provinsi Terdaftar</span>
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

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[780px] border-collapse text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 font-bold border-b border-slate-200 uppercase tracking-wider">
                        <th class="px-4 py-3.5 text-center w-12">No</th>
                        <th class="px-5 py-3.5 min-w-[180px]">Provinsi</th>
                        <th class="px-4 py-3.5 text-center w-28">Tahun PDRB</th>
                        <th class="px-4 py-3.5 text-center min-w-[150px]">Sektor Terisi</th>
                        <th class="px-5 py-3.5 text-right w-44">Total PDRB (Rp Juta)</th>
                        <th class="px-4 py-3.5 text-center w-36">Aksi & Manajemen</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pdrbGroups as $index => $group)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="px-4 py-4 text-center text-slate-400 font-mono">
                                {{ $pdrbGroups->firstItem() + $index }}
                            </td>
                            <td class="px-5 py-4 text-slate-900 font-bold">
                                {{ $group->provinsi->nama_provinsi ?? '-' }}
                            </td>
                            <td class="px-4 py-4 text-center font-mono font-semibold whitespace-nowrap">
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
                                @if(($tab ?? 'own') === 'own' && Auth::user()->canManageProvinsi($group->provinsi_id))
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
                                    <a href="{{ route('operator.pdrb-provinsi.detail', ['provinsi_id' => $group->provinsi_id, 'tahun' => $group->tahun]) }}"
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

        <!-- Pagination Component -->
        <x-pagination :paginator="$pdrbGroups" />
    </div>

    <!-- MODAL INISIASI PDRB PROVINSI BARU -->
    <template x-teleport="body">
        <div x-show="isPdrbModalOpen" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-md w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="isPdrbModalOpen = false">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                            <i class="fa-solid fa-building-columns text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDRB Provinsi</h3>
                            <p class="text-xs text-slate-500">Pilih Provinsi & Tahun yang belum terdaftar</p>
                        </div>
                    </div>
                    <button type="button" @click="isPdrbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form action="{{ route('operator.pdrb-provinsi.init') }}" method="POST" class="space-y-4 pt-4 text-sm flex-1 overflow-y-auto">
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

                    <div class="pt-4 flex flex-col-reverse sm:flex-row items-center justify-end gap-3 border-t border-slate-100 shrink-0">
                        <button type="button" @click="isPdrbModalOpen = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-2">
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

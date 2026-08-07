@extends('layouts.admin')

@section('title', 'Data PDB Nasional')

@section('content')
<div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8 space-y-6" x-data="{ 
    isPdbModalOpen: false,
    isDeleteModalOpen: false,
    deleteActionUrl: '',
    deleteTargetYear: '',
    openDeleteModal(url, year) {
        this.deleteActionUrl = url;
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
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-7 md:p-8 shadow-lg text-white flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-globe text-[#FFD54F]"></i>
                <span>Administrator Central Access (Skala PDB Nasional)</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Kelola Data PDB Nasional
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Akses penuh Admin untuk mengelola, menginputkan, mengedit, dan menghapus seluruh basis data PDB (Produk Domestik Bruto) Nasional per 17 Sektor Lapangan Usaha sebagai pembanding analisis ekonomi regional.
            </p>
        </div>

        <div class="relative z-10">
            <button type="button" @click="isPdbModalOpen = true"
                class="px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Inisiasi Data PDB Baru</span>
            </button>
        </div>
    </section>

    <!-- Filter Section Component -->
    <x-pdb-filter-bar
        :action="route('admin.pdb-nasional.index')"
        :available-years="$availableYears"
    />

    <!-- Data Table Card -->
    <div class="rounded-2xl border border-slate-100 bg-white shadow-sm overflow-hidden">
        <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Daftar Record PDB Nasional Terdaftar
                </h2>
                <p class="mt-1 text-xs text-slate-500">
                    Kelola data PDB Nasional per Tahun untuk 17 Sektor Lapangan Usaha.
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
                        <th class="px-5 py-3.5 text-center w-36">Aksi & Manajemen</th>
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
                                <div class="flex items-center justify-center gap-1.5">
                                    {{-- Edit Button (Icon Only) --}}
                                    <a href="{{ route('admin.pdb-nasional.entry', ['tahun' => $group->tahun]) }}" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 transition-colors shadow-2xs"
                                        title="Edit Data PDB Nasional Tahun {{ $group->tahun }}">
                                        <i class="fa-regular fa-pen-to-square text-xs"></i>
                                    </a>

                                    {{-- Delete Button --}}
                                    <button type="button"
                                        @click="openDeleteModal('{{ route('admin.pdb-nasional.destroy-group', ['tahun' => $group->tahun]) }}', '{{ $group->tahun }}')"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 transition-colors shadow-2xs"
                                        title="Hapus Data PDB Nasional Tahun {{ $group->tahun }}">
                                        <i class="fa-solid fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-slate-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-1 block text-slate-300"></i>
                                Belum ada data PDB Nasional yang terdaftar. Klik <strong>"Inisiasi Data PDB Baru"</strong> untuk menginputkan data tahun baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Component -->
        <x-pagination :paginator="$pdbGroups" />
    </div>

    <!-- MODAL INISIASI PDB BARU -->
    <template x-teleport="body">
        <div x-show="isPdbModalOpen" x-cloak class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-emerald-100 relative" @click.outside="isPdbModalOpen = false">
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                            <i class="fa-solid fa-plus-circle text-lg"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Inisiasi Data PDB Nasional</h3>
                            <p class="text-xs text-slate-500">Pilih Tahun PDB Nasional baru</p>
                        </div>
                    </div>
                    <button type="button" @click="isPdbModalOpen = false" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form action="{{ route('admin.pdb-nasional.init') }}" method="POST" class="space-y-4 pt-4 text-sm">
                    @csrf

                    {{-- Tahun PDB --}}
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">
                            Tahun PDB Nasional <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="tahun" required value="{{ date('Y') - 1 }}" min="2000" max="2100" class="w-full h-[42px] rounded-xl border border-slate-200 focus:border-emerald-600 focus:ring-emerald-600 text-sm font-medium text-slate-700 px-3.5">
                        <p class="text-[11px] text-slate-400 mt-1">
                            Sistem akan mengecek apakah PDB Nasional Tahun ini sudah pernah didaftarkan.
                        </p>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="isPdbModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md transition-colors flex items-center gap-2">
                            <span>Lanjut ke Input Nilai</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    <!-- MODAL KONFIRMASI HAPUS DATA PDB NASIONAL (ADMIN) -->
    <x-confirm-delete-modal title="Konfirmasi Hapus Data PDB Nasional" />
</div>
@endsection

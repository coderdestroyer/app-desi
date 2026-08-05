@extends('partials.layouts.operator')

@section('title', 'Daftar Proyek Investasi')
@section('page_heading', 'Daftar Proyek Investasi')

@section('content')
    <div x-data="{
        isCreateModalOpen: false,
        isEditModalOpen: false,
        isDeleteModalOpen: false,
        searchQuery: '',
        filterKabupaten: '',
        filterSektor: '',
        selectedProject: {
            id: null,
            nama_proyek: '',
            kabupaten_id: '',
            sektor_id: '',
            lokasi_id: '',
            deskripsi: '',
            tahun_awal: {{ date('Y') }},
            jangka_waktu_tahun: 10,
            status_publikasi: 'published'
        },
        openEditModal(project) {
            this.selectedProject = { ...project };
            this.isEditModalOpen = true;
        },
        openDeleteModal(project) {
            this.selectedProject = { ...project };
            this.isDeleteModalOpen = true;
        }
    }">

        <!-- Header & Action Buttons Card (55:45 Ratio & Multi-Line Flexible Buttons) -->
        <div class="bg-white rounded-2xl p-6 border border-[#CFE3D5] shadow-xs flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6 mb-6">
            <!-- Title & Subtitle Section (~55% - 60% Width) -->
            <div class="w-full lg:w-[55%] xl:w-[60%]">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#EEF8F2] text-[#145239] text-xs font-bold mb-2 border border-[#CFE3D5]">
                    <i class="fa-solid fa-briefcase text-[#D8A62A]"></i>
                    <span>Daftar Proyek (IPRO)</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-800 tracking-tight leading-tight">Daftar Proyek Investasi Daerah</h1>
                <p class="text-slate-500 text-xs md:text-sm mt-0.5">Kelola kelayakan proyek finansial daerah, komputasi CAPEX, Laba Rugi, dan Arus Kas.</p>
            </div>

            <!-- Action Buttons (~45% Width) -->
            <div class="w-full lg:w-[45%] xl:w-[40%] flex justify-start lg:justify-end">
                <div class="flex items-center gap-3 shrink-0">
                    <a href="{{ route('operator.peluang-investasi') }}" class="px-4 py-2.5 rounded-xl bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs border border-[#CFE3D5] shadow-sm transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali ke Dashboard</span>
                    </a>
                    <button @click="isCreateModalOpen = true" class="px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white font-bold text-xs shadow-md transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-plus text-xs"></i>
                        <span>Tambah Proyek Baru</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Notification Alert -->
        @if(session('success'))
            <div class="mb-6 p-4 rounded-xl bg-[#EEF8F2] border border-[#CFE3D5] text-[#145239] flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <i class="fa-solid fa-circle-check text-lg text-[#145239]"></i>
                    <span class="text-sm font-semibold">{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-[#1E5D41] hover:text-[#145239]">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#CFE3D5] mb-6 flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
            <div class="relative flex-1">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" x-model="searchQuery" placeholder="Cari nama proyek atau deskripsi..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] text-sm placeholder:text-slate-400">
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative">
                    <select x-model="filterKabupaten" class="px-3.5 pr-10 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white appearance-none">
                        <option value="">Semua Kabupaten/Kota</option>
                        @foreach($kabupatens as $kab)
                            <option value="{{ $kab->kab_id }}">{{ $kab->nama_kabupaten }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
                        <svg class="w-4 h-4 text-slate-500 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                    </div>
                </div>

                <div class="relative">
                    <select x-model="filterSektor" class="px-3.5 pr-10 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white appearance-none">
                        <option value="">Semua Sektor Ekonomi</option>
                        @foreach($sektors as $sek)
                            <option value="{{ $sek->sektor_id }}">{{ $sek->nama_sektor }}</option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3">
                        <svg class="w-4 h-4 text-slate-500 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABEL MEMANJANG (WIDE ROW TABLE VIEW) -->
        <div class="bg-white rounded-2xl shadow-sm border border-[#CFE3D5] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-[#F7FAF8] border-b border-[#EEF8F2]">
                        <tr>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[50px]">No</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[280px]">Nama Proyek & Deskripsi</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[160px]">Kabupaten / Kota</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider min-w-[180px]">Sektor Ekonomi</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider text-center min-w-[140px]">Tahun & Tenor</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider text-center min-w-[200px]">Modul Dokumen</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider text-center min-w-[110px]">Status</th>
                            <th scope="col" class="px-5 py-4 text-xs font-bold text-slate-600 uppercase tracking-wider text-center min-w-[210px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#EEF8F2]">
                        @forelse($projects as $index => $project)
                            <tr x-show="
                                (!searchQuery || '{{ strtolower(addslashes($project->nama_proyek)) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($project->deskripsi ?: '')) }}'.includes(searchQuery.toLowerCase())) &&
                                (!filterKabupaten || '{{ $project->kabupaten_id }}' === filterKabupaten) &&
                                (!filterSektor || '{{ $project->sektor_id }}' === filterSektor)
                            " class="hover:bg-[#F7FAF8] transition-colors">
                                
                                <td class="px-5 py-4 text-slate-500 font-mono font-semibold">{{ $index + 1 }}</td>
                                
                                <td class="px-5 py-4">
                                    <a href="{{ route('operator.projects.show', $project->id) }}" class="font-bold text-slate-900 hover:text-[#145239] transition-colors text-sm">
                                        {{ $project->nama_proyek }}
                                    </a>
                                    @if($project->deskripsi)
                                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-1" title="{{ $project->deskripsi }}">
                                            {{ $project->deskripsi }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-[#EEF8F2] text-[#145239] border border-[#CFE3D5]">
                                        <i class="fa-solid fa-location-dot text-[10px] mr-1"></i>
                                        {{ $project->kabupaten ? $project->kabupaten->nama_kabupaten : 'Wilayah Sumut' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="text-xs font-semibold text-slate-700 block truncate max-w-[180px]">
                                        {{ $project->sektor ? $project->sektor->nama_sektor : '-' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="text-xs font-mono font-bold text-slate-800">{{ $project->tahun_awal }}</div>
                                    <div class="text-[11px] text-[#1E5D41] font-semibold">{{ $project->jangka_waktu_tahun }} Tahun</div>
                                </td>

                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="inline-flex items-center gap-1">
                                        <a href="{{ route('operator.projects.capex.index', $project->id) }}" class="px-2 py-1 rounded-md bg-[#EEF8F2] hover:bg-[#E7F2EB] text-[#145239] text-[10px] font-bold border border-[#CFE3D5] transition-colors" title="Kelola CAPEX">
                                            CAPEX
                                        </a>
                                        <a href="{{ route('operator.projects.pl.index', $project->id) }}" class="px-2 py-1 rounded-md bg-amber-50 hover:bg-amber-100 text-[#D4A017] text-[10px] font-bold border border-amber-200 transition-colors" title="Kelola P&L">
                                            P&L
                                        </a>
                                        <a href="{{ route('operator.projects.cashflow.index', $project->id) }}" class="px-2 py-1 rounded-md bg-emerald-50 hover:bg-emerald-100 text-emerald-800 text-[10px] font-bold border border-emerald-200 transition-colors" title="Kelola Cashflow">
                                            Cashflow
                                        </a>
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $project->status_publikasi === 'published' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($project->status_publikasi ?: 'published') }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('operator.projects.show', $project->id) }}" class="px-3 py-1.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold transition-colors shadow-sm inline-flex items-center gap-1">
                                            <i class="fa-solid fa-calculator text-[10px]"></i>
                                            <span>Kelola</span>
                                        </a>
                                        <button @click="openEditModal({{ json_encode($project) }})" class="p-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs transition-colors" title="Edit Proyek">
                                            <i class="fa-solid fa-pen"></i>
                                        </button>
                                        <button @click="openDeleteModal({{ json_encode($project) }})" class="p-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 text-xs transition-colors" title="Hapus Proyek">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-12 text-slate-400">
                                    <div class="w-12 h-12 rounded-2xl bg-[#EEF8F2] text-[#145239] flex items-center justify-center mx-auto mb-3 text-xl">
                                        <i class="fa-solid fa-folder-open"></i>
                                    </div>
                                    <p class="font-bold text-slate-700 text-sm">Belum Ada Proyek Investasi</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol 'Tambah Proyek Baru' untuk membuat proyek IPRO baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal Tambah Proyek Baru -->
        <div x-show="isCreateModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#CFE3D5]" @click.outside="isCreateModalOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-[#EEF8F2] mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#EEF8F2] text-[#145239] flex items-center justify-center font-bold">
                            <i class="fa-solid fa-folder-plus"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base">Buat Proyek Investasi IPRO</h3>
                            <p class="text-xs text-slate-500">Input parameter utama kelayakan proyek</p>
                        </div>
                    </div>
                    <button @click="isCreateModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('operator.projects.store') }}" method="POST" class="space-y-4 text-sm">
                    @csrf
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Proyek Investasi <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_proyek" required placeholder="Contoh: Pembangunan Kawasan Industri Pengolahan Sawit" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Kabupaten / Kota</label>
                            <div class="relative">
                                <select name="kabupaten_id" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm bg-white pr-10 appearance-none">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                    @foreach($kabupatens as $kab)
                                        <option value="{{ $kab->kab_id }}">{{ $kab->nama_kabupaten }}@if($kab->latitude && $kab->longitude) ({{ $kab->latitude }}, {{ $kab->longitude }})@endif</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5">
                                    <svg class="w-4 h-4 text-slate-500 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Sektor Ekonomi</label>
                            <div class="relative">
                                <select name="sektor_id" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm bg-white pr-10 appearance-none">
                                    <option value="">Pilih Sektor</option>
                                    @foreach($sektors as $sek)
                                        <option value="{{ $sek->sektor_id }}">{{ $sek->nama_sektor }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5">
                                    <svg class="w-4 h-4 text-slate-500 fill-current" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z" /></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahun Awal <span class="text-rose-500">*</span></label>
                            <input type="number" name="tahun_awal" required value="{{ date('Y') }}" min="2000" max="2100" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jangka Waktu (Tahun) <span class="text-rose-500">*</span></label>
                            <input type="number" name="jangka_waktu_tahun" required value="10" min="1" max="50" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Alamat / Lokasi Spesifik GIS</label>
                        <input type="text" name="alamat_lokasi" placeholder="Contoh: Jl. Lintas Sumatera Km 45, Medan / Koordinat GIS" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Deskripsi Ringkas</label>
                        <textarea name="deskripsi" rows="3" placeholder="Tuliskan gambaran umum potensi dan ruang lingkup proyek..." class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm resize-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-[#EEF8F2] flex items-center justify-end gap-3">
                        <button type="button" @click="isCreateModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors">
                            Simpan & Buat Proyek
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Proyek -->
        <div x-show="isEditModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-[#CFE3D5]" @click.outside="isEditModalOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-[#EEF8F2] mb-5">
                    <h3 class="font-bold text-slate-900 text-base">Edit Proyek Investasi</h3>
                    <button @click="isEditModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form :action="'{{ url('/operator/projects') }}/' + selectedProject.id" method="POST" class="space-y-4 text-sm">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Nama Proyek</label>
                        <input type="text" name="nama_proyek" x-model="selectedProject.nama_proyek" required class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Tahun Awal</label>
                            <input type="number" name="tahun_awal" x-model="selectedProject.tahun_awal" required class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm font-mono">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Jangka Waktu (Tahun)</label>
                            <input type="number" name="jangka_waktu_tahun" x-model="selectedProject.jangka_waktu_tahun" required class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm font-mono">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="deskripsi" x-model="selectedProject.deskripsi" rows="3" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm resize-none"></textarea>
                    </div>

                    <div class="pt-4 border-t border-[#EEF8F2] flex items-center justify-end gap-3">
                        <button type="button" @click="isEditModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Hapus Proyek -->
        <div x-show="isDeleteModalOpen" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-transition>
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-rose-200" @click.outside="isDeleteModalOpen = false">
                <div class="text-center p-4">
                    <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4 text-xl">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-bold text-slate-900 text-base mb-1">Hapus Proyek Investasi?</h3>
                    <p class="text-xs text-slate-500 leading-relaxed mb-4">
                        Proyek <strong class="text-slate-800" x-text="selectedProject.nama_proyek"></strong> beserta seluruh data CAPEX, P&L, dan Arus Kas akan dihapus permanen.
                    </p>
                </div>

                <form :action="'{{ url('/operator/projects') }}/' + selectedProject.id" method="POST" class="flex justify-center gap-3 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="isDeleteModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold shadow-md transition-colors">
                        Ya, Hapus Proyek
                    </button>
                </form>
            </div>
        </div>

    </div>
@endsection

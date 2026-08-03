@extends('partials.layouts.operator')

@section('title', 'Daftar Proyek Investasi (IPRO Engine) - DPMPTSP')
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

        <!-- Top Header & Breadcrumb Bar -->
        <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ route('operator.peluang-investasi') }}" class="hover:text-[#145239] font-medium">Peluang Investasi</a>
                    <span>/</span>
                    <span class="text-slate-800 font-semibold">Daftar Proyek (IPRO)</span>
                </div>
                <h1 class="text-2xl font-bold text-slate-900">Daftar Proyek Investasi Daerah</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola kelayakan proyek finansial daerah, komputasi CAPEX, Laba Rugi, dan Arus Kas.</p>
            </div>

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
                <select x-model="filterKabupaten" class="px-3.5 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white">
                    <option value="">Semua Kabupaten/Kota</option>
                    @foreach($kabupatens as $kab)
                        <option value="{{ $kab->kab_id }}">{{ $kab->nama_kabupaten }}</option>
                    @endforeach
                </select>

                <select x-model="filterSektor" class="px-3.5 py-2.5 rounded-xl border border-[#CFE3D5] focus:border-[#145239] text-sm text-slate-700 bg-white">
                    <option value="">Semua Sektor Ekonomi</option>
                    @foreach($sektors as $sek)
                        <option value="{{ $sek->sektor_id }}">{{ $sek->nama_sektor }}</option>
                    @endforeach
                </select>
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
                        <div x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('kabupaten_id', '') }}',
                            selectedName: '{{ old('kabupaten_id') ? ($kabupatens->firstWhere('kab_id', old('kabupaten_id'))->nama_kabupaten ?? '') : '' }}',
                            items: [
                                @foreach($kabupatens as $kab)
                                    {
                                        id: '{{ $kab->kab_id }}',
                                        name: '{{ addslashes($kab->nama_kabupaten) }}',
                                        coords: '{{ $kab->latitude && $kab->longitude ? "({$kab->latitude}, {$kab->longitude})" : "" }}'
                                    },
                                @endforeach
                            ],
                            get filteredItems() {
                                if (!this.search) return this.items;
                                const q = this.search.toLowerCase();
                                return this.items.filter(i => i.name.toLowerCase().includes(q) || i.coords.toLowerCase().includes(q));
                            },
                            selectItem(item) {
                                this.selectedId = item.id;
                                this.selectedName = item.name + (item.coords ? ' ' + item.coords : '');
                                this.open = false;
                                this.search = '';
                            },
                            clear() {
                                this.selectedId = '';
                                this.selectedName = '';
                                this.open = false;
                                this.search = '';
                            }
                        }" class="relative">
                            <label class="block font-semibold text-slate-700 mb-1">
                                Kabupaten / Kota <span class="text-[11px] text-[#145239] font-normal">(Scope Terdaftar)</span>
                            </label>

                            {{-- Input tersembunyi untuk dikirim saat form submit --}}
                            <input type="hidden" name="kabupaten_id" :value="selectedId">

                            {{-- Tombol Pemicu Dropdown --}}
                            <div @click="open = !open; if(open) $nextTick(() => $refs.searchInput.focus())"
                                class="w-full min-h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors focus-within:ring-2 focus-within:ring-[#145239]/20 shadow-sm">
                                <span x-text="selectedName || 'Pilih / Cari Kabupaten/Kota...'"
                                    :class="selectedName ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                <div class="flex items-center gap-1.5 text-slate-400">
                                    <template x-if="selectedId">
                                        <button type="button" @click.stop="clear()" class="hover:text-rose-500 p-0.5 rounded-full transition-colors" title="Hapus Pilihan">
                                            <i class="fa-solid fa-xmark text-xs"></i>
                                        </button>
                                    </template>
                                    <i class="fa-solid fa-chevron-down text-xs transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                            </div>

                            {{-- Menu Dropdown Dengan Pencarian --}}
                            <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">

                                {{-- Input Pencarian --}}
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                    <input type="text" x-model="search" x-ref="searchInput" @keydown.escape="open = false"
                                        placeholder="Ketik untuk mencari kabupaten/kota..."
                                        class="w-full pl-8 pr-3 py-2 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] outline-none">
                                </div>

                                {{-- Daftar Opsi Kabupaten --}}
                                <div class="max-h-52 overflow-y-auto space-y-0.5 text-xs divide-y divide-slate-50">
                                    <template x-for="item in filteredItems" :key="item.id">
                                        <div @click="selectItem(item)"
                                            :class="selectedId == item.id ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                            class="px-3 py-2.5 rounded-lg cursor-pointer flex items-center justify-between transition-colors">
                                            <div>
                                                <span x-text="item.name"></span>
                                                <span x-text="item.coords" class="text-[10px] text-slate-400 font-mono ml-1"></span>
                                            </div>
                                            <i x-show="selectedId == item.id" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                        </div>
                                    </template>

                                    <template x-if="filteredItems.length === 0">
                                        <div class="px-3 py-4 text-center text-xs text-slate-400">
                                            Kabupaten/Kota tidak ditemukan
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Sektor Ekonomi</label>
                            <select name="sektor_id" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm bg-white">
                                <option value="">Pilih Sektor</option>
                                @foreach($sektors as $sek)
                                    <option value="{{ $sek->sektor_id }}">{{ $sek->nama_sektor }}</option>
                                @endforeach
                            </select>
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
                        <textarea name="deskripsi" rows="3" placeholder="Tuliskan gambaran umum potensi dan ruang lingkup proyek..." class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] focus:ring-[#145239] text-sm"></textarea>
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
                        <textarea name="deskripsi" x-model="selectedProject.deskripsi" rows="3" class="w-full rounded-xl border-[#CFE3D5] focus:border-[#145239] text-sm"></textarea>
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

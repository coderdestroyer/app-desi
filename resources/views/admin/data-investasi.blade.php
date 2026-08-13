@extends('layouts.admin')

@section('title', 'Data Investasi')

@section('content')
    @php
        $isModalOpen = in_array($mode, ['create', 'edit'], true);
        $isEdit = $mode === 'edit' && $editData;
        $formAction = $isEdit
            ? route('admin.data-investasi.update', $editData->id)
            : route('admin.data-investasi.store');
            
        $statStyles = [
            'green' => [
                'icon' => 'bg-[#079A6A] text-white',
                'corner' => 'bg-emerald-50',
            ],
            'blue' => [
                'icon' => 'bg-[#0794CE] text-white',
                'corner' => 'bg-sky-50',
            ],
            'orange' => [
                'icon' => 'bg-[#FF9D00] text-white',
                'corner' => 'bg-amber-50',
            ],
            'violet' => [
                'icon' => 'bg-violet-600 text-white',
                'corner' => 'bg-violet-50',
            ],
        ];
    @endphp

    <script>
        function dataInvestasiManager() {
            return {
                isModalOpen: {{ (session('errors') || $isModalOpen) ? 'true' : 'false' }},
                isEdit: {{ (old('_method') === 'PUT' || $isEdit) ? 'true' : 'false' }},
                formAction: '{{ old('_method') === 'PUT' || $isEdit ? route('admin.data-investasi.update', old('id', $isEdit ? $editData->id : 0)) : route('admin.data-investasi.store') }}',
                
                // Form states
                nama_perusahaan: @json(old('nama_perusahaan', $isEdit ? $editData->nama_perusahaan : '')),
                status: @json(old('status', $isEdit ? $editData->status : 'PMDN')),
                provinsi_id: @json(old('provinsi_id', $isEdit ? $editData->provinsi_id : 12)),
                kabupaten_id: @json(old('kabupaten_id', $isEdit ? $editData->kabupaten_id : '')),
                nama_sektor: @json(old('nama_sektor', $isEdit ? $editData->nama_sektor : '')),
                tahun: @json(old('tahun', $isEdit ? $editData->tahun : date('Y'))),
                nilai_investasi: @json(old('nilai_investasi', $isEdit ? $editData->nilai_investasi : 0)),
                id_laporan_lkpm: @json(old('id_laporan_lkpm', $isEdit ? $editData->id_laporan_lkpm : '')),

                // Master lists for searchable dropdowns
                allProvinsi: @json($provinsiList),
                allKabupaten: @json($kabupatenList),

                // Dropdown open states & search queries
                provSearch: '',
                provOpen: false,
                kabSearch: '',
                kabOpen: false,

                // Delete & import modal states
                deleteModalOpen: false,
                deleteUrl: '',
                deleteCompany: '',
                importModalOpen: false,

                get selectedProvinsiName() {
                    let found = this.allProvinsi.find(p => p.provinsi_id == this.provinsi_id);
                    return found ? found.nama_provinsi : 'Pilih Provinsi';
                },

                get filteredProvinsi() {
                    if (!this.provSearch) return this.allProvinsi;
                    let q = this.provSearch.toLowerCase();
                    return this.allProvinsi.filter(p => p.nama_provinsi.toLowerCase().includes(q));
                },

                get selectedKabupatenName() {
                    if (!this.kabupaten_id) return '-- Pilih Kabupaten / Kota --';
                    let found = this.allKabupaten.find(k => k.kab_id == this.kabupaten_id);
                    return found ? found.nama_kabupaten : '-- Pilih Kabupaten / Kota --';
                },

                get filteredKabupaten() {
                    let list = this.allKabupaten;
                    if (this.provinsi_id) {
                        list = list.filter(k => k.provinsi_id == this.provinsi_id);
                    }
                    if (this.kabSearch) {
                        let q = this.kabSearch.toLowerCase();
                        list = list.filter(k => k.nama_kabupaten.toLowerCase().includes(q));
                    }
                    return list;
                },

                selectProvinsi(id) {
                    this.provinsi_id = id;
                    this.provOpen = false;
                    this.provSearch = '';
                    
                    if (this.kabupaten_id) {
                        let currentKab = this.allKabupaten.find(k => k.kab_id == this.kabupaten_id);
                        if (!currentKab || currentKab.provinsi_id != id) {
                            this.kabupaten_id = '';
                        }
                    }
                },

                selectKabupaten(id) {
                    this.kabupaten_id = id;
                    this.kabOpen = false;
                    this.kabSearch = '';
                    
                    if (id) {
                        let foundKab = this.allKabupaten.find(k => k.kab_id == id);
                        if (foundKab && foundKab.provinsi_id) {
                            this.provinsi_id = foundKab.provinsi_id;
                        }
                    }
                },

                openCreateModal() {
                    this.isEdit = false;
                    this.formAction = '{{ route('admin.data-investasi.store') }}';
                    this.nama_perusahaan = '';
                    this.status = 'PMDN';
                    this.provinsi_id = 12;
                    this.kabupaten_id = '';
                    this.nama_sektor = '';
                    this.tahun = '{{ date('Y') }}';
                    this.nilai_investasi = 0;
                    this.id_laporan_lkpm = '';
                    this.provSearch = '';
                    this.kabSearch = '';
                    this.provOpen = false;
                    this.kabOpen = false;
                    this.isModalOpen = true;
                },

                openEditModal(item) {
                    this.isEdit = true;
                    this.formAction = '{{ route('admin.data-investasi.update', 'PLACEHOLDER') }}'.replace('PLACEHOLDER', item.id);
                    this.nama_perusahaan = item.nama_perusahaan;
                    this.status = item.status;
                    this.provinsi_id = item.provinsi_id;
                    this.kabupaten_id = item.kabupaten_id || '';
                    this.nama_sektor = item.nama_sektor || '';
                    this.tahun = item.tahun;
                    this.nilai_investasi = item.nilai_investasi;
                    this.id_laporan_lkpm = item.id_laporan_lkpm || '';
                    this.provSearch = '';
                    this.kabSearch = '';
                    this.provOpen = false;
                    this.kabOpen = false;
                    this.isModalOpen = true;
                },
                
                closeModal() {
                    this.isModalOpen = false;
                    this.provOpen = false;
                    this.kabOpen = false;
                },

                confirmDelete(url, companyName) {
                    this.deleteUrl = url;
                    this.deleteCompany = companyName;
                    this.deleteModalOpen = true;
                }
            };
        }
    </script>

    <div class="min-h-screen bg-[#f7f9fc] p-4 sm:p-6 lg:p-8 space-y-6" x-data="dataInvestasiManager()">
        {{-- HEADER --}}
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-chart-line text-[#FFD54F]"></i>
                    <span>Menu Admin</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Manajemen Data Investasi
                </h1>
                <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Kelola data realisasi investasi LKPM, perusahaan, sektor bisnis, status modal (PMDN/PMA), dan sebaran wilayah kabupaten.
                </p>
            </div>

            <div class="relative z-10 w-full sm:w-auto shrink-0 flex flex-wrap items-center gap-3">
                <button type="button" @click="importModalOpen = true"
                    class="w-full sm:w-auto px-4 py-3 rounded-xl bg-emerald-800/80 hover:bg-emerald-800 text-white font-bold text-xs shadow-md transition-all duration-300 flex items-center justify-center gap-2 border border-emerald-600 cursor-pointer">
                    <i class="fa-solid fa-file-import text-sm text-[#FFD54F]"></i>
                    <span>Impor CSV / Excel</span>
                </button>
                <button type="button" @click="openCreateModal()"
                    class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5 cursor-pointer">
                    <i class="fa-solid fa-plus text-sm"></i>
                    <span>Tambah Data Investasi</span>
                </button>
            </div>
        </section>

        {{-- FLASH MESSAGES --}}
        @if (session('success'))
            <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        {{-- STATS CARDS --}}
        <section class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $statStyle = $statStyles[$stat['tone']] ?? $statStyles['green'];
                @endphp
                <article class="group relative min-h-[112px] overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md sm:min-h-[118px] sm:p-5">
                    <div class="absolute -right-8 -top-8 h-24 w-24 rounded-bl-full transition-transform duration-500 group-hover:scale-125 {{ $statStyle['corner'] }}"></div>
                    <div class="relative z-10 flex h-full items-center justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <p class="m-0 text-sm font-bold leading-5 text-slate-500">
                                {{ $stat['label'] }}
                            </p>
                            <p class="mb-0 mt-2 text-2xl font-black tracking-tight text-slate-900">
                                {{ $stat['value'] }}
                            </p>
                            <p class="mb-0 mt-1 text-xs font-medium leading-5 text-slate-400">
                                {{ $stat['description'] }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-lg shadow-sm sm:h-14 sm:w-14 sm:text-xl {{ $statStyle['icon'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- FILTERS --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
            <form action="{{ route('admin.data-investasi.index') }}" method="GET" data-live-filter data-no-loader
                class="grid w-full min-w-0 grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-12 items-center">
                
                {{-- 1. Provinsi Filter (Default: Sumatera Utara / 12) --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="provinsi_id"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="all" @selected($selectedProvId === 'all')>Semua Provinsi</option>
                        @foreach ($provinsiList as $prov)
                            <option value="{{ $prov->provinsi_id }}" @selected((string)$selectedProvId === (string)$prov->provinsi_id)>
                                {{ $prov->nama_provinsi }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 2. Kabupaten / Kota Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="kabupaten_id"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kabupaten / Kota</option>
                        @foreach ($kabupatenFilterList as $kab)
                            <option value="{{ $kab->kab_id }}" @selected((string)request('kabupaten_id') === (string)$kab->kab_id)>
                                {{ $kab->nama_kabupaten }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 3. Tahun Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="tahun"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Tahun</option>
                        @foreach ($tahunList as $th)
                            <option value="{{ $th }}" @selected((string)request('tahun') === (string)$th)>{{ $th }}</option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 4. Status Filter --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="status"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Status Modal</option>
                        <option value="PMDN" @selected(request('status') === 'PMDN')>PMDN</option>
                        <option value="PMA" @selected(request('status') === 'PMA')>PMA</option>
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- 5. Search Input --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                </div>

                {{-- 6. Reset Button --}}
                <div class="flex min-w-0 items-center justify-end sm:col-span-1 xl:col-span-1">
                    <a href="{{ route('admin.data-investasi.index') }}" title="Reset filter"
                        class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                        <span class="xl:hidden text-xs font-semibold">Reset</span>
                    </a>
                </div>
            </form>
        </section>

        {{-- TABLE SECTION --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="m-0 text-base sm:text-lg font-black text-slate-900">
                        Daftar Realisasi Data Investasi
                    </h2>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        Menampilkan entri data investasi berdasarkan filter pencarian.
                    </p>
                </div>

                <div class="inline-flex h-10 w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-black text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($dataInvestasi->total(), 0, ',', '.') }} Data
                </div>
            </header>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left min-w-[1000px]">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                            <th class="w-[60px] px-5 py-3 text-center">No</th>
                            <th class="min-w-[200px] px-4 py-3">Perusahaan & ID LKPM</th>
                            <th class="w-[110px] px-4 py-3 text-center">Status</th>
                            <th class="min-w-[180px] px-4 py-3">Sektor USaha</th>
                            <th class="min-w-[180px] px-4 py-3">Wilayah</th>
                            <th class="w-[90px] px-4 py-3 text-center">Tahun</th>
                            <th class="min-w-[170px] px-4 py-3 text-right">Nilai Investasi</th>
                            <th class="w-[100px] px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataInvestasi as $index => $item)
                            <tr class="transition hover:bg-slate-50/80">
                                <td class="px-5 py-4 text-center text-xs font-bold text-slate-400">
                                    {{ $dataInvestasi->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800 leading-snug">
                                        {{ $item->nama_perusahaan }}
                                    </div>
                                    @if ($item->id_laporan_lkpm)
                                        <div class="mt-1 inline-flex items-center gap-1 text-[11px] font-mono text-slate-400">
                                            <span>LKPM: #{{ $item->id_laporan_lkpm }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if ($item->status === 'PMA')
                                        <span class="inline-flex rounded-xl border border-violet-200 bg-violet-50 px-2.5 py-1 text-xs font-bold text-violet-700">
                                            PMA
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-xl border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                            PMDN
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-xs font-semibold text-slate-700 leading-relaxed max-w-[250px]">
                                        {{ $item->nama_sektor ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="text-xs font-medium text-slate-700">
                                        {{ $item->kabupaten->nama_kabupaten ?? ($item->provinsi->nama_provinsi ?? '-') }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex rounded-lg bg-slate-100 px-2 py-1 font-mono text-xs font-bold text-slate-700">
                                        {{ $item->tahun }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right font-mono font-bold text-emerald-800">
                                    Rp {{ number_format($item->nilai_investasi, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <button type="button" @click="openEditModal({{ json_encode($item) }})" title="Edit data"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600 cursor-pointer">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" @click="confirmDelete('{{ route('admin.data-investasi.destroy', $item->id) }}', '{{ addslashes($item->nama_perusahaan) }}')" title="Hapus data"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 cursor-pointer">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">
                                            <i class="fa-solid fa-chart-line"></i>
                                        </div>
                                        <h3 class="mb-0 mt-4 text-base font-bold text-slate-700">Data Investasi Tidak Ditemukan</h3>
                                        <p class="mb-0 mt-1 text-sm leading-relaxed text-slate-400">Gunakan kata kunci atau filter lain untuk menemukan data.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="p-4 border-t border-slate-100">
                <x-pagination :paginator="$dataInvestasi" />
            </div>
        </section>

        {{-- MODAL CREATE / EDIT --}}
        <template x-teleport="body">
            <div x-show="isModalOpen" x-cloak x-transition @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl max-w-2xl w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                                <i class="fa-solid" :class="isEdit ? 'fa-pen-to-square' : 'fa-plus'"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Edit Data Investasi' : 'Tambah Data Investasi Baru'"></h3>
                                <p class="text-xs text-slate-500">Lengkapi formulir detail realisasi investasi perusahaan.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeModal()"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="overflow-y-auto pt-4 flex-1 space-y-4 text-sm text-slate-700">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Nama Perusahaan --}}
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Nama Perusahaan <span class="text-red-500">*</span></label>
                                <input type="text" name="nama_perusahaan" x-model="nama_perusahaan" required placeholder="PT. Example Name"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            </div>

                            {{-- Status Modal --}}
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Status Modal <span class="text-red-500">*</span></label>
                                <select name="status" x-model="status" required
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                                    <option value="PMDN">PMDN (Penanaman Modal Dalam Negeri)</option>
                                    <option value="PMA">PMA (Penanaman Modal Asing)</option>
                                </select>
                            </div>

                            {{-- Tahun --}}
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Tahun Realisasi <span class="text-red-500">*</span></label>
                                <input type="number" name="tahun" x-model="tahun" required min="2000" max="2100"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs font-mono font-bold text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            </div>

                            {{-- Searchable & Dependent Dropdown: Provinsi --}}
                            <div class="relative" @click.outside="provOpen = false">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Provinsi <span class="text-red-500">*</span></label>
                                <input type="hidden" name="provinsi_id" :value="provinsi_id" required>
                                
                                <button type="button" @click="provOpen = !provOpen"
                                    class="w-full h-10 rounded-xl border border-slate-200 bg-white px-3.5 text-xs text-left text-slate-800 flex items-center justify-between outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 cursor-pointer">
                                    <span class="font-medium truncate" x-text="selectedProvinsiName"></span>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="provOpen ? 'rotate-180' : ''"></i>
                                </button>

                                {{-- Dropdown Menu --}}
                                <div x-show="provOpen" x-cloak x-transition
                                    class="absolute left-0 right-0 z-50 mt-1 max-h-60 rounded-xl bg-white p-2 shadow-xl border border-slate-200 flex flex-col gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                        <input type="text" x-model="provSearch" placeholder="Cari provinsi..."
                                            class="w-full h-8 rounded-lg bg-slate-50 border border-slate-200 pl-8 pr-3 text-xs outline-none focus:border-emerald-600 focus:bg-white">
                                    </div>
                                    <div class="overflow-y-auto max-h-44 space-y-0.5 pr-1">
                                        <template x-for="p in filteredProvinsi" :key="p.provinsi_id">
                                            <button type="button" @click="selectProvinsi(p.provinsi_id)"
                                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-emerald-50 hover:text-emerald-900 transition flex items-center justify-between cursor-pointer"
                                                :class="provinsi_id == p.provinsi_id ? 'bg-emerald-100/70 font-bold text-emerald-900' : 'text-slate-700'">
                                                <span x-text="p.nama_provinsi"></span>
                                                <i class="fa-solid fa-check text-emerald-600 text-xs" x-show="provinsi_id == p.provinsi_id"></i>
                                            </button>
                                        </template>
                                        <div x-show="filteredProvinsi.length === 0" class="py-3 text-center text-xs text-slate-400">
                                            Tidak ada provinsi yang cocok.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Searchable & Dependent Dropdown: Kabupaten / Kota --}}
                            <div class="relative" @click.outside="kabOpen = false">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Kabupaten / Kota</label>
                                <input type="hidden" name="kabupaten_id" :value="kabupaten_id">
                                
                                <button type="button" @click="kabOpen = !kabOpen"
                                    class="w-full h-10 rounded-xl border border-slate-200 bg-white px-3.5 text-xs text-left text-slate-800 flex items-center justify-between outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600 cursor-pointer">
                                    <span class="font-medium truncate" x-text="selectedKabupatenName"></span>
                                    <i class="fa-solid fa-chevron-down text-slate-400 text-xs transition-transform duration-200" :class="kabOpen ? 'rotate-180' : ''"></i>
                                </button>

                                {{-- Dropdown Menu --}}
                                <div x-show="kabOpen" x-cloak x-transition
                                    class="absolute left-0 right-0 z-50 mt-1 max-h-60 rounded-xl bg-white p-2 shadow-xl border border-slate-200 flex flex-col gap-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                                        <input type="text" x-model="kabSearch" placeholder="Cari kabupaten / kota..."
                                            class="w-full h-8 rounded-lg bg-slate-50 border border-slate-200 pl-8 pr-3 text-xs outline-none focus:border-emerald-600 focus:bg-white">
                                    </div>
                                    <div class="overflow-y-auto max-h-44 space-y-0.5 pr-1">
                                        <button type="button" @click="selectKabupaten('')"
                                            class="w-full text-left px-3 py-2 rounded-lg text-xs text-slate-500 hover:bg-slate-100 transition flex items-center justify-between cursor-pointer">
                                            <span>-- Pilih Kabupaten / Kota --</span>
                                            <i class="fa-solid fa-check text-emerald-600 text-xs" x-show="!kabupaten_id"></i>
                                        </button>
                                        <template x-for="k in filteredKabupaten" :key="k.kab_id">
                                            <button type="button" @click="selectKabupaten(k.kab_id)"
                                                class="w-full text-left px-3 py-2 rounded-lg text-xs hover:bg-emerald-50 hover:text-emerald-900 transition flex items-center justify-between cursor-pointer"
                                                :class="kabupaten_id == k.kab_id ? 'bg-emerald-100/70 font-bold text-emerald-900' : 'text-slate-700'">
                                                <span x-text="k.nama_kabupaten"></span>
                                                <i class="fa-solid fa-check text-emerald-600 text-xs" x-show="kabupaten_id == k.kab_id"></i>
                                            </button>
                                        </template>
                                        <div x-show="filteredKabupaten.length === 0" class="py-3 text-center text-xs text-slate-400">
                                            Tidak ada kabupaten/kota yang cocok.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Nama Sektor --}}
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs font-bold text-slate-700">Nama Sektor Usaha</label>
                                <input type="text" name="nama_sektor" x-model="nama_sektor" placeholder="Contoh: Industri Pengolahan / Perdagangan"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            </div>

                            {{-- Nilai Investasi --}}
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">Nilai Investasi (Rp) <span class="text-red-500">*</span></label>
                                <input type="number" step="any" name="nilai_investasi" x-model="nilai_investasi" required placeholder="0"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs font-mono font-bold text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            </div>

                            {{-- ID LKPM --}}
                            <div>
                                <label class="mb-1 block text-xs font-bold text-slate-700">ID Laporan LKPM</label>
                                <input type="number" name="id_laporan_lkpm" x-model="id_laporan_lkpm" placeholder="Opsional"
                                    class="w-full h-10 rounded-xl border border-slate-200 px-3.5 text-xs text-slate-800 outline-none focus:border-emerald-600 focus:ring-1 focus:ring-emerald-600">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                            <button type="button" @click="closeModal()"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#145239] text-white text-xs font-bold shadow-md hover:bg-[#0f3d2a] transition cursor-pointer flex items-center gap-2">
                                <i class="fa-solid fa-save"></i>
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Data'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- MODAL IMPORT CSV / EXCEL --}}
        <template x-teleport="body">
            <div x-show="importModalOpen" x-cloak x-transition @keydown.escape.window="importModalOpen = false"
                class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl max-w-xl w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="importModalOpen = false">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                                <i class="fa-solid fa-file-csv text-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base">Impor Data Investasi (CSV / Excel)</h3>
                                <p class="text-xs text-slate-500">Unggah berkas spreadsheet (.csv) hasil ekspor data investasi.</p>
                            </div>
                        </div>

                        <button type="button" @click="importModalOpen = false"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0 cursor-pointer">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.data-investasi.import') }}" method="POST" enctype="multipart/form-data" class="overflow-y-auto pt-4 flex-1 space-y-4 text-sm text-slate-700">
                        @csrf

                        {{-- Aturan Header Kolom --}}
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 space-y-2">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <h4 class="font-bold text-xs text-[#145239] flex items-center gap-1.5">
                                    <i class="fa-solid fa-circle-info"></i>
                                    Aturan Kolom File Spreadsheet:
                                </h4>
                                <a href="{{ route('admin.data-investasi.template') }}" download="Template_Import_Data_Investasi.csv" data-no-loader title="Unduh contoh format CSV"
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#145239] bg-white px-2.5 py-1 rounded-lg border border-emerald-300 hover:bg-emerald-100 transition shadow-2xs">
                                    <i class="fa-solid fa-download text-[10px]"></i>
                                    <span>Unduh Template Format</span>
                                </a>
                            </div>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Mendukung berkas <strong>.xlsx</strong> / <strong>.csv</strong>. Sistem otomatis membaca 9 kolom target di bawah dan mengabaikan kolom tambahan seperti <em>(No, Negara, triwulan, Email, Nomor Izin, Deskripsi KBLI, tki)</em>:
                            </p>
                            <div class="p-2.5 rounded-lg bg-white border border-emerald-200 text-[11px] font-mono text-emerald-900 leading-normal overflow-x-auto select-all">
                                id_laporan_lkpm | id_proyek_nku | Nama Perusahaan | Status | Tahun | Kab / Kota usaha | provinsi usaha | Nama Sektor | Nilai Investasi (Rp)
                            </div>
                            <ul class="text-[11px] text-slate-500 space-y-1 pl-4 list-disc">
                                <li><strong>Nama Perusahaan</strong> & <strong>Nilai Investasi (Rp)</strong> wajib diisi.</li>
                                <li><strong>Status</strong>: `PMDN` atau `PMA` (otomatis PMDN jika kosong).</li>
                                <li><strong>Kab / Kota usaha</strong>: Contoh <em>Kabupaten Tapanuli Selatan</em>, <em>Kabupaten Deli Serdang</em>, atau <em>Kota Medan</em> akan dicocokkan otomatis dengan master wilayah.</li>
                                <li><strong>Nilai Investasi (Rp)</strong>: Sistem otomatis mengonversi angka mentah atau format rupiah ke nilai numerik desimal.</li>
                            </ul>
                        </div>

                        {{-- File Input --}}
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-slate-700">Pilih Berkas CSV / Spreadsheet <span class="text-red-500">*</span></label>
                            <input type="file" name="file" accept=".csv,.txt,.xlsx,.xls" required
                                class="w-full text-xs text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#EEF8F2] file:text-[#145239] hover:file:bg-emerald-100 border border-slate-200 rounded-xl cursor-pointer p-1">
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3 shrink-0">
                            <button type="button" @click="importModalOpen = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-semibold text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#145239] text-white text-xs font-bold shadow-md hover:bg-[#0f3d2a] transition cursor-pointer flex items-center gap-2">
                                <i class="fa-solid fa-file-import"></i>
                                <span>Mulai Impor Data</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <footer class="pb-2 pt-8 text-center text-xs text-slate-400">
        Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
    </footer>
@endsection

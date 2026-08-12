@extends('layouts.admin')

@section('title', 'Data KBLI 2025')

@section('content')
    @php
        $isModalOpen = in_array($mode, ['create', 'edit'], true);
        $isEdit = $mode === 'edit' && $editData;
        $formAction = $isEdit
            ? route('admin.data-kbli.update', $editData->id)
            : route('admin.data-kbli.store');
        $closeModalUrl = route(
            'admin.data-kbli.index',
            request()->except(['mode', 'edit'])
        );
        $createUrl = route(
            'admin.data-kbli.index',
            array_merge(
                request()->except(['page', 'edit', 'export']),
                ['mode' => 'create']
            )
        );
        $exportUrl = route(
            'admin.data-kbli.index',
            array_merge(
                request()->except(['page', 'mode', 'edit', 'export']),
                ['export' => 1]
            )
        );
        $badgeStyles = [
            'Kategori' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'Golongan Pokok' => 'border-orange-200 bg-orange-50 text-orange-700',
            'Golongan' => 'border-sky-200 bg-sky-50 text-sky-700',
            'Subgolongan' => 'border-red-200 bg-red-50 text-red-700',
            'Kelompok' => 'border-violet-200 bg-violet-50 text-violet-700',
        ];
        $statStyles = [
            'green' => [
                'icon' => 'bg-[#079A6A] text-white',
                'corner' => 'bg-emerald-50',
            ],
            'orange' => [
                'icon' => 'bg-[#FF9D00] text-white',
                'corner' => 'bg-amber-50',
            ],
            'blue' => [
                'icon' => 'bg-[#0794CE] text-white',
                'corner' => 'bg-sky-50',
            ],
            'red' => [
                'icon' => 'bg-[#F94242] text-white',
                'corner' => 'bg-red-50',
            ],
            'violet' => [
                'icon' => 'bg-violet-600 text-white',
                'corner' => 'bg-violet-50',
            ],
            'teal' => [
                'icon' => 'bg-teal-700 text-white',
                'corner' => 'bg-teal-50',
            ],
        ];
        $selectedStructure = old('struktur', $isEdit ? ($editData->struktur ?? '') : '');
        $selectedParent = old('kode_induk', $isEdit ? ($editData->kode_induk ?? '') : '');
        $selectedCode = old('kode', $isEdit ? ($editData->kode ?? '') : '');
        $selectedTitle = old('judul', $isEdit ? ($editData->judul ?? '') : '');
        $selectedCakupan = old('cakupan', $isEdit ? ($editData->cakupan ?? '') : '');
        $selectedTidakCakupan = old('tidak_cakupan', $isEdit ? ($editData->tidak_cakupan ?? '') : '');
        $selectedCatatan = old('catatan', $isEdit ? ($editData->catatan ?? '') : '');
        $editQueryParams = array_merge(request()->except(['edit', 'mode']), ['mode' => 'edit']);
        $editBaseUrl = route('admin.data-kbli.index', $editQueryParams) . '&edit=';
        $deleteBaseUrl = route('admin.data-kbli.destroy', 'PLACEHOLDER');
    @endphp

    <div class="min-h-screen bg-[#f7f9fc] p-4 sm:p-6 lg:p-8 space-y-6" x-data="{
        isModalOpen: {{ (session('errors') || old('kode') || $isModalOpen) ? 'true' : 'false' }},
        isEdit: {{ (old('_method') === 'PUT' || $isEdit) ? 'true' : 'false' }},
        formAction: '{{ old('_method') === 'PUT' || $isEdit ? route('admin.data-kbli.update', old('kbli_id', $isEdit ? $editData->id : 0)) : route('admin.data-kbli.store') }}',

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.data-kbli.store') }}';
            this.isModalOpen = true;
        },
        
        closeModal() {
            this.isModalOpen = false;
            if (window.location.search) {
                window.location.href = '{{ route('admin.data-kbli.index') }}';
            }
        }
    }">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-table-cells-large text-[#FFD54F]"></i>
                    <span>Menu Admin</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Manajemen Data KBLI
                </h1>
                <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Kelola struktur Kategori, Golongan Pokok, Golongan, Subgolongan, Kelompok, cakupan, dan pengecualian KBLI 2025.
                </p>
            </div>

            <div class="relative z-10 w-full sm:w-auto shrink-0">
                @if ($columnsReady)
                    <button type="button" @click="openCreateModal()"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span>Tambah KBLI</span>
                    </button>
                @endif
            </div>
        </section>

        @if (!$tableExists)
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Tabel <strong>data_kbli</strong> belum tersedia di Supabase.</span>
            </div>
        @elseif (!$columnsReady)
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Struktur kolom tabel <strong>data_kbli</strong> belum sesuai dengan dataset KBLI 2025.</span>
            </div>
        @endif

        @if (session('success'))
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <section class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
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
                            <p class="mb-0 mt-2 text-3xl font-black tracking-tight text-slate-900">
                                {{ number_format($stat['value'], 0, ',', '.') }}
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

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
            <form action="{{ route('admin.data-kbli.index') }}" method="GET" data-live-filter data-no-loader
                class="grid w-full min-w-0 grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-12 items-center">
                
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                    <select name="struktur"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Level</option>
                        @foreach (['Kategori', 'Golongan Pokok', 'Golongan', 'Subgolongan', 'Kelompok'] as $structureOption)
                            <option value="{{ $structureOption }}" @selected(request('struktur') === $structureOption)>
                                {{ $structureOption }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="kategori"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->kode }}" @selected(request('kategori') === $category->kode)>
                                {{ $category->kode }} — {{ \Illuminate\Support\Str::limit($category->judul, 45) }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="per_page"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        @foreach ($perPageOptions as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 22) === $perPageOption)>
                                {{ $perPageOption }} {{ $hierarchyMode ? 'kategori' : 'data' }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode, judul, cakupan..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <button type="submit" aria-label="Cari KBLI"
                    class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
                </div>

                <div class="flex min-w-0 items-center justify-end sm:col-span-2 xl:col-span-1">
                    <a href="{{ route('admin.data-kbli.index') }}" title="Reset filter"
                        class="inline-flex h-11 w-full sm:w-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                        <span class="sm:hidden text-xs font-semibold">Reset Filter</span>
                    </a>
                </div>
            </form>
        </section>

        <div id="tableContainer" class="transition-opacity duration-200">
            <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="m-0 text-base sm:text-lg font-black text-slate-900">
                        {{ $hierarchyMode ? 'Struktur Hierarki KBLI' : 'Hasil Pencarian KBLI' }}
                    </h2>
                    <p class="mb-0 mt-1 text-xs text-slate-500">
                        @if ($hierarchyMode)
                            Semua kategori ditampilkan terlebih dahulu. Buka kategori untuk melihat data turunannya.
                        @else
                            Data ditampilkan sesuai kata pencarian dan filter yang digunakan.
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                    @if ($hierarchyMode && $dataKbli->isNotEmpty())
                        <div class="relative min-w-[180px] flex-1 sm:flex-none">
                            <select id="jumpCategory"
                                class="h-10 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3.5 pr-9 text-xs font-bold text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                <option value="">Lompat ke kategori</option>
                                @foreach ($dataKbli->where('level', 1) as $categoryRow)
                                    <option value="{{ $categoryRow->kode }}">
                                        {{ $categoryRow->kode }} — {{ \Illuminate\Support\Str::limit($categoryRow->judul, 32) }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                        </div>

                        <button type="button" id="collapseAllKbli"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                            <i class="fa-solid fa-angles-up"></i>
                            <span class="hidden sm:inline">Tutup Semua</span>
                        </button>
                    @endif

                    <a href="{{ $exportUrl }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                        <i class="fa-solid fa-download"></i>
                        <span class="hidden sm:inline">Ekspor CSV</span>
                    </a>

                    <div class="inline-flex h-10 w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 text-xs font-black text-emerald-700">
                        <i class="fa-solid fa-database"></i>
                        {{ number_format($totalData, 0, ',', '.') }} Data
                    </div>
                </div>
            </header>

            <div id="adminKbliTableWrapper" class="overflow-x-auto">
                <table id="adminKbliTable"
                    class="w-full border-collapse text-left min-w-[1000px] [&_thead]:!table-header-group [&_tbody]:!table-row-group [&_tr]:!table-row [&_th]:!table-cell [&_td]:!table-cell [&_tr[hidden]]:!hidden">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                            <th class="w-[200px] px-5 py-3">Struktur</th>
                            <th class="w-[100px] px-4 py-3">Kode</th>
                            <th class="min-w-[280px] px-4 py-3">Judul KBLI</th>
                            <th class="min-w-[240px] px-4 py-3">Cakupan</th>
                            <th class="min-w-[240px] px-4 py-3">Tidak Cakupan</th>
                            <th class="w-[100px] px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataKbli as $item)
                            @php
                                $level = (int) $item->level;
                                $indent = max(0, ($level - 1) * 24);
                                $hasChildren = (int) $item->child_count > 0;
                                $badgeStyle = $badgeStyles[$item->struktur] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                            @endphp
                            <tr data-kbli-row data-code="{{ $item->kode }}" data-parent="{{ $item->kode_induk }}"
                                data-level="{{ $level }}" @if ($level === 1) id="kategori-{{ $item->kode }}" @endif @if ($hierarchyMode && $level > 1) hidden @endif
                                class="transition {{ $level === 1 ? 'bg-slate-50/80 hover:bg-emerald-50/60' : 'hover:bg-slate-50/80' }}">
                                <td class="relative px-5 py-4">
                                    @for ($treeLevel = 1; $treeLevel < $level; $treeLevel++)
                                        <span class="absolute top-0 bottom-0 w-[1px] bg-[#dbe3ec]"
                                            style="left: {{ 22 + (($treeLevel - 1) * 24) }}px"></span>
                                    @endfor
                                    @if ($level > 1)
                                        <span class="absolute top-1/2 h-[1px] bg-[#dbe3ec]"
                                            style="left: {{ 22 + (($level - 2) * 24) }}px; width: 18px"></span>
                                    @endif
                                    <div class="relative flex items-center gap-2" style="padding-left: {{ $indent }}px">
                                        @if ($hasChildren && $hierarchyMode)
                                            <button type="button" data-tree-toggle="{{ $item->kode }}"
                                                aria-label="Buka atau tutup turunan {{ $item->kode }}"
                                                aria-expanded="{{ $hierarchyMode ? 'false' : 'true' }}"
                                                class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-[10px] text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                                <i class="fa-solid {{ $hierarchyMode ? 'fa-chevron-right' : 'fa-chevron-down' }}"></i>
                                            </button>
                                        @else
                                            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center text-[8px] text-slate-300">
                                                <i class="fa-solid fa-circle"></i>
                                            </span>
                                        @endif
                                        <span class="inline-flex whitespace-nowrap rounded-xl border px-2.5 py-1 text-[11px] font-bold {{ $badgeStyle }}">
                                            {{ $item->struktur }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex rounded-lg border border-emerald-200 bg-emerald-50 px-2.5 py-1.5 font-mono text-xs font-black text-emerald-700">
                                        {{ $item->kode }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="max-w-[360px] font-semibold leading-relaxed text-slate-700"
                                        title="{{ $item->judul }}">
                                        {{ $item->judul }}
                                    </div>
                                    @if ($item->catatan)
                                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700"
                                            title="{{ $item->catatan }}">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Ada catatan data
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="line-clamp-2 max-w-[300px] text-xs leading-relaxed text-slate-500"
                                        title="{{ $item->cakupan }}">
                                        {{ $item->cakupan ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="line-clamp-2 max-w-[300px] text-xs leading-relaxed text-slate-500"
                                        title="{{ $item->tidak_cakupan }}">
                                        {{ $item->tidak_cakupan ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ $editBaseUrl }}{{ $item->id }}" title="Edit data"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <button type="button" title="Hapus data" data-delete-kbli
                                            data-delete-url="{{ str_replace('PLACEHOLDER', $item->id, $deleteBaseUrl) }}"
                                            data-delete-code="{{ $item->kode }}" data-delete-title="{{ $item->judul }}"
                                            data-delete-children="{{ (int) $item->child_count }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-2xl text-slate-400">
                                            <i class="fa-solid fa-sitemap"></i>
                                        </div>
                                        <h3 class="mb-0 mt-4 text-base font-bold text-slate-700">Data KBLI tidak ditemukan</h3>
                                        <p class="mb-0 mt-1 text-sm leading-relaxed text-slate-400">Ubah kata pencarian atau filter untuk menampilkan data lainnya.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Component -->
            <x-pagination :paginator="$paginator" />
        </section>
    </div>

        <footer class="pb-2 pt-8 text-center text-xs text-slate-400">
            Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
        </footer>

        <template x-teleport="body">
            <div x-show="isModalOpen" x-cloak x-transition @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl max-w-6xl w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                                <i class="fa-solid" :class="isEdit ? 'fa-pen-to-square' : 'fa-plus'"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Edit Data KBLI 2025' : 'Tambah Data KBLI 2025'"></h3>
                                <p class="text-xs text-slate-500">Isi data sesuai level dan hubungan induk pada struktur KBLI.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeModal()"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="overflow-y-auto pt-4 flex-1 space-y-4 text-sm text-slate-700"
                        x-data="kbliFormHandler()" @submit="validateSubmit($event)">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-[minmax(0,1fr)_310px]">
                        <div class="py-2 space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                                <div>
                                    <label class="mb-1 block text-sm font-bold text-slate-700">
                                        Level Struktur <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <input type="hidden" name="struktur" :value="struktur" required>

                                    <!-- Dropdown Trigger -->
                                    <div x-data="{ open: false }" class="relative z-50">
                                        <div @click="open = !open"
                                            class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                            <span x-text="struktur || 'Pilih level struktur...'" :class="struktur ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                        </div>

                                        <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-1 space-y-0.5 text-xs">
                                            <template x-for="opt in strukturOptions" :key="opt">
                                                <div @click="selectStruktur(opt); open = false"
                                                    :class="struktur === opt ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                    class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                    <span x-text="opt"></span>
                                                    <i x-show="struktur === opt" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>

                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400"
                                        x-text="structureConfig ? 'Level ' + structureConfig.level + ' dari 5 pada struktur KBLI.' : 'Pilih posisi data pada hierarki KBLI.'"></p>
                                    
                                    @error('struktur')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-bold text-slate-700">
                                        Induk / Parent <span x-show="structureConfig && structureConfig.parentLevel !== null" class="text-red-500">*</span>
                                    </label>
                                    
                                    <input type="hidden" name="kode_induk" :value="kode_induk">

                                    <!-- Dropdown Trigger -->
                                    <div class="relative z-40">
                                        <div @click="if(structureConfig && structureConfig.parentLevel !== null) { parentDropdownOpen = !parentDropdownOpen; if(parentDropdownOpen) $nextTick(() => $refs.parentSearchInput.focus()) }"
                                            :class="(structureConfig && structureConfig.parentLevel !== null) ? 'bg-white cursor-pointer hover:border-[#145239]' : 'bg-slate-50 cursor-not-allowed text-slate-400'"
                                            class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] text-sm flex items-center justify-between transition-colors shadow-2xs">
                                            <span x-text="selectedParentLabel" :class="kode_induk ? 'text-slate-800 font-medium' : 'text-slate-400'" class="truncate"></span>
                                            <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200 shrink-0 ml-1" :class="parentDropdownOpen ? 'rotate-180' : ''"></i>
                                        </div>

                                        <div x-show="parentDropdownOpen" @click.outside="parentDropdownOpen = false" x-transition.origin.top.duration.150ms
                                            class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                            <div class="relative">
                                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                                <input type="text" x-model="parentSearch" x-ref="parentSearchInput" placeholder="Cari data induk..."
                                                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                            </div>
                                            <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                                <template x-for="item in filteredParents" :key="item.kode">
                                                    <div @click="selectParent(item)"
                                                        :class="kode_induk == item.kode ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                        <span x-text="item.kode + ' — ' + item.judul"></span>
                                                        <i x-show="kode_induk == item.kode" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                                    </div>
                                                </template>
                                                <template x-if="filteredParents.length === 0">
                                                    <div class="px-3 py-2 text-slate-400 text-center">Data induk tidak ditemukan</div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Pilihan induk disesuaikan otomatis dengan level struktur.</p>
                                    
                                    @error('kode_induk')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="kode" class="mb-1 block text-sm font-bold text-slate-700">
                                        Kode KBLI <span class="text-red-500">*</span>
                                    </label>
                                    <input id="kode" type="text" name="kode" x-model="kode" @input="onCodeInput()"
                                        :maxlength="structureConfig ? structureConfig.length : ''"
                                        :placeholder="structureConfig ? structureConfig.placeholder : 'Pilih level struktur'"
                                        :inputmode="structureConfig ? (structureConfig.level === 1 ? 'text' : 'numeric') : 'text'"
                                        :pattern="structureConfig ? (structureConfig.level === 1 ? '[A-Va-v]' : '[0-9]{' + structureConfig.length + '}') : ''"
                                        required autocomplete="off"
                                        class="h-[42px] w-full rounded-xl border border-[#CFE3D5] px-3.5 font-mono text-sm font-bold tracking-wide text-slate-700 outline-none transition placeholder:font-sans placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs">
                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400"
                                        x-text="structureConfig ? structureConfig.help : 'Format kode akan menyesuaikan level.'"></p>
                                    @error('kode')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="judul" class="mb-1 block text-sm font-bold text-slate-700">
                                        Judul KBLI <span class="text-red-500">*</span>
                                    </label>
                                    <input id="judul" type="text" name="judul" x-model="judul"
                                        placeholder="Masukkan judul kegiatan usaha" required
                                        class="h-[42px] w-full rounded-xl border border-[#CFE3D5] px-3.5 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs">
                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Gunakan nama kegiatan yang jelas dan sesuai klasifikasi.</p>
                                    @error('judul')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rounded-2xl border border-[#CFE3D5] bg-slate-50/60 p-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </span>
                                        <label for="cakupan" class="text-sm font-bold text-slate-700">Cakupan</label>
                                    </div>
                                    <textarea id="cakupan" name="cakupan" rows="5" x-model="cakupan"
                                        placeholder="Jelaskan kegiatan, proses, produk, atau layanan yang termasuk dalam cakupan KBLI ini"
                                        class="w-full resize-none rounded-xl border border-[#CFE3D5] bg-white px-3.5 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs"></textarea>
                                    @error('cakupan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rounded-2xl border border-[#CFE3D5] bg-slate-50/60 p-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 text-red-600">
                                            <i class="fa-solid fa-ban"></i>
                                        </span>
                                        <label for="tidak_cakupan" class="text-sm font-bold text-slate-700">Tidak Cakupan</label>
                                    </div>
                                    <textarea id="tidak_cakupan" name="tidak_cakupan" rows="5" x-model="tidak_cakupan"
                                        placeholder="Jelaskan kegiatan yang tidak termasuk dalam cakupan KBLI ini"
                                        class="w-full resize-none rounded-xl border border-[#CFE3D5] bg-white px-3.5 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs"></textarea>
                                    @error('tidak_cakupan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-2">
                                    <label for="catatan" class="mb-1 block text-sm font-bold text-slate-700">Catatan Data</label>
                                    <textarea id="catatan" name="catatan" rows="3" x-model="catatan"
                                        placeholder="Tambahkan catatan koreksi atau keterangan khusus bila diperlukan"
                                        class="w-full resize-none rounded-xl border border-[#CFE3D5] px-3.5 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs"></textarea>
                                    @error('catatan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <aside class="border-t border-slate-200 bg-slate-50/70 p-4 sm:p-5 lg:border-l lg:border-t-0 space-y-4">
                            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white">
                                <header class="border-b border-emerald-100 bg-emerald-50 px-4 py-3 text-center">
                                    <h3 class="m-0 text-sm font-black text-emerald-800">Preview Hierarki</h3>
                                </header>
                                <div class="p-4">
                                    <div class="flex min-h-12 flex-wrap items-center gap-2">
                                        <template x-if="previewChips.length === 0">
                                            <span class="text-xs text-slate-400">Pilih level struktur untuk melihat hierarki.</span>
                                        </template>
                                        <template x-for="(chip, index) in previewChips" :key="index">
                                            <div class="flex items-center gap-2">
                                                <i x-show="index > 0" class="fa-solid fa-chevron-right text-[9px] text-slate-300"></i>
                                                <span class="inline-flex rounded-lg border px-2.5 py-1 font-mono text-xs font-black"
                                                    :class="chip.struktur === 'Kategori' ? 'border-emerald-200 bg-emerald-50 text-emerald-700' :
                                                             chip.struktur === 'Golongan Pokok' ? 'border-orange-200 bg-orange-50 text-orange-700' :
                                                             chip.struktur === 'Golongan' ? 'border-sky-200 bg-sky-50 text-sky-700' :
                                                             chip.struktur === 'Subgolongan' ? 'border-red-200 bg-red-50 text-red-700' :
                                                             'border-violet-200 bg-violet-50 text-violet-700'"
                                                    x-text="chip.code"></span>
                                            </div>
                                        </template>
                                    </div>
                                    <p class="mb-0 mt-3 text-xs leading-relaxed text-slate-400">Urutan kode akan berubah mengikuti level dan data induk yang dipilih.</p>
                                </div>
                            </section>

                            <section class="rounded-2xl border border-slate-200 bg-white p-4">
                                <h3 class="m-0 flex items-center gap-2 text-sm font-black text-slate-800">
                                    <i class="fa-solid fa-list-check text-emerald-600"></i>
                                    Struktur KBLI 2025
                                </h3>
                                <div class="mt-3 space-y-2.5">
                                    @foreach ([
                                            ['Kategori', 'A', 'Huruf A–V'],
                                            ['Golongan Pokok', '01', '2 digit'],
                                            ['Golongan', '011', '3 digit'],
                                            ['Subgolongan', '0111', '4 digit'],
                                            ['Kelompok', '01111', '5 digit'],
                                        ] as [$guideLevel, $guideCode, $guideDescription])
                                        <div class="flex items-center gap-3">
                                            <span class="inline-flex min-w-[58px] justify-center rounded-lg border px-2 py-1 font-mono text-[11px] font-black {{ $badgeStyles[$guideLevel] }}">
                                                {{ $guideCode }}
                                            </span>
                                            <div>
                                                <p class="m-0 text-xs font-bold text-slate-700">{{ $guideLevel }}</p>
                                                <p class="mb-0 mt-0.5 text-[11px] text-slate-400">{{ $guideDescription }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </section>

                            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
                                <h3 class="m-0 flex items-center gap-2 text-sm font-black text-amber-800">
                                    <i class="fa-solid fa-lightbulb"></i>
                                    Panduan Singkat
                                </h3>
                                <ul class="mb-0 mt-3 space-y-2 pl-4 text-xs leading-relaxed text-amber-900/75">
                                    <li>Kategori tidak memiliki data induk.</li>
                                    <li>Level lain wajib memilih induk satu tingkat di atasnya.</li>
                                    <li>Kode Golongan sampai Kelompok harus diawali kode induknya.</li>
                                    <li>Data yang memiliki turunan tidak dapat dipindahkan level atau induknya.</li>
                                </ul>
                            </section>
                        </aside>
                    </div>

                        <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row shrink-0">
                            <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                Batal
                            </button>
                            <button type="submit"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Data KBLI'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <div id="deleteKbliModal"
        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl border border-white/20 bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="mb-0 mt-5 text-xl font-black text-slate-900">Hapus data KBLI?</h3>
            <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
                Kode <strong id="deleteKbliCode" class="text-slate-700"></strong> — <span id="deleteKbliTitle"></span> akan dihapus.
            </p>
            <div id="deleteKbliWarning"
                class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold leading-relaxed text-amber-800">
                Data ini memiliki turunan dan tidak dapat dihapus sebelum seluruh turunannya dihapus.
            </div>
            <form id="deleteKbliForm" action="" method="POST"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                @csrf
                @method('DELETE')
                <button type="button" id="cancelDeleteKbli"
                    class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="confirmDeleteKbli"
                    class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                    <i class="fa-regular fa-trash-can"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const hierarchyMode = @json($hierarchyMode);
                const rows = Array.from(document.querySelectorAll('[data-kbli-row]'));
                const childrenByParent = new Map();
                const toggleByCode = new Map();

                rows.forEach(function (row) {
                    const parent = row.dataset.parent;

                    if (!parent) {
                        return;
                    }

                    if (!childrenByParent.has(parent)) {
                        childrenByParent.set(parent, []);
                    }

                    childrenByParent.get(parent).push(row);
                });

                document.querySelectorAll('[data-tree-toggle]').forEach(function (button) {
                    toggleByCode.set(button.dataset.treeToggle, button);
                });

                function setToggleState(code, expanded) {
                    const button = toggleByCode.get(code);

                    if (!button) {
                        return;
                    }

                    button.dataset.expanded = expanded ? '1' : '0';
                    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                    const icon = button.querySelector('i');

                    if (icon) {
                        icon.classList.toggle('fa-chevron-down', expanded);
                        icon.classList.toggle('fa-chevron-right', !expanded);
                    }
                }

                function hideDescendants(code) {
                    const children = childrenByParent.get(code) || [];

                    children.forEach(function (child) {
                        child.hidden = true;
                        setToggleState(child.dataset.code, false);
                        hideDescendants(child.dataset.code);
                    });
                }

                function showDirectChildren(code) {
                    const children = childrenByParent.get(code) || [];

                    children.forEach(function (child) {
                        child.hidden = false;
                        setToggleState(child.dataset.code, false);
                        hideDescendants(child.dataset.code);
                    });
                }

                function collapseAll() {
                    rows.forEach(function (row) {
                        row.hidden = Number(row.dataset.level) > 1;
                    });

                    toggleByCode.forEach(function (button, code) {
                        setToggleState(code, false);
                    });
                }

                if (hierarchyMode) {
                    collapseAll();

                    toggleByCode.forEach(function (button, code) {
                        button.addEventListener('click', function () {
                            const expanded = button.dataset.expanded === '1';

                            if (expanded) {
                                hideDescendants(code);
                                setToggleState(code, false);
                            } else {
                                showDirectChildren(code);
                                setToggleState(code, true);
                            }
                        });
                    });
                }

                document.getElementById('collapseAllKbli')?.addEventListener('click', collapseAll);

                document.getElementById('jumpCategory')?.addEventListener('change', function (event) {
                    const code = event.target.value;

                    if (!code) {
                        return;
                    }

                    const row = document.getElementById('kategori-' + code);

                    if (!row) {
                        return;
                    }

                    row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    row.classList.add('animate-kbliCategoryFocus');

                    window.setTimeout(function () {
                        row.classList.remove('animate-kbliCategoryFocus');
                    }, 1600);
                });

                const deleteModal = document.getElementById('deleteKbliModal');
                const deleteForm = document.getElementById('deleteKbliForm');
                const deleteCode = document.getElementById('deleteKbliCode');
                const deleteTitle = document.getElementById('deleteKbliTitle');
                const deleteWarning = document.getElementById('deleteKbliWarning');
                const confirmDelete = document.getElementById('confirmDeleteKbli');
                const cancelDelete = document.getElementById('cancelDeleteKbli');

                function closeDeleteModal() {
                    deleteModal?.classList.add('hidden');
                    deleteModal?.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                document.addEventListener('click', function (event) {
                    const button = event.target.closest('[data-delete-kbli]');
                    if (button) {
                        const childCount = Number(button.dataset.deleteChildren || 0);

                        if (deleteForm) {
                            deleteForm.action = button.dataset.deleteUrl || '';
                        }

                        if (deleteCode) {
                            deleteCode.textContent = button.dataset.deleteCode || '-';
                        }

                        if (deleteTitle) {
                            deleteTitle.textContent = button.dataset.deleteTitle || '-';
                        }

                        deleteWarning?.classList.toggle('hidden', childCount === 0);

                        if (confirmDelete) {
                            confirmDelete.disabled = childCount > 0;
                        }

                        deleteModal?.classList.remove('hidden');
                        deleteModal?.classList.add('flex');
                        document.body.style.overflow = 'hidden';
                    }
                });

                cancelDelete?.addEventListener('click', closeDeleteModal);
                deleteModal?.addEventListener('click', function (event) {
                    if (event.target === deleteModal) {
                        closeDeleteModal();
                    }
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    if (deleteModal && !deleteModal.classList.contains('hidden')) {
                        closeDeleteModal();
                    }
                });
            });

            function kbliFormHandler() {
                return {
                    parentOptions: @json($parentOptions->values()),
                    
                    // Input states (JSON-encoded to avoid quote syntax errors)
                    struktur: @json($selectedStructure ?? ''),
                    kode_induk: @json($selectedParent ?? ''),
                    kode: @json($selectedCode ?? ''),
                    judul: @json($selectedTitle ?? ''),
                    cakupan: @json($selectedCakupan ?? ''),
                    tidak_cakupan: @json($selectedTidakCakupan ?? ''),
                    catatan: @json($selectedCatatan ?? ''),

                    strukturDropdownOpen: false,
                    parentDropdownOpen: false,
                    parentSearch: '',
                    strukturOptions: ['Kategori', 'Golongan Pokok', 'Golongan', 'Subgolongan', 'Kelompok'],

                    get structureConfig() {
                        const config = {
                            'Kategori': { level: 1, length: 1, parentLevel: null, placeholder: 'Contoh: A', help: 'Kategori menggunakan satu huruf A sampai V.' },
                            'Golongan Pokok': { level: 2, length: 2, parentLevel: 1, placeholder: 'Contoh: 01', help: 'Golongan Pokok menggunakan kode 2 digit.' },
                            'Golongan': { level: 3, length: 3, parentLevel: 2, placeholder: 'Contoh: 011', help: 'Golongan menggunakan kode 3 digit dan mengikuti kode induk.' },
                            'Subgolongan': { level: 4, length: 4, parentLevel: 3, placeholder: 'Contoh: 0111', help: 'Subgolongan menggunakan kode 4 digit dan mengikuti kode induk.' },
                            'Kelompok': { level: 5, length: 5, parentLevel: 4, placeholder: 'Contoh: 01111', help: 'Kelompok menggunakan kode 5 digit dan mengikuti kode induk.' }
                        };
                        return config[this.struktur] || null;
                    },

                    get filteredParents() {
                        const config = this.structureConfig;
                        if (!config || config.parentLevel === null) return [];
                        const currentCode = @json($isEdit ? $editData->kode : null);
                        let list = this.parentOptions.filter(option => Number(option.level) === config.parentLevel && option.kode !== currentCode);
                        if (this.parentSearch) {
                            const q = this.parentSearch.toLowerCase();
                            list = list.filter(o => o.kode.toLowerCase().includes(q) || o.judul.toLowerCase().includes(q));
                        }
                        return list;
                    },

                    get selectedParentLabel() {
                        if (!this.kode_induk) return 'Pilih data induk...';
                        const found = this.parentOptions.find(o => o.kode === this.kode_induk);
                        return found ? `${found.kode} — ${found.judul}` : this.kode_induk;
                    },

                    selectStruktur(val) {
                        this.struktur = val;
                        this.strukturDropdownOpen = false;
                        this.kode_induk = '';
                        this.parentSearch = '';
                        this.onStrukturChange();
                    },

                    selectParent(item) {
                        this.kode_induk = item.kode;
                        this.parentDropdownOpen = false;
                        this.parentSearch = '';
                        const config = this.structureConfig;
                        if (config && config.level >= 3 && this.kode_induk && !this.kode.startsWith(this.kode_induk)) {
                            this.kode = this.kode_induk;
                        }
                        this.updatePreview();
                    },

                    onStrukturChange() {
                        const config = this.structureConfig;
                        if (config) {
                            this.updatePreview();
                        }
                    },

                    onCodeInput() {
                        const config = this.structureConfig;
                        if (config) {
                            if (config.level === 1) {
                                this.kode = this.kode.toUpperCase().replace(/[^A-V]/g, '').slice(0, 1);
                            } else {
                                this.kode = this.kode.replace(/\D/g, '').slice(0, config.length);
                            }
                        }
                        this.updatePreview();
                    },

                    previewChips: [],
                    updatePreview() {
                        const config = this.structureConfig;
                        if (!config) {
                            this.previewChips = [];
                            return;
                        }
                        const chain = [];
                        let parentCode = this.kode_induk;
                        const optionByCode = new Map(this.parentOptions.map(o => [o.kode, o]));
                        while (parentCode && optionByCode.has(parentCode)) {
                            const parent = optionByCode.get(parentCode);
                            chain.unshift(parent);
                            parentCode = parent.kode_induk;
                        }
                        const chips = [];
                        chain.forEach(item => {
                            chips.push({ code: item.kode, struktur: item.struktur });
                        });
                        chips.push({ code: this.kode.trim().toUpperCase() || '—', struktur: this.struktur });
                        this.previewChips = chips;
                    },

                    init() {
                        this.onStrukturChange();
                    },

                    validateSubmit(e) {
                        const config = this.structureConfig;
                        if (config && config.parentLevel !== null && !this.kode_induk) {
                            e.preventDefault();
                            alert('Data induk wajib dipilih.');
                        }
                    }
                };
            }
        </script>
    @endpush
@endsection
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
        $selectedStructure = old('struktur', $isEdit ? $editData->struktur : '');
        $selectedParent = old('kode_induk', $isEdit ? $editData->kode_induk : '');
        $selectedCode = old('kode', $isEdit ? $editData->kode : '');
        $selectedTitle = old('judul', $isEdit ? $editData->judul : '');
        $selectedCakupan = old('cakupan', $isEdit ? $editData->cakupan : '');
        $selectedTidakCakupan = old('tidak_cakupan', $isEdit ? $editData->tidak_cakupan : '');
        $selectedCatatan = old('catatan', $isEdit ? $editData->catatan : '');
        $editQueryParams = array_merge(request()->except(['edit', 'mode']), ['mode' => 'edit']);
        $editBaseUrl = route('admin.data-kbli.index', $editQueryParams) . '&edit=';
        $deleteBaseUrl = route('admin.data-kbli.destroy', 'PLACEHOLDER');
    @endphp



    <div class="min-h-screen bg-[#f7f9fc] p-4 sm:p-6 lg:p-8">
        <section class="rounded-2xl bg-gradient-to-r from-[#145239] via-[#0E8F62] to-[#1E5D41] p-6 shadow-lg sm:p-7 lg:p-8">
            <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="min-w-0">
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-900/25 px-3 py-1.5 text-xs font-bold text-white/90 backdrop-blur-sm">
                        <i class="fa-solid fa-table-cells-large text-[#FFD54F]"></i>
                        Menu Admin
                    </div>

                    <h1 class="m-0 text-2xl font-black tracking-tight text-white md:text-3xl">
                        Manajemen <span class="text-[#FFD54F]">Data KBLI</span>
                    </h1>

                    <p class="mb-0 mt-2 max-w-3xl text-sm font-medium leading-6 text-emerald-50/90">
                        Kelola struktur Kategori, Golongan Pokok, Golongan, Subgolongan, Kelompok, cakupan, dan pengecualian KBLI 2025.
                    </p>
                </div>

                @if ($columnsReady)
                    <a
                        href="{{ $createUrl }}"
                        class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#FFD54F] px-6 text-sm font-black text-emerald-900 shadow-lg shadow-emerald-950/15 transition hover:-translate-y-0.5 hover:bg-yellow-300 sm:w-auto md:flex-shrink-0"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Tambah KBLI
                    </a>
                @endif
            </div>
        </section>

        @if (! $tableExists)
            <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Tabel <strong>data_kbli</strong> belum tersedia di Supabase.</span>
            </div>
        @elseif (! $columnsReady)
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

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
            <form
                action="{{ route('admin.data-kbli.index') }}"
                method="GET"
                class="grid w-full min-w-0 grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12"
            >
                <div class="relative min-w-0 md:col-span-2 xl:col-span-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, judul, cakupan, atau tidak cakupan..."
                        class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                </div>

                <div class="relative min-w-0 xl:col-span-2">
                    <select
                        name="struktur"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">Semua Level</option>
                        @foreach (['Kategori', 'Golongan Pokok', 'Golongan', 'Subgolongan', 'Kelompok'] as $structureOption)
                            <option value="{{ $structureOption }}" @selected(request('struktur') === $structureOption)>
                                {{ $structureOption }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="relative min-w-0 xl:col-span-3">
                    <select
                        name="kategori"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">Semua Kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->kode }}" @selected(request('kategori') === $category->kode)>
                                {{ $category->kode }} — {{ \Illuminate\Support\Str::limit($category->judul, 45) }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="relative min-w-0 xl:col-span-2">
                    <select
                        name="per_page"
                        class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        @foreach ($perPageOptions as $perPageOption)
                            <option value="{{ $perPageOption }}" @selected((int) request('per_page', 22) === $perPageOption)>
                                {{ $perPageOption }} {{ $hierarchyMode ? 'kategori' : 'data' }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                <div class="flex min-w-0 gap-2 md:col-span-1 xl:col-span-2">
                    <button
                        type="submit"
                        class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white transition hover:bg-emerald-700"
                    >
                        <i class="fa-solid fa-filter"></i>
                        Terapkan
                    </button>
                    <a
                        href="{{ route('admin.data-kbli.index') }}"
                        title="Reset filter"
                        class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <header class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="m-0 text-lg font-black text-slate-900">
                        {{ $hierarchyMode ? 'Struktur Hierarki KBLI' : 'Hasil Pencarian KBLI' }}
                    </h2>
                    <p class="mb-0 mt-1 text-sm text-slate-500">
                        @if ($hierarchyMode)
                            Semua kategori ditampilkan terlebih dahulu. Buka kategori untuk melihat data turunannya.
                        @else
                            Data ditampilkan sesuai kata pencarian dan filter yang digunakan.
                        @endif
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                    @if ($hierarchyMode && $dataKbli->isNotEmpty())
                        <div class="relative min-w-[190px]">
                            <select
                                id="jumpCategory"
                                class="h-10 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-xs font-bold text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >
                                <option value="">Lompat ke kategori</option>
                                @foreach ($dataKbli->where('level', 1) as $categoryRow)
                                    <option value="{{ $categoryRow->kode }}">
                                        {{ $categoryRow->kode }} — {{ \Illuminate\Support\Str::limit($categoryRow->judul, 32) }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                        </div>

                        <button
                            type="button"
                            id="collapseAllKbli"
                            class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                        >
                            <i class="fa-solid fa-angles-up"></i>
                            Tutup Semua
                        </button>
                    @endif

                    <a
                        href="{{ $exportUrl }}"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700"
                    >
                        <i class="fa-solid fa-download"></i>
                        Ekspor CSV
                    </a>

                    <div class="inline-flex h-10 w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 text-xs font-black text-emerald-700">
                        <i class="fa-solid fa-database"></i>
                        {{ number_format($totalData, 0, ',', '.') }} Data
                    </div>
                </div>
            </header>

            <div id="adminKbliTableWrapper" class="overflow-x-auto">
                <table id="adminKbliTable" class="w-full border-collapse text-left min-w-[1320px] [&_thead]:!table-header-group [&_tbody]:!table-row-group [&_tr]:!table-row [&_th]:!table-cell [&_td]:!table-cell [&_th]:!whitespace-nowrap [&_tr[hidden]]:!hidden">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                            <th class="w-[230px] px-5 py-3">Struktur</th>
                            <th class="w-[110px] px-4 py-3">Kode</th>
                            <th class="min-w-[320px] px-4 py-3">Judul KBLI</th>
                            <th class="min-w-[260px] px-4 py-3">Cakupan</th>
                            <th class="min-w-[260px] px-4 py-3">Tidak Cakupan</th>
                            <th class="w-[110px] px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataKbli as $item)
                            @php
                                $level = (int) $item->level;
                                $indent = max(0, ($level - 1) * 28);
                                $hasChildren = (int) $item->child_count > 0;
                                $badgeStyle = $badgeStyles[$item->struktur] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                            @endphp
                            <tr
                                data-kbli-row
                                data-code="{{ $item->kode }}"
                                data-parent="{{ $item->kode_induk }}"
                                data-level="{{ $level }}"
                                @if ($level === 1) id="kategori-{{ $item->kode }}" @endif
                                @if ($hierarchyMode && $level > 1) hidden @endif
                                class="transition {{ $level === 1 ? 'bg-slate-50/80 hover:bg-emerald-50/60' : 'hover:bg-slate-50/80' }}"
                            >
                                <td class="relative px-5 py-4">
                                    @for ($treeLevel = 1; $treeLevel < $level; $treeLevel++)
                                        <span class="absolute top-0 bottom-0 w-[1px] bg-[#dbe3ec]" style="left: {{ 22 + (($treeLevel - 1) * 28) }}px"></span>
                                    @endfor
                                    @if ($level > 1)
                                        <span class="absolute top-1/2 h-[1px] bg-[#dbe3ec]" style="left: {{ 22 + (($level - 2) * 28) }}px; width: 20px"></span>
                                    @endif
                                    <div class="relative flex items-center gap-2" style="padding-left: {{ $indent }}px">
                                        @if ($hasChildren && $hierarchyMode)
                                            <button
                                                type="button"
                                                data-tree-toggle="{{ $item->kode }}"
                                                aria-label="Buka atau tutup turunan {{ $item->kode }}"
                                                aria-expanded="{{ $hierarchyMode ? 'false' : 'true' }}"
                                                class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-[10px] text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                                            >
                                                <i class="fa-solid {{ $hierarchyMode ? 'fa-chevron-right' : 'fa-chevron-down' }}"></i>
                                            </button>
                                        @else
                                            <span class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center text-[8px] text-slate-300">
                                                <i class="fa-solid fa-circle"></i>
                                            </span>
                                        @endif
                                        <span class="inline-flex whitespace-nowrap rounded-full border px-2.5 py-1 text-[11px] font-bold {{ $badgeStyle }}">
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
                                    <div class="max-w-[390px] font-semibold leading-relaxed text-slate-700" title="{{ $item->judul }}">
                                        {{ $item->judul }}
                                    </div>
                                    @if ($item->catatan)
                                        <div class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2 py-1 text-[10px] font-semibold text-amber-700" title="{{ $item->catatan }}">
                                            <i class="fa-solid fa-circle-info"></i>
                                            Ada catatan data
                                        </div>
                                    @endif
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="line-clamp-2 max-w-[320px] text-xs leading-relaxed text-slate-500" title="{{ $item->cakupan }}">
                                        {{ $item->cakupan ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4 align-top">
                                    <div class="line-clamp-2 max-w-[320px] text-xs leading-relaxed text-slate-500" title="{{ $item->tidak_cakupan }}">
                                        {{ $item->tidak_cakupan ?: '-' }}
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a
                                            href="{{ $editBaseUrl }}{{ $item->id }}"
                                            title="Edit data"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                                        >
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>
                                        <button
                                            type="button"
                                            title="Hapus data"
                                            data-delete-kbli
                                            data-delete-url="{{ str_replace('PLACEHOLDER', $item->id, $deleteBaseUrl) }}"
                                            data-delete-code="{{ $item->kode }}"
                                            data-delete-title="{{ $item->judul }}"
                                            data-delete-children="{{ (int) $item->child_count }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        >
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

        <footer class="pb-2 pt-8 text-center text-xs text-slate-400">
            Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
        </footer>
    </div>

    @if ($isModalOpen)
        <div id="kbliFormModal" class="fixed inset-0 z-[999] flex items-start justify-center overflow-y-auto bg-slate-950/55 p-3 backdrop-blur-sm sm:p-6">
            <div class="my-auto w-full max-w-6xl overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl">
                <header class="flex items-start justify-between gap-5 border-b border-slate-200 px-5 py-5 sm:px-7">
                    <div>
                        <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                            <i class="fa-solid {{ $isEdit ? 'fa-pen-to-square' : 'fa-plus' }}"></i>
                            {{ $isEdit ? 'Perbarui Data' : 'Data Baru' }}
                        </div>
                        <h2 class="m-0 text-xl font-black text-slate-900 sm:text-2xl">
                            {{ $isEdit ? 'Edit Data KBLI 2025' : 'Tambah Data KBLI 2025' }}
                        </h2>
                        <p class="mb-0 mt-1 text-sm text-slate-500">Isi data sesuai level dan hubungan induk pada struktur KBLI.</p>
                    </div>
                    <a href="{{ $closeModalUrl }}" class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100">
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </header>

                <form action="{{ $formAction }}" method="POST">
                    @csrf
                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <div class="grid grid-cols-1 gap-0 lg:grid-cols-[minmax(0,1fr)_310px]">
                        <div class="p-5 sm:p-7">
                            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <div>
                                    <label for="struktur" class="mb-2 block text-sm font-bold text-slate-700">
                                        Level Struktur <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select
                                            id="struktur"
                                            name="struktur"
                                            required
                                            class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                        >
                                            <option value="">Pilih level struktur</option>
                                            @foreach (['Kategori', 'Golongan Pokok', 'Golongan', 'Subgolongan', 'Kelompok'] as $structureOption)
                                                <option value="{{ $structureOption }}" @selected($selectedStructure === $structureOption)>
                                                    {{ $structureOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                    </div>
                                    <p id="structureHelp" class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Pilih posisi data pada hierarki KBLI.</p>
                                    @error('struktur')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="kode_induk" class="mb-2 block text-sm font-bold text-slate-700">
                                        Induk / Parent <span id="parentRequired" class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select
                                            id="kode_induk"
                                            name="kode_induk"
                                            class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400"
                                        >
                                            <option value="">Pilih level struktur terlebih dahulu</option>
                                        </select>
                                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                    </div>
                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Pilihan induk disesuaikan otomatis dengan level struktur.</p>
                                    @error('kode_induk')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="kode" class="mb-2 block text-sm font-bold text-slate-700">
                                        Kode KBLI <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="kode"
                                        type="text"
                                        name="kode"
                                        value="{{ $selectedCode }}"
                                        placeholder="Pilih level struktur"
                                        required
                                        autocomplete="off"
                                        class="h-12 w-full rounded-xl border border-slate-200 px-4 font-mono text-sm font-bold tracking-wide text-slate-700 outline-none transition placeholder:font-sans placeholder:font-normal placeholder:tracking-normal placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    >
                                    <p id="codeHelp" class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Format kode akan menyesuaikan level.</p>
                                    @error('kode')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="judul" class="mb-2 block text-sm font-bold text-slate-700">
                                        Judul KBLI <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="judul"
                                        type="text"
                                        name="judul"
                                        value="{{ $selectedTitle }}"
                                        placeholder="Masukkan judul kegiatan usaha"
                                        required
                                        class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    >
                                    <p class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Gunakan nama kegiatan yang jelas dan sesuai klasifikasi.</p>
                                    @error('judul')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </span>
                                        <label for="cakupan" class="text-sm font-bold text-slate-700">Cakupan</label>
                                    </div>
                                    <textarea
                                        id="cakupan"
                                        name="cakupan"
                                        rows="7"
                                        placeholder="Jelaskan kegiatan, proses, produk, atau layanan yang termasuk dalam cakupan KBLI ini"
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    >{{ $selectedCakupan }}</textarea>
                                    @error('cakupan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-slate-50/60 p-4">
                                    <div class="mb-3 flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-red-100 text-red-600">
                                            <i class="fa-solid fa-ban"></i>
                                        </span>
                                        <label for="tidak_cakupan" class="text-sm font-bold text-slate-700">Tidak Cakupan</label>
                                    </div>
                                    <textarea
                                        id="tidak_cakupan"
                                        name="tidak_cakupan"
                                        rows="7"
                                        placeholder="Jelaskan kegiatan yang tidak termasuk dalam cakupan KBLI ini"
                                        class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    >{{ $selectedTidakCakupan }}</textarea>
                                    @error('tidak_cakupan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="catatan" class="mb-2 block text-sm font-bold text-slate-700">Catatan Data</label>
                                    <textarea
                                        id="catatan"
                                        name="catatan"
                                        rows="3"
                                        placeholder="Tambahkan catatan koreksi atau keterangan khusus bila diperlukan"
                                        class="w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm leading-relaxed text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                    >{{ $selectedCatatan }}</textarea>
                                    @error('catatan')
                                        <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <aside class="border-t border-slate-200 bg-slate-50/70 p-5 lg:border-l lg:border-t-0 sm:p-6">
                            <section class="overflow-hidden rounded-2xl border border-emerald-200 bg-white">
                                <header class="border-b border-emerald-100 bg-emerald-50 px-4 py-3 text-center">
                                    <h3 class="m-0 text-sm font-black text-emerald-800">Preview Hierarki</h3>
                                </header>
                                <div class="p-4">
                                    <div id="hierarchyPreview" class="flex min-h-12 flex-wrap items-center gap-2"></div>
                                    <p class="mb-0 mt-3 text-xs leading-relaxed text-slate-400">Urutan kode akan berubah mengikuti level dan data induk yang dipilih.</p>
                                </div>
                            </section>

                            <section class="mt-4 rounded-2xl border border-slate-200 bg-white p-4">
                                <h3 class="m-0 flex items-center gap-2 text-sm font-black text-slate-800">
                                    <i class="fa-solid fa-list-check text-emerald-600"></i>
                                    Struktur KBLI 2025
                                </h3>
                                <div class="mt-4 space-y-3">
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

                            <section class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
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

                    <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7">
                        <div class="inline-flex items-start gap-2 rounded-xl bg-emerald-50 px-3 py-2 text-xs font-medium leading-relaxed text-emerald-700">
                            <i class="fa-solid fa-circle-info mt-0.5"></i>
                            Pastikan level, kode induk, dan panjang kode sudah sesuai sebelum menyimpan data.
                        </div>
                        <div class="flex flex-col-reverse gap-2 sm:flex-row">
                            <a href="{{ $closeModalUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                                <i class="fa-solid fa-circle-check"></i>
                                {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data KBLI' }}
                            </button>
                        </div>
                    </footer>
                </form>
            </div>
        </div>
    @endif

    <div id="deleteKbliModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-3xl border border-white/20 bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
                <i class="fa-regular fa-trash-can"></i>
            </div>
            <h3 class="mb-0 mt-5 text-xl font-black text-slate-900">Hapus data KBLI?</h3>
            <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
                Kode <strong id="deleteKbliCode" class="text-slate-700"></strong> — <span id="deleteKbliTitle"></span> akan dihapus.
            </p>
            <div id="deleteKbliWarning" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-semibold leading-relaxed text-amber-800">
                Data ini memiliki turunan dan tidak dapat dihapus sebelum seluruh turunannya dihapus.
            </div>
            <form id="deleteKbliForm" action="" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                @csrf
                @method('DELETE')
                <button type="button" id="cancelDeleteKbli" class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit" id="confirmDeleteKbli" class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-300">
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

                    if (! parent) {
                        return;
                    }

                    if (! childrenByParent.has(parent)) {
                        childrenByParent.set(parent, []);
                    }

                    childrenByParent.get(parent).push(row);
                });

                document.querySelectorAll('[data-tree-toggle]').forEach(function (button) {
                    toggleByCode.set(button.dataset.treeToggle, button);
                });

                function setToggleState(code, expanded) {
                    const button = toggleByCode.get(code);

                    if (! button) {
                        return;
                    }

                    button.dataset.expanded = expanded ? '1' : '0';
                    button.setAttribute('aria-expanded', expanded ? 'true' : 'false');

                    const icon = button.querySelector('i');

                    if (icon) {
                        icon.classList.toggle('fa-chevron-down', expanded);
                        icon.classList.toggle('fa-chevron-right', ! expanded);
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

                    if (! code) {
                        return;
                    }

                    const row = document.getElementById('kategori-' + code);

                    if (! row) {
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

                document.querySelectorAll('[data-delete-kbli]').forEach(function (button) {
                    button.addEventListener('click', function () {
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
                    });
                });

                cancelDelete?.addEventListener('click', closeDeleteModal);
                deleteModal?.addEventListener('click', function (event) {
                    if (event.target === deleteModal) {
                        closeDeleteModal();
                    }
                });

                const formModal = document.getElementById('kbliFormModal');
                const structureSelect = document.getElementById('struktur');
                const parentSelect = document.getElementById('kode_induk');
                const codeInput = document.getElementById('kode');
                const parentRequired = document.getElementById('parentRequired');
                const structureHelp = document.getElementById('structureHelp');
                const codeHelp = document.getElementById('codeHelp');
                const hierarchyPreview = document.getElementById('hierarchyPreview');
                const parentOptions = @json($parentOptions->values());
                const initialParent = @json($selectedParent);
                const currentCode = @json($isEdit ? $editData->kode : null);

                const structureConfig = {
                    'Kategori': { level: 1, length: 1, parentLevel: null, placeholder: 'Contoh: A', help: 'Kategori menggunakan satu huruf A sampai V.' },
                    'Golongan Pokok': { level: 2, length: 2, parentLevel: 1, placeholder: 'Contoh: 01', help: 'Golongan Pokok menggunakan kode 2 digit.' },
                    'Golongan': { level: 3, length: 3, parentLevel: 2, placeholder: 'Contoh: 011', help: 'Golongan menggunakan kode 3 digit dan mengikuti kode induk.' },
                    'Subgolongan': { level: 4, length: 4, parentLevel: 3, placeholder: 'Contoh: 0111', help: 'Subgolongan menggunakan kode 4 digit dan mengikuti kode induk.' },
                    'Kelompok': { level: 5, length: 5, parentLevel: 4, placeholder: 'Contoh: 01111', help: 'Kelompok menggunakan kode 5 digit dan mengikuti kode induk.' }
                };

                const previewClasses = {
                    'Kategori': 'border-emerald-200 bg-emerald-50 text-emerald-700',
                    'Golongan Pokok': 'border-orange-200 bg-orange-50 text-orange-700',
                    'Golongan': 'border-sky-200 bg-sky-50 text-sky-700',
                    'Subgolongan': 'border-red-200 bg-red-50 text-red-700',
                    'Kelompok': 'border-violet-200 bg-violet-50 text-violet-700'
                };

                const optionByCode = new Map(parentOptions.map((option) => [option.kode, option]));

                function setParentOptions(selectedValue) {
                    if (! parentSelect || ! structureSelect) {
                        return;
                    }

                    const config = structureConfig[structureSelect.value];
                    parentSelect.innerHTML = '';

                    if (! config || config.parentLevel === null) {
                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = 'Kategori tidak memiliki induk';
                        parentSelect.appendChild(option);
                        parentSelect.disabled = true;
                        parentRequired?.classList.add('hidden');
                        return;
                    }

                    parentSelect.disabled = false;
                    parentRequired?.classList.remove('hidden');

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Pilih data induk';
                    parentSelect.appendChild(placeholder);

                    parentOptions
                        .filter((option) => Number(option.level) === config.parentLevel && option.kode !== currentCode)
                        .forEach(function (optionData) {
                            const option = document.createElement('option');
                            option.value = optionData.kode;
                            option.textContent = optionData.kode + ' — ' + optionData.judul;
                            option.selected = optionData.kode === selectedValue;
                            parentSelect.appendChild(option);
                        });
                }

                function configureCodeInput() {
                    if (! codeInput || ! structureSelect) {
                        return;
                    }

                    const config = structureConfig[structureSelect.value];

                    if (! config) {
                        codeInput.removeAttribute('maxlength');
                        codeInput.removeAttribute('pattern');
                        codeInput.placeholder = 'Pilih level struktur';
                        codeInput.inputMode = 'text';
                        if (codeHelp) {
                            codeHelp.textContent = 'Format kode akan menyesuaikan level.';
                        }
                        if (structureHelp) {
                            structureHelp.textContent = 'Pilih posisi data pada hierarki KBLI.';
                        }
                        return;
                    }

                    codeInput.maxLength = config.length;
                    codeInput.placeholder = config.placeholder;
                    codeInput.inputMode = config.level === 1 ? 'text' : 'numeric';
                    codeInput.pattern = config.level === 1 ? '[A-Va-v]' : '[0-9]{' + config.length + '}';

                    if (codeHelp) {
                        codeHelp.textContent = config.help;
                    }

                    if (structureHelp) {
                        structureHelp.textContent = 'Level ' + config.level + ' dari 5 pada struktur KBLI.';
                    }
                }

                function createPreviewChip(code, structure) {
                    const chip = document.createElement('span');
                    chip.className = 'inline-flex rounded-lg border px-2.5 py-1 font-mono text-xs font-black ' + (previewClasses[structure] || previewClasses['Kategori']);
                    chip.textContent = code || '—';
                    return chip;
                }

                function createPreviewArrow() {
                    const arrow = document.createElement('i');
                    arrow.className = 'fa-solid fa-chevron-right text-[9px] text-slate-300';
                    return arrow;
                }

                function updatePreview() {
                    if (! hierarchyPreview || ! structureSelect || ! parentSelect || ! codeInput) {
                        return;
                    }

                    hierarchyPreview.innerHTML = '';
                    const structure = structureSelect.value;
                    const config = structureConfig[structure];

                    if (! config) {
                        const empty = document.createElement('span');
                        empty.className = 'text-xs text-slate-400';
                        empty.textContent = 'Pilih level struktur untuk melihat hierarki.';
                        hierarchyPreview.appendChild(empty);
                        return;
                    }

                    const chain = [];
                    let parentCode = parentSelect.value;

                    while (parentCode && optionByCode.has(parentCode)) {
                        const parent = optionByCode.get(parentCode);
                        chain.unshift(parent);
                        parentCode = parent.kode_induk;
                    }

                    chain.forEach(function (item, index) {
                        if (index > 0) {
                            hierarchyPreview.appendChild(createPreviewArrow());
                        }
                        hierarchyPreview.appendChild(createPreviewChip(item.kode, item.struktur));
                    });

                    if (chain.length > 0) {
                        hierarchyPreview.appendChild(createPreviewArrow());
                    }

                    hierarchyPreview.appendChild(createPreviewChip(codeInput.value.trim().toUpperCase() || '—', structure));
                }

                structureSelect?.addEventListener('change', function () {
                    setParentOptions('');
                    configureCodeInput();
                    updatePreview();
                });

                parentSelect?.addEventListener('change', function () {
                    const config = structureConfig[structureSelect?.value];
                    const parentCode = parentSelect.value;

                    if (config && config.level >= 3 && codeInput && parentCode && ! codeInput.value.startsWith(parentCode)) {
                        codeInput.value = parentCode;
                        codeInput.focus();
                        codeInput.setSelectionRange(codeInput.value.length, codeInput.value.length);
                    }

                    updatePreview();
                });

                codeInput?.addEventListener('input', function () {
                    const config = structureConfig[structureSelect?.value];

                    if (config?.level === 1) {
                        codeInput.value = codeInput.value.toUpperCase().replace(/[^A-V]/g, '').slice(0, 1);
                    } else if (config) {
                        codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, config.length);
                    }

                    updatePreview();
                });

                if (structureSelect) {
                    setParentOptions(initialParent);
                    configureCodeInput();
                    updatePreview();
                }

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    if (deleteModal && ! deleteModal.classList.contains('hidden')) {
                        closeDeleteModal();
                        return;
                    }

                    if (formModal) {
                        window.location.href = @json($closeModalUrl);
                    }
                });
            });
        </script>
    @endpush
@endsection
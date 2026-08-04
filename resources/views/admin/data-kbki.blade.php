@extends('layouts.admin')

@section('title', 'Data KBKI 2015')

@section('content')
@php
$isModalOpen = in_array($mode, ['create', 'edit'], true);
$isEdit = $mode === 'edit' && $editData;
$formAction = $isEdit
? route('admin.data-kbki.update', $editData->id)
: route('admin.data-kbki.store');
$closeModalUrl = route(
'admin.data-kbki.index',
request()->except(['mode', 'edit'])
);
$createUrl = route(
'admin.data-kbki.index',
array_merge(
request()->except(['page', 'edit', 'export']),
['mode' => 'create']
)
);
$exportUrl = route(
'admin.data-kbki.index',
array_merge(
request()->except(['page', 'mode', 'edit', 'export']),
['export' => 1]
)
);
$structures = [
'Seksi',
'Divisi',
'Kelompok',
'Kelas',
'Subkelas',
'Kelompok Komoditas',
'Komoditas',
];
$badgeStyles = [
'Seksi' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
'Divisi' => 'border-orange-200 bg-orange-50 text-orange-700',
'Kelompok' => 'border-sky-200 bg-sky-50 text-sky-700',
'Kelas' => 'border-red-200 bg-red-50 text-red-700',
'Subkelas' => 'border-violet-200 bg-violet-50 text-violet-700',
'Kelompok Komoditas' => 'border-pink-200 bg-pink-50 text-pink-700',
'Komoditas' => 'border-cyan-200 bg-cyan-50 text-cyan-700',
];
$statStyles = [
'green' => ['icon' => 'bg-[#079A6A] text-white', 'corner' => 'bg-emerald-50'],
'orange' => ['icon' => 'bg-[#FF9D00] text-white', 'corner' => 'bg-amber-50'],
'blue' => ['icon' => 'bg-[#0794CE] text-white', 'corner' => 'bg-sky-50'],
'red' => ['icon' => 'bg-[#F94242] text-white', 'corner' => 'bg-red-50'],
'violet' => ['icon' => 'bg-violet-600 text-white', 'corner' => 'bg-violet-50'],
'pink' => ['icon' => 'bg-pink-600 text-white', 'corner' => 'bg-pink-50'],
'cyan' => ['icon' => 'bg-cyan-600 text-white', 'corner' => 'bg-cyan-50'],
'teal' => ['icon' => 'bg-teal-700 text-white', 'corner' => 'bg-teal-50'],
];
$selectedStructure = old('struktur', $isEdit ? $editData->struktur : '');
$selectedParent = old('kode_induk', $isEdit ? $editData->kode_induk : '');
$selectedCode = old('kode', $isEdit ? $editData->kode : '');
$selectedTitle = old('judul', $isEdit ? $editData->judul : '');
$selectedPage = old('halaman', $isEdit ? $editData->halaman : '');
$selectedSource = old('sumber_sheet', $isEdit ? $editData->sumber_sheet : 'Input Web');
$selectedNote = old('catatan', $isEdit ? $editData->catatan : '');
$selectedStatus = old('status', $isEdit ? $editData->status : 'Aktif');
$editQueryParams = array_merge(request()->except(['edit', 'mode']), ['mode' => 'edit']);
$editBaseUrl = route('admin.data-kbki.index', $editQueryParams) . '&edit=';
$deleteBaseUrl = route('admin.data-kbki.destroy', 'PLACEHOLDER');
@endphp



<div class="min-h-screen bg-[#f7f9fc] p-4 sm:p-6 lg:p-8">
    <section class="rounded-2xl bg-gradient-to-r from-[#145239] via-[#0E8F62] to-[#1E5D41] p-6 shadow-lg sm:p-7 lg:p-8">
        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-300/20 bg-emerald-900/25 px-3 py-1.5 text-xs font-bold text-white/90 backdrop-blur-sm">
                    <i class="fa-solid fa-boxes-stacked text-[#FFD54F]"></i>
                    Menu Admin
                </div>

                <h1 class="m-0 text-2xl font-black tracking-tight text-white md:text-3xl">
                    Manajemen <span class="text-[#FFD54F]">Data KBKI</span>
                </h1>

                <p class="mb-0 mt-2 max-w-3xl text-sm font-medium leading-6 text-emerald-50/90">
                    Kelola struktur Seksi, Divisi, Kelompok, Kelas, Subkelas, Kelompok Komoditas, dan Komoditas KBKI 2015.
                </p>
            </div>

            @if ($columnsReady)
            <a
                href="{{ $createUrl }}"
                class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#FFD54F] px-6 text-sm font-black text-emerald-900 shadow-lg shadow-emerald-950/15 transition hover:-translate-y-0.5 hover:bg-yellow-300 sm:w-auto md:flex-shrink-0">
                <i class="fa-solid fa-plus"></i>
                Tambah KBKI
            </a>
            @endif
        </div>
    </section>

    @if (! $tableExists)
    <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <span>Tabel <strong>data_kbki</strong> belum tersedia di Supabase.</span>
    </div>
    @elseif (! $columnsReady)
    <div class="mt-6 flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <span>Struktur kolom tabel <strong>data_kbki</strong> belum sesuai dengan dataset KBKI 2015.</span>
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

    <section class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $stat)
        @php
        $statStyle = $statStyles[$stat['tone']] ?? $statStyles['green'];
        @endphp
        <article class="group relative min-h-[112px] overflow-hidden rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:shadow-md sm:min-h-[118px] sm:p-5">
            <div class="absolute -right-8 -top-8 h-24 w-24 rounded-bl-full transition-transform duration-500 group-hover:scale-125 {{ $statStyle['corner'] }}"></div>
            <div class="relative z-10 flex h-full items-center justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <p class="m-0 text-sm font-bold leading-5 text-slate-500">{{ $stat['label'] }}</p>
                    <p class="mb-0 mt-2 text-3xl font-black tracking-tight text-slate-900">
                        {{ number_format($stat['value'], 0, ',', '.') }}
                    </p>
                    <p class="mb-0 mt-1 text-xs font-medium leading-5 text-slate-400">{{ $stat['description'] }}</p>
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
            action="{{ route('admin.data-kbki.index') }}"
            method="GET"
            class="grid w-full min-w-0 grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-12">
            <div class="relative min-w-0 md:col-span-2 xl:col-span-3">
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari kode, judul, halaman, atau sumber..."
                    class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-11 pr-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
            </div>

            <div class="relative min-w-0 xl:col-span-2">
                <select
                    name="struktur"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Level</option>
                    @foreach ($structures as $structureOption)
                    <option value="{{ $structureOption }}" @selected(request('struktur')===$structureOption)>
                        {{ $structureOption }}
                    </option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 xl:col-span-2">
                <select
                    name="seksi"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Seksi</option>
                    @foreach ($sections as $section)
                    <option value="{{ $section->kode }}" @selected((string) request('seksi')===(string) $section->kode)>
                        {{ $section->kode }} — {{ \Illuminate\Support\Str::limit($section->judul, 38) }}
                    </option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 xl:col-span-2">
                <select
                    name="status"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(request('status')==='Aktif' )>Aktif</option>
                    <option value="Nonaktif" @selected(request('status')==='Nonaktif' )>Nonaktif</option>
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 xl:col-span-1">
                <select
                    name="per_page"
                    class="h-11 w-full appearance-none rounded-xl border border-slate-200 bg-white px-3 pr-8 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    @foreach ($perPageOptions as $perPageOption)
                    <option value="{{ $perPageOption }}" @selected((int) request('per_page', 10)===$perPageOption)>
                        {{ $perPageOption }}
                    </option>
                    @endforeach
                </select>
                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="flex min-w-0 gap-2 md:col-span-1 xl:col-span-2">
                <button
                    type="submit"
                    class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-emerald-600 px-4 text-sm font-bold text-white transition hover:bg-emerald-700">
                    <i class="fa-solid fa-filter"></i>
                    Terapkan
                </button>
                <a
                    href="{{ route('admin.data-kbki.index') }}"
                    title="Reset filter"
                    class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </section>

    <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <header class="flex flex-col gap-4 border-b border-slate-200 px-5 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="m-0 text-lg font-black text-slate-900">
                    {{ $hierarchyMode ? 'Struktur Hierarki KBKI' : 'Hasil Pencarian KBKI' }}
                </h2>
                <p class="mb-0 mt-1 text-sm text-slate-500">
                    @if ($hierarchyMode)
                    Seluruh Seksi ditampilkan lebih dahulu. Buka Seksi untuk melihat Divisi dan struktur turunannya.
                    @else
                    Data ditampilkan sesuai kata pencarian dan filter yang digunakan.
                    @endif
                </p>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                @if ($hierarchyMode && $dataKbki->isNotEmpty())
                <div class="relative min-w-[190px]">
                    <select
                        id="jumpSection"
                        class="h-10 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-xs font-bold text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Lompat ke Seksi</option>
                        @foreach ($dataKbki->where('level', 1) as $sectionRow)
                        <option value="{{ $sectionRow->kode }}">
                            {{ $sectionRow->kode }} — {{ \Illuminate\Support\Str::limit($sectionRow->judul, 32) }}
                        </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                </div>

                <button
                    type="button"
                    id="collapseAllKbki"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="fa-solid fa-angles-up"></i>
                    Tutup Semua
                </button>
                @endif

                <a
                    href="{{ $exportUrl }}"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-600 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                    <i class="fa-solid fa-download"></i>
                    Ekspor CSV
                </a>

                <div class="inline-flex h-10 w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-4 text-xs font-black text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($totalData, 0, ',', '.') }} Data
                </div>
            </div>
        </header>

        <div id="adminKbkiTableWrapper" class="overflow-x-auto">
            <table id="adminKbkiTable" class="w-full border-collapse text-left min-w-[1260px] [&_thead]:!table-header-group [&_tbody]:!table-row-group [&_tr]:!table-row [&_th]:!table-cell [&_td]:!table-cell [&_th]:!whitespace-nowrap [&_tr[hidden]]:!hidden">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-[11px] font-bold uppercase tracking-[0.08em] text-slate-500">
                        <th class="w-[250px] px-5 py-3">Struktur</th>
                        <th class="w-[150px] px-4 py-3">Kode</th>
                        <th class="min-w-[420px] px-4 py-3">Judul KBKI</th>
                        <th class="w-[95px] px-4 py-3 text-center">Halaman</th>
                        <th class="w-[130px] px-4 py-3">Status</th>
                        <th class="min-w-[145px] px-4 py-3">Sumber</th>
                        <th class="w-[110px] px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($dataKbki as $item)
                    @php
                    $level = (int) $item->level;
                    $indent = max(0, ($level - 1) * 28);
                    $hasChildren = (int) $item->child_count > 0;
                    $badgeStyle = $badgeStyles[$item->struktur] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                    $isActive = ($item->status ?? 'Aktif') === 'Aktif';
                    @endphp
                    <tr
                        data-kbki-row
                        data-code="{{ $item->kode }}"
                        data-parent="{{ $item->kode_induk }}"
                        data-level="{{ $level }}"
                        @if ($level===1) id="seksi-{{ $item->kode }}" @endif
                        @if ($hierarchyMode && $level> 1) hidden @endif
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
                                        aria-expanded="false"
                                        class="inline-flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-[10px] text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                        <i class="fa-solid fa-chevron-right"></i>
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
                            <div class="line-clamp-2 font-bold leading-6 text-slate-700" title="{{ $item->judul }}">
                                {{ $item->judul }}
                            </div>
                            @if ($item->catatan)
                            <div class="mt-1 text-xs leading-5 text-slate-400" title="{{ $item->catatan }}">
                                {{ \Illuminate\Support\Str::limit($item->catatan, 95) }}
                            </div>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-center text-sm font-semibold text-slate-500">
                            {{ $item->halaman ?: '-' }}
                        </td>
                        <td class="px-4 py-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold {{ $isActive ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-red-200 bg-red-50 text-red-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $isActive ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                {{ $item->status ?? 'Aktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-xs font-medium text-slate-500">
                            {{ $item->sumber_sheet ?: '-' }}
                        </td>
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a
                                    href="{{ $editBaseUrl }}{{ $item->id }}"
                                    title="Edit KBKI"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>
                                <button
                                    type="button"
                                    title="Hapus KBKI"
                                    data-delete-kbki
                                    data-delete-url="{{ str_replace('PLACEHOLDER', $item->id, $deleteBaseUrl) }}"
                                    data-delete-code="{{ $item->kode }}"
                                    data-delete-title="{{ $item->judul }}"
                                    data-delete-children="{{ (int) $item->child_count }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-16 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">
                                    <i class="fa-solid fa-boxes-stacked"></i>
                                </div>
                                <h3 class="mb-0 mt-4 text-sm font-bold text-slate-700">Data KBKI tidak ditemukan</h3>
                                <p class="mb-0 mt-1 text-xs leading-relaxed text-slate-400">Coba ubah filter atau kata pencarian yang digunakan.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($paginator->total() > 0)
        <footer class="flex flex-col gap-4 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="m-0 text-xs text-slate-500">
                Menampilkan <strong class="text-slate-700">{{ $paginator->firstItem() }}</strong>–<strong class="text-slate-700">{{ $paginator->lastItem() }}</strong> dari <strong class="text-slate-700">{{ number_format($paginator->total(), 0, ',', '.') }}</strong> {{ $hierarchyMode ? 'Seksi' : 'data' }}
            </p>

            @if ($paginator->hasPages())
            <div class="flex flex-wrap items-center gap-2">
                @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-slate-400">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </span>
                @else
                <a href="{{ $paginator->previousPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
                    <i class="fa-solid fa-chevron-left text-xs"></i>
                </a>
                @endif

                @php
                $currentPage = $paginator->currentPage();
                $lastPage = $paginator->lastPage();
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
                    <a href="{{ $paginator->url($page) }}" class="inline-flex h-9 min-w-9 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-600 transition hover:bg-slate-50">
                        {{ $page }}
                    </a>
                    @endif

                    @php
                    $previousPageNumber = $page;
                    @endphp
                    @endforeach

                    @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50">
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
    </section>

    <footer class="pb-2 pt-8 text-center text-xs text-slate-400">
        Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
    </footer>
</div>

@if ($isModalOpen)
<div id="kbkiFormModal" class="fixed inset-0 z-[999] flex items-start justify-center overflow-y-auto bg-slate-950/55 p-3 backdrop-blur-sm sm:p-6">
    <div class="my-auto w-full max-w-6xl overflow-hidden rounded-3xl border border-white/20 bg-white shadow-2xl">
        <header class="flex items-start justify-between gap-5 border-b border-slate-200 px-5 py-5 sm:px-7">
            <div>
                <div class="mb-2 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-[0.12em] text-emerald-700">
                    <i class="fa-solid {{ $isEdit ? 'fa-pen-to-square' : 'fa-plus' }}"></i>
                    {{ $isEdit ? 'Perbarui Data' : 'Data Baru' }}
                </div>
                <h2 class="m-0 text-xl font-black text-slate-900 sm:text-2xl">
                    {{ $isEdit ? 'Edit Data KBKI 2015' : 'Tambah Data KBKI 2015' }}
                </h2>
                <p class="mb-0 mt-1 text-sm text-slate-500">Isi data sesuai level dan hubungan induk pada struktur KBKI.</p>
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
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="">Pilih level struktur</option>
                                    @foreach ($structures as $structureOption)
                                    <option value="{{ $structureOption }}" @selected($selectedStructure===$structureOption)>
                                        {{ $structureOption }}
                                    </option>
                                    @endforeach
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            <p id="structureHelp" class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Pilih posisi data pada hierarki KBKI.</p>
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
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400">
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
                                Kode KBKI <span class="text-red-500">*</span>
                            </label>
                            <input
                                id="kode"
                                type="text"
                                name="kode"
                                value="{{ $selectedCode }}"
                                required
                                autocomplete="off"
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 font-mono text-sm font-bold text-slate-700 outline-none transition placeholder:font-sans placeholder:font-normal placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <p id="codeHelp" class="mb-0 mt-2 text-xs leading-relaxed text-slate-400">Format kode menyesuaikan level struktur.</p>
                            @error('kode')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="status" class="mb-2 block text-sm font-bold text-slate-700">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                    <option value="Aktif" @selected($selectedStatus==='Aktif' )>Aktif</option>
                                    <option value="Nonaktif" @selected($selectedStatus==='Nonaktif' )>Nonaktif</option>
                                </select>
                                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                            </div>
                            @error('status')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="judul" class="mb-2 block text-sm font-bold text-slate-700">
                                Judul KBKI <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                id="judul"
                                name="judul"
                                rows="3"
                                required
                                placeholder="Masukkan judul KBKI"
                                class="w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">{{ $selectedTitle }}</textarea>
                            @error('judul')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="halaman" class="mb-2 block text-sm font-bold text-slate-700">Halaman</label>
                            <input
                                id="halaman"
                                type="number"
                                min="1"
                                name="halaman"
                                value="{{ $selectedPage }}"
                                placeholder="Contoh: 125"
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            @error('halaman')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="sumber_sheet" class="mb-2 block text-sm font-bold text-slate-700">Sumber Data</label>
                            <input
                                id="sumber_sheet"
                                type="text"
                                name="sumber_sheet"
                                value="{{ $selectedSource }}"
                                placeholder="Contoh: Input Web"
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            @error('sumber_sheet')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="catatan" class="mb-2 block text-sm font-bold text-slate-700">Catatan</label>
                            <textarea
                                id="catatan"
                                name="catatan"
                                rows="4"
                                placeholder="Tambahkan catatan bila diperlukan"
                                class="w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm leading-6 text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">{{ $selectedNote }}</textarea>
                            @error('catatan')
                            <p class="mb-0 mt-2 text-xs font-semibold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <aside class="border-t border-slate-200 bg-slate-50/70 p-5 lg:border-l lg:border-t-0 sm:p-6">
                    <div class="rounded-2xl border border-emerald-200 bg-white p-4">
                        <div class="flex items-center gap-2 text-sm font-black text-emerald-700">
                            <i class="fa-solid fa-diagram-project"></i>
                            Preview Hierarki
                        </div>
                        <div id="hierarchyPreview" class="mt-4 flex flex-wrap items-center gap-2">
                            <span class="text-xs text-slate-400">Pilih level struktur untuk melihat hierarki.</span>
                        </div>
                    </div>

                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">
                        <div class="flex items-center gap-2 text-sm font-black text-amber-800">
                            <i class="fa-solid fa-lightbulb"></i>
                            Panduan Struktur
                        </div>
                        <ul class="mb-0 mt-3 space-y-2 pl-4 text-xs leading-5 text-amber-900/80">
                            <li>Seksi menggunakan 1 digit.</li>
                            <li>Divisi menggunakan 2 digit.</li>
                            <li>Kelompok menggunakan 3 digit.</li>
                            <li>Kelas menggunakan 4 digit.</li>
                            <li>Subkelas menggunakan 5 digit.</li>
                            <li>Kelompok Komoditas menggunakan 7 digit.</li>
                            <li>Komoditas menggunakan 10 digit.</li>
                        </ul>
                    </div>
                </aside>
            </div>

            <footer class="flex flex-col-reverse gap-3 border-t border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-end sm:px-7">
                <a href="{{ $closeModalUrl }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </a>
                <button type="submit" class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-700">
                    <i class="fa-solid fa-floppy-disk"></i>
                    {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Data KBKI' }}
                </button>
            </footer>
        </form>
    </div>
</div>
@endif

<div id="deleteKbkiModal" class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-950/55 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
            <i class="fa-regular fa-trash-can"></i>
        </div>
        <h3 class="mb-0 mt-5 text-xl font-black text-slate-800">Hapus data KBKI?</h3>
        <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
            Kode <strong id="deleteKbkiCode" class="text-slate-700"></strong> —
            <span id="deleteKbkiTitle"></span> akan dihapus.
        </p>
        <div id="deleteKbkiWarning" class="mt-4 hidden rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-left text-xs font-semibold leading-5 text-amber-800">
            Data ini masih memiliki turunan sehingga tidak dapat dihapus sebelum seluruh turunannya dihapus.
        </div>
        <form id="deleteKbkiForm" action="" method="POST" class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            @csrf
            @method('DELETE')
            <button type="button" id="cancelDeleteKbki" class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-bold text-slate-600 transition hover:bg-slate-50">
                Batal
            </button>
            <button type="submit" id="confirmDeleteKbki" class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-300">
                <i class="fa-regular fa-trash-can"></i>
                Hapus
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const hierarchyMode = @json($hierarchyMode);
        const rows = Array.from(document.querySelectorAll('[data-kbki-row]'));
        const childrenByParent = new Map();
        const toggleByCode = new Map();

        rows.forEach(function(row) {
            const parent = row.dataset.parent;

            if (parent === null || parent === undefined || parent === '') {
                return;
            }

            if (!childrenByParent.has(parent)) {
                childrenByParent.set(parent, []);
            }

            childrenByParent.get(parent).push(row);
        });

        document.querySelectorAll('[data-tree-toggle]').forEach(function(button) {
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

            children.forEach(function(child) {
                child.hidden = true;
                setToggleState(child.dataset.code, false);
                hideDescendants(child.dataset.code);
            });
        }

        function showDirectChildren(code) {
            const children = childrenByParent.get(code) || [];

            children.forEach(function(child) {
                child.hidden = false;
                setToggleState(child.dataset.code, false);
                hideDescendants(child.dataset.code);
            });
        }

        function collapseAll() {
            rows.forEach(function(row) {
                row.hidden = Number(row.dataset.level) > 1;
            });

            toggleByCode.forEach(function(button, code) {
                setToggleState(code, false);
            });
        }

        if (hierarchyMode) {
            collapseAll();

            toggleByCode.forEach(function(button, code) {
                button.addEventListener('click', function() {
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

        document.getElementById('collapseAllKbki')?.addEventListener('click', collapseAll);

        document.getElementById('jumpSection')?.addEventListener('change', function(event) {
            const code = event.target.value;

            if (!code) {
                return;
            }

            const row = document.getElementById('seksi-' + code);

            if (!row) {
                return;
            }

            row.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });
            row.classList.add('animate-kbkiSectionFocus');

            window.setTimeout(function() {
                row.classList.remove('animate-kbkiSectionFocus');
            }, 1600);
        });

        const deleteModal = document.getElementById('deleteKbkiModal');
        const deleteForm = document.getElementById('deleteKbkiForm');
        const deleteCode = document.getElementById('deleteKbkiCode');
        const deleteTitle = document.getElementById('deleteKbkiTitle');
        const deleteWarning = document.getElementById('deleteKbkiWarning');
        const confirmDelete = document.getElementById('confirmDeleteKbki');
        const cancelDelete = document.getElementById('cancelDeleteKbki');

        function closeDeleteModal() {
            deleteModal?.classList.add('hidden');
            deleteModal?.classList.remove('flex');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-delete-kbki]').forEach(function(button) {
            button.addEventListener('click', function() {
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
        deleteModal?.addEventListener('click', function(event) {
            if (event.target === deleteModal) {
                closeDeleteModal();
            }
        });

        const formModal = document.getElementById('kbkiFormModal');
        const structureSelect = document.getElementById('struktur');
        const parentSelect = document.getElementById('kode_induk');
        const codeInput = document.getElementById('kode');
        const parentRequired = document.getElementById('parentRequired');
        const structureHelp = document.getElementById('structureHelp');
        const codeHelp = document.getElementById('codeHelp');
        const hierarchyPreview = document.getElementById('hierarchyPreview');
        const parentOptionsUrl = @json(route('admin.data-kbki.parent-options'));
        const initialParent = @json($selectedParent);
        const currentCode = @json($isEdit ? $editData->kode : null);
        let parentOptions = [];

        const structureConfig = {
            'Seksi': {
                level: 1,
                length: 1,
                parentLevel: null,
                placeholder: 'Contoh: 0',
                help: 'Seksi menggunakan kode 1 digit.'
            },
            'Divisi': {
                level: 2,
                length: 2,
                parentLevel: 1,
                placeholder: 'Contoh: 01',
                help: 'Divisi menggunakan kode 2 digit.'
            },
            'Kelompok': {
                level: 3,
                length: 3,
                parentLevel: 2,
                placeholder: 'Contoh: 011',
                help: 'Kelompok menggunakan kode 3 digit.'
            },
            'Kelas': {
                level: 4,
                length: 4,
                parentLevel: 3,
                placeholder: 'Contoh: 0111',
                help: 'Kelas menggunakan kode 4 digit.'
            },
            'Subkelas': {
                level: 5,
                length: 5,
                parentLevel: 4,
                placeholder: 'Contoh: 01111',
                help: 'Subkelas menggunakan kode 5 digit.'
            },
            'Kelompok Komoditas': {
                level: 6,
                length: 7,
                parentLevel: 5,
                placeholder: 'Contoh: 0111100',
                help: 'Kelompok Komoditas menggunakan kode 7 digit.'
            },
            'Komoditas': {
                level: 7,
                length: 10,
                parentLevel: 6,
                placeholder: 'Contoh: 0111100001',
                help: 'Komoditas menggunakan kode 10 digit.'
            }
        };

        const previewClasses = {
            'Seksi': 'border-emerald-200 bg-emerald-50 text-emerald-700',
            'Divisi': 'border-orange-200 bg-orange-50 text-orange-700',
            'Kelompok': 'border-sky-200 bg-sky-50 text-sky-700',
            'Kelas': 'border-red-200 bg-red-50 text-red-700',
            'Subkelas': 'border-violet-200 bg-violet-50 text-violet-700',
            'Kelompok Komoditas': 'border-pink-200 bg-pink-50 text-pink-700',
            'Komoditas': 'border-cyan-200 bg-cyan-50 text-cyan-700'
        };

        async function setParentOptions(selectedValue) {
            if (!parentSelect || !structureSelect) {
                return;
            }

            const config = structureConfig[structureSelect.value];
            parentSelect.innerHTML = '';
            parentOptions = [];

            if (!config || config.parentLevel === null) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Seksi tidak memiliki induk';
                parentSelect.appendChild(option);
                parentSelect.disabled = true;
                parentRequired?.classList.add('hidden');
                updatePreview();
                return;
            }

            parentSelect.disabled = true;
            parentRequired?.classList.remove('hidden');

            const loadingOption = document.createElement('option');
            loadingOption.value = '';
            loadingOption.textContent = 'Memuat data induk...';
            parentSelect.appendChild(loadingOption);

            const query = new URLSearchParams({
                level: String(config.parentLevel),
            });

            if (currentCode) {
                query.set('exclude', currentCode);
            }

            try {
                const response = await fetch(parentOptionsUrl + '?' + query.toString(), {
                    headers: {
                        Accept: 'application/json',
                    },
                });

                if (!response.ok) {
                    throw new Error('Gagal memuat data induk.');
                }

                const result = await response.json();
                parentOptions = Array.isArray(result.data) ? result.data : [];
                parentSelect.innerHTML = '';

                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = parentOptions.length > 0 ?
                    'Pilih data induk' :
                    'Data induk tidak tersedia';
                parentSelect.appendChild(placeholder);

                parentOptions.forEach(function(optionData) {
                    const option = document.createElement('option');
                    option.value = optionData.kode;
                    option.textContent = optionData.kode + ' — ' + optionData.judul;
                    option.selected = optionData.kode === selectedValue;
                    parentSelect.appendChild(option);
                });

                parentSelect.disabled = parentOptions.length === 0;
            } catch (error) {
                parentSelect.innerHTML = '';

                const errorOption = document.createElement('option');
                errorOption.value = '';
                errorOption.textContent = 'Data induk gagal dimuat';
                parentSelect.appendChild(errorOption);
                parentSelect.disabled = true;
            }

            updatePreview();
        }

        function configureCodeInput() {
            if (!codeInput || !structureSelect) {
                return;
            }

            const config = structureConfig[structureSelect.value];

            if (!config) {
                codeInput.removeAttribute('maxlength');
                codeInput.removeAttribute('pattern');
                codeInput.placeholder = 'Pilih level struktur';
                codeInput.inputMode = 'numeric';

                if (codeHelp) {
                    codeHelp.textContent = 'Format kode akan menyesuaikan level.';
                }

                if (structureHelp) {
                    structureHelp.textContent = 'Pilih posisi data pada hierarki KBKI.';
                }

                return;
            }

            codeInput.maxLength = config.length;
            codeInput.placeholder = config.placeholder;
            codeInput.inputMode = 'numeric';
            codeInput.pattern = '[0-9]{' + config.length + '}';

            if (codeHelp) {
                codeHelp.textContent = config.help;
            }

            if (structureHelp) {
                structureHelp.textContent = 'Level ' + config.level + ' dari 7 pada struktur KBKI.';
            }
        }

        function createPreviewChip(code, structure) {
            const chip = document.createElement('span');
            chip.className = 'inline-flex rounded-lg border px-2.5 py-1 font-mono text-xs font-black ' + (previewClasses[structure] || previewClasses['Seksi']);
            chip.textContent = code || '—';
            return chip;
        }

        function createPreviewArrow() {
            const arrow = document.createElement('i');
            arrow.className = 'fa-solid fa-chevron-right text-[9px] text-slate-300';
            return arrow;
        }

        function buildHierarchyChain(code) {
            if (!code) {
                return [];
            }

            const hierarchy = [{
                    length: 1,
                    structure: 'Seksi'
                },
                {
                    length: 2,
                    structure: 'Divisi'
                },
                {
                    length: 3,
                    structure: 'Kelompok'
                },
                {
                    length: 4,
                    structure: 'Kelas'
                },
                {
                    length: 5,
                    structure: 'Subkelas'
                },
                {
                    length: 7,
                    structure: 'Kelompok Komoditas'
                },
                {
                    length: 10,
                    structure: 'Komoditas'
                },
            ];

            return hierarchy
                .filter((item) => item.length <= code.length)
                .map((item) => ({
                    kode: code.slice(0, item.length),
                    struktur: item.structure,
                }));
        }

        function updatePreview() {
            if (!hierarchyPreview || !structureSelect || !parentSelect || !codeInput) {
                return;
            }

            hierarchyPreview.innerHTML = '';
            const structure = structureSelect.value;
            const config = structureConfig[structure];

            if (!config) {
                const empty = document.createElement('span');
                empty.className = 'text-xs text-slate-400';
                empty.textContent = 'Pilih level struktur untuk melihat hierarki.';
                hierarchyPreview.appendChild(empty);
                return;
            }

            const chain = buildHierarchyChain(parentSelect.value);

            chain.forEach(function(item, index) {
                if (index > 0) {
                    hierarchyPreview.appendChild(createPreviewArrow());
                }

                hierarchyPreview.appendChild(
                    createPreviewChip(item.kode, item.struktur)
                );
            });

            if (chain.length > 0) {
                hierarchyPreview.appendChild(createPreviewArrow());
            }

            hierarchyPreview.appendChild(
                createPreviewChip(
                    codeInput.value.trim() || '—',
                    structure
                )
            );
        }

        structureSelect?.addEventListener('change', async function() {
            configureCodeInput();
            await setParentOptions('');
            updatePreview();
        });

        parentSelect?.addEventListener('change', function() {
            const config = structureConfig[structureSelect?.value];
            const parentCode = parentSelect.value;

            if (config && config.level >= 2 && codeInput && parentCode && !codeInput.value.startsWith(parentCode)) {
                codeInput.value = parentCode;
                codeInput.focus();
                codeInput.setSelectionRange(codeInput.value.length, codeInput.value.length);
            }

            updatePreview();
        });

        codeInput?.addEventListener('input', function() {
            const config = structureConfig[structureSelect?.value];

            if (config) {
                codeInput.value = codeInput.value.replace(/\D/g, '').slice(0, config.length);
            }

            updatePreview();
        });

        if (structureSelect) {
            configureCodeInput();
            setParentOptions(initialParent).then(updatePreview);
        }

        document.addEventListener('keydown', function(event) {
            if (event.key !== 'Escape') {
                return;
            }

            if (deleteModal && !deleteModal.classList.contains('hidden')) {
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
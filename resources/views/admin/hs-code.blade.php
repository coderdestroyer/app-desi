@extends('layouts.admin')

@section('title', 'Data HS Code')

@section('content')
    @php
        $isModalOpen = in_array(
            $mode,
            ['create', 'edit'],
            true
        );

        $isEdit = $mode === 'edit' && $editData;

        $formAction = $isEdit
            ? route(
                'admin.hs-code.update',
                $editData->id
            )
            : route('admin.hs-code.store');

        $closeModalUrl = route(
            'admin.hs-code.index',
            request()->except(['mode', 'edit'])
        );

        $statStyles = [
            'green' => [
                'icon' => 'bg-emerald-600',
                'corner' => 'bg-emerald-50',
            ],
            'yellow' => [
                'icon' => 'bg-amber-500',
                'corner' => 'bg-amber-50',
            ],
            'blue' => [
                'icon' => 'bg-sky-600',
                'corner' => 'bg-sky-50',
            ],
            'red' => [
                'icon' => 'bg-red-500',
                'corner' => 'bg-red-50',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-slate-50 p-4 sm:p-6 md:p-7 lg:p-8 space-y-6" x-data="{
        isModalOpen: {{ (session('errors') || old('hs_code') || $isModalOpen) ? 'true' : 'false' }},
        isEdit: {{ (old('_method') === 'PUT' || $isEdit) ? 'true' : 'false' }},
        formAction: '{{ old('_method') === 'PUT' || $isEdit ? route('admin.hs-code.update', old('hs_id', $isEdit ? $editData->id : 0)) : route('admin.hs-code.store') }}',

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.hs-code.store') }}';
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            if (window.location.search) {
                window.location.href = '{{ route('admin.hs-code.index') }}';
            }
        }
    }">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
            <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                    <i class="fa-solid fa-tags text-[#FFD54F]"></i>
                    <span>Menu Admin</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                    Manajemen Data HS Code
                </h1>
                <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                    Kelola kategori, kelompok, subkelompok, kode HS, dan uraian barang perdagangan.
                </p>
            </div>

            <div class="relative z-10 w-full sm:w-auto shrink-0">
                @if ($columnsReady)
                    <button type="button" @click="openCreateModal()"
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                        <i class="fa-solid fa-plus text-sm"></i>
                        <span>Tambah HS Code</span>
                    </button>
                @endif
            </div>
        </section>

        @if (!$tableExists)
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Tabel <strong>hs_codes</strong> belum tersedia di Supabase.</span>
            </div>
        @elseif (!$columnsReady)
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>Kolom tabel <strong>hs_codes</strong> belum sesuai. Minimal harus ada kolom <strong>hs_code</strong> dan <strong>uraian_barang</strong>.</span>
            </div>
        @endif

        @if (session('success'))
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="fa-solid fa-circle-check mt-0.5"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $style = $statStyles[$stat['color']] ?? $statStyles['green'];
                @endphp

                <article class="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <div class="absolute -mr-8 -mt-8 right-0 top-0 h-20 w-20 rounded-bl-full transition-transform duration-500 group-hover:scale-150 {{ $style['corner'] }}"></div>

                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div>
                            <p class="m-0 text-xs font-semibold text-slate-500">
                                {{ $stat['label'] }}
                            </p>

                            <p class="mb-0 mt-2 text-3xl font-black tracking-tight text-slate-800">
                                {{ number_format($stat['value'], 0, ',', '.') }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-lg text-white shadow-sm {{ $style['icon'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="mt-6 rounded-2xl border border-slate-100 bg-white p-4 sm:p-5 shadow-sm">
            <form action="{{ route('admin.hs-code.index') }}" method="GET" data-live-filter data-no-loader
                class="grid min-w-0 grid-cols-1 gap-3.5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-12 items-center">
                {{-- Filter Kategori --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="kategori" class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kategori</option>
                        @foreach ($kategoriOptions ?? [] as $option)
                            <option value="{{ $option }}" @selected(request('kategori') == $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- Filter Kelompok --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="kelompok" class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Kelompok</option>
                        @foreach ($kelompokOptions ?? [] as $option)
                            <option value="{{ $option }}" @selected(request('kelompok') == $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- Filter Subkelompok --}}
                <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                    <select name="subkelompok" class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                        <option value="">Semua Subkelompok</option>
                        @foreach ($subkelompokOptions ?? [] as $option)
                            <option value="{{ $option }}" @selected(request('subkelompok') == $option)>
                                {{ $option }}
                            </option>
                        @endforeach
                    </select>
                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                {{-- Filter Status --}}
                @if ($hasStatusColumn)
                    <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                        <select name="status" class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                            <option value="">Semua Status</option>
                            <option value="Aktif" @selected(strtolower(request('status', '')) === 'aktif')>Aktif</option>
                            <option value="Nonaktif" @selected(strtolower(request('status', '')) === 'nonaktif')>Nonaktif</option>
                        </select>
                        <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                    </div>
                @endif

                {{-- Pencarian --}}
                <div class="relative min-w-0 sm:col-span-1 {{ $hasStatusColumn ? 'xl:col-span-3' : 'xl:col-span-5' }}">
                    <input type="text" name="search" value="{{ request('search') }}"
                        placeholder="Cari kode, kelompok, atau uraian..." class="h-11 w-full min-w-0 rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">

                    <button type="submit" aria-label="Cari HS Code" title="Cari" class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                {{-- Reset Filter --}}
                <div class="flex min-w-0 items-center justify-end sm:col-span-1 xl:col-span-1">
                    <a href="{{ route('admin.hs-code.index') }}" title="Reset filter" aria-label="Reset filter" class="inline-flex h-11 w-full sm:w-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                        <i class="fa-solid fa-rotate-left text-sm"></i>
                        <span class="sm:hidden text-xs font-semibold">Reset Filter</span>
                    </a>
                </div>
            </form>
        </section>

    <div id="tableContainer" class="transition-opacity duration-200">
        <section id="adminHsTableCard"
            class="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:p-5 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">
                        Daftar HS Code
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Daftar klasifikasi barang berdasarkan struktur HS Code.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($dataHsCode->total(), 0, ',', '.') }} Data
                </div>
            </header>

            <div id="adminHsTableWrapper" class="w-full overflow-x-auto">
                <table id="adminHsTable"
                    class="w-full min-w-[1000px] border-collapse text-left [&_tr[hidden]]:!hidden">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-4 py-3 text-center w-14">No.</th>
                            <th class="px-4 py-3 w-28">Kategori</th>
                            <th class="px-5 py-3 min-w-[200px]">Kelompok</th>
                            <th class="px-5 py-3 min-w-[200px]">Subkelompok</th>
                            <th class="px-4 py-3 w-32">HS Code</th>
                            <th class="px-5 py-3 min-w-[260px]">Uraian Barang</th>
                            <th class="px-4 py-3 w-28">Status</th>
                            <th class="px-4 py-3 text-center w-28">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataHsCode as $item)
                            @php
                                $nomor = ($dataHsCode->currentPage() - 1) * $dataHsCode->perPage() + $loop->iteration;
                                $status = trim($item->status ?? 'Aktif');
                                $statusLower = strtolower($status);
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/60">
                                <td class="whitespace-nowrap px-4 py-4 text-center text-slate-500 font-mono">
                                    {{ str_pad($nomor, 3, '0', STR_PAD_LEFT) }}
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 font-mono text-xs font-bold text-amber-700">
                                        {{ $item->kode_kategori ?: '-' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[260px] font-semibold leading-relaxed text-slate-700" title="{{ $item->uraian_kelompok }}">
                                        {{ \Illuminate\Support\Str::limit($item->uraian_kelompok ?: '-', 75) }}
                                    </div>
                                    <div class="mt-1 font-mono text-xs text-slate-400">
                                        {{ $item->kode_kelompok ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[260px] font-semibold leading-relaxed text-slate-700" title="{{ $item->uraian_subkelompok }}">
                                        {{ \Illuminate\Support\Str::limit($item->uraian_subkelompok ?: '-', 75) }}
                                    </div>
                                    <div class="mt-1 font-mono text-xs text-slate-400">
                                        {{ $item->kode_subkelompok ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 font-mono text-xs font-bold tracking-wide text-sky-700">
                                        {{ $item->hs_code ?: '-' }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="max-w-[340px] text-xs leading-relaxed text-slate-500" title="{{ $item->uraian_barang }}">
                                        {{ \Illuminate\Support\Str::limit($item->uraian_barang ?: '-', 135) }}
                                    </div>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 rounded-xl border px-2.5 py-1 text-xs font-semibold {{ $statusLower === 'nonaktif' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusLower === 'nonaktif' ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                        {{ $status }}
                                    </span>
                                </td>

                                <td class="px-4 py-4 whitespace-nowrap">
                                    @if ($hasIdColumn && $item->id)
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('admin.hs-code.index', array_merge(request()->query(), ['edit' => $item->id, 'mode' => 'edit'])) }}" title="Edit HS Code"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                                <i class="fa-regular fa-pen-to-square"></i>
                                            </a>

                                            <button type="button" title="Hapus HS Code" data-delete-hs data-delete-url="{{ route('admin.hs-code.destroy', $item->id) }}" data-delete-name="{{ $item->hs_code }}"
                                                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                                <i class="fa-regular fa-trash-can"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-center text-slate-400">-</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">
                                            <i class="fa-solid fa-tags"></i>
                                        </div>

                                        <h3 class="mb-0 mt-4 text-sm font-semibold text-slate-700">
                                            Data HS Code tidak ditemukan
                                        </h3>

                                        <p class="mb-0 mt-1 text-xs leading-relaxed text-slate-400">
                                            Coba ubah filter atau kata pencarian yang digunakan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Pagination Component -->
        <x-pagination :paginator="$dataHsCode" />
    </div>

        <footer class="pb-1 pt-8 text-center text-xs text-slate-400">
            Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
        </footer>

        <template x-teleport="body">
            <div x-show="isModalOpen" x-cloak x-transition @keydown.escape.window="closeModal()"
                class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm">

                <div class="bg-white rounded-2xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="closeModal()">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                                <i class="fa-solid" :class="isEdit ? 'fa-pen-to-square' : 'fa-plus'"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Edit Data HS Code' : 'Tambah Data HS Code'"></h3>
                                <p class="text-xs text-slate-500">Lengkapi kategori, kelompok, subkelompok, HS Code, serta uraian barang.</p>
                            </div>
                        </div>

                        <button type="button" @click="closeModal()"
                            class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0">
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <form :action="formAction" method="POST" class="overflow-y-auto pt-4 flex-1 space-y-4 text-sm text-slate-700">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label for="excel_id" class="mb-1 block text-sm font-semibold text-slate-700">
                                ID Excel
                            </label>

                            <input id="excel_id" type="number" name="excel_id" value="{{ old('excel_id', $isEdit ? $editData->excel_id : '') }}" placeholder="Opsional"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('excel_id')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if ($hasStatusColumn)
                            <div>
                                <label for="status" class="mb-1 block text-sm font-semibold text-slate-700">
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>

                                @php
                                    $selectedStatus = old(
                                        'status',
                                        $isEdit
                                        ? ($editData->status ?? 'Aktif')
                                        : 'Aktif'
                                    );
                                @endphp

                                <div class="relative">
                                    <select id="status" name="status" required
                                        class="h-11 w-full appearance-none rounded-xl border border-[#CFE3D5] bg-white px-4 pr-10 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">
                                        <option value="Aktif" @selected(strtolower($selectedStatus) === 'aktif')>
                                            Aktif
                                        </option>

                                        <option value="Nonaktif" @selected(strtolower($selectedStatus) === 'nonaktif')>
                                            Nonaktif
                                        </option>
                                    </select>
                                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                </div>

                                @error('status')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        <div>
                            <label for="kode_kategori" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Kategori
                            </label>

                            <input id="kode_kategori" type="text" name="kode_kategori" value="{{ old('kode_kategori', $isEdit ? $editData->kode_kategori : '') }}" placeholder="Contoh: 01"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 font-mono text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('kode_kategori')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_kelompok" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Kelompok
                            </label>

                            <input id="kode_kelompok" type="text" name="kode_kelompok" value="{{ old('kode_kelompok', $isEdit ? $editData->kode_kelompok : '') }}" placeholder="Contoh: 01.01"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 font-mono text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('kode_kelompok')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_subkelompok" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Subkelompok
                            </label>

                            <input id="kode_subkelompok" type="text" name="kode_subkelompok" value="{{ old('kode_subkelompok', $isEdit ? $editData->kode_subkelompok : '') }}" placeholder="Contoh: 0101.30"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 font-mono text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('kode_subkelompok')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label for="hs_code" class="mb-1 block text-sm font-semibold text-slate-700">
                                HS Code
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="hs_code" type="text" name="hs_code" value="{{ old('hs_code', $isEdit ? $editData->hs_code : '') }}" placeholder="Contoh: 0101.21.00" required
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 font-mono text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('hs_code')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="uraian_kelompok" class="mb-1 block text-sm font-semibold text-slate-700">
                                Uraian Kelompok
                            </label>

                            <input id="uraian_kelompok" type="text" name="uraian_kelompok" value="{{ old('uraian_kelompok', $isEdit ? $editData->uraian_kelompok : '') }}" placeholder="Masukkan uraian kelompok"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('uraian_kelompok')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="uraian_subkelompok" class="mb-1 block text-sm font-semibold text-slate-700">
                                Uraian Subkelompok
                            </label>

                            <input id="uraian_subkelompok" type="text" name="uraian_subkelompok" value="{{ old('uraian_subkelompok', $isEdit ? $editData->uraian_subkelompok : '') }}" placeholder="Masukkan uraian subkelompok"
                                class="h-11 w-full rounded-xl border border-[#CFE3D5] px-4 text-sm text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">

                            @error('uraian_subkelompok')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="uraian_barang" class="mb-1 block text-sm font-semibold text-slate-700">
                                Uraian Barang
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea id="uraian_barang" name="uraian_barang" rows="4" placeholder="Masukkan uraian barang" required
                                class="w-full resize-none rounded-xl border border-[#CFE3D5] px-4 py-3 text-sm leading-relaxed text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">{{ old('uraian_barang', $isEdit ? $editData->uraian_barang : '') }}</textarea>

                            @error('uraian_barang')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="keterangan" class="mb-1 block text-sm font-semibold text-slate-700">
                                Keterangan
                            </label>

                            <textarea id="keterangan" name="keterangan" rows="3" placeholder="Masukkan keterangan tambahan bila diperlukan"
                                class="w-full resize-none rounded-xl border border-[#CFE3D5] px-4 py-3 text-sm leading-relaxed text-slate-700 outline-none transition-all focus:border-[#145239] focus:ring-2 focus:ring-[#145239]/20 hover:border-[#145239] shadow-2xs">{{ old('keterangan', $isEdit ? $editData->keterangan : '') }}</textarea>

                            @error('keterangan')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                        <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row shrink-0">
                            <button type="button" @click="closeModal()"
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                                Batal
                            </button>

                            <button type="submit"
                                class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-2">
                                <i class="fa-solid fa-floppy-disk text-xs"></i>
                                <span x-text="isEdit ? 'Simpan Perubahan' : 'Tambah HS Code'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <div id="deleteHsModal"
        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
                <i class="fa-regular fa-trash-can"></i>
            </div>

            <h3 class="mb-0 mt-5 text-xl font-bold text-slate-800">
                Hapus data HS Code?
            </h3>

            <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
                Data HS Code <strong id="deleteHsName" class="text-slate-700"></strong> akan dihapus. Tindakan ini tidak dapat dibatalkan.
            </p>

            <form id="deleteHsForm" action="" method="POST"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
                @csrf
                @method('DELETE')

                <button type="button" id="cancelDeleteHs"
                    class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit"
                    class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700">
                    <i class="fa-regular fa-trash-can"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const deleteModal = document.getElementById(
                    'deleteHsModal'
                );

                const deleteForm = document.getElementById(
                    'deleteHsForm'
                );

                const deleteName = document.getElementById(
                    'deleteHsName'
                );

                const cancelDelete = document.getElementById(
                    'cancelDeleteHs'
                );

                const deleteButtons = document.querySelectorAll(
                    '[data-delete-hs]'
                );

                function openDeleteModal(url, name) {
                    if (
                        !deleteModal
                        || !deleteForm
                        || !deleteName
                    ) {
                        return;
                    }

                    deleteForm.action = url;
                    deleteName.textContent = name || '-';

                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');

                    document.body.style.overflow = 'hidden';
                }

                function closeDeleteModal() {
                    if (!deleteModal) {
                        return;
                    }

                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');

                    document.body.style.overflow = '';
                }

                deleteButtons.forEach(function (button) {
                    button.addEventListener(
                        'click',
                        function () {
                            openDeleteModal(
                                button.dataset.deleteUrl,
                                button.dataset.deleteName
                            );
                        }
                    );
                });

                cancelDelete?.addEventListener(
                    'click',
                    closeDeleteModal
                );

                deleteModal?.addEventListener(
                    'click',
                    function (event) {
                        if (event.target === deleteModal) {
                            closeDeleteModal();
                        }
                    }
                );

                document.addEventListener(
                    'keydown',
                    function (event) {
                        if (event.key === 'Escape') {
                            closeDeleteModal();
                        }
                    }
                );
            });
        </script>
    @endpush
@endsection
@extends('layouts.admin')

@section('title', 'Data Wilayah')

@section('content')
@php
$isModalOpen = in_array($mode, ['create', 'edit'], true);
$isEdit = $mode === 'edit' && $editData;

$formAction = $isEdit
? route('admin.data-wilayah.update', $editData->id)
: route('admin.data-wilayah.store');

$closeModalUrl = route(
'admin.data-wilayah.index',
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
        isModalOpen: {{ (session('errors') || old('nama_provinsi') || $isModalOpen) ? 'true' : 'false' }},
        isEdit: {{ (old('_method') === 'PUT' || $isEdit) ? 'true' : 'false' }},
        formAction: '{{ old('_method') === 'PUT' || $isEdit ? route('admin.data-wilayah.update', old('wilayah_id', $isEdit ? $editData->id : 0)) : route('admin.data-wilayah.store') }}',

        openCreateModal() {
            this.isEdit = false;
            this.formAction = '{{ route('admin.data-wilayah.store') }}';
            this.isModalOpen = true;
        },

        closeModal() {
            this.isModalOpen = false;
            if (window.location.search) {
                window.location.href = '{{ route('admin.data-wilayah.index') }}';
            }
        }
    }">
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-6 sm:p-7 md:p-8 shadow-lg text-white flex flex-col sm:flex-row items-start sm:items-center justify-between gap-5">
        <div class="relative z-10 space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-800/60 border border-emerald-700/60 text-emerald-100 text-xs font-bold backdrop-blur-sm">
                <i class="fa-solid fa-shield-halved text-[#FFD54F]"></i>
                <span>Menu Admin</span>
            </div>
            <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight">
                Manajemen Data Wilayah
            </h1>
            <p class="text-emerald-100/90 text-xs md:text-sm max-w-2xl leading-relaxed">
                Kelola data provinsi, kabupaten atau kota, kecamatan, serta desa atau kelurahan di Sumatera Utara.
            </p>
        </div>

        <div class="relative z-10 w-full sm:w-auto shrink-0">
            <button type="button" @click="openCreateModal()"
                class="w-full sm:w-auto px-5 py-3 rounded-xl bg-[#FFD54F] hover:bg-amber-400 text-slate-900 font-extrabold text-xs shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 border border-amber-300 transform hover:-translate-y-0.5">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Wilayah</span>
            </button>
        </div>
    </section>

    @if (!$tableExists)
    <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
        <i class="fa-solid fa-circle-exclamation mt-0.5"></i>
        <span>
            Tabel <strong>provinsi, kabupaten, kecamatan, dan kelurahan_desa</strong> belum tersedia lengkap di Supabase.
        </span>
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
        <form action="{{ route('admin.data-wilayah.index') }}" method="GET" data-live-filter data-no-loader
            class="grid min-w-0 grid-cols-1 gap-3.5 sm:grid-cols-2 xl:grid-cols-12 items-center">

            <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                <select name="kode_kabupaten" id="filterKabupaten"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Kab/Kota</option>

                    @foreach ($kabupatenOptions as $option)
                    <option value="{{ $option->kode_kabupaten }}" @selected(
                        request('kode_kabupaten')===$option->kode_kabupaten
                        )>
                        {{ $option->nama_kabupaten }}
                    </option>
                    @endforeach
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                <select name="kode_kecamatan" id="filterKecamatan"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Kecamatan</option>

                    @foreach ($kecamatanOptions as $option)
                    <option value="{{ $option->kode_kecamatan }}" @selected(
                        request('kode_kecamatan')===$option->kode_kecamatan
                        )>
                        {{ $option->nama_kecamatan }}
                    </option>
                    @endforeach
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 sm:col-span-1 xl:col-span-2">
                <select name="status"
                    class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                    <option value="">Semua Status</option>
                    <option value="Aktif" @selected(strtolower(request('status', '' ))==='aktif' )>Aktif</option>
                    <option value="Nonaktif" @selected(strtolower(request('status', '' ))==='nonaktif' )>Nonaktif</option>
                </select>

                <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
            </div>

            <div class="relative min-w-0 sm:col-span-1 xl:col-span-3">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau kode wilayah..."
                    class="h-11 w-full min-w-0 rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">

                <button type="submit" aria-label="Cari wilayah"
                    class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </div>

            <div class="flex min-w-0 items-center justify-end sm:col-span-2 xl:col-span-1">
                <a href="{{ route('admin.data-wilayah.index') }}" title="Reset filter"
                    class="inline-flex h-11 w-full sm:w-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-rose-50 hover:text-rose-600 hover:border-rose-200 shadow-xs">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span class="sm:hidden text-xs font-semibold">Reset Filter</span>
                </a>
            </div>
        </form>
    </section>

    <div id="tableContainer" class="transition-opacity duration-200">
        <section id="adminWilayahTableCard"
            class="mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm">
            <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-4 sm:p-5 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800">
                        Daftar Wilayah
                    </h2>
                    <p class="mt-1 text-xs text-slate-500">
                        Data administratif wilayah Provinsi Sumatera Utara.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <i class="fa-solid fa-database"></i>
                    {{ number_format($dataWilayah->total(), 0, ',', '.') }} Data
                </div>
            </header>

            <div id="adminWilayahTableWrapper" class="w-full overflow-x-auto">
                <table id="adminWilayahTable"
                    class="w-full min-w-[1000px] border-collapse text-left [&_tr[hidden]]:!hidden">
                <thead>
                    <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <th class="px-4 py-3 text-center w-14">No.</th>
                        <th class="px-5 py-3 min-w-[180px]">Provinsi</th>
                        <th class="px-5 py-3 min-w-[200px]">Kabupaten/Kota</th>
                        <th class="px-5 py-3 min-w-[200px]">Kecamatan</th>
                        <th class="px-5 py-3 min-w-[200px]">Desa/Kelurahan</th>
                        <th class="px-5 py-3 w-32">Status</th>
                        <th class="px-5 py-3 text-center w-28">Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-sm">
                    @forelse ($dataWilayah as $item)
                    @php
                    $nomor = ($dataWilayah->currentPage() - 1) * $dataWilayah->perPage() + $loop->iteration;
                    $status = trim($item->status ?? 'Aktif');
                    $statusLower = strtolower($status);
                    @endphp

                    <tr class="transition-colors hover:bg-slate-50/60">
                        <td class="whitespace-nowrap px-4 py-4 text-center text-slate-500 font-mono">
                            {{ str_pad($nomor, 3, '0', STR_PAD_LEFT) }}
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-700">
                                {{ $item->nama_provinsi ?: '-' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-400 font-mono">
                                {{ $item->kode_provinsi ?: '-' }}
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-700">
                                {{ $item->nama_kabupaten ?: '-' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-400 font-mono">
                                {{ $item->kode_kabupaten ?: '-' }}
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-700">
                                {{ $item->nama_kecamatan ?: '-' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-400 font-mono">
                                {{ $item->kode_kecamatan ?: '-' }}
                            </div>
                        </td>

                        <td class="px-5 py-4">
                            <div class="font-semibold text-slate-700">
                                {{ $item->nama_desa ?: '-' }}
                            </div>
                            <div class="mt-1 text-xs text-slate-400 font-mono">
                                {{ $item->kode_desa ?: '-' }}
                            </div>
                        </td>

                        <td class="px-5 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center gap-1.5 rounded-xl border px-2.5 py-1 text-xs font-semibold {{ $statusLower === 'nonaktif' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                <span class="h-1.5 w-1.5 rounded-full {{ $statusLower === 'nonaktif' ? 'bg-red-500' : 'bg-emerald-500' }}"></span>
                                {{ $status }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('admin.data-wilayah.index', array_merge(request()->query(), ['edit' => $item->id, 'mode' => 'edit'])) }}" title="Edit wilayah"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600">
                                    <i class="fa-regular fa-pen-to-square"></i>
                                </a>

                                <button type="button" title="Hapus wilayah" data-delete-wilayah data-delete-url="{{ route('admin.data-wilayah.destroy', $item->id) }}" data-delete-name="{{ $item->nama_desa }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600">
                                    <i class="fa-regular fa-trash-can"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-14 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-xl text-slate-400">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                </div>

                                <h3 class="mb-0 mt-4 text-sm font-semibold text-slate-700">
                                    Data wilayah tidak ditemukan
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
        <x-pagination :paginator="$dataWilayah" />
    </div>

    <footer class="pb-1 pt-8 text-center text-xs text-slate-400">
        Copyright &copy; {{ date('Y') }} DPMPTSP Provinsi Sumatera Utara
    </footer>

    @php
    $formValues = [
        'nama_provinsi' => old('nama_provinsi', $isEdit ? $editData->nama_provinsi : ''),
        'kode_provinsi' => old('kode_provinsi', $isEdit ? $editData->kode_provinsi : ''),
        'nama_kabupaten' => old('nama_kabupaten', $isEdit ? $editData->nama_kabupaten : ''),
        'kode_kabupaten' => old('kode_kabupaten', $isEdit ? $editData->kode_kabupaten : ''),
        'nama_kecamatan' => old('nama_kecamatan', $isEdit ? $editData->nama_kecamatan : ''),
        'kode_kecamatan' => old('kode_kecamatan', $isEdit ? $editData->kode_kecamatan : ''),
        'nama_desa' => old('nama_desa', $isEdit ? $editData->nama_desa : ''),
        'kode_desa' => old('kode_desa', $isEdit ? $editData->kode_desa : ''),
    ];

    $selectedStatus = old('status', $isEdit ? ($editData->status ?? 'Aktif') : 'Aktif');
    @endphp

    <template x-teleport="body">
        <div x-show="isModalOpen" x-cloak x-transition @keydown.escape.window="closeModal()"
            class="fixed inset-0 z-[99999] flex items-center justify-center overflow-y-auto bg-slate-900/60 p-3 sm:p-4 backdrop-blur-sm">

            <div class="bg-white rounded-2xl max-w-3xl w-full p-5 sm:p-6 shadow-2xl border border-emerald-100 relative my-auto max-h-[90vh] flex flex-col overflow-hidden" @click.outside="closeModal()">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-[#E7F2EB] text-[#145239] flex items-center justify-center font-bold shrink-0">
                            <i class="fa-solid" :class="isEdit ? 'fa-map-pen' : 'fa-map-location-dot'"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900 text-base" x-text="isEdit ? 'Edit Data Wilayah' : 'Tambah Data Wilayah'"></h3>
                            <p class="text-xs text-slate-500">
                                Pilih nama wilayah dari dropdown atau gunakan pilihan Isi sendiri, kemudian masukkan kode wilayah secara manual.
                            </p>
                        </div>
                    </div>

                    <button type="button" @click="closeModal()"
                        class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition-colors shrink-0">
                        <i class="fa-solid fa-xmark text-sm"></i>
                    </button>
                </div>

                <form :action="formAction" method="POST" id="wilayahForm" class="overflow-y-auto pt-4 flex-1 text-sm text-slate-700 space-y-4"
                    x-data="wilayahFormHandler()" @submit="validateSubmit($event)">
                    @csrf
                    <template x-if="isEdit">
                        <input type="hidden" name="_method" value="PUT">
                    </template>

                    <input type="hidden" name="nama_provinsi" id="nama_provinsi" :value="nama_provinsi">
                    <input type="hidden" name="nama_kabupaten" id="nama_kabupaten" :value="nama_kabupaten">
                    <input type="hidden" name="nama_kecamatan" id="nama_kecamatan" :value="nama_kecamatan">
                    <input type="hidden" name="nama_desa" id="nama_desa" :value="nama_desa">

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">
                                Provinsi
                                <span class="text-red-500">*</span>
                            </label>

                            <!-- Dropdown Select -->
                            <div class="relative z-[60]">
                                <div @click="provinceDropdownOpen = !provinceDropdownOpen; if(provinceDropdownOpen) $nextTick(() => $refs.provinceSearchInput.focus())"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                    <span x-text="nama_provinsi || 'Pilih Provinsi...'" :class="nama_provinsi ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="provinceDropdownOpen ? 'rotate-180' : ''"></i>
                                </div>

                                <div x-show="provinceDropdownOpen" @click.outside="provinceDropdownOpen = false" x-transition.origin.top.duration.150ms
                                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                        <input type="text" x-model="provinceSearch" x-ref="provinceSearchInput" placeholder="Cari provinsi..."
                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                        <template x-for="item in filteredProvinces" :key="item.code">
                                            <div @click="selectProvince(item)"
                                                :class="kode_provinsi == item.code ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="item.name"></span>
                                                <i x-show="kode_provinsi == item.code" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            @error('nama_provinsi')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_provinsi" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Provinsi
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="kode_provinsi" type="text" name="kode_provinsi" x-model="kode_provinsi"
                                maxlength="2" inputmode="numeric" pattern="[0-9]{2}" placeholder="Contoh: 13" required
                                class="w-full h-[42px] rounded-xl border border-[#CFE3D5] px-3.5 text-sm font-medium text-slate-700 outline-none transition-colors hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs placeholder:text-slate-400">

                            <p class="mb-0 mt-1.5 text-xs text-slate-400">
                                Contoh: 13
                            </p>

                            @error('kode_provinsi')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">
                                Kabupaten/Kota
                                <span class="text-red-500">*</span>
                            </label>

                            <!-- Dropdown Select -->
                            <div class="relative z-50">
                                <div @click="if(kode_provinsi) { regencyDropdownOpen = !regencyDropdownOpen; if(regencyDropdownOpen) $nextTick(() => $refs.regencySearchInput.focus()) }"
                                    :class="kode_provinsi ? 'bg-white cursor-pointer hover:border-[#145239]' : 'bg-slate-50 cursor-not-allowed text-slate-400'"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] text-sm flex items-center justify-between transition-colors shadow-2xs">
                                    <span x-text="nama_kabupaten || ( kode_provinsi ? 'Pilih Kabupaten/Kota...' : 'Pilih provinsi terlebih dahulu' )" :class="nama_kabupaten ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="regencyDropdownOpen ? 'rotate-180' : ''"></i>
                                </div>

                                <div x-show="regencyDropdownOpen" @click.outside="regencyDropdownOpen = false" x-transition.origin.top.duration.150ms
                                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                        <input type="text" x-model="regencySearch" x-ref="regencySearchInput" placeholder="Cari kabupaten/kota..."
                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                        <template x-for="item in filteredRegencies" :key="item.code">
                                            <div @click="selectRegency(item)"
                                                :class="kode_kabupaten == item.code ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="item.name"></span>
                                                <i x-show="kode_kabupaten == item.code" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            @error('nama_kabupaten')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_kabupaten" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Kabupaten/Kota
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="kode_kabupaten" type="text" name="kode_kabupaten" x-model="kode_kabupaten"
                                maxlength="5" inputmode="numeric" placeholder="Contoh: 13.01" required
                                class="w-full h-[42px] rounded-xl border border-[#CFE3D5] px-3.5 text-sm font-medium text-slate-700 outline-none transition-colors hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs placeholder:text-slate-400">

                            <p class="mb-0 mt-1.5 text-xs text-slate-400">
                                Contoh: 13.01
                            </p>

                            @error('kode_kabupaten')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">
                                Kecamatan
                                <span class="text-red-500">*</span>
                            </label>

                            <!-- Dropdown Select -->
                            <div class="relative z-40">
                                <div @click="if(kode_kabupaten) { districtDropdownOpen = !districtDropdownOpen; if(districtDropdownOpen) $nextTick(() => $refs.districtSearchInput.focus()) }"
                                    :class="kode_kabupaten ? 'bg-white cursor-pointer hover:border-[#145239]' : 'bg-slate-50 cursor-not-allowed text-slate-400'"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] text-sm flex items-center justify-between transition-colors shadow-2xs">
                                    <span x-text="nama_kecamatan || ( kode_kabupaten ? 'Pilih Kecamatan...' : 'Pilih kabupaten/kota terlebih dahulu' )" :class="nama_kecamatan ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="districtDropdownOpen ? 'rotate-180' : ''"></i>
                                </div>

                                <div x-show="districtDropdownOpen" @click.outside="districtDropdownOpen = false" x-transition.origin.top.duration.150ms
                                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                        <input type="text" x-model="districtSearch" x-ref="districtSearchInput" placeholder="Cari kecamatan..."
                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                        <template x-for="item in filteredDistricts" :key="item.code">
                                            <div @click="selectDistrict(item)"
                                                :class="kode_kecamatan == item.code ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="item.name"></span>
                                                <i x-show="kode_kecamatan == item.code" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            @error('nama_kecamatan')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_kecamatan" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Kecamatan
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="kode_kecamatan" type="text" name="kode_kecamatan" x-model="kode_kecamatan"
                                maxlength="8" inputmode="numeric" placeholder="Contoh: 13.01.01" required
                                class="w-full h-[42px] rounded-xl border border-[#CFE3D5] px-3.5 text-sm font-medium text-slate-700 outline-none transition-colors hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs placeholder:text-slate-400">

                            <p class="mb-0 mt-1.5 text-xs text-slate-400">
                                Contoh: 13.01.01
                            </p>

                            @error('kode_kecamatan')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">
                                Desa/Kelurahan
                                <span class="text-red-500">*</span>
                            </label>

                            <!-- Dropdown Select -->
                            <div class="relative z-30">
                                <div @click="if(kode_kecamatan) { villageDropdownOpen = !villageDropdownOpen; if(villageDropdownOpen) $nextTick(() => $refs.villageSearchInput.focus()) }"
                                    :class="kode_kecamatan ? 'bg-white cursor-pointer hover:border-[#145239]' : 'bg-slate-50 cursor-not-allowed text-slate-400'"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] text-sm flex items-center justify-between transition-colors shadow-2xs">
                                    <span x-text="nama_desa || ( kode_kecamatan ? 'Pilih Desa/Kelurahan...' : 'Pilih kecamatan terlebih dahulu' )" :class="nama_desa ? 'text-slate-800 font-medium' : 'text-slate-400'"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="villageDropdownOpen ? 'rotate-180' : ''"></i>
                                </div>

                                <div x-show="villageDropdownOpen" @click.outside="villageDropdownOpen = false" x-transition.origin.top.duration.150ms
                                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-2 space-y-2">
                                    <div class="relative">
                                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                                        <input type="text" x-model="villageSearch" x-ref="villageSearchInput" placeholder="Cari desa/kelurahan..."
                                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-[#CFE3D5] focus:border-[#145239] outline-none">
                                    </div>
                                    <div class="max-h-48 overflow-y-auto space-y-0.5 text-xs">
                                        <template x-for="item in filteredVillages" :key="item.code">
                                            <div @click="selectVillage(item)"
                                                :class="kode_desa == item.code ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                                class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                                <span x-text="item.name"></span>
                                                <i x-show="kode_desa == item.code" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            @error('nama_desa')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        <div>
                            <label for="kode_desa" class="mb-1 block text-sm font-semibold text-slate-700">
                                Kode Desa/Kelurahan
                                <span class="text-red-500">*</span>
                            </label>

                            <input id="kode_desa" type="text" name="kode_desa" x-model="kode_desa"
                                maxlength="13" inputmode="numeric" placeholder="Contoh: 13.01.01.2001" required
                                class="w-full h-[42px] rounded-xl border border-[#CFE3D5] px-3.5 text-sm font-medium text-slate-700 outline-none transition-colors hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs placeholder:text-slate-400">

                            <p class="mb-0 mt-1.5 text-xs text-slate-400">
                                Contoh: 13.01.01.2001
                            </p>

                            @error('kode_desa')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>

                        @if (Schema::hasColumn('kelurahan_desa', 'status'))
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">
                                Status
                                <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="status" :value="status" required>

                            <div x-data="{ open: false }" class="relative z-20">
                                <div @click="open = !open"
                                    class="w-full h-[42px] px-3.5 py-2 rounded-xl border border-[#CFE3D5] bg-white text-sm flex items-center justify-between cursor-pointer hover:border-[#145239] transition-colors shadow-2xs">
                                    <span x-text="status" class="text-slate-800 font-medium"></span>
                                    <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''"></i>
                                </div>
                                <div x-show="open" @click.outside="open = false" x-transition.origin.top.duration.150ms
                                    class="absolute z-50 left-0 right-0 mt-1.5 bg-white rounded-xl border border-[#CFE3D5] shadow-2xl overflow-hidden p-1 space-y-0.5 text-xs">
                                    <div @click="status = 'Aktif'; open = false"
                                        :class="status === 'Aktif' ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                        <span>Aktif</span>
                                        <i x-show="status === 'Aktif'" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                    </div>
                                    <div @click="status = 'Nonaktif'; open = false"
                                        :class="status === 'Nonaktif' ? 'bg-[#EEF8F2] text-[#145239] font-bold' : 'hover:bg-slate-50 text-slate-700'"
                                        class="px-3 py-2 rounded-lg cursor-pointer flex items-center justify-between">
                                        <span>Nonaktif</span>
                                        <i x-show="status === 'Nonaktif'" class="fa-solid fa-check text-xs text-[#145239]"></i>
                                    </div>
                                </div>
                            </div>

                            @error('status')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                        @endif

                        @if (Schema::hasColumn('kelurahan_desa', 'keterangan'))
                        <div class="sm:col-span-2">
                            <label for="keterangan" class="mb-1 block text-sm font-semibold text-slate-700">
                                Keterangan
                            </label>

                            <textarea id="keterangan" name="keterangan" rows="3" x-model="keterangan"
                                placeholder="Masukkan keterangan tambahan bila diperlukan"
                                class="w-full resize-y rounded-xl border border-[#CFE3D5] bg-white px-3.5 py-3 text-sm font-medium text-slate-700 outline-none transition-colors hover:border-[#145239] focus:border-[#145239] focus:ring-1 focus:ring-[#145239] shadow-2xs placeholder:text-slate-400"></textarea>

                            @error('keterangan')
                            <p class="mb-0 mt-1.5 text-xs text-red-600">
                                {{ $message }}
                            </p>
                            @enderror
                        </div>
                        @endif
                    </div>

                    <div class="mt-6 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row shrink-0">
                        <button type="button" @click="closeModal()"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            Batal
                        </button>

                        <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#145239] hover:bg-[#0B5D3D] text-white text-xs font-bold shadow-md transition-colors flex items-center justify-center gap-2">
                            <i class="fa-solid fa-floppy-disk text-xs"></i>
                            <span x-text="isEdit ? 'Simpan Perubahan' : 'Simpan Wilayah'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<div id="deleteWilayahModal"
    class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
            <i class="fa-regular fa-trash-can"></i>
        </div>

        <h3 class="mb-0 mt-5 text-xl font-bold text-slate-800">
            Hapus data wilayah?
        </h3>

        <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
            Data wilayah <strong id="deleteWilayahName" class="text-slate-700"></strong> akan dihapus. Tindakan ini tidak dapat dibatalkan.
        </p>

        <form id="deleteWilayahForm" action="" method="POST"
            class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center">
            @csrf
            @method('DELETE')

            <button type="button" id="cancelDeleteWilayah"
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
    document.addEventListener('DOMContentLoaded', function() {
        const filterKabupaten = document.getElementById(
            'filterKabupaten'
        );

        const filterKecamatan = document.getElementById(
            'filterKecamatan'
        );

        filterKabupaten?.addEventListener('change', function() {
            if (filterKecamatan) {
                filterKecamatan.value = '';
            }
        });

        const deleteModal = document.getElementById(
            'deleteWilayahModal'
        );

        const deleteForm = document.getElementById(
            'deleteWilayahForm'
        );

        const deleteName = document.getElementById(
            'deleteWilayahName'
        );

        const cancelDelete = document.getElementById(
            'cancelDeleteWilayah'
        );

        function openDeleteModal(url, name) {
            if (!deleteModal || !deleteForm || !deleteName) {
                return;
            }

            deleteForm.action = url;
            deleteName.textContent = name || 'yang dipilih';
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

        document.querySelectorAll(
            '[data-delete-wilayah]'
        ).forEach(function(button) {
            button.addEventListener('click', function() {
                openDeleteModal(
                    button.dataset.deleteUrl,
                    button.dataset.deleteName
                );
            });
        });

        cancelDelete?.addEventListener(
            'click',
            closeDeleteModal
        );

        deleteModal?.addEventListener(
            'click',
            function(event) {
                if (event.target === deleteModal) {
                    closeDeleteModal();
                }
            }
        );

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeDeleteModal();
            }
        });

    });

    function wilayahFormHandler() {
        return {
            endpoints: {
                provinces: @json(route('admin.data-wilayah.options.provinsi')),
                regencies: @json(route('admin.data-wilayah.options.kabupaten')),
                districts: @json(route('admin.data-wilayah.options.kecamatan')),
                villages: @json(route('admin.data-wilayah.options.desa')),
            },
            
            // Dropdown option lists
            provinces: [],
            regencies: [],
            districts: [],
            villages: [],

            // Selected codes (to query parent-child relationships)
            kode_provinsi: @json($formValues['kode_provinsi'] ?? ''),
            kode_kabupaten: @json($formValues['kode_kabupaten'] ?? ''),
            kode_kecamatan: @json($formValues['kode_kecamatan'] ?? ''),
            kode_desa: @json($formValues['kode_desa'] ?? ''),

            // Selected names (what gets submitted in hidden inputs / text fields)
            nama_provinsi: @json($formValues['nama_provinsi'] ?? ''),
            nama_kabupaten: @json($formValues['nama_kabupaten'] ?? ''),
            nama_kecamatan: @json($formValues['nama_kecamatan'] ?? ''),
            nama_desa: @json($formValues['nama_desa'] ?? ''),

            // Open states for the custom dropdowns
            provinceDropdownOpen: false,
            regencyDropdownOpen: false,
            districtDropdownOpen: false,
            villageDropdownOpen: false,

            // Search inputs for filtering lists
            provinceSearch: '',
            regencySearch: '',
            districtSearch: '',
            villageSearch: '',

            // Status & Keterangan
            status: @json($selectedStatus ?? 'Aktif'),
            keterangan: @json(old('keterangan', $isEdit ? ($editData->keterangan ?? '') : '')),

            // Helper to get filtered items for display
            get filteredProvinces() {
                if (!this.provinceSearch) return this.provinces;
                const q = this.provinceSearch.toLowerCase();
                return this.provinces.filter(p => p.name.toLowerCase().includes(q));
            },
            get filteredRegencies() {
                if (!this.regencySearch) return this.regencies;
                const q = this.regencySearch.toLowerCase();
                return this.regencies.filter(r => r.name.toLowerCase().includes(q));
            },
            get filteredDistricts() {
                if (!this.districtSearch) return this.districts;
                const q = this.districtSearch.toLowerCase();
                return this.districts.filter(d => d.name.toLowerCase().includes(q));
            },
            get filteredVillages() {
                if (!this.villageSearch) return this.villages;
                const q = this.villageSearch.toLowerCase();
                return this.villages.filter(v => v.name.toLowerCase().includes(q));
            },

            async init() {
                // Fetch provinces on initialization
                try {
                    this.provinces = await this.fetchItems(this.endpoints.provinces);
                    
                    // Pre-load regencies if province selected
                    if (this.kode_provinsi) {
                        this.regencies = await this.fetchItems(this.endpoints.regencies, { province_code: this.kode_provinsi });
                    }

                    // Pre-load districts
                    if (this.kode_kabupaten) {
                        this.districts = await this.fetchItems(this.endpoints.districts, { regency_code: this.kode_kabupaten });
                    }

                    // Pre-load villages
                    if (this.kode_kecamatan) {
                        this.villages = await this.fetchItems(this.endpoints.villages, { district_code: this.kode_kecamatan });
                    }
                } catch (e) {
                    console.error('Initial load failed', e);
                }
            },

            async fetchItems(url, params = {}) {
                const query = new URLSearchParams(params);
                const target = query.toString() ? url + '?' + query.toString() : url;
                const response = await fetch(target, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) throw new Error('Failed to fetch items');
                const payload = await response.json();
                return Array.isArray(payload.data) ? payload.data : [];
            },

            async selectProvince(item) {
                this.kode_provinsi = item.code;
                this.nama_provinsi = item.name;
                this.provinceDropdownOpen = false;
                this.provinceSearch = '';

                // Reset child levels
                this.resetRegency();
                this.resetDistrict();
                this.resetVillage();

                // Load child levels
                try {
                    this.regencies = await this.fetchItems(this.endpoints.regencies, { province_code: item.code });
                } catch (e) {
                    this.regencies = [];
                }
            },

            async selectRegency(item) {
                this.kode_kabupaten = item.code;
                this.nama_kabupaten = item.name;
                this.regencyDropdownOpen = false;
                this.regencySearch = '';

                // Reset child levels
                this.resetDistrict();
                this.resetVillage();

                // Load child levels
                try {
                    this.districts = await this.fetchItems(this.endpoints.districts, { regency_code: item.code });
                } catch (e) {
                    this.districts = [];
                }
            },

            async selectDistrict(item) {
                this.kode_kecamatan = item.code;
                this.nama_kecamatan = item.name;
                this.districtDropdownOpen = false;
                this.districtSearch = '';

                // Reset child levels
                this.resetVillage();

                // Load child levels
                try {
                    this.villages = await this.fetchItems(this.endpoints.villages, { district_code: item.code });
                } catch (e) {
                    this.villages = [];
                }
            },

            selectVillage(item) {
                this.kode_desa = item.code;
                this.nama_desa = item.name;
                this.villageDropdownOpen = false;
                this.villageSearch = '';
            },

            resetRegency() {
                this.kode_kabupaten = '';
                this.nama_kabupaten = '';
                this.regencies = [];
            },

            resetDistrict() {
                this.kode_kecamatan = '';
                this.nama_kecamatan = '';
                this.districts = [];
            },

            resetVillage() {
                this.kode_desa = '';
                this.nama_desa = '';
                this.villages = [];
            },

            validateSubmit(e) {
                if (!this.nama_provinsi.trim() || !this.nama_kabupaten.trim() || !this.nama_kecamatan.trim() || !this.nama_desa.trim()) {
                    e.preventDefault();
                    alert('Semua wilayah kerja harus terisi.');
                }
            }
        };
    }
</script>
@endpush
@endsection
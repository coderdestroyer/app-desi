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



    <div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8">
        <section class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-[#145239] via-[#0F8A5F] to-[#1E5D41] p-7 shadow-lg md:p-8">
            <div class="absolute right-0 top-0 h-56 w-56 rounded-full bg-emerald-400 opacity-20 blur-3xl mix-blend-overlay"></div>

            <div class="absolute bottom-0 right-32 h-40 w-40 rounded-full bg-yellow-400 opacity-20 blur-3xl mix-blend-overlay"></div>

            <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10"></div>

            <div class="relative z-10 flex flex-col items-start justify-between gap-5 md:flex-row md:items-center">
                <div>
                    <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-emerald-700/50 bg-emerald-800/50 px-3 py-1 text-xs font-bold text-emerald-100 backdrop-blur-sm">
                        <i class="fa-solid fa-map-location-dot text-[#FFD54F]"></i>
                        Menu Admin
                    </div>

                    <h1 class="text-2xl font-extrabold tracking-tight text-white md:text-3xl">
                        Manajemen
                        <span class="text-[#FFD54F]">
                            Data Wilayah
                        </span>
                    </h1>

                    <p class="mt-2 max-w-2xl text-sm font-medium leading-relaxed text-emerald-100/90">
                        Kelola data provinsi, kabupaten atau kota, kecamatan, serta desa atau kelurahan di Sumatera Utara.
                    </p>
                </div>

                @if ($tableExists)
                    <a
                        href="{{ route(
                            'admin.data-wilayah.index',
                            array_merge(
                                request()->except(['edit']),
                                ['mode' => 'create']
                            )
                        ) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#FFD54F] px-5 py-3 text-sm font-bold text-emerald-900 shadow-lg transition hover:-translate-y-0.5 hover:bg-yellow-300"
                    >
                        <i class="fa-solid fa-plus"></i>
                        Tambah Wilayah
                    </a>
                @endif
            </div>
        </section>

        @if (! $tableExists)
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                <span>
                    Tabel
                    <strong>provinsi, kabupaten, kecamatan, dan kelurahan_desa</strong>
                    belum tersedia lengkap di Supabase.
                </span>
            </div>
        @endif

        @if (session('success'))
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                <i class="fa-solid fa-circle-check mt-0.5"></i>

                <span>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if (session('error'))
            <div class="mt-5 flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                <span>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        <section class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($stats as $stat)
                @php
                    $style = $statStyles[$stat['color']]
                        ?? $statStyles['green'];
                @endphp

                <article class="group relative overflow-hidden rounded-xl border border-slate-100 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                    <div class="absolute -mr-8 -mt-8 right-0 top-0 h-20 w-20 rounded-bl-full transition-transform duration-500 group-hover:scale-150 {{ $style['corner'] }}"></div>

                    <div class="relative z-10 flex items-center justify-between gap-4">
                        <div>
                            <p class="m-0 text-xs font-semibold text-slate-500">
                                {{ $stat['label'] }}
                            </p>

                            <p class="mb-0 mt-2 text-3xl font-black tracking-tight text-slate-800">
                                {{ number_format(
                                    $stat['value'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </p>
                        </div>

                        <div class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-xl text-lg text-white shadow-sm {{ $style['icon'] }}">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="mt-6 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <form
                action="{{ route('admin.data-wilayah.index') }}"
                method="GET"
                class="grid min-w-0 grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-12"
            >
                
                <div class="relative min-w-0 xl:col-span-2">
                    <select
                        name="kode_kabupaten"
                        id="filterKabupaten"
                        class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">Semua Kab/Kota</option>

                        @foreach ($kabupatenOptions as $option)
                            <option
                                value="{{ $option->kode_kabupaten }}"
                                @selected(
                                    request('kode_kabupaten')
                                    === $option->kode_kabupaten
                                )
                            >
                                {{ $option->nama_kabupaten }}
                            </option>
                        @endforeach
                    </select>

                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                
                <div class="relative min-w-0 xl:col-span-2">
                    <select
                        name="kode_kecamatan"
                        id="filterKecamatan"
                        class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">Semua Kecamatan</option>

                        @foreach ($kecamatanOptions as $option)
                            <option
                                value="{{ $option->kode_kecamatan }}"
                                @selected(
                                    request('kode_kecamatan')
                                    === $option->kode_kecamatan
                                )
                            >
                                {{ $option->nama_kecamatan }}
                            </option>
                        @endforeach
                    </select>

                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                
                <div class="relative min-w-0 xl:col-span-2">
                    <select
                        name="status"
                        class="h-11 w-full min-w-0 appearance-none truncate rounded-xl border border-slate-200 bg-white px-4 pr-10 text-sm font-medium text-slate-600 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >
                        <option value="">Semua Status</option>

                        <option
                            value="Aktif"
                            @selected(strtolower(request('status', '')) === 'aktif')
                        >
                            Aktif
                        </option>

                        <option
                            value="Nonaktif"
                            @selected(strtolower(request('status', '')) === 'nonaktif')
                        >
                            Nonaktif
                        </option>
                    </select>

                    <i class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"></i>
                </div>

                
                <div class="relative min-w-0 xl:col-span-3">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama atau kode wilayah..."
                        class="h-11 w-full min-w-0 rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                    >

                    <button
                        type="submit"
                        aria-label="Cari wilayah"
                        class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg bg-emerald-50 text-sm text-emerald-600 transition hover:bg-emerald-100"
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                
                <div class="flex min-w-0 gap-2 md:col-span-2 xl:col-span-3">
                    <button
                        type="submit"
                        class="inline-flex h-11 min-w-0 flex-1 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700"
                    >
                        <i class="fa-solid fa-filter"></i>
                        <span class="truncate">Terapkan</span>
                    </button>

                    <a
                        href="{{ route('admin.data-wilayah.index') }}"
                        title="Reset filter"
                        class="inline-flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-emerald-600"
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        <section
            id="adminWilayahTableCard"
            class="!block !visible !opacity-100 !relative !w-full !min-h-[100px] !h-auto !overflow-hidden !transform-none mt-6 overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-sm"
        >
            <header class="flex flex-col justify-between gap-3 border-b border-slate-100 bg-slate-50/50 p-5 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Daftar Wilayah
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Data administratif wilayah Provinsi Sumatera Utara.
                    </p>
                </div>

                <div class="inline-flex w-fit items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                    <i class="fa-solid fa-database"></i>

                    {{ number_format(
                        $dataWilayah->total(),
                        0,
                        ',',
                        '.'
                    ) }}
                    Data
                </div>
            </header>

            <div id="adminWilayahTableWrapper" class="!block !visible !opacity-100 !w-full !overflow-x-auto !overflow-y-visible">
                <table
                    id="adminWilayahTable"
                    class="!table !visible !opacity-100 !w-full !min-w-[1150px] !border-collapse !table-auto text-left [&_thead]:!table-header-group [&_tbody]:!table-row-group [&_tr]:!table-row [&_th]:!table-cell [&_th]:!visible [&_th]:!opacity-100 [&_th]:!whitespace-nowrap [&_td]:!table-cell [&_td]:!visible [&_td]:!opacity-100"
                >
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <th class="px-5 py-3">
                                No.
                            </th>

                            <th class="px-5 py-3">
                                Provinsi
                            </th>

                            <th class="px-5 py-3">
                                Kabupaten/Kota
                            </th>

                            <th class="px-5 py-3">
                                Kecamatan
                            </th>

                            <th class="px-5 py-3">
                                Desa/Kelurahan
                            </th>

                            <th class="px-5 py-3">
                                Status
                            </th>

                            <th class="px-5 py-3 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($dataWilayah as $item)
                            @php
                                $nomor =
                                    ($dataWilayah->currentPage() - 1)
                                    * $dataWilayah->perPage()
                                    + $loop->iteration;

                                $status = trim(
                                    $item->status ?? 'Aktif'
                                );

                                $statusLower = strtolower($status);
                            @endphp

                            <tr class="transition-colors hover:bg-slate-50/60">
                                <td class="whitespace-nowrap px-5 py-4 text-slate-500">
                                    {{ str_pad(
                                        $nomor,
                                        3,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $item->nama_provinsi ?: '-' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $item->kode_provinsi ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $item->nama_kabupaten ?: '-' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $item->kode_kabupaten ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $item->nama_kecamatan ?: '-' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $item->kode_kecamatan ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="font-semibold text-slate-700">
                                        {{ $item->nama_desa ?: '-' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $item->kode_desa ?: '-' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold {{ $statusLower === 'nonaktif' ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-700' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $statusLower === 'nonaktif' ? 'bg-red-500' : 'bg-emerald-500' }}"></span>

                                        {{ $status }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a
                                            href="{{ route(
                                                'admin.data-wilayah.index',
                                                array_merge(
                                                    request()->query(),
                                                    [
                                                        'edit' => $item->id,
                                                        'mode' => 'edit',
                                                    ]
                                                )
                                            ) }}"
                                            title="Edit wilayah"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                                        >
                                            <i class="fa-regular fa-pen-to-square"></i>
                                        </a>

                                        <button
                                            type="button"
                                            title="Hapus wilayah"
                                            data-delete-wilayah
                                            data-delete-url="{{ route(
                                                'admin.data-wilayah.destroy',
                                                $item->id
                                            ) }}"
                                            data-delete-name="{{ $item->nama_desa }}"
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-red-100 bg-white text-red-500 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                                        >
                                            <i class="fa-regular fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-5 py-14 text-center"
                                >
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

        <footer class="pb-1 pt-8 text-center text-xs text-slate-400">
            Copyright &copy;
            {{ date('Y') }}
            DPMPTSP Provinsi Sumatera Utara
        </footer>
    </div>

    @if ($isModalOpen)
        @php
            $formValues = [
                'nama_provinsi' => old(
                    'nama_provinsi',
                    $isEdit ? $editData->nama_provinsi : ''
                ),
                'kode_provinsi' => old(
                    'kode_provinsi',
                    $isEdit ? $editData->kode_provinsi : ''
                ),
                'nama_kabupaten' => old(
                    'nama_kabupaten',
                    $isEdit ? $editData->nama_kabupaten : ''
                ),
                'kode_kabupaten' => old(
                    'kode_kabupaten',
                    $isEdit ? $editData->kode_kabupaten : ''
                ),
                'nama_kecamatan' => old(
                    'nama_kecamatan',
                    $isEdit ? $editData->nama_kecamatan : ''
                ),
                'kode_kecamatan' => old(
                    'kode_kecamatan',
                    $isEdit ? $editData->kode_kecamatan : ''
                ),
                'nama_desa' => old(
                    'nama_desa',
                    $isEdit ? $editData->nama_desa : ''
                ),
                'kode_desa' => old(
                    'kode_desa',
                    $isEdit ? $editData->kode_desa : ''
                ),
            ];

            $selectedStatus = old(
                'status',
                $isEdit ? ($editData->status ?? 'Aktif') : 'Aktif'
            );
        @endphp

        <div class="fixed inset-0 z-[999] overflow-y-auto bg-slate-900/50 p-4 backdrop-blur-sm">
            <div class="flex min-h-full items-start justify-center">
                <div class="my-2 flex max-h-[calc(100vh-1rem)] w-full max-w-5xl flex-col overflow-hidden rounded-2xl border border-slate-100 bg-white shadow-2xl">
                    <header class="sticky top-0 z-20 flex flex-shrink-0 items-start justify-between gap-5 border-b border-slate-100 bg-white p-6">
                    <div>
                        <div class="mb-3 inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600">
                            <i class="fa-solid {{ $isEdit ? 'fa-map-pen' : 'fa-map-location-dot' }}"></i>
                        </div>

                        <h2 class="m-0 text-xl font-bold text-slate-800">
                            {{ $isEdit ? 'Edit Data Wilayah' : 'Tambah Data Wilayah' }}
                        </h2>

                        <p class="mb-0 mt-1 text-sm text-slate-500">
                            Pilih nama wilayah dari dropdown atau gunakan pilihan Isi sendiri, kemudian masukkan kode wilayah secara manual.
                        </p>
                    </div>

                    <a
                        href="{{ $closeModalUrl }}"
                        class="inline-flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-100"
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </header>

                    <form
                        action="{{ $formAction }}"
                        method="POST"
                        id="wilayahForm"
                        class="overflow-y-auto p-6"
                    >
                    @csrf

                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    <input
                        type="hidden"
                        name="nama_provinsi"
                        id="nama_provinsi"
                        value="{{ $formValues['nama_provinsi'] }}"
                    >

                    <input
                        type="hidden"
                        name="nama_kabupaten"
                        id="nama_kabupaten"
                        value="{{ $formValues['nama_kabupaten'] }}"
                    >

                    <input
                        type="hidden"
                        name="nama_kecamatan"
                        id="nama_kecamatan"
                        value="{{ $formValues['nama_kecamatan'] }}"
                    >

                    <input
                        type="hidden"
                        name="nama_desa"
                        id="nama_desa"
                        value="{{ $formValues['nama_desa'] }}"
                    >

                    <div class="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2">
                        <div>
                            <label
                                for="provinsiSelect"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Provinsi
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select
                                    id="provinsiSelect"
                                    required
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option value="">Memuat data provinsi...</option>
                                </select>

                                <input
                                    type="text"
                                    id="provinsiInlineInput"
                                    value="{{ $formValues['nama_provinsi'] }}"
                                    placeholder="Ketik nama provinsi"
                                    autocomplete="off"
                                    class="hidden h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >

                                <button
                                    type="button"
                                    id="provinsiBackButton"
                                    title="Kembali ke daftar provinsi"
                                    class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-emerald-600"
                                >
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>

                                <i
                                    id="provinsiChevron"
                                    class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"
                                ></i>
                            </div>

                            @error('nama_provinsi')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="kode_provinsi"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kode Provinsi
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="kode_provinsi"
                                type="text"
                                name="kode_provinsi"
                                value="{{ $formValues['kode_provinsi'] }}"
                                maxlength="2"
                                inputmode="numeric"
                                pattern="[0-9]{2}"
                                placeholder="Contoh: 13"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >

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
                            <label
                                for="kabupatenSelect"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kabupaten/Kota
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select
                                    id="kabupatenSelect"
                                    disabled
                                    required
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option value="">Pilih provinsi terlebih dahulu</option>
                                </select>

                                <input
                                    type="text"
                                    id="kabupatenInlineInput"
                                    value="{{ $formValues['nama_kabupaten'] }}"
                                    placeholder="Ketik nama kabupaten/kota"
                                    autocomplete="off"
                                    class="hidden h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >

                                <button
                                    type="button"
                                    id="kabupatenBackButton"
                                    title="Kembali ke daftar kabupaten/kota"
                                    class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-emerald-600"
                                >
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>

                                <i
                                    id="kabupatenChevron"
                                    class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"
                                ></i>
                            </div>

                            @error('nama_kabupaten')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="kode_kabupaten"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kode Kabupaten/Kota
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="kode_kabupaten"
                                type="text"
                                name="kode_kabupaten"
                                value="{{ $formValues['kode_kabupaten'] }}"
                                maxlength="5"
                                inputmode="numeric"
                                placeholder="Contoh: 13.01"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >

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
                            <label
                                for="kecamatanSelect"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kecamatan
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select
                                    id="kecamatanSelect"
                                    disabled
                                    required
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option value="">Pilih kabupaten/kota terlebih dahulu</option>
                                </select>

                                <input
                                    type="text"
                                    id="kecamatanInlineInput"
                                    value="{{ $formValues['nama_kecamatan'] }}"
                                    placeholder="Ketik nama kecamatan"
                                    autocomplete="off"
                                    class="hidden h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >

                                <button
                                    type="button"
                                    id="kecamatanBackButton"
                                    title="Kembali ke daftar kecamatan"
                                    class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-emerald-600"
                                >
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>

                                <i
                                    id="kecamatanChevron"
                                    class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"
                                ></i>
                            </div>

                            @error('nama_kecamatan')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="kode_kecamatan"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kode Kecamatan
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="kode_kecamatan"
                                type="text"
                                name="kode_kecamatan"
                                value="{{ $formValues['kode_kecamatan'] }}"
                                maxlength="8"
                                inputmode="numeric"
                                placeholder="Contoh: 13.01.01"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >

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
                            <label
                                for="desaSelect"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Desa/Kelurahan
                                <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                <select
                                    id="desaSelect"
                                    disabled
                                    required
                                    class="h-12 w-full appearance-none rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition disabled:cursor-not-allowed disabled:bg-slate-100 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option value="">Pilih kecamatan terlebih dahulu</option>
                                </select>

                                <input
                                    type="text"
                                    id="desaInlineInput"
                                    value="{{ $formValues['nama_desa'] }}"
                                    placeholder="Ketik nama desa/kelurahan"
                                    autocomplete="off"
                                    class="hidden h-12 w-full rounded-xl border border-slate-200 bg-white px-4 pr-11 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >

                                <button
                                    type="button"
                                    id="desaBackButton"
                                    title="Kembali ke daftar desa/kelurahan"
                                    class="absolute right-3 top-1/2 hidden h-8 w-8 -translate-y-1/2 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-emerald-600"
                                >
                                    <i class="fa-solid fa-arrow-left"></i>
                                </button>

                                <i
                                    id="desaChevron"
                                    class="fa-solid fa-chevron-down pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-xs text-emerald-600"
                                ></i>
                            </div>

                            @error('nama_desa')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="kode_desa"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Kode Desa/Kelurahan
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="kode_desa"
                                type="text"
                                name="kode_desa"
                                value="{{ $formValues['kode_desa'] }}"
                                maxlength="13"
                                inputmode="numeric"
                                placeholder="Contoh: 13.01.01.2001"
                                required
                                class="h-12 w-full rounded-xl border border-slate-200 px-4 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                            >

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
                                <label
                                    for="status"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="h-12 w-full rounded-xl border border-slate-200 bg-white px-4 text-sm text-slate-700 outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >
                                    <option
                                        value="Aktif"
                                        @selected(strtolower($selectedStatus) === 'aktif')
                                    >
                                        Aktif
                                    </option>

                                    <option
                                        value="Nonaktif"
                                        @selected(strtolower($selectedStatus) === 'nonaktif')
                                    >
                                        Nonaktif
                                    </option>
                                </select>

                                @error('status')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        @if (Schema::hasColumn('kelurahan_desa', 'keterangan'))
                            <div class="{{ Schema::hasColumn('kelurahan_desa', 'status') ? 'md:col-span-2' : 'md:col-span-2' }}">
                                <label
                                    for="keterangan"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Keterangan
                                </label>

                                <textarea
                                    id="keterangan"
                                    name="keterangan"
                                    rows="3"
                                    placeholder="Masukkan keterangan tambahan bila diperlukan"
                                    class="w-full resize-y rounded-xl border border-slate-200 px-4 py-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100"
                                >{{ old(
                                    'keterangan',
                                    $isEdit ? ($editData->keterangan ?? '') : ''
                                ) }}</textarea>

                                @error('keterangan')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    <div class="mt-7 flex flex-col-reverse justify-end gap-3 border-t border-slate-100 pt-5 sm:flex-row">
                        <a
                            href="{{ $closeModalUrl }}"
                            class="inline-flex h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>

                            {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Wilayah' }}
                        </button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <div
        id="deleteWilayahModal"
        class="fixed inset-0 z-[1000] hidden items-center justify-center bg-slate-900/50 p-4 backdrop-blur-sm"
    >
        <div class="w-full max-w-md rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-red-100 text-2xl text-red-600">
                <i class="fa-regular fa-trash-can"></i>
            </div>

            <h3 class="mb-0 mt-5 text-xl font-bold text-slate-800">
                Hapus data wilayah?
            </h3>

            <p class="mb-0 mt-2 text-sm leading-relaxed text-slate-500">
                Data wilayah

                <strong
                    id="deleteWilayahName"
                    class="text-slate-700"
                ></strong>

                akan dihapus. Tindakan ini tidak dapat dibatalkan.
            </p>

            <form
                id="deleteWilayahForm"
                action=""
                method="POST"
                class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-center"
            >
                @csrf
                @method('DELETE')

                <button
                    type="button"
                    id="cancelDeleteWilayah"
                    class="inline-flex h-11 min-w-32 items-center justify-center rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="inline-flex h-11 min-w-32 items-center justify-center gap-2 rounded-xl bg-red-600 px-5 text-sm font-semibold text-white transition hover:bg-red-700"
                >
                    <i class="fa-regular fa-trash-can"></i>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const filterKabupaten = document.getElementById(
                    'filterKabupaten'
                );

                const filterKecamatan = document.getElementById(
                    'filterKecamatan'
                );

                filterKabupaten?.addEventListener('change', function () {
                    if (filterKecamatan) {
                        filterKecamatan.value = '';
                    }

                    filterKabupaten.form.submit();
                });

                filterKecamatan?.addEventListener('change', function () {
                    filterKecamatan.form.submit();
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
                    if (! deleteModal || ! deleteForm || ! deleteName) {
                        return;
                    }

                    deleteForm.action = url;
                    deleteName.textContent = name || 'yang dipilih';
                    deleteModal.classList.remove('hidden');
                    deleteModal.classList.add('flex');
                    document.body.style.overflow = 'hidden';
                }

                function closeDeleteModal() {
                    if (! deleteModal) {
                        return;
                    }

                    deleteModal.classList.add('hidden');
                    deleteModal.classList.remove('flex');
                    document.body.style.overflow = '';
                }

                document.querySelectorAll(
                    '[data-delete-wilayah]'
                ).forEach(function (button) {
                    button.addEventListener('click', function () {
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
                    function (event) {
                        if (event.target === deleteModal) {
                            closeDeleteModal();
                        }
                    }
                );

                document.addEventListener('keydown', function (event) {
                    if (event.key === 'Escape') {
                        closeDeleteModal();
                    }
                });

                const wilayahForm = document.getElementById(
                    'wilayahForm'
                );

                if (! wilayahForm) {
                    return;
                }

                const manualValue = '__manual__';

                const endpoints = {
                    provinces: @json(
                        route(
                            'admin.data-wilayah.options.provinsi'
                        )
                    ),
                    regencies: @json(
                        route(
                            'admin.data-wilayah.options.kabupaten'
                        )
                    ),
                    districts: @json(
                        route(
                            'admin.data-wilayah.options.kecamatan'
                        )
                    ),
                    villages: @json(
                        route(
                            'admin.data-wilayah.options.desa'
                        )
                    ),
                };

                const initial = {
                    provinceCode: @json(
                        $formValues['kode_provinsi'] ?? ''
                    ),
                    provinceName: @json(
                        $formValues['nama_provinsi'] ?? ''
                    ),
                    regencyCode: @json(
                        $formValues['kode_kabupaten'] ?? ''
                    ),
                    regencyName: @json(
                        $formValues['nama_kabupaten'] ?? ''
                    ),
                    districtCode: @json(
                        $formValues['kode_kecamatan'] ?? ''
                    ),
                    districtName: @json(
                        $formValues['nama_kecamatan'] ?? ''
                    ),
                    villageCode: @json(
                        $formValues['kode_desa'] ?? ''
                    ),
                    villageName: @json(
                        $formValues['nama_desa'] ?? ''
                    ),
                };

                const levels = {
                    province: {
                        select: document.getElementById(
                            'provinsiSelect'
                        ),
                        input: document.getElementById(
                            'provinsiInlineInput'
                        ),
                        back: document.getElementById(
                            'provinsiBackButton'
                        ),
                        chevron: document.getElementById(
                            'provinsiChevron'
                        ),
                        hidden: document.getElementById(
                            'nama_provinsi'
                        ),
                    },
                    regency: {
                        select: document.getElementById(
                            'kabupatenSelect'
                        ),
                        input: document.getElementById(
                            'kabupatenInlineInput'
                        ),
                        back: document.getElementById(
                            'kabupatenBackButton'
                        ),
                        chevron: document.getElementById(
                            'kabupatenChevron'
                        ),
                        hidden: document.getElementById(
                            'nama_kabupaten'
                        ),
                    },
                    district: {
                        select: document.getElementById(
                            'kecamatanSelect'
                        ),
                        input: document.getElementById(
                            'kecamatanInlineInput'
                        ),
                        back: document.getElementById(
                            'kecamatanBackButton'
                        ),
                        chevron: document.getElementById(
                            'kecamatanChevron'
                        ),
                        hidden: document.getElementById(
                            'nama_kecamatan'
                        ),
                    },
                    village: {
                        select: document.getElementById(
                            'desaSelect'
                        ),
                        input: document.getElementById(
                            'desaInlineInput'
                        ),
                        back: document.getElementById(
                            'desaBackButton'
                        ),
                        chevron: document.getElementById(
                            'desaChevron'
                        ),
                        hidden: document.getElementById(
                            'nama_desa'
                        ),
                    },
                };

                function placeholder(level) {
                    const labels = {
                        province: 'Pilih Provinsi',
                        regency: 'Pilih Kabupaten/Kota',
                        district: 'Pilih Kecamatan',
                        village: 'Pilih Desa/Kelurahan',
                    };

                    return labels[level];
                }

                async function fetchItems(url, parameters = {}) {
                    const query = new URLSearchParams(parameters);
                    const target = query.toString()
                        ? url + '?' + query.toString()
                        : url;

                    const response = await fetch(target, {
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });

                    if (! response.ok) {
                        throw new Error(
                            'Data wilayah gagal dimuat.'
                        );
                    }

                    const payload = await response.json();

                    return Array.isArray(payload.data)
                        ? payload.data
                        : [];
                }

                function renderOptions(
                    level,
                    items,
                    selectedCode = '',
                    selectedName = ''
                ) {
                    const field = levels[level];

                    field.select.innerHTML = '';

                    const emptyOption =
                        document.createElement('option');

                    emptyOption.value = '';
                    emptyOption.textContent = placeholder(level);

                    field.select.appendChild(emptyOption);

                    items.forEach(function (item) {
                        const option =
                            document.createElement('option');

                        option.value = item.code;
                        option.textContent = item.name;
                        option.dataset.name = item.name;

                        if (
                            selectedCode
                            && String(item.code)
                                === String(selectedCode)
                        ) {
                            option.selected = true;
                        }

                        field.select.appendChild(option);
                    });

                    const manualOption =
                        document.createElement('option');

                    manualOption.value = manualValue;
                    manualOption.textContent = 'Lainnya...';

                    field.select.appendChild(manualOption);
                    field.select.disabled = false;

                    const found = items.some(function (item) {
                        return selectedCode
                            && String(item.code)
                                === String(selectedCode);
                    });

                    if (selectedName && ! found) {
                        field.select.value = manualValue;
                        showInlineInput(level, selectedName);
                    } else if (found) {
                        const selected =
                            field.select.options[
                                field.select.selectedIndex
                            ];

                        field.hidden.value =
                            selected?.dataset.name || '';
                    }
                }

                function resetLevel(level, disabled = true) {
                    const field = levels[level];

                    field.select.innerHTML = '';

                    const option =
                        document.createElement('option');

                    option.value = '';
                    option.textContent = placeholder(level);

                    field.select.appendChild(option);
                    field.select.disabled = disabled;
                    field.select.classList.remove('hidden');
                    field.input.classList.add('hidden');
                    field.input.required = false;
                    field.back.classList.add('hidden');
                    field.back.classList.remove('flex');
                    field.chevron.classList.remove('hidden');
                    field.hidden.value = '';
                }

                function showInlineInput(level, value = '') {
                    const field = levels[level];

                    field.select.classList.add('hidden');
                    field.input.classList.remove('hidden');
                    field.input.value = value;
                    field.input.required = true;
                    field.back.classList.remove('hidden');
                    field.back.classList.add('flex');
                    field.chevron.classList.add('hidden');
                    field.hidden.value = value.trim();

                    window.setTimeout(function () {
                        field.input.focus();
                        field.input.setSelectionRange(
                            field.input.value.length,
                            field.input.value.length
                        );
                    }, 0);
                }

                function showSelect(level) {
                    const field = levels[level];

                    field.input.classList.add('hidden');
                    field.input.required = false;
                    field.select.classList.remove('hidden');
                    field.select.value = '';
                    field.back.classList.add('hidden');
                    field.back.classList.remove('flex');
                    field.chevron.classList.remove('hidden');
                    field.hidden.value = '';
                    field.select.focus();
                }

                function selectedName(level) {
                    const field = levels[level];

                    if (! field.input.classList.contains('hidden')) {
                        return field.input.value.trim();
                    }

                    const selected =
                        field.select.options[
                            field.select.selectedIndex
                        ];

                    return selected?.dataset.name || '';
                }

                function selectedCode(level) {
                    const field = levels[level];

                    if (! field.input.classList.contains('hidden')) {
                        return null;
                    }

                    return field.select.value || null;
                }

                function syncHidden(level) {
                    levels[level].hidden.value =
                        selectedName(level);
                }

                Object.keys(levels).forEach(function (level) {
                    const field = levels[level];

                    field.input.addEventListener(
                        'input',
                        function () {
                            syncHidden(level);
                        }
                    );

                    field.back.addEventListener(
                        'click',
                        function () {
                            showSelect(level);

                            if (level === 'province') {
                                resetLevel('regency');
                                resetLevel('district');
                                resetLevel('village');
                            }

                            if (level === 'regency') {
                                resetLevel('district');
                                resetLevel('village');
                            }

                            if (level === 'district') {
                                resetLevel('village');
                            }
                        }
                    );
                });

                levels.province.select.addEventListener(
                    'change',
                    async function () {
                        resetLevel('regency');
                        resetLevel('district');
                        resetLevel('village');

                        if (
                            levels.province.select.value
                            === manualValue
                        ) {
                            showInlineInput('province');

                            levels.regency.select.disabled = false;
                            levels.district.select.disabled = false;
                            levels.village.select.disabled = false;

                            renderOptions('regency', []);
                            renderOptions('district', []);
                            renderOptions('village', []);

                            return;
                        }

                        syncHidden('province');

                        const code = selectedCode('province');

                        if (! code) {
                            return;
                        }

                        const items = await fetchItems(
                            endpoints.regencies,
                            {
                                province_code: code,
                            }
                        );

                        renderOptions('regency', items);
                    }
                );

                levels.regency.select.addEventListener(
                    'change',
                    async function () {
                        resetLevel('district');
                        resetLevel('village');

                        if (
                            levels.regency.select.value
                            === manualValue
                        ) {
                            showInlineInput('regency');
                            renderOptions('district', []);
                            renderOptions('village', []);
                            return;
                        }

                        syncHidden('regency');

                        const code = selectedCode('regency');

                        if (! code) {
                            return;
                        }

                        const items = await fetchItems(
                            endpoints.districts,
                            {
                                regency_code: code,
                            }
                        );

                        renderOptions('district', items);
                    }
                );

                levels.district.select.addEventListener(
                    'change',
                    async function () {
                        resetLevel('village');

                        if (
                            levels.district.select.value
                            === manualValue
                        ) {
                            showInlineInput('district');
                            renderOptions('village', []);
                            return;
                        }

                        syncHidden('district');

                        const code = selectedCode('district');

                        if (! code) {
                            return;
                        }

                        const items = await fetchItems(
                            endpoints.villages,
                            {
                                district_code: code,
                            }
                        );

                        renderOptions('village', items);
                    }
                );

                levels.village.select.addEventListener(
                    'change',
                    function () {
                        if (
                            levels.village.select.value
                            === manualValue
                        ) {
                            showInlineInput('village');
                            return;
                        }

                        syncHidden('village');
                    }
                );

                wilayahForm.addEventListener(
                    'submit',
                    function (event) {
                        Object.keys(levels).forEach(function (level) {
                            syncHidden(level);
                        });

                        const missing = Object.values(levels)
                            .some(function (field) {
                                return ! field.hidden.value.trim();
                            });

                        if (missing) {
                            event.preventDefault();
                        }
                    }
                );

                async function initializeForm() {
                    resetLevel('province', false);
                    resetLevel('regency');
                    resetLevel('district');
                    resetLevel('village');

                    const provinces = await fetchItems(
                        endpoints.provinces
                    );

                    renderOptions(
                        'province',
                        provinces,
                        initial.provinceCode,
                        initial.provinceName
                    );

                    if (
                        levels.province.select.value
                        && levels.province.select.value
                            !== manualValue
                    ) {
                        const regencies = await fetchItems(
                            endpoints.regencies,
                            {
                                province_code:
                                    levels.province.select.value,
                            }
                        );

                        renderOptions(
                            'regency',
                            regencies,
                            initial.regencyCode,
                            initial.regencyName
                        );
                    } else if (
                        ! levels.province.input.classList
                            .contains('hidden')
                    ) {
                        renderOptions(
                            'regency',
                            [],
                            '',
                            initial.regencyName
                        );
                    }

                    if (
                        levels.regency.select.value
                        && levels.regency.select.value
                            !== manualValue
                    ) {
                        const districts = await fetchItems(
                            endpoints.districts,
                            {
                                regency_code:
                                    levels.regency.select.value,
                            }
                        );

                        renderOptions(
                            'district',
                            districts,
                            initial.districtCode,
                            initial.districtName
                        );
                    } else if (
                        ! levels.regency.input.classList
                            .contains('hidden')
                    ) {
                        renderOptions(
                            'district',
                            [],
                            '',
                            initial.districtName
                        );
                    }

                    if (
                        levels.district.select.value
                        && levels.district.select.value
                            !== manualValue
                    ) {
                        const villages = await fetchItems(
                            endpoints.villages,
                            {
                                district_code:
                                    levels.district.select.value,
                            }
                        );

                        renderOptions(
                            'village',
                            villages,
                            initial.villageCode,
                            initial.villageName
                        );
                    } else if (
                        ! levels.district.input.classList
                            .contains('hidden')
                    ) {
                        renderOptions(
                            'village',
                            [],
                            '',
                            initial.villageName
                        );
                    }
                }

                initializeForm().catch(function () {
                    renderOptions(
                        'province',
                        [],
                        '',
                        initial.provinceName
                    );

                    renderOptions(
                        'regency',
                        [],
                        '',
                        initial.regencyName
                    );

                    renderOptions(
                        'district',
                        [],
                        '',
                        initial.districtName
                    );

                    renderOptions(
                        'village',
                        [],
                        '',
                        initial.villageName
                    );
                });
            });
        </script>
    @endpush
@endsection
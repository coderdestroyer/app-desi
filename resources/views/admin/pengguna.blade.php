@extends('layouts.admin')

@section('title', 'Data Pengguna')

@section('content')
    @php
        $isModalOpen = in_array($mode, ['create', 'edit'], true);
        $isEdit = $mode === 'edit' && $editData;

        $formAction = $isEdit
            ? route('admin.pengguna.update', $editData->id)
            : route('admin.pengguna.store');

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

        {{-- ========================================================= --}}
        {{-- HEADER HALAMAN --}}
        {{-- ========================================================= --}}

        <section
            class="
                relative
                overflow-hidden
                rounded-2xl
                bg-gradient-to-r
                from-[#145239]
                via-[#0F8A5F]
                to-[#1E5D41]
                p-7
                shadow-lg
                md:p-8
            "
        >
            <div
                class="
                    absolute
                    right-0
                    top-0
                    h-56
                    w-56
                    rounded-full
                    bg-emerald-400
                    opacity-20
                    blur-3xl
                    mix-blend-overlay
                "
            ></div>

            <div
                class="
                    absolute
                    bottom-0
                    right-32
                    h-40
                    w-40
                    rounded-full
                    bg-yellow-400
                    opacity-20
                    blur-3xl
                    mix-blend-overlay
                "
            ></div>

            <div
                class="
                    absolute
                    inset-0
                    bg-[url('https://www.transparenttextures.com/patterns/cubes.png')]
                    opacity-10
                "
            ></div>

            <div
                class="
                    relative
                    z-10
                    flex
                    flex-col
                    items-start
                    justify-between
                    gap-5
                    md:flex-row
                    md:items-center
                "
            >
                <div>
                    <div
                        class="
                            mb-3
                            inline-flex
                            items-center
                            gap-2
                            rounded-full
                            border
                            border-emerald-700/50
                            bg-emerald-800/50
                            px-3
                            py-1
                            text-xs
                            font-bold
                            text-emerald-100
                            backdrop-blur-sm
                        "
                    >
                        <i class="fa-solid fa-users text-[#FFD54F]"></i>
                        Menu Admin
                    </div>

                    <h1
                        class="
                            text-2xl
                            font-extrabold
                            tracking-tight
                            text-white
                            md:text-3xl
                        "
                    >
                        Manajemen
                        <span class="text-[#FFD54F]">
                            Pengguna
                        </span>
                    </h1>

                    <p
                        class="
                            mt-2
                            max-w-2xl
                            text-sm
                            font-medium
                            leading-relaxed
                            text-emerald-100/90
                        "
                    >
                        Kelola akun yang terdaftar, role pengguna,
                        status akun, dan akses pengguna dalam sistem.
                    </p>
                </div>

                <a
                    href="{{ route('admin.pengguna.index', array_merge(
                        request()->except(['edit']),
                        ['mode' => 'create']
                    )) }}"
                    class="
                        inline-flex
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-[#FFD54F]
                        px-5
                        py-3
                        text-sm
                        font-bold
                        text-emerald-900
                        shadow-lg
                        transition
                        hover:-translate-y-0.5
                        hover:bg-yellow-300
                    "
                >
                    <i class="fa-solid fa-plus"></i>
                    Tambah Pengguna
                </a>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- ALERT --}}
        {{-- ========================================================= --}}

        @if (session('success'))
            <div
                class="
                    mt-5
                    flex
                    items-start
                    gap-3
                    rounded-xl
                    border
                    border-emerald-200
                    bg-emerald-50
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-emerald-700
                "
            >
                <i class="fa-solid fa-circle-check mt-0.5"></i>

                <span>
                    {{ session('success') }}
                </span>
            </div>
        @endif

        @if (session('error'))
            <div
                class="
                    mt-5
                    flex
                    items-start
                    gap-3
                    rounded-xl
                    border
                    border-red-200
                    bg-red-50
                    px-4
                    py-3
                    text-sm
                    font-medium
                    text-red-700
                "
            >
                <i class="fa-solid fa-circle-exclamation mt-0.5"></i>

                <span>
                    {{ session('error') }}
                </span>
            </div>
        @endif

        {{-- ========================================================= --}}
        {{-- STATISTIK --}}
        {{-- ========================================================= --}}

        <section
            class="
                mt-6
                grid
                grid-cols-1
                gap-4
                sm:grid-cols-2
                xl:grid-cols-4
            "
        >
            @foreach ($stats as $stat)
                @php
                    $style = $statStyles[$stat['color']]
                        ?? $statStyles['green'];
                @endphp

                <article
                    class="
                        group
                        relative
                        overflow-hidden
                        rounded-xl
                        border
                        border-slate-100
                        bg-white
                        p-5
                        shadow-sm
                        transition-all
                        duration-300
                        hover:-translate-y-1
                        hover:shadow-md
                    "
                >
                    <div
                        class="
                            absolute
                            -mr-8
                            -mt-8
                            right-0
                            top-0
                            h-20
                            w-20
                            rounded-bl-full
                            transition-transform
                            duration-500
                            group-hover:scale-150
                            {{ $style['corner'] }}
                        "
                    ></div>

                    <div
                        class="
                            relative
                            z-10
                            flex
                            items-center
                            justify-between
                            gap-4
                        "
                    >
                        <div>
                            <p
                                class="
                                    m-0
                                    text-xs
                                    font-semibold
                                    text-slate-500
                                "
                            >
                                {{ $stat['label'] }}
                            </p>

                            <p
                                class="
                                    mb-0
                                    mt-2
                                    text-3xl
                                    font-black
                                    tracking-tight
                                    text-slate-800
                                "
                            >
                                {{ number_format(
                                    $stat['value'],
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </p>
                        </div>

                        <div
                            class="
                                flex
                                h-12
                                w-12
                                flex-shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                text-lg
                                text-white
                                shadow-sm
                                {{ $style['icon'] }}
                            "
                        >
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- ========================================================= --}}
        {{-- FILTER --}}
        {{-- ========================================================= --}}

        <section
            class="
                mt-6
                rounded-2xl
                border
                border-slate-100
                bg-white
                p-5
                shadow-sm
            "
        >
            <form
                action="{{ route('admin.pengguna.index') }}"
                method="GET"
                class="
                    grid
                    grid-cols-1
                    gap-4
                    md:grid-cols-2
                    xl:grid-cols-[190px_190px_minmax(260px,1fr)_auto]
                "
            >
                @if ($hasRoleColumn)
                    <div class="relative">
                        <select
                            name="role"
                            class="
                                h-11
                                w-full
                                appearance-none
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                                px-4
                                pr-10
                                text-sm
                                font-medium
                                text-slate-600
                                outline-none
                                transition
                                focus:border-emerald-500
                                focus:ring-2
                                focus:ring-emerald-100
                            "
                        >
                            <option value="">
                                Semua Role
                            </option>

                            <option
                                value="admin"
                                @selected(request('role') === 'admin')
                            >
                                Admin
                            </option>

                            <option
                                value="operator"
                                @selected(request('role') === 'operator')
                            >
                                Operator
                            </option>

                        </select>

                        <i
                            class="
                                fa-solid
                                fa-chevron-down
                                pointer-events-none
                                absolute
                                right-4
                                top-1/2
                                -translate-y-1/2
                                text-xs
                                text-emerald-600
                            "
                        ></i>
                    </div>
                @endif

                @if ($hasStatusColumn)
                    <div class="relative">
                        <select
                            name="status"
                            class="
                                h-11
                                w-full
                                appearance-none
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                                px-4
                                pr-10
                                text-sm
                                font-medium
                                text-slate-600
                                outline-none
                                transition
                                focus:border-emerald-500
                                focus:ring-2
                                focus:ring-emerald-100
                            "
                        >
                            <option value="">
                                Semua Status
                            </option>

                            <option
                                value="Aktif"
                                @selected(
                                    strtolower(request('status', ''))
                                    === 'aktif'
                                )
                            >
                                Aktif
                            </option>

                            <option
                                value="Suspend"
                                @selected(
                                    strtolower(request('status', ''))
                                    === 'suspend'
                                )
                            >
                                Suspend
                            </option>
                        </select>

                        <i
                            class="
                                fa-solid
                                fa-chevron-down
                                pointer-events-none
                                absolute
                                right-4
                                top-1/2
                                -translate-y-1/2
                                text-xs
                                text-emerald-600
                            "
                        ></i>
                    </div>
                @endif

                <div class="relative">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, email, role, atau status..."
                        class="
                            h-11
                            w-full
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            px-4
                            pr-11
                            text-sm
                            text-slate-700
                            outline-none
                            transition
                            placeholder:text-slate-400
                            focus:border-emerald-500
                            focus:ring-2
                            focus:ring-emerald-100
                        "
                    >

                    <button
                        type="submit"
                        aria-label="Cari pengguna"
                        class="
                            absolute
                            right-1.5
                            top-1/2
                            flex
                            h-8
                            w-8
                            -translate-y-1/2
                            items-center
                            justify-center
                            rounded-lg
                            bg-emerald-50
                            text-sm
                            text-emerald-600
                            transition
                            hover:bg-emerald-100
                        "
                    >
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>

                <div class="flex gap-2">
                    <button
                        type="submit"
                        class="
                            inline-flex
                            h-11
                            flex-1
                            items-center
                            justify-center
                            gap-2
                            rounded-xl
                            bg-emerald-600
                            px-4
                            text-sm
                            font-semibold
                            text-white
                            transition
                            hover:bg-emerald-700
                        "
                    >
                        <i class="fa-solid fa-filter"></i>
                        Terapkan
                    </button>

                    <a
                        href="{{ route('admin.pengguna.index') }}"
                        title="Reset filter"
                        class="
                            inline-flex
                            h-11
                            w-11
                            flex-shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            text-slate-500
                            transition
                            hover:bg-slate-50
                            hover:text-emerald-600
                        "
                    >
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </section>

        {{-- ========================================================= --}}
        {{-- TABEL --}}
        {{-- ========================================================= --}}

        <section
            class="
                mt-6
                overflow-hidden
                rounded-2xl
                border
                border-slate-100
                bg-white
                shadow-sm
            "
            style="opacity: 1 !important; transform: none !important;"
        >
            <header
                class="
                    flex
                    flex-col
                    justify-between
                    gap-3
                    border-b
                    border-slate-100
                    bg-slate-50/50
                    p-5
                    sm:flex-row
                    sm:items-center
                "
            >
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Daftar Pengguna
                    </h2>

                    <p class="mt-1 text-xs text-slate-500">
                        Data akun yang terdaftar melalui sistem.
                    </p>
                </div>

                <div
                    class="
                        inline-flex
                        w-fit
                        items-center
                        gap-2
                        rounded-full
                        border
                        border-emerald-200
                        bg-emerald-50
                        px-3
                        py-1.5
                        text-xs
                        font-semibold
                        text-emerald-700
                    "
                >
                    <i class="fa-solid fa-database"></i>

                    {{ number_format(
                        $pengguna->total(),
                        0,
                        ',',
                        '.'
                    ) }}
                    Data
                </div>
            </header>

            <div class="overflow-x-auto">
                <table
                    class="
                        w-full
                        min-w-[980px]
                        border-collapse
                        text-left
                    "
                >
                    <thead>
                        <tr
                            class="
                                border-b
                                border-slate-200
                                bg-slate-50
                                text-xs
                                font-semibold
                                uppercase
                                tracking-wide
                                text-slate-500
                            "
                        >
                            <th class="px-5 py-3">
                                No.
                            </th>

                            <th class="px-5 py-3">
                                Pengguna
                            </th>

                            <th class="px-5 py-3">
                                Role
                            </th>

                            <th class="px-5 py-3">
                                Wilayah Kerja (Scope)
                            </th>

                            <th class="px-5 py-3">
                                Status
                            </th>

                            <th class="px-5 py-3">
                                Terdaftar
                            </th>

                            <th class="px-5 py-3 text-center">
                                Aksi
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-sm">
                        @forelse ($pengguna as $item)
                            @php
                                $nomor =
                                    ($pengguna->currentPage() - 1)
                                    * $pengguna->perPage()
                                    + $loop->iteration;

                                $role = $hasRoleColumn
                                    ? strtolower(trim($item->role ?? 'user'))
                                    : 'user';

                                $status = $hasStatusColumn
                                    ? trim($item->status ?? 'Aktif')
                                    : 'Aktif';

                                $statusLower = strtolower($status);

                                $initials = collect(
                                    preg_split('/\s+/', trim($item->name))
                                )
                                    ->filter()
                                    ->take(2)
                                    ->map(
                                        fn ($word) => strtoupper(
                                            mb_substr($word, 0, 1)
                                        )
                                    )
                                    ->implode('');

                                $roleBadge = match ($role) {
                                    'admin' =>
                                        'border-amber-200 bg-amber-50 text-amber-700',

                                    'operator' =>
                                        'border-sky-200 bg-sky-50 text-sky-700',

                                    default =>
                                        'border-emerald-200 bg-emerald-50 text-emerald-700',
                                };
                            @endphp

                            <tr
                                class="
                                    transition-colors
                                    hover:bg-slate-50/60
                                "
                            >
                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-4
                                        text-slate-500
                                    "
                                >
                                    {{ str_pad(
                                        $nomor,
                                        2,
                                        '0',
                                        STR_PAD_LEFT
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="
                                                flex
                                                h-10
                                                w-10
                                                flex-shrink-0
                                                items-center
                                                justify-center
                                                rounded-xl
                                                bg-gradient-to-br
                                                from-emerald-600
                                                to-emerald-400
                                                text-sm
                                                font-bold
                                                text-white
                                                shadow-sm
                                            "
                                        >
                                            {{ $initials ?: 'U' }}
                                        </div>

                                        <div class="min-w-0">
                                            <p
                                                class="
                                                    m-0
                                                    truncate
                                                    font-semibold
                                                    text-slate-700
                                                "
                                            >
                                                {{ $item->name }}
                                            </p>

                                            <p
                                                class="
                                                    mb-0
                                                    mt-1
                                                    truncate
                                                    text-xs
                                                    text-slate-400
                                                "
                                            >
                                                {{ $item->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="
                                            inline-flex
                                            items-center
                                            rounded-full
                                            border
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-semibold
                                            {{ $roleBadge }}
                                        "
                                    >
                                        {{ ucfirst($role) }}
                                    </span>
                                </td>

                                <td class="px-5 py-4">
                                    @php
                                        $scope = (!empty($hasScopesTable) && $item->relationLoaded('wilayahScopes'))
                                            ? $item->wilayahScopes->first()
                                            : (!empty($hasScopesTable) ? $item->wilayahScopes->first() : null);
                                        $isPending = strtolower($item->status ?? '') === 'pending';
                                    @endphp
                                    @if ($role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border border-amber-300 bg-amber-50 text-amber-800">
                                            <i class="fa-solid fa-earth-asia text-[10px]"></i> Global (Semua Wilayah)
                                        </span>
                                    @elseif ($scope)
                                        @if ($scope->kabupaten)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isPending ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-sky-300 bg-sky-50 text-sky-800' }}" title="{{ $scope->kabupaten->nama_kabupaten }}">
                                                <i class="fa-solid {{ $isPending ? 'fa-hourglass-half' : 'fa-location-dot' }} text-[10px]"></i>
                                                {{ $isPending ? 'Pengajuan' : 'Scope' }} Kab: {{ $scope->kabupaten->nama_kabupaten }}
                                            </span>
                                        @elseif ($scope->provinsi)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isPending ? 'border-amber-300 bg-amber-50 text-amber-800' : 'border-emerald-300 bg-emerald-50 text-emerald-800' }}" title="{{ $scope->provinsi->nama_provinsi }}">
                                                <i class="fa-solid {{ $isPending ? 'fa-hourglass-half' : 'fa-map' }} text-[10px]"></i>
                                                {{ $isPending ? 'Pengajuan' : 'Scope' }} Prov: {{ $scope->provinsi->nama_provinsi }} (+ Sub-Kab)
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border border-slate-200 bg-slate-100 text-slate-500">
                                                Belum Set
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border border-slate-200 bg-slate-100 text-slate-500">
                                            Belum Set
                                        </span>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="
                                            inline-flex
                                            items-center
                                            gap-1.5
                                            rounded-full
                                            border
                                            px-2.5
                                            py-1
                                            text-xs
                                            font-semibold

                                            @php
                                                $badgeClass = match($statusLower) {
                                                    'approved', 'aktif' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                                                    'pending' => 'border-amber-200 bg-amber-50 text-amber-700',
                                                    'rejected' => 'border-red-200 bg-red-50 text-red-700',
                                                    'nonactive', 'suspend' => 'border-slate-300 bg-slate-100 text-slate-700',
                                                    default => 'border-slate-200 bg-slate-50 text-slate-600',
                                                };
                                                $dotClass = match($statusLower) {
                                                    'approved', 'aktif' => 'bg-emerald-500',
                                                    'pending' => 'bg-amber-500',
                                                    'rejected' => 'bg-red-500',
                                                    'nonactive', 'suspend' => 'bg-slate-500',
                                                    default => 'bg-slate-400',
                                                };
                                                $statusLabel = match($statusLower) {
                                                    'approved', 'aktif' => 'Approved',
                                                    'pending' => 'Pending',
                                                    'rejected' => 'Rejected',
                                                    'nonactive', 'suspend' => 'Nonactive',
                                                    default => ucfirst($statusLower),
                                                };
                                            @endphp
                                            {{ $badgeClass }}
                                        "
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full {{ $dotClass }}"></span>
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td
                                    class="
                                        whitespace-nowrap
                                        px-5
                                        py-4
                                        text-slate-500
                                    "
                                >
                                    <div class="font-medium text-slate-600">
                                        {{
                                            $item->created_at
                                                ? $item->created_at
                                                    ->translatedFormat('d M Y')
                                                : '-'
                                        }}
                                    </div>

                                    @if ($item->created_at)
                                        <div class="mt-1 text-xs text-slate-400">
                                            {{ $item->created_at->format('H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="
                                            flex
                                            items-center
                                            justify-center
                                            gap-2
                                        "
                                    >
                                        <a
                                            href="{{ route(
                                                'admin.pengguna.index',
                                                array_merge(
                                                    request()->query(),
                                                    [
                                                        'edit' => $item->id,
                                                        'mode' => 'edit',
                                                    ]
                                                )
                                            ) }}"
                                            title="Edit pengguna"
                                            class="
                                                inline-flex
                                                h-9
                                                w-9
                                                items-center
                                                justify-center
                                                rounded-lg
                                                border
                                                border-slate-200
                                                bg-white
                                                text-slate-500
                                                transition
                                                hover:border-emerald-200
                                                hover:bg-emerald-50
                                                hover:text-emerald-600
                                            "
                                        >
                                            <i
                                                class="
                                                    fa-regular
                                                    fa-pen-to-square
                                                "
                                            ></i>
                                        </a>

                                        <button
                                            type="button"
                                            title="Hapus pengguna"
                                            data-delete-user
                                            data-delete-url="{{ route(
                                                'admin.pengguna.destroy',
                                                $item->id
                                            ) }}"
                                            data-delete-name="{{ $item->name }}"
                                            class="
                                                inline-flex
                                                h-9
                                                w-9
                                                items-center
                                                justify-center
                                                rounded-lg
                                                border
                                                border-red-100
                                                bg-white
                                                text-red-500
                                                transition
                                                hover:border-red-200
                                                hover:bg-red-50
                                                hover:text-red-600
                                            "
                                        >
                                            <i
                                                class="
                                                    fa-regular
                                                    fa-trash-can
                                                "
                                            ></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="
                                        px-5
                                        py-14
                                        text-center
                                    "
                                >
                                    <div
                                        class="
                                            mx-auto
                                            flex
                                            max-w-sm
                                            flex-col
                                            items-center
                                        "
                                    >
                                        <div
                                            class="
                                                flex
                                                h-14
                                                w-14
                                                items-center
                                                justify-center
                                                rounded-2xl
                                                bg-slate-100
                                                text-xl
                                                text-slate-400
                                            "
                                        >
                                            <i class="fa-solid fa-users-slash"></i>
                                        </div>

                                        <h3
                                            class="
                                                mb-0
                                                mt-4
                                                text-sm
                                                font-semibold
                                                text-slate-700
                                            "
                                        >
                                            Data pengguna tidak ditemukan
                                        </h3>

                                        <p
                                            class="
                                                mb-0
                                                mt-1
                                                text-xs
                                                leading-relaxed
                                                text-slate-400
                                            "
                                        >
                                            Coba ubah filter atau kata pencarian
                                            yang digunakan.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- PAGINATION --}}
        {{-- ========================================================= --}}

        @if ($pengguna->hasPages())
            <section
                class="
                    mt-5
                    flex
                    flex-col
                    justify-between
                    gap-4
                    sm:flex-row
                    sm:items-center
                "
            >
                <p class="m-0 text-sm text-slate-500">
                    Menampilkan
                    <span class="font-semibold text-slate-700">
                        {{ $pengguna->firstItem() }}
                    </span>

                    sampai

                    <span class="font-semibold text-slate-700">
                        {{ $pengguna->lastItem() }}
                    </span>

                    dari

                    <span class="font-semibold text-slate-700">
                        {{ $pengguna->total() }}
                    </span>

                    pengguna
                </p>

                <div class="flex flex-wrap items-center gap-2">
                    @if ($pengguna->onFirstPage())
                        <span
                            class="
                                inline-flex
                                h-9
                                items-center
                                gap-2
                                rounded-lg
                                border
                                border-slate-200
                                bg-slate-100
                                px-3
                                text-xs
                                font-semibold
                                text-slate-400
                            "
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                            Prev
                        </span>
                    @else
                        <a
                            href="{{ $pengguna->previousPageUrl() }}"
                            class="
                                inline-flex
                                h-9
                                items-center
                                gap-2
                                rounded-lg
                                border
                                border-slate-200
                                bg-white
                                px-3
                                text-xs
                                font-semibold
                                text-slate-600
                                transition
                                hover:bg-slate-50
                            "
                        >
                            <i class="fa-solid fa-chevron-left"></i>
                            Prev
                        </a>
                    @endif

                    @foreach ($pengguna->onEachSide(1)->links()->elements as $element)
                        @if (is_string($element))
                            <span
                                class="
                                    inline-flex
                                    h-9
                                    min-w-9
                                    items-center
                                    justify-center
                                    text-xs
                                    text-slate-400
                                "
                            >
                                ...
                            </span>
                        @endif

                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page === $pengguna->currentPage())
                                    <span
                                        class="
                                            inline-flex
                                            h-9
                                            min-w-9
                                            items-center
                                            justify-center
                                            rounded-lg
                                            border
                                            border-[#FFD54F]
                                            bg-[#FFD54F]
                                            px-3
                                            text-xs
                                            font-bold
                                            text-emerald-900
                                        "
                                    >
                                        {{ $page }}
                                    </span>
                                @else
                                    <a
                                        href="{{ $url }}"
                                        class="
                                            inline-flex
                                            h-9
                                            min-w-9
                                            items-center
                                            justify-center
                                            rounded-lg
                                            border
                                            border-slate-200
                                            bg-white
                                            px-3
                                            text-xs
                                            font-semibold
                                            text-slate-600
                                            transition
                                            hover:bg-slate-50
                                        "
                                    >
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($pengguna->hasMorePages())
                        <a
                            href="{{ $pengguna->nextPageUrl() }}"
                            class="
                                inline-flex
                                h-9
                                items-center
                                gap-2
                                rounded-lg
                                border
                                border-slate-200
                                bg-white
                                px-3
                                text-xs
                                font-semibold
                                text-slate-600
                                transition
                                hover:bg-slate-50
                            "
                        >
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span
                            class="
                                inline-flex
                                h-9
                                items-center
                                gap-2
                                rounded-lg
                                border
                                border-slate-200
                                bg-slate-100
                                px-3
                                text-xs
                                font-semibold
                                text-slate-400
                            "
                        >
                            Next
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </section>
        @endif

        <footer
            class="
                pb-1
                pt-8
                text-center
                text-xs
                text-slate-400
            "
        >
            Copyright &copy;
            {{ date('Y') }}
            DPMPTSP Provinsi Sumatera Utara
        </footer>
    </div>

    {{-- ============================================================= --}}
    {{-- MODAL TAMBAH / EDIT --}}
    {{-- ============================================================= --}}

    @if ($isModalOpen)
        <div
            id="userFormModal"
            class="
                fixed
                inset-0
                z-[999]
                flex
                items-center
                justify-center
                overflow-y-auto
                bg-slate-900/50
                p-4
                backdrop-blur-sm
            "
        >
            <div
                class="
                    my-6
                    w-full
                    max-w-3xl
                    overflow-hidden
                    rounded-2xl
                    border
                    border-slate-100
                    bg-white
                    shadow-2xl
                "
            >
                <header
                    class="
                        flex
                        items-start
                        justify-between
                        gap-5
                        border-b
                        border-slate-100
                        bg-slate-50/50
                        p-6
                    "
                >
                    <div>
                        <div
                            class="
                                mb-3
                                inline-flex
                                h-10
                                w-10
                                items-center
                                justify-center
                                rounded-xl
                                bg-emerald-100
                                text-emerald-600
                            "
                        >
                            <i
                                class="
                                    fa-solid
                                    {{ $isEdit ? 'fa-user-pen' : 'fa-user-plus' }}
                                "
                            ></i>
                        </div>

                        <h2
                            class="
                                m-0
                                text-xl
                                font-bold
                                text-slate-800
                            "
                        >
                            {{
                                $isEdit
                                    ? 'Edit Pengguna'
                                    : 'Tambah Pengguna'
                            }}
                        </h2>

                        <p
                            class="
                                mb-0
                                mt-1
                                text-sm
                                text-slate-500
                            "
                        >
                            {{
                                $isEdit
                                    ? 'Perbarui informasi akun pengguna.'
                                    : 'Tambahkan akun pengguna baru ke dalam sistem.'
                            }}
                        </p>
                    </div>

                    <a
                        href="{{ route(
                            'admin.pengguna.index',
                            request()->except(['mode', 'edit'])
                        ) }}"
                        class="
                            inline-flex
                            h-10
                            w-10
                            flex-shrink-0
                            items-center
                            justify-center
                            rounded-xl
                            border
                            border-slate-200
                            bg-white
                            text-slate-500
                            transition
                            hover:bg-slate-100
                        "
                    >
                        <i class="fa-solid fa-xmark"></i>
                    </a>
                </header>

                <form
                    action="{{ $formAction }}"
                    method="POST"
                    class="p-6"
                >
                    @csrf

                    @if ($isEdit)
                        @method('PUT')
                    @endif

                    @php
                        $editScope = (!empty($hasScopesTable) && $isEdit) ? $editData->wilayahScopes->first() : null;
                        $initialScopeType = 'none';
                        $initialProvId = '';
                        $initialKabId = '';
                        if ($editScope) {
                            if ($editScope->kabupaten_id) {
                                $initialScopeType = 'kabupaten';
                                $initialKabId = $editScope->kabupaten_id;
                                $initialProvId = $editScope->provinsi_id;
                            } elseif ($editScope->provinsi_id) {
                                $initialScopeType = 'provinsi';
                                $initialProvId = $editScope->provinsi_id;
                            }
                        }
                    @endphp

                    <div
                        x-data="{
                            role: '{{ old('role', $isEdit ? $editData->role : 'operator') }}',
                            scopeType: '{{ old('scope_type', $initialScopeType) }}'
                        }"
                        class="
                            grid
                            grid-cols-1
                            gap-5
                            md:grid-cols-2
                        "
                    >
                        <div>
                            <label
                                for="name"
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                "
                            >
                                Nama Pengguna
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="name"
                                type="text"
                                name="name"
                                value="{{ old(
                                    'name',
                                    $isEdit ? $editData->name : ''
                                ) }}"
                                required
                                autocomplete="name"
                                placeholder="Masukkan nama pengguna"
                                class="
                                    h-11
                                    w-full
                                    rounded-xl
                                    border
                                    border-slate-200
                                    px-4
                                    text-sm
                                    text-slate-700
                                    outline-none
                                    transition
                                    focus:border-emerald-500
                                    focus:ring-2
                                    focus:ring-emerald-100
                                "
                            >

                            @error('name')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="email"
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                "
                            >
                                Email
                                <span class="text-red-500">*</span>
                            </label>

                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old(
                                    'email',
                                    $isEdit ? $editData->email : ''
                                ) }}"
                                required
                                autocomplete="email"
                                placeholder="nama@email.com"
                                class="
                                    h-11
                                    w-full
                                    rounded-xl
                                    border
                                    border-slate-200
                                    px-4
                                    text-sm
                                    text-slate-700
                                    outline-none
                                    transition
                                    focus:border-emerald-500
                                    focus:ring-2
                                    focus:ring-emerald-100
                                "
                            >

                            @error('email')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        @if ($hasRoleColumn)
                            <div>
                                <label
                                    for="role"
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                    "
                                >
                                    Role
                                    <span class="text-red-500">*</span>
                                </label>

                                @php
                                    $selectedRole = old(
                                        'role',
                                        $isEdit
                                            ? $editData->role
                                            : 'operator'
                                    );
                                @endphp

                                <select
                                    id="role"
                                    name="role"
                                    x-model="role"
                                    required
                                    class="
                                        h-11
                                        w-full
                                        rounded-xl
                                        border
                                        border-slate-200
                                        bg-white
                                        px-4
                                        text-sm
                                        text-slate-700
                                        outline-none
                                        transition
                                        focus:border-emerald-500
                                        focus:ring-2
                                        focus:ring-emerald-100
                                    "
                                >
                                    <option
                                        value="admin"
                                        @selected($selectedRole === 'admin')
                                    >
                                        Admin
                                    </option>

                                    <option
                                        value="operator"
                                        @selected($selectedRole === 'operator')
                                    >
                                        Operator
                                    </option>
                                </select>

                                @error('role')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        @if ($hasStatusColumn)
                            <div>
                                <label
                                    for="status"
                                    class="
                                        mb-2
                                        block
                                        text-sm
                                        font-semibold
                                        text-slate-700
                                    "
                                >
                                    Status
                                    <span class="text-red-500">*</span>
                                </label>

                                @php
                                    $selectedStatus = old(
                                        'status',
                                        $isEdit
                                            ? $editData->status
                                            : 'Aktif'
                                    );
                                @endphp

                                <select
                                    id="status"
                                    name="status"
                                    required
                                    class="
                                        h-11
                                        w-full
                                        rounded-xl
                                        border
                                        border-slate-200
                                        bg-white
                                        px-4
                                        text-sm
                                        text-slate-700
                                        outline-none
                                        transition
                                        focus:border-emerald-500
                                        focus:ring-2
                                        focus:ring-emerald-100
                                    "
                                >
                                    <option value="approved" @selected(in_array(strtolower($selectedStatus), ['approved', 'aktif']))>
                                        Approved (Disetujui / Aktif)
                                    </option>
                                    <option value="pending" @selected(strtolower($selectedStatus) === 'pending')>
                                        Pending (Menunggu Verifikasi)
                                    </option>
                                    <option value="rejected" @selected(strtolower($selectedStatus) === 'rejected')>
                                        Rejected (Ditolak)
                                    </option>
                                    <option value="nonactive" @selected(in_array(strtolower($selectedStatus), ['nonactive', 'suspend']))>
                                        Nonactive (Non-Aktif)
                                    </option>
                                </select>

                                @error('status')
                                    <p class="mb-0 mt-1.5 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        @endif

                        {{-- REGIONAL SCOPE ASSIGNMENT --}}
                        <div x-show="role === 'operator'" class="md:col-span-2 p-4 rounded-2xl bg-[#EEF8F2] border border-[#CFE3D5] space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold text-[#1E5E3F] uppercase tracking-wider">
                                    Alokasi Wilayah Kerja Operator (Regional Scope Authorization)
                                </label>
                            </div>

                            @if ($editScope)
                                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200/80 text-amber-900 text-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <i class="fa-solid fa-bell-concierge text-amber-600 text-sm"></i>
                                        <div>
                                            <span class="font-bold">Pengajuan Wilayah Saat Registrasi:</span>
                                            @if ($editScope->kabupaten)
                                                <span class="font-bold text-sky-900 bg-white px-2 py-0.5 rounded ml-1 border border-sky-200">
                                                    📍 Kab/Kota: {{ $editScope->kabupaten->nama_kabupaten }}
                                                </span>
                                            @elseif ($editScope->provinsi)
                                                <span class="font-bold text-emerald-900 bg-white px-2 py-0.5 rounded ml-1 border border-emerald-200">
                                                    🗺️ Provinsi: {{ $editScope->provinsi->nama_provinsi }} (+ Sub-Kab)
                                                </span>
                                            @else
                                                <span class="text-slate-500 ml-1">(Belum Memilih Scope)</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <p class="text-xs text-slate-600">
                                Cek & sesuaikan alokasi wilayah kerja data PDRB yang diberikan kepada Operator ini:
                            </p>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tingkat Wilayah Scope</label>
                                    <select name="scope_type" x-model="scopeType" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                        <option value="none">-- Belum Dialokasikan --</option>
                                        <option value="provinsi">Tingkat Provinsi (+ Sub-Kabupaten)</option>
                                        <option value="kabupaten">Tingkat Kabupaten / Kota Spesifik</option>
                                    </select>
                                </div>

                                <div x-show="scopeType === 'provinsi' || scopeType === 'kabupaten'">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Provinsi Scope</label>
                                    <select name="provinsi_id" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                        <option value="">-- Pilih Provinsi --</option>
                                        @foreach($provinsis ?? [] as $prov)
                                            <option value="{{ $prov->provinsi_id }}" @selected(old('provinsi_id', $initialProvId) == $prov->provinsi_id)>
                                                {{ $prov->nama_provinsi }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div x-show="scopeType === 'kabupaten'">
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Kabupaten / Kota Scope</label>
                                    <select name="kabupaten_id" class="h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-700 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
                                        <option value="">-- Pilih Kabupaten / Kota --</option>
                                        @foreach($kabupatens ?? [] as $kab)
                                            <option value="{{ $kab->kab_id }}" @selected(old('kabupaten_id', $initialKabId) == $kab->kab_id)>
                                                {{ $kab->nama_kabupaten }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                "
                            >
                                {{
                                    $isEdit
                                        ? 'Password Baru'
                                        : 'Password'
                                }}

                                @if (! $isEdit)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            <div class="relative">
                                <input
                                    id="password"
                                    type="password"
                                    name="password"
                                    autocomplete="new-password"
                                    placeholder="{{
                                        $isEdit
                                            ? 'Kosongkan jika tidak diganti'
                                            : 'Minimal 8 karakter'
                                    }}"
                                    @required(! $isEdit)
                                    class="
                                        h-11
                                        w-full
                                        rounded-xl
                                        border
                                        border-slate-200
                                        px-4
                                        pr-11
                                        text-sm
                                        text-slate-700
                                        outline-none
                                        transition
                                        focus:border-emerald-500
                                        focus:ring-2
                                        focus:ring-emerald-100
                                    "
                                >

                                <button
                                    type="button"
                                    data-toggle-password="password"
                                    class="
                                        absolute
                                        right-3
                                        top-1/2
                                        -translate-y-1/2
                                        text-slate-400
                                        hover:text-emerald-600
                                    "
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>

                            @error('password')
                                <p class="mb-0 mt-1.5 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="password_confirmation"
                                class="
                                    mb-2
                                    block
                                    text-sm
                                    font-semibold
                                    text-slate-700
                                "
                            >
                                Konfirmasi Password

                                @if (! $isEdit)
                                    <span class="text-red-500">*</span>
                                @endif
                            </label>

                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    name="password_confirmation"
                                    autocomplete="new-password"
                                    placeholder="Ulangi password"
                                    @required(! $isEdit)
                                    class="
                                        h-11
                                        w-full
                                        rounded-xl
                                        border
                                        border-slate-200
                                        px-4
                                        pr-11
                                        text-sm
                                        text-slate-700
                                        outline-none
                                        transition
                                        focus:border-emerald-500
                                        focus:ring-2
                                        focus:ring-emerald-100
                                    "
                                >

                                <button
                                    type="button"
                                    data-toggle-password="password_confirmation"
                                    class="
                                        absolute
                                        right-3
                                        top-1/2
                                        -translate-y-1/2
                                        text-slate-400
                                        hover:text-emerald-600
                                    "
                                >
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div
                        class="
                            mt-7
                            flex
                            flex-col-reverse
                            justify-end
                            gap-3
                            border-t
                            border-slate-100
                            pt-5
                            sm:flex-row
                        "
                    >
                        <a
                            href="{{ route(
                                'admin.pengguna.index',
                                request()->except(['mode', 'edit'])
                            ) }}"
                            class="
                                inline-flex
                                h-11
                                items-center
                                justify-center
                                rounded-xl
                                border
                                border-slate-200
                                bg-white
                                px-5
                                text-sm
                                font-semibold
                                text-slate-600
                                transition
                                hover:bg-slate-50
                            "
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="
                                inline-flex
                                h-11
                                items-center
                                justify-center
                                gap-2
                                rounded-xl
                                bg-emerald-600
                                px-5
                                text-sm
                                font-semibold
                                text-white
                                shadow-sm
                                transition
                                hover:bg-emerald-700
                            "
                        >
                            <i class="fa-solid fa-floppy-disk"></i>

                            {{
                                $isEdit
                                    ? 'Simpan Perubahan'
                                    : 'Tambah Pengguna'
                            }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============================================================= --}}
    {{-- MODAL HAPUS --}}
    {{-- ============================================================= --}}

    <div
        id="deleteUserModal"
        class="
            fixed
            inset-0
            z-[1000]
            hidden
            items-center
            justify-center
            bg-slate-900/50
            p-4
            backdrop-blur-sm
        "
    >
        <div
            class="
                w-full
                max-w-md
                rounded-2xl
                border
                border-slate-100
                bg-white
                p-6
                text-center
                shadow-2xl
            "
        >
            <div
                class="
                    mx-auto
                    flex
                    h-16
                    w-16
                    items-center
                    justify-center
                    rounded-2xl
                    bg-red-100
                    text-2xl
                    text-red-600
                "
            >
                <i class="fa-regular fa-trash-can"></i>
            </div>

            <h3
                class="
                    mb-0
                    mt-5
                    text-xl
                    font-bold
                    text-slate-800
                "
            >
                Hapus pengguna?
            </h3>

            <p
                class="
                    mb-0
                    mt-2
                    text-sm
                    leading-relaxed
                    text-slate-500
                "
            >
                Pengguna
                <strong
                    id="deleteUserName"
                    class="text-slate-700"
                ></strong>
                akan dihapus dari sistem. Tindakan ini tidak dapat dibatalkan.
            </p>

            <form
                id="deleteUserForm"
                action=""
                method="POST"
                class="
                    mt-6
                    flex
                    flex-col-reverse
                    gap-3
                    sm:flex-row
                    sm:justify-center
                "
            >
                @csrf
                @method('DELETE')

                <button
                    type="button"
                    id="cancelDeleteUser"
                    class="
                        inline-flex
                        h-11
                        min-w-32
                        items-center
                        justify-center
                        rounded-xl
                        border
                        border-slate-200
                        bg-white
                        px-5
                        text-sm
                        font-semibold
                        text-slate-600
                        transition
                        hover:bg-slate-50
                    "
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="
                        inline-flex
                        h-11
                        min-w-32
                        items-center
                        justify-center
                        gap-2
                        rounded-xl
                        bg-red-600
                        px-5
                        text-sm
                        font-semibold
                        text-white
                        transition
                        hover:bg-red-700
                    "
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
                const deleteModal = document.getElementById(
                    'deleteUserModal'
                );

                const deleteForm = document.getElementById(
                    'deleteUserForm'
                );

                const deleteName = document.getElementById(
                    'deleteUserName'
                );

                const cancelDelete = document.getElementById(
                    'cancelDeleteUser'
                );

                const deleteButtons = document.querySelectorAll(
                    '[data-delete-user]'
                );

                function openDeleteModal(url, name) {
                    if (!deleteModal || !deleteForm || !deleteName) {
                        return;
                    }

                    deleteForm.action = url;
                    deleteName.textContent = name;

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

                document.addEventListener(
                    'keydown',
                    function (event) {
                        if (event.key === 'Escape') {
                            closeDeleteModal();
                        }
                    }
                );

                const passwordButtons = document.querySelectorAll(
                    '[data-toggle-password]'
                );

                passwordButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        const inputId =
                            button.dataset.togglePassword;

                        const input = document.getElementById(
                            inputId
                        );

                        const icon = button.querySelector('i');

                        if (!input || !icon) {
                            return;
                        }

                        const isPassword =
                            input.type === 'password';

                        input.type = isPassword
                            ? 'text'
                            : 'password';

                        icon.classList.toggle(
                            'fa-eye',
                            !isPassword
                        );

                        icon.classList.toggle(
                            'fa-eye-slash',
                            isPassword
                        );
                    });
                });
            });
        </script>
    @endpush
@endsection
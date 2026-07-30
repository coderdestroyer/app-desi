@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@section('content')
    @php
        /*
        |--------------------------------------------------------------------------
        | Mapping tampilan kartu
        |--------------------------------------------------------------------------
        |
        | Mapping dibuat statis supaya class Tailwind tetap terbaca oleh Vite.
        |
        */
        $statStyles = [
            'green' => [
                'corner' => 'bg-emerald-50',
                'icon' => 'bg-emerald-600',
            ],
            'purple' => [
                'corner' => 'bg-indigo-50',
                'icon' => 'bg-indigo-600',
            ],
            'blue' => [
                'corner' => 'bg-sky-50',
                'icon' => 'bg-sky-600',
            ],
            'teal' => [
                'corner' => 'bg-emerald-50',
                'icon' => 'bg-emerald-600',
            ],
            'cyan' => [
                'corner' => 'bg-cyan-50',
                'icon' => 'bg-cyan-600',
            ],
            'orange' => [
                'corner' => 'bg-orange-50',
                'icon' => 'bg-orange-500',
            ],
        ];
    @endphp

    <div class="min-h-screen bg-slate-50 p-5 md:p-7 lg:p-8">

        {{-- ========================================================= --}}
        {{-- HERO DASHBOARD --}}
        {{-- ========================================================= --}}

        <section
            class="
                relative
                flex
                flex-col
                items-center
                justify-between
                gap-6
                overflow-hidden
                rounded-2xl
                bg-gradient-to-r
                from-[#145239]
                via-[#0F8A5F]
                to-[#1E5D41]
                p-8
                pb-16
                shadow-xl
                md:flex-row
                md:p-10
                md:pb-24
            "
        >
            {{-- Ornamen background --}}
            <div
                class="
                    absolute
                    right-0
                    top-0
                    h-64
                    w-64
                    animate-pulse
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
                    h-48
                    w-48
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
                    left-1/2
                    top-10
                    h-72
                    w-72
                    rounded-full
                    bg-emerald-500
                    opacity-10
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

            {{-- Konten kiri --}}
            <div class="relative z-10 flex-1 text-white">
                <div
                    class="
                        mb-4
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
                    <svg
                        class="h-4 w-4 text-[#FFD54F]"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z"
                        />
                    </svg>

                    Dashboard Utama
                </div>

                <h1
                    class="
                        mb-3
                        text-3xl
                        font-extrabold
                        tracking-tight
                        md:text-4xl
                    "
                >
                    Selamat Datang,
                    <span class="text-[#FFD54F]">
                        Admin
                    </span>
                </h1>

                <p
                    class="
                        max-w-xl
                        text-sm
                        font-medium
                        leading-relaxed
                        text-emerald-100/90
                    "
                >
                    Kelola dan pantau data pengguna, wilayah, KBLI, KBKI,
                    serta HS Code DPMPTSP Provinsi Sumatera Utara.
                </p>
            </div>

            {{-- Tanggal kanan --}}
            <div
                class="
                    relative
                    z-10
                    mt-4
                    flex
                    items-center
                    md:mt-0
                "
            >
                <div
                    class="
                        flex
                        flex-col
                        items-end
                        justify-center
                    "
                >
                    <span
                        class="
                            text-2xl
                            font-black
                            tracking-tight
                            text-white
                            drop-shadow-md
                            md:text-3xl
                        "
                    >
                        {{ now()->locale('id')->translatedFormat('d F Y') }}
                    </span>

                    <span
                        class="
                            mt-1
                            text-xs
                            font-semibold
                            uppercase
                            tracking-wider
                            text-[#FFD54F]
                        "
                    >
                        Tanggal Hari Ini
                    </span>
                </div>
            </div>
        </section>

        {{-- ========================================================= --}}
        {{-- KARTU STATISTIK --}}
        {{-- ========================================================= --}}

        <section
            class="
                relative
                z-20
                -mt-10
                grid
                grid-cols-1
                gap-4
                px-2
                sm:grid-cols-2
                md:-mt-14
                md:px-4
                xl:grid-cols-3
                xl:gap-5
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
                        min-h-[132px]
                        overflow-hidden
                        rounded-2xl
                        border
                        border-emerald-100
                        bg-white
                        p-5
                        shadow-sm
                        transition-all
                        duration-300
                        hover:-translate-y-1
                        hover:shadow-md
                        sm:min-h-[140px]
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
                            h-full
                            items-center
                            justify-between
                            gap-5
                        "
                    >
                        <div class="min-w-0 flex-1">
                            <p
                                class="
                                    m-0
                                    text-sm
                                    font-bold
                                    leading-snug
                                    text-slate-500
                                "
                            >
                                {{ $stat['label'] }}
                            </p>

                            <p
                                class="
                                    m-0
                                    mt-4
                                    whitespace-nowrap
                                    text-3xl
                                    font-black
                                    leading-none
                                    tracking-tight
                                    text-slate-800
                                    tabular-nums
                                    sm:text-[2.15rem]
                                    2xl:text-[2.45rem]
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
                                h-14
                                w-14
                                flex-shrink-0
                                items-center
                                justify-center
                                rounded-xl
                                text-white
                                shadow-sm
                                {{ $style['icon'] }}
                            "
                        >
                            <i
                                class="
                                    fa-solid
                                    {{ $stat['icon'] }}
                                    text-xl
                                "
                            ></i>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        {{-- ========================================================= --}}
        {{-- KONTEN UTAMA --}}
        {{-- ========================================================= --}}

        <section
            class="
                mt-8
                grid
                grid-cols-1
                gap-6
                lg:grid-cols-3
            "
        >
            {{-- Ringkasan data --}}
            <div class="space-y-6 lg:col-span-2">
                <article
                    class="
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-100
                        bg-white
                        shadow-sm
                    "
                >
                    <header
                        class="
                            border-b
                            border-slate-100
                            bg-slate-50/50
                            p-5
                        "
                    >
                        <h2 class="text-lg font-bold text-slate-800">
                            Ringkasan Data
                        </h2>
                    </header>

                    <div class="overflow-x-auto">
                        <table
                            class="
                                w-full
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
                                        text-slate-500
                                    "
                                >
                                    <th class="px-5 py-3">
                                        Jenis Data
                                    </th>

                                    <th class="px-5 py-3">
                                        Data Terakhir
                                    </th>

                                    <th class="px-5 py-3 text-center">
                                        Total Data
                                    </th>

                                    <th class="px-5 py-3 text-center">
                                        Riwayat
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="
                                    divide-y
                                    divide-slate-100
                                    text-sm
                                "
                            >
                                @forelse ($summaryRows as $row)
                                    <tr
                                        class="
                                            transition-colors
                                            hover:bg-slate-50/50
                                        "
                                    >
                                        <td
                                            class="
                                                px-5
                                                py-3.5
                                                font-medium
                                                text-slate-700
                                            "
                                        >
                                            {{ $row['label'] }}
                                        </td>

                                        <td
                                            class="
                                                max-w-[280px]
                                                px-5
                                                py-3.5
                                                text-slate-500
                                            "
                                        >
                                            <span
                                                class="
                                                    block
                                                    truncate
                                                "
                                                title="{{ $row['data_terakhir'] }}"
                                            >
                                                {{ $row['data_terakhir'] ?: '-' }}
                                            </span>
                                        </td>

                                        <td
                                            class="
                                                px-5
                                                py-3.5
                                                text-center
                                                font-semibold
                                                text-slate-600
                                            "
                                        >
                                            {{ number_format(
                                                $row['total'],
                                                0,
                                                ',',
                                                '.'
                                            ) }}
                                        </td>

                                        <td
                                            class="
                                                px-5
                                                py-3.5
                                                text-center
                                            "
                                        >
                                            @if (
                                                $row['total'] > 0 &&
                                                ! empty($row['url'])
                                            )
                                                <a
                                                    href="{{ $row['url'] }}"
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        rounded-full
                                                        border
                                                        border-emerald-200
                                                        bg-emerald-50
                                                        px-2.5
                                                        py-1
                                                        text-xs
                                                        font-medium
                                                        text-emerald-700
                                                        shadow-sm
                                                        transition
                                                        hover:bg-emerald-100
                                                    "
                                                >
                                                    Lihat
                                                </a>
                                            @else
                                                <span
                                                    class="
                                                        inline-flex
                                                        items-center
                                                        rounded-full
                                                        border
                                                        border-slate-200
                                                        bg-slate-100
                                                        px-2.5
                                                        py-1
                                                        text-xs
                                                        font-medium
                                                        text-slate-500
                                                        shadow-sm
                                                    "
                                                >
                                                    Kosong
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="4"
                                            class="
                                                px-5
                                                py-10
                                                text-center
                                                text-sm
                                                text-slate-500
                                            "
                                        >
                                            Belum ada data yang dapat ditampilkan.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>
            </div>

            {{-- Aktivitas terbaru --}}
            <div class="space-y-6">
                <article
                    class="
                        sticky
                        top-6
                        overflow-hidden
                        rounded-2xl
                        border
                        border-slate-100
                        bg-white
                        shadow-sm
                    "
                >
                    <header
                        class="
                            flex
                            items-center
                            justify-between
                            border-b
                            border-slate-100
                            bg-slate-50/50
                            p-5
                        "
                    >
                        <h2 class="text-lg font-bold text-slate-800">
                            Aktivitas Terbaru
                        </h2>

                        <span class="relative flex h-3 w-3">
                            <span
                                class="
                                    absolute
                                    inline-flex
                                    h-full
                                    w-full
                                    animate-ping
                                    rounded-full
                                    bg-emerald-400
                                    opacity-75
                                "
                            ></span>

                            <span
                                class="
                                    relative
                                    inline-flex
                                    h-3
                                    w-3
                                    rounded-full
                                    bg-emerald-500
                                "
                            ></span>
                        </span>
                    </header>

                    <div class="space-y-5 p-5">
                        @forelse ($activities->take(5) as $activity)
                            @php
                                $activityClass = match (
                                    $activity['color'] ?? 'green'
                                ) {
                                    'blue' =>
                                        'bg-sky-100 text-sky-600',

                                    'purple' =>
                                        'bg-purple-100 text-purple-600',

                                    'orange' =>
                                        'bg-orange-100 text-orange-600',

                                    'red' =>
                                        'bg-red-100 text-red-600',

                                    default =>
                                        'bg-emerald-100 text-emerald-600',
                                };
                            @endphp

                            <div
                                class="
                                    group
                                    flex
                                    cursor-default
                                    items-start
                                    gap-4
                                "
                            >
                                <div
                                    class="
                                        mt-0.5
                                        flex
                                        h-9
                                        w-9
                                        flex-shrink-0
                                        items-center
                                        justify-center
                                        rounded-full
                                        transition-transform
                                        group-hover:scale-110
                                        {{ $activityClass }}
                                    "
                                >
                                    <i
                                        class="
                                            fa-solid
                                            {{ $activity['icon'] }}
                                            text-sm
                                        "
                                    ></i>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="
                                            m-0
                                            text-sm
                                            font-medium
                                            leading-snug
                                            text-slate-700
                                        "
                                    >
                                        {{ $activity['aktivitas'] }}
                                    </p>

                                    <p
                                        class="
                                            mb-0
                                            mt-1.5
                                            text-xs
                                            text-slate-400
                                        "
                                    >
                                        {{ $activity['waktu'] }}

                                        <span>&bull;</span>

                                        {{ $activity['category_label'] }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div
                                class="
                                    py-6
                                    text-center
                                    text-sm
                                    text-slate-500
                                "
                            >
                                <svg
                                    class="
                                        mx-auto
                                        mb-2
                                        h-12
                                        w-12
                                        text-slate-300
                                    "
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="1.5"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"
                                    />
                                </svg>

                                Belum ada aktivitas terbaru saat ini.
                            </div>
                        @endforelse
                    </div>

                    <footer
                        class="
                            border-t
                            border-slate-100
                            bg-slate-50/50
                            p-4
                            text-sm
                        "
                    >
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="
                                group
                                flex
                                items-center
                                gap-1
                                font-medium
                                text-emerald-600
                                hover:text-emerald-700
                            "
                        >
                            Lihat Semua Aktivitas

                            <svg
                                class="
                                    h-4
                                    w-4
                                    transform
                                    transition-transform
                                    group-hover:translate-x-1
                                "
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"
                                />
                            </svg>
                        </a>
                    </footer>
                </article>
            </div>
        </section>

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
@endsection
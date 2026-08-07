<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>@yield('title', 'Admin') | DPMPTSP Sumatera Utara</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    {{-- Vite --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

</head>

<body class="font-['Poppins',sans-serif] bg-bg-main text-text-dark min-h-screen m-0 p-0">
    <x-page-loader />

    <div class="flex flex-col lg:flex-row min-h-screen">
        <aside class="w-full lg:w-[290px] bg-white border-b lg:border-b-0 lg:border-r border-border-soft py-6 px-[22px] lg:fixed relative top-0 left-0 lg:bottom-0 lg:overflow-y-auto z-20">
            <div class="flex items-center mb-[26px]">
                <img
                    src="{{ asset('images/logo-dpmptsp.png') }}"
                    alt="Logo DPMPTSP"
                    class="w-[160px] h-auto object-contain block"
                >
            </div>

            <div class="flex items-center gap-3.5 mb-7 py-3 pb-1.5">
                <div class="w-[58px] h-[58px] rounded-full bg-green-dark text-white flex items-center justify-center text-2xl font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <div class="text-[15px] font-bold text-[#101828] leading-[1.3] mb-0.5">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </div>
                    <div class="text-sm font-normal text-text-soft">
                        {{ ucfirst(auth()->user()->role ?? 'admin') }}
                    </div>
                </div>
            </div>

            <div class="text-sm font-bold text-navy mt-[22px] mb-3">Menu Utama</div>
            <ul class="list-none m-0 p-0">
                <li class="mb-1.5">
                    <a
                        href="{{ route('home') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 text-navy hover:bg-green-pale hover:text-green-dark {{ request()->routeIs('home') ? 'bg-[#e5f1e8] text-green-dark' : '' }}"
                    >
                        <i class="fa-solid fa-house w-[22px] text-center text-lg"></i>
                        <span>Beranda</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('home') }}#tentang"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 text-navy hover:bg-green-pale hover:text-green-dark"
                    >
                        <i class="fa-solid fa-circle-info w-[22px] text-center text-lg"></i>
                        <span>Tentang</span>
                    </a>
                </li>
            </ul>

            <div class="text-sm font-bold text-navy mt-[22px] mb-3">Menu Admin</div>
            <ul class="list-none m-0 p-0">
                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/dashboard*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-chart-line w-[22px] text-center text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.pengguna.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/pengguna*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-users w-[22px] text-center text-lg"></i>
                        <span>Pengguna</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.proyek-ipro.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/proyek-ipro*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-file-invoice-dollar w-[22px] text-center text-lg"></i>
                        <span>Dokumen IPRO</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.pdrb.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/pdrb*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-coins w-[22px] text-center text-lg"></i>
                        <span>Data PDRB Daerah</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.pdb-nasional.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/pdb-nasional*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-globe w-[22px] text-center text-lg"></i>
                        <span>Data PDB Nasional</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.data-wilayah.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/data-wilayah*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-map w-[22px] text-center text-lg"></i>
                        <span>Data Wilayah</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.data-kbli.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/data-kbli*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-table-cells w-[22px] text-center text-lg"></i>
                        <span>Kode KBLI</span>
                    </a>
                </li>

                <li class="mb-1.5">
                    <a
                        href="{{ route('admin.hs-code.index') }}"
                        class="flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 {{ request()->is('admin/hs-code*') ? 'bg-[#e5f1e8] text-green-dark' : 'text-navy hover:bg-green-pale hover:text-green-dark' }}"
                    >
                        <i class="fa-solid fa-qrcode w-[22px] text-center text-lg"></i>
                        <span>Kode HS</span>
                    </a>
                    <div class="mt-[22px] pt-[18px] border-t border-[#edf2f7]">
                        <form
                            id="logoutForm"
                            action="{{ route('logout') }}"
                            method="POST"
                            class="m-0"
                        >
                            @csrf

                            <button
                                type="button"
                                id="openLogoutModal"
                                class="w-full flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 text-red-600 hover:bg-red-100 hover:text-red-700 border-0 bg-transparent cursor-pointer text-left"
                            >
                                <i class="fa-solid fa-right-from-bracket w-[22px] text-center text-lg"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </li>

                <li class="mt-8">
                    <form method="POST" action="{{ route('logout') }}" class="m-0 p-0">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3.5 no-underline p-3.5 rounded-2xl text-[15px] font-semibold transition-all duration-200 text-red-700 bg-red-100 border-0 cursor-pointer text-left hover:bg-red-200">
                            <i class="fa-solid fa-arrow-right-from-bracket w-[22px] text-center text-lg"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>

        <main class="lg:ml-[290px] w-full lg:w-[calc(100%-290px)] min-h-screen bg-bg-main">
            @yield('content')
        </main>
    </div>

    <div
        id="logoutConfirmModal"
        class="fixed inset-0 z-[99999] flex items-center justify-center p-6 [&[hidden]]:!hidden"
        hidden
    >
        <div
            class="absolute inset-0 bg-[#0f172a]/[0.45] backdrop-blur-[3px]"
            data-close-logout
        ></div>

        <div class="relative w-[min(430px,100%)] bg-white rounded-[24px] p-[30px_28px] text-center border border-border-soft shadow-[0_30px_80px_rgba(15,23,42,0.24)] animate-modalAppear">
            <div class="w-[72px] h-[72px] mx-auto mb-[18px] rounded-[22px] bg-red-100 text-red-600 flex items-center justify-center text-3xl">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>

            <h3 class="m-0 text-navy text-[22px] font-bold tracking-[-0.02em]">Keluar dari akun?</h3>

            <p class="mt-3 mb-0 text-text-soft text-sm leading-[1.7]">
                Kamu akan keluar dari halaman admin dan perlu login kembali untuk mengakses dashboard.
            </p>

            <div class="mt-[26px] flex justify-center gap-3">
                <button
                    type="button"
                    class="min-w-[130px] h-[46px] rounded-[14px] text-sm font-semibold cursor-pointer border border-[#d9dee8] bg-white text-[#344054] hover:bg-[#f8fafc]"
                    data-close-logout
                >
                    Batal
                </button>

                <button
                    type="button"
                    id="confirmLogoutButton"
                    class="min-w-[130px] h-[46px] rounded-[14px] text-sm font-semibold cursor-pointer border-0 bg-red-600 text-white shadow-[0_12px_28px_rgba(220,38,38,0.22)] hover:bg-red-700"
                >
                    Ya, Keluar
                </button>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const openButton = document.getElementById('openLogoutModal');
        const modal = document.getElementById('logoutConfirmModal');
        const confirmButton = document.getElementById('confirmLogoutButton');
        const logoutForm = document.getElementById('logoutForm');
        const closeButtons = document.querySelectorAll('[data-close-logout]');

        if (!openButton || !modal || !confirmButton || !logoutForm) {
            return;
        }

        openButton.addEventListener('click', function () {
            modal.hidden = false;
        });

        confirmButton.addEventListener('click', function () {
            logoutForm.submit();
        });

        closeButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                modal.hidden = true;
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                modal.hidden = true;
            }
        });
    });
</script>
    @include('partials.live-filter-script')
</body>
</html>
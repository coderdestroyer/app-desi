<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Admin') | DPMPTSP Sumatera Utara
    </title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

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

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])



    @stack('styles')
</head>

<body class="font-['Poppins',sans-serif] text-[#202b3c] bg-[#f7f9fb] min-h-screen m-0">
    <button
        type="button"
        id="mobileSidebarButton"
        class="hidden fixed top-[14px] left-[14px] z-[120] w-[44px] h-[44px] border-0 rounded-[12px] text-white bg-[#08794d] shadow-[0_8px_22px_rgba(5,80,47,0.2)] cursor-pointer max-lg:grid max-lg:place-items-center"
        aria-label="Buka menu"
    >
        <i class="fa-solid fa-bars"></i>
    </button>

    <div
        id="sidebarBackdrop"
        class="hidden fixed inset-0 z-[90] bg-[#0f172a]/[0.46] [&.show]:block"
    ></div>

    <div class="min-h-screen flex">
        <aside
            id="adminSidebar"
            class="fixed inset-y-0 left-0 right-auto z-[100] w-[300px] flex flex-col overflow-y-auto text-white bg-[radial-gradient(circle_at_20%_0%,rgba(255,255,255,0.08),transparent_28%),linear-gradient(180deg,#075735_0%,#087849_48%,#0c8d58_100%)] shadow-[10px_0_30px_rgba(5,66,40,0.12)] [&::-webkit-scrollbar]:w-[6px] [&::-webkit-scrollbar-track]:bg-white/[0.05] [&::-webkit-scrollbar-thumb]:bg-white/[0.2] [&::-webkit-scrollbar-thumb]:rounded-[10px] hover:[&::-webkit-scrollbar-thumb]:bg-white/[0.3] max-lg:w-[280px] max-lg:-translate-x-full max-lg:transition-transform max-lg:duration-[250ms] max-lg:ease-in-out max-lg:[&.open]:translate-x-0 max-[520px]:w-[min(285px,88vw)]"
        >
            <div class="pt-6 px-7 pb-[18px]">
                <img
                    src="{{ asset('images/logo-dpmptsp.png') }}"
                    alt="Logo DPMPTSP Sumatera Utara"
                    class="block w-[220px] max-w-full h-[72px] object-contain object-left"
                >
            </div>

            <div id="adminProfile" class="border-b border-white/[0.16] group">
                <button
                    type="button"
                    id="adminProfileToggle"
                    class="w-full min-h-[82px] py-3.5 px-7 flex items-center gap-3.5 text-white bg-transparent border-0 cursor-pointer text-left hover:bg-white/[0.06] transition-colors"
                >
                    <span class="w-12 h-12 shrink-0 grid place-items-center rounded-full text-[#1c6744] bg-[#ffd457] border-2 border-white/[0.82] text-[15px] font-semibold">
                        @php
                            $adminName = auth()->user()->name ?? 'Admin';

                            $initials = collect(
                                preg_split('/\s+/', trim($adminName))
                            )
                                ->filter()
                                ->take(2)
                                ->map(fn ($word) => strtoupper(
                                    mb_substr($word, 0, 1)
                                    ))
                                ->implode('');
                        @endphp

                        {{ $initials ?: 'AD' }}
                    </span>

                    <span class="min-w-0 flex-1 flex flex-col items-start justify-center">
                        <span class="block max-w-full truncate text-white text-[15px] font-semibold leading-[1.35]">
                            {{ $adminName }}
                        </span>

                        <span class="block mt-[3px] text-[#ffd457] text-xs font-medium leading-[1.25] lowercase">
                            {{ auth()->user()->role ?? 'admin' }}
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-down text-[#ffd457] text-[13px] transition-transform duration-[220ms] ease-out group-[.open]:rotate-180"></i>
                </button>

                <div class="max-h-0 overflow-hidden bg-[#012f1d]/[0.23] transition-[max-height] duration-[250ms] ease-in-out group-[.open]:max-h-[240px]">
                    <div class="pt-[6px] px-[18px] pb-[12px]">
                        <a
                            href="{{ route('admin.profile.index') }}"
                            class="w-full flex items-center gap-[13px] py-[11px] px-[10px] text-white bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-user w-[18px] text-[#ffd457] text-center"></i>
                            <span>Profile</span>
                        </a>

                        <a
                            href="{{ route('admin.settings.index') }}"
                            class="w-full flex items-center gap-[13px] py-[11px] px-[10px] text-white bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-gear w-[18px] text-[#ffd457] text-center"></i>
                            <span>Settings</span>
                        </a>

                        <div class="h-[1px] my-2 bg-white/[0.16]"></div>

                        <button
                            type="button"
                            id="openLogoutModal"
                            class="w-full flex items-center gap-[13px] py-[11px] px-[10px] text-white bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors logout"
                        >
                            <i class="fa-solid fa-right-from-bracket w-[18px] text-[#ff8f8f] text-center"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>

            <nav class="pt-0 px-[17px] pb-7">
                <div class="mt-[18px] mx-[12px] mb-[10px] text-[#ffd457] text-xs font-bold uppercase">
                    Menu Utama
                </div>

                <ul class="list-none m-0 p-0">
                    <li class="mb-[5px]">
                        <a
                            href="{{ route('home') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)]"
                        >
                            <i class="fa-solid fa-house w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                </ul>

                <div class="mt-[18px] mx-[12px] mb-[10px] text-[#ffd457] text-xs font-bold uppercase">
                    Menu Admin
                </div>

                <ul class="list-none m-0 p-0">
                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-table-cells-large w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.pengguna.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-users w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Data Pengguna</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.proyek-ipro.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.proyek-ipro.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-file-invoice-dollar w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Dokumen IPRO</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.data-wilayah.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.data-wilayah.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-location-dot w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Data Wilayah</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.data-kbli.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.data-kbli.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-table-cells w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Data KBLI</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.data-kbki.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.data-kbki.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-layer-group w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Data KBKI</span>
                        </a>
                    </li>

                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.hs-code.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('admin.hs-code.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-link w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Data HS Code</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a
                            href="{{ route('admin.money-currency.index') }}"
                            class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)]
                                {{ request()->routeIs('admin.money-currency.*')
                                    ? 'active'
                                    : '' }}"
                        >
                            <i class="fa-solid fa-circle-dollar-to-slot w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>

                            <span>Konversi Mata Uang</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="w-[calc(100%-300px)] min-h-screen ml-[300px] bg-[#f7f9fb] max-lg:w-full max-lg:ml-0">
            @yield('content')
        </main>
    </div>

    <form
        id="logoutForm"
        action="{{ route('logout') }}"
        method="POST"
        hidden
    >
        @csrf
    </form>

    <div id="logoutModal" class="fixed inset-0 z-[1000] grid place-items-center p-6 [&[hidden]]:!hidden" hidden>
        <div class="absolute inset-0 bg-[#0f172a]/[0.52] backdrop-blur-[3px]" data-close-logout></div>
        <div class="relative w-[min(420px,100%)] pt-[30px] px-[28px] pb-[27px] rounded-[22px] bg-white text-center shadow-[0_30px_80px_rgba(15,23,42,0.25)] animate-modalAppear">
            <div class="w-[68px] h-[68px] my-0 mx-auto mb-[18px] grid place-items-center rounded-[20px] text-[#dc2626] bg-[#fee2e2] text-[26px]">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3 class="m-0 text-[#202b3c] text-[21px] font-bold">Keluar dari akun?</h3>
            <p class="mt-2.5 mx-auto mb-0 max-w-[340px] text-[#758096] text-[13px] leading-[1.7]">
                Anda akan keluar dari halaman admin dan perlu login kembali untuk mengakses dashboard.
            </p>
            <div class="mt-[25px] flex justify-center gap-3 max-[520px]:flex-col-reverse">
                <button type="button" class="min-w-[128px] h-[44px] rounded-[12px] text-[13px] font-semibold cursor-pointer max-[520px]:w-full text-[#475467] bg-white border border-[#d9dee8]" data-close-logout>Batal</button>
                <button type="button" id="confirmLogout" class="min-w-[128px] h-[44px] rounded-[12px] text-[13px] font-semibold cursor-pointer max-[520px]:w-full text-white bg-[#dc2626] border border-[#dc2626] hover:bg-[#b91c1c]">Ya, Keluar</button>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const profile = document.getElementById('adminProfile');
            const profileToggle = document.getElementById('adminProfileToggle');

            const sidebar = document.getElementById('adminSidebar');
            const sidebarButton = document.getElementById('mobileSidebarButton');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            const logoutButton = document.getElementById('openLogoutModal');
            const logoutModal = document.getElementById('logoutModal');
            const confirmLogout = document.getElementById('confirmLogout');
            const logoutForm = document.getElementById('logoutForm');
            const closeLogoutButtons = document.querySelectorAll(
                '[data-close-logout]'
            );

            if (profile && profileToggle) {
                profileToggle.addEventListener('click', function () {
                    profile.classList.toggle('open');
                });
            }

            function closeSidebar() {
                sidebar?.classList.remove('open');
                sidebarBackdrop?.classList.remove('show');
                if (sidebarButton) {
                    sidebarButton.style.display = '';
                }
            }

            sidebarButton?.addEventListener('click', function () {
                const isOpen = sidebar?.classList.toggle('open');
                sidebarBackdrop?.toggleAttribute('show', isOpen);
                sidebarBackdrop?.classList.toggle('show', isOpen);
                if (sidebarButton) {
                    sidebarButton.style.display = isOpen ? 'none' : '';
                }
            });

            sidebarBackdrop?.addEventListener('click', closeSidebar);

            function openLogoutModal() {
                if (! logoutModal) {
                    return;
                }

                logoutModal.hidden = false;
                document.body.classList.add('overflow-hidden');
            }

            function closeLogoutModal() {
                if (! logoutModal) {
                    return;
                }

                logoutModal.hidden = true;
                document.body.classList.remove('overflow-hidden');
            }

            logoutButton?.addEventListener('click', openLogoutModal);

            closeLogoutButtons.forEach(function (button) {
                button.addEventListener('click', closeLogoutModal);
            });

            confirmLogout?.addEventListener('click', function () {
                logoutForm?.submit();
            });

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeLogoutModal();
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>
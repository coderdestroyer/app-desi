<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Operator</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/data-wilayah.js') }}"></script>



    @stack('styles')
</head>

<body class="font-['Poppins',sans-serif] text-[#202b3c] bg-[#f7f9fb] min-h-screen m-0">
    @php
    $isSelectionScreen = request()->routeIs('operator.dashboard');
    $isPeluangInvestasi = request()->routeIs('operator.peluang-investasi*') || request()->routeIs('operator.projects*');
    @endphp

    @if(!$isSelectionScreen)
    <button type="button" id="mobileSidebarButton" class="hidden fixed top-[14px] left-[14px] z-[120] w-[44px] h-[44px] border-0 rounded-[12px] text-white bg-[#08794d] shadow-[0_8px_22px_rgba(5,80,47,0.2)] cursor-pointer max-lg:grid max-lg:place-items-center" aria-label="Buka menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div id="sidebarBackdrop" class="hidden fixed inset-0 z-[90] bg-[#0f172a]/[0.46] [&.show]:block"></div>
    @endif

    <div class="min-h-screen flex">
        @if(!$isSelectionScreen)
        <aside id="adminSidebar" class="fixed inset-y-0 left-0 right-auto z-[100] w-[300px] flex flex-col overflow-y-auto text-white bg-[radial-gradient(circle_at_20%_0%,rgba(255,255,255,0.08),transparent_28%),linear-gradient(180deg,#075735_0%,#087849_48%,#0c8d58_100%)] shadow-[10px_0_30px_rgba(5,66,40,0.12)] [&::-webkit-scrollbar]:w-[6px] [&::-webkit-scrollbar-track]:bg-white/[0.05] [&::-webkit-scrollbar-thumb]:bg-white/[0.2] [&::-webkit-scrollbar-thumb]:rounded-[10px] hover:[&::-webkit-scrollbar-thumb]:bg-white/[0.3] max-lg:w-[280px] max-lg:-translate-x-full max-lg:transition-transform max-lg:duration-[250ms] max-lg:ease-in-out max-lg:[&.open]:translate-x-0 max-[520px]:w-[min(285px,88vw)]">
            <div class="pt-6 px-7 pb-[18px]">
                <img src="{{ asset('images/logo-dpmptsp.png') }}" alt="Logo DPMPTSP Sumatera Utara" class="block w-[220px] max-w-full h-[72px] object-contain object-left">
            </div>

            <div class="px-5 py-3 border-b border-white/10">
                <a href="{{ route('operator.dashboard') }}" class="min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] hover:bg-white/15 transition-all text-xs font-semibold text-[#FFD54F] border border-[#FFD54F]/30 rounded-xl">
                    <i class="fa-solid fa-arrow-left w-[21px] text-[#FFD54F] text-center text-[17px]"></i>
                    <span>Pilih Ruang Kerja</span>
                </a>
            </div>

            <div id="adminProfile" class="border-b border-white/[0.16] group">
                <button type="button" id="adminProfileToggle" class="w-full min-h-[82px] py-3.5 px-7 flex items-center gap-3.5 text-white bg-transparent border-0 cursor-pointer text-left hover:bg-white/[0.06] transition-colors">
                    <span class="w-12 h-12 shrink-0 block overflow-hidden p-0 rounded-full text-[#1c6744] bg-[#ffd457] border-2 border-white/[0.82] text-[15px] font-semibold">
                        <img src="{{ Auth::user()?->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()?->name ?? 'Siti') . '&background=FFD54F&color=145239' }}" alt="Profile" class="w-full h-full object-cover">
                    </span>

                    <span class="min-w-0 flex-1 flex flex-col items-start justify-center">
                        <span class="block max-w-full truncate text-white text-[15px] font-semibold leading-[1.35]">
                            {{ Auth::user()?->name ?? 'Operator' }}
                        </span>
                        <span class="block mt-[3px] text-[#ffd457] text-xs font-medium leading-[1.25] lowercase">
                            {{ auth()->user()->role ?? 'operator' }}
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-down text-[#ffd457] text-[13px] transition-transform duration-[220ms] ease-out group-[.open]:rotate-180"></i>
                </button>

                <div class="max-h-0 overflow-hidden bg-[#012f1d]/[0.23] transition-[max-height] duration-[250ms] ease-in-out group-[.open]:max-h-[240px]">
                    <div class="pt-[6px] px-[18px] pb-[12px]">
                        <a href="{{ route('operator.profile') }}" class="w-full flex items-center gap-[13px] py-[11px] px-[10px] text-white bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors {{ request()->routeIs('operator.profile') ? 'active' : '' }}">
                            <i class="fa-regular fa-user w-[18px] text-[#ffd457] text-center"></i>
                            <span>Profile</span>
                        </a>

                        <a href="{{ route('operator.settings') }}" class="w-full flex items-center gap-[13px] py-[11px] px-[10px] text-white bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors {{ request()->routeIs('operator.settings') ? 'active' : '' }}">
                            <i class="fa-solid fa-gear w-[18px] text-[#ffd457] text-center"></i>
                            <span>Settings</span>
                        </a>

                        <div class="h-[1px] my-2 bg-white/[0.16]"></div>

                        <button type="button" class="openLogoutModalBtn w-full flex items-center gap-[13px] py-[11px] px-[10px] text-[#ffaaaa] bg-transparent border-0 rounded-[9px] no-underline cursor-pointer text-left text-[13px] font-medium hover:bg-white/[0.08] transition-colors">
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
                        <a href="{{ url('/') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->is('/') ? 'active' : '' }}">
                            <i class="fa-solid fa-house w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                </ul>
                
                @if($isPeluangInvestasi)
                <!-- SIDEBAR MENU PELUANG INVESTASI (IPRO) -->
                <div class="mt-[18px] mx-[12px] mb-[10px] text-[#ffd457] text-xs font-bold uppercase">
                    Modul Peluang Investasi
                </div>

                <ul class="list-none m-0 p-0">
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.peluang-investasi') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.peluang-investasi') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Dashboard Peluang</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.projects.index') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.projects.*') ? 'active' : '' }}">
                            <i class="fa-solid fa-calculator w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Kalkulasi & Daftar Proyek</span>
                        </a>
                    </li>
                </ul>
                @else
                <!-- SIDEBAR MENU POTENSI UNGGULAN (MAKRO) -->
                <div class="mt-[18px] mx-[12px] mb-[10px] text-[#ffd457] text-xs font-bold uppercase">
                    Potensi Unggulan Daerah
                </div>

                <ul class="list-none m-0 p-0">
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.potensi-unggulan') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.potensi-unggulan') ? 'active' : '' }}">
                            <i class="fa-solid fa-table-cells-large w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Dashboard Potensi</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.lq.index') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.lq.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Analisis LQ</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.ss.index') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.ss.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Analisis SS</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.tipologi.index') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.tipologi.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Analisis Tipologi Sektor</span>
                        </a>
                    </li>
                    <li class="mb-[5px]">
                        <a href="{{ route('operator.klassen.index') }}" class="group min-h-[50px] flex items-center gap-3.5 py-3 px-[15px] rounded-[10px] text-white/90 no-underline text-sm font-medium transition-[background,color,transform] duration-[180ms] ease-in-out hover:text-white hover:bg-white/[0.09] hover:translate-x-[2px] [&.active]:text-[#176541] [&.active]:bg-[#ffd457] [&.active]:shadow-[0_10px_22px_rgba(0,0,0,0.11)] {{ request()->routeIs('operator.klassen.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-bar w-[21px] text-white/[0.76] text-center text-[17px] transition-colors duration-[180ms] group-hover:text-white group-[.active]:text-[#176541]"></i>
                            <span>Analisis Klassen</span>
                        </a>
                    </li>
                </ul>
                @endif
            </nav>
        </aside>
        @endif

        <main class="min-h-screen bg-[#f7f9fb] {{ $isSelectionScreen ? 'w-full ml-0' : 'w-[calc(100%-300px)] ml-[300px] max-lg:w-full max-lg:ml-0' }}">
            @if($isSelectionScreen)
            <!-- TOPBAR UNTUK SELECTION SCREEN (TANPA SIDEBAR) -->
            <header class="bg-white border-b border-[#CFE3D5] px-6 py-4 flex items-center justify-between shadow-sm sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('images/logo-dpmptsp.png') }}" alt="Logo DPMPTSP" class="h-10 object-contain">
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right hidden sm:block">
                        <span class="block text-sm font-semibold text-[#17201C]">{{ Auth::user()?->name }}</span>
                        <span class="block text-xs text-[#667069] font-medium uppercase tracking-wider">Operator DPMPTSP</span>
                    </div>
                    <a href="{{ route('operator.profile') }}" class="w-10 h-10 rounded-full bg-[#E7F2EB] text-[#145239] border border-[#CFE3D5] flex items-center justify-center font-bold text-sm hover:bg-[#145239] hover:text-white transition-colors" title="Profil Operator">
                        {{ strtoupper(substr(Auth::user()?->name ?? 'O', 0, 1)) }}
                    </a>
                    <button type="button" class="openLogoutModalBtn px-3.5 py-2 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 text-xs font-semibold flex items-center gap-2 transition-colors border border-red-200">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">Keluar</span>
                    </button>
                </div>
            </header>
            @endif

            <div class="p-4 md:p-6 lg:p-8 w-full space-y-6 flex-1">
                @yield('content')
            </div>
        </main>
    </div>

    <form id="logoutForm" action="{{ route('logout') }}" method="POST" hidden>
        @csrf
    </form>

    <div id="logoutModal" class="fixed inset-0 z-[1000] grid place-items-center p-6 [&[hidden]]:!hidden" hidden>
        <div class="absolute inset-0 bg-[#0f172a]/[0.52] backdrop-blur-[3px]" data-close-logout></div>
        <div class="relative w-[min(420px,100%)] pt-[30px] px-[28px] pb-[27px] rounded-[22px] bg-white text-center shadow-[0_30px_80px_rgba(15,23,42,0.25)] animate-modalAppear">
            <div class="w-[68px] h-[68px] my-0 mx-auto mb-[18px] grid place-items-center rounded-[20px] text-[#dc2626] bg-[#fee2e2] text-[26px]">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3 class="m-0 text-[#202b3c] text-[21px] font-bold">Keluar dari akun?</h3>
            <p class="mt-2.5 mx-auto mb-0 max-w-[340px] text-[#758096] text-[13px] leading-[1.7]">Anda akan keluar dari halaman operator dan perlu login kembali untuk mengakses dashboard.</p>
            <div class="mt-[25px] flex justify-center gap-3 max-[520px]:flex-col-reverse">
                <button type="button" class="min-w-[128px] h-[44px] rounded-[12px] text-[13px] font-semibold cursor-pointer max-[520px]:w-full text-[#475467] bg-white border border-[#d9dee8]" data-close-logout>Batal</button>
                <button type="button" id="confirmLogout" class="min-w-[128px] h-[44px] rounded-[12px] text-[13px] font-semibold cursor-pointer max-[520px]:w-full text-white bg-[#dc2626] border border-[#dc2626] hover:bg-[#b91c1c]">Ya, Keluar</button>
            </div>
        </div>
    </div>

    @stack('scripts')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const profile = document.getElementById('adminProfile');
            const profileToggle = document.getElementById('adminProfileToggle');

            const sidebar = document.getElementById('adminSidebar');
            const sidebarButton = document.getElementById('mobileSidebarButton');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            const logoutModal = document.getElementById('logoutModal');
            const confirmLogout = document.getElementById('confirmLogout');
            const logoutForm = document.getElementById('logoutForm');
            const closeLogoutButtons = document.querySelectorAll(
                '[data-close-logout]'
            );
            const openLogoutBtns = document.querySelectorAll('#openLogoutModal, .openLogoutModalBtn');
            openLogoutBtns.forEach(btn => btn.addEventListener('click', openLogoutModal));

            if (profile && profileToggle) {
                profileToggle.addEventListener('click', function() {
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

            sidebarButton?.addEventListener('click', function() {
                const isOpen = sidebar?.classList.toggle('open');
                sidebarBackdrop?.classList.toggle('show', isOpen);
                if (sidebarButton) {
                    sidebarButton.style.display = isOpen ? 'none' : '';
                }
            });

            sidebarBackdrop?.addEventListener('click', closeSidebar);

            function openLogoutModal() {
                if (!logoutModal) {
                    return;
                }

                logoutModal.hidden = false;
                document.body.classList.add('overflow-hidden');
            }

            function closeLogoutModal() {
                if (!logoutModal) {
                    return;
                }

                logoutModal.hidden = true;
                document.body.classList.remove('overflow-hidden');
            }

            closeLogoutButtons.forEach(function(button) {
                button.addEventListener('click', closeLogoutModal);
            });

            confirmLogout?.addEventListener('click', function(e) {
                e.preventDefault();
                const form = document.getElementById('logoutForm');
                if (form) {
                    form.submit();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape') {
                    closeLogoutModal();
                    closeSidebar();
                }
            });
        });
    </script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(event, form) {
            event.preventDefault();
            Swal.fire({
                title: '<span class="text-lg">Hapus Data?</span>',
                html: '<span class="text-sm">Data yang dihapus tidak dapat dikembalikan!</span>',
                icon: 'warning',
                width: '24em',
                padding: '1.5em',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'rounded-lg text-sm px-4 py-2',
                    cancelButton: 'rounded-lg text-sm px-4 py-2',
                    popup: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        function confirmDeleteAll(event, form) {
            event.preventDefault();
            Swal.fire({
                title: '<span class="text-lg">Hapus Semua Data?</span>',
                html: '<span class="text-sm">Apakah Anda yakin ingin menghapus semua data? Aksi ini tidak dapat dibatalkan!</span>',
                icon: 'warning',
                width: '24em',
                padding: '1.5em',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'rounded-lg text-sm px-4 py-2',
                    cancelButton: 'rounded-lg text-sm px-4 py-2',
                    popup: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }

        const pageKey = 'selected_rows_' + window.location.pathname;

        function getSelectedIds() {
            const ids = sessionStorage.getItem(pageKey);
            return ids ? JSON.parse(ids) : [];
        }

        function saveSelectedIds(ids) {
            sessionStorage.setItem(pageKey, JSON.stringify(ids));
        }

        function toggleSelectAll(source) {
            let selectedIds = getSelectedIds();
            const checkboxes = document.querySelectorAll('.row-checkbox');

            checkboxes.forEach(cb => {
                cb.checked = source.checked;
                if (source.checked) {
                    if (!selectedIds.includes(cb.value)) selectedIds.push(cb.value);
                } else {
                    selectedIds = selectedIds.filter(id => id !== cb.value);
                }
            });

            saveSelectedIds(selectedIds);
            updateBulkDeleteState();
        }

        function updateBulkDeleteState() {
            const selectedIds = getSelectedIds();
            const bulkBtn = document.getElementById('bulkDeleteBtn');
            const form = document.getElementById('bulkDeleteForm');

            if (!bulkBtn) return;

            if (selectedIds.length > 0) {
                bulkBtn.classList.remove('hidden');

                // Update button text with count while preserving SVG
                bulkBtn.innerHTML = `
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Terpilih (${selectedIds.length})
                `;

                if (form) {
                    // Update hidden inputs for submission
                    document.querySelectorAll('.bulk-id-input').forEach(el => el.remove());
                    selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        input.className = 'bulk-id-input';
                        form.appendChild(input);
                    });
                }
            } else {
                bulkBtn.classList.add('hidden');
            }

            // Update master checkbox state for current page
            const allCheckboxes = document.querySelectorAll('.row-checkbox');
            const selectAll = document.getElementById('selectAll');

            if (selectAll && allCheckboxes.length > 0) {
                const checkedCount = Array.from(allCheckboxes).filter(cb => cb.checked).length;
                selectAll.checked = checkedCount === allCheckboxes.length;
                selectAll.indeterminate = checkedCount > 0 && checkedCount < allCheckboxes.length;
            }
        }

        // Initialize checkboxes on page load based on sessionStorage
        document.addEventListener('DOMContentLoaded', function() {
            const selectedIds = getSelectedIds();
            if (selectedIds.length > 0) {
                const checkboxes = document.querySelectorAll('.row-checkbox');
                checkboxes.forEach(cb => {
                    if (selectedIds.includes(cb.value)) {
                        cb.checked = true;
                    }
                });
            }
            updateBulkDeleteState();
        });

        // Event listener for individual checkboxes
        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('row-checkbox')) {
                let selectedIds = getSelectedIds();
                const id = e.target.value;

                if (e.target.checked) {
                    if (!selectedIds.includes(id)) selectedIds.push(id);
                } else {
                    selectedIds = selectedIds.filter(i => i !== id);
                }

                saveSelectedIds(selectedIds);
                updateBulkDeleteState();
            }
        });

        function confirmBulkDelete(event, form) {
            event.preventDefault();
            const selectedIds = getSelectedIds();
            const count = selectedIds.length;

            if (count === 0) return false;

            Swal.fire({
                title: '<span class="text-lg">Hapus Terpilih?</span>',
                html: `<span class="text-sm">Apakah Anda yakin ingin menghapus <b>${count}</b> data terpilih?<br>Aksi ini tidak dapat dibatalkan!</span>`,
                icon: 'warning',
                width: '24em',
                padding: '1.5em',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'rounded-lg text-sm px-4 py-2',
                    cancelButton: 'rounded-lg text-sm px-4 py-2',
                    popup: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    sessionStorage.removeItem(pageKey); // Clear memory after deletion
                    form.submit();
                }
            });
            return false;
        }
    </script>

</body>

</html>
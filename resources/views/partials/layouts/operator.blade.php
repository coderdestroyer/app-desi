<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Operator</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/data-wilayah.js') }}"></script>

    <style>
        :root {
            --sidebar-dark: #075936;
            --sidebar-main: #08794d;
            --sidebar-light: #10935e;
            --yellow: #ffd457;
            --yellow-dark: #e9b930;
            --white: #ffffff;
            --background: #f7f9fb;
            --text-dark: #202b3c;
            --text-muted: #758096;
            --border: #e6eaf0;
            --danger: #e05252;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background: var(--background);
        }

        body.modal-open {
            overflow: hidden;
        }

        button,
        input,
        select,
        textarea {
            font-family: inherit;
        }

        .admin-shell {
            min-height: 100vh;
            display: flex;
        }

        .admin-sidebar {
            position: fixed;
            inset: 0 auto 0 0;
            z-index: 100;
            width: 300px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            color: #ffffff;
            background:
                radial-gradient(circle at 20% 0%,
                    rgba(255, 255, 255, 0.08),
                    transparent 28%),
                linear-gradient(180deg,
                    #075735 0%,
                    #087849 48%,
                    #0c8d58 100%);
            box-shadow: 10px 0 30px rgba(5, 66, 40, 0.12);
        }

        /* Custom Scrollbar for Sidebar */
        .admin-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .admin-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .admin-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
        }

        .admin-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .sidebar-logo {
            padding: 24px 28px 18px;
        }

        .sidebar-logo img {
            display: block;
            width: 220px;
            max-width: 100%;
            height: 72px;
            object-fit: contain;
            object-position: left center;
        }

        .sidebar-profile {
            border-bottom: 1px solid rgba(255, 255, 255, 0.16);
        }

        .profile-toggle {
            width: 100%;
            min-height: 82px;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 14px;
            color: #ffffff;
            background: transparent;
            border: 0;
            cursor: pointer;
            text-align: left;
        }

        .profile-toggle:hover {
            background: rgba(255, 255, 255, 0.06);
        }

        .profile-avatar {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: #1c6744;
            background: var(--yellow);
            border: 2px solid rgba(255, 255, 255, 0.82);
            font-size: 15px;
            font-weight: 600;
        }

        .profile-copy {
            min-width: 0;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
        }

        .profile-name {
            display: block;
            max-width: 100%;
            overflow: hidden;
            color: #ffffff;
            font-size: 15px;
            font-weight: 600;
            line-height: 1.35;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .profile-role {
            display: block;
            margin-top: 3px;
            color: var(--yellow);
            font-size: 12px;
            font-weight: 500;
            line-height: 1.25;
            text-transform: lowercase;
        }

        .profile-chevron {
            color: var(--yellow);
            font-size: 13px;
            transition: transform 0.22s ease;
        }

        .sidebar-profile.open .profile-chevron {
            transform: rotate(180deg);
        }

        .profile-dropdown {
            max-height: 0;
            overflow: hidden;
            background: rgba(1, 47, 29, 0.23);
            transition: max-height 0.25s ease;
        }

        .sidebar-profile.open .profile-dropdown {
            max-height: 240px;
        }

        .profile-dropdown-inner {
            padding: 6px 18px 12px;
        }

        .profile-dropdown-link {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 13px;
            padding: 11px 10px;
            color: #ffffff;
            background: transparent;
            border: 0;
            border-radius: 9px;
            text-decoration: none;
            cursor: pointer;
            text-align: left;
            font-size: 13px;
            font-weight: 500;
        }

        .profile-dropdown-link i {
            width: 18px;
            color: var(--yellow);
            text-align: center;
        }

        .profile-dropdown-link:hover {
            background: rgba(255, 255, 255, 0.08);
        }

        .profile-dropdown-divider {
            height: 1px;
            margin: 8px 0;
            background: rgba(255, 255, 255, 0.16);
        }

        .profile-dropdown-link.logout {
            color: #ffaaaa;
        }

        .profile-dropdown-link.logout i {
            color: #ff8f8f;
        }

        .sidebar-content {
            padding: 0 17px 28px;
        }

        .sidebar-section-title {
            margin: 18px 12px 10px;
            color: var(--yellow);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-link {
            min-height: 50px;
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 12px 15px;
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition:
                background 0.18s ease,
                color 0.18s ease,
                transform 0.18s ease;
        }

        .sidebar-link i {
            width: 21px;
            color: rgba(255, 255, 255, 0.76);
            text-align: center;
            font-size: 17px;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.09);
            transform: translateX(2px);
        }

        .sidebar-link.active {
            color: #176541;
            background: var(--yellow);
            box-shadow: 0 10px 22px rgba(0, 0, 0, 0.11);
        }

        .sidebar-link.active i {
            color: #176541;
        }

        .admin-main {
            width: calc(100% - 300px);
            min-height: 100vh;
            margin-left: 300px;
            background: var(--background);
        }

        .admin-main.selection-screen {
            width: 100% !important;
            margin-left: 0 !important;
        }

        .mobile-sidebar-button {
            display: none;
            position: fixed;
            top: 14px;
            left: 14px;
            z-index: 120;
            width: 44px;
            height: 44px;
            place-items: center;
            border: 0;
            border-radius: 12px;
            color: #ffffff;
            background: #08794d;
            box-shadow: 0 8px 22px rgba(5, 80, 47, 0.2);
            cursor: pointer;
        }

        .sidebar-backdrop {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 90;
            background: rgba(15, 23, 42, 0.46);
        }

        .logout-modal[hidden] {
            display: none !important;
        }

        .logout-modal {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: grid;
            place-items: center;
            padding: 24px;
        }

        .logout-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.52);
            backdrop-filter: blur(3px);
        }

        .logout-modal-card {
            position: relative;
            width: min(420px, 100%);
            padding: 30px 28px 27px;
            border-radius: 22px;
            background: #ffffff;
            text-align: center;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.25);
            animation: modalAppear 0.2s ease-out;
        }

        .logout-modal-icon {
            width: 68px;
            height: 68px;
            margin: 0 auto 18px;
            display: grid;
            place-items: center;
            border-radius: 20px;
            color: #dc2626;
            background: #fee2e2;
            font-size: 26px;
        }

        .logout-modal-card h3 {
            margin: 0;
            color: #202b3c;
            font-size: 21px;
            font-weight: 700;
        }

        .logout-modal-card p {
            margin: 10px auto 0;
            max-width: 340px;
            color: #758096;
            font-size: 13px;
            line-height: 1.7;
        }

        .logout-modal-actions {
            margin-top: 25px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .logout-modal-actions button {
            min-width: 128px;
            height: 44px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-cancel {
            color: #475467;
            background: #ffffff;
            border: 1px solid #d9dee8;
        }

        .logout-confirm {
            color: #ffffff;
            background: #dc2626;
            border: 1px solid #dc2626;
        }

        .logout-confirm:hover {
            background: #b91c1c;
        }

        @keyframes modalAppear {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 280px;
                transform: translateX(-100%);
                transition: transform 0.25s ease;
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                width: 100%;
                margin-left: 0;
            }

            .mobile-sidebar-button {
                display: grid;
            }

            .sidebar-backdrop.show {
                display: block;
            }
        }

        @media (max-width: 520px) {
            .admin-sidebar {
                width: min(285px, 88vw);
            }

            .logout-modal-actions {
                flex-direction: column-reverse;
            }

            .logout-modal-actions button {
                width: 100%;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    @php
    $isSelectionScreen = request()->routeIs('operator.dashboard');
    $isPeluangInvestasi = request()->routeIs('operator.peluang-investasi*');
    @endphp

    @if(!$isSelectionScreen)
    <button type="button" id="mobileSidebarButton" class="mobile-sidebar-button" aria-label="Buka menu">
        <i class="fa-solid fa-bars"></i>
    </button>
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>
    @endif

    <div class="admin-shell">
        @if(!$isSelectionScreen)
        <aside id="adminSidebar" class="admin-sidebar">
            <div class="sidebar-logo">
                <img src="{{ asset('images/logo-dpmptsp.png') }}" alt="Logo DPMPTSP Sumatera Utara">
            </div>

            <div class="px-5 py-3 border-b border-white/10">
                <a href="{{ route('operator.dashboard') }}" class="sidebar-link hover:bg-white/15 transition-all text-xs font-semibold text-[#FFD54F] border border-[#FFD54F]/30 rounded-xl">
                    <i class="fa-solid fa-arrow-left text-[#FFD54F]"></i>
                    <span>Pilih Ruang Kerja</span>
                </a>
            </div>

            <div id="adminProfile" class="sidebar-profile">
                <button type="button" id="adminProfileToggle" class="profile-toggle">
                    <span class="profile-avatar" style="overflow: hidden; padding: 0; display: block;">
                        <img src="{{ Auth::user()?->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()?->name ?? 'Siti') . '&background=FFD54F&color=145239' }}" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
                    </span>

                    <span class="profile-copy">
                        <span class="profile-name">
                            {{ Auth::user()?->name ?? 'Operator' }}
                        </span>
                        <span class="profile-role">
                            {{ auth()->user()->role ?? 'operator' }}
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-down profile-chevron"></i>
                </button>

                <div class="profile-dropdown">
                    <div class="profile-dropdown-inner">
                        <a href="{{ route('operator.profile') }}" class="profile-dropdown-link {{ request()->routeIs('operator.profile') ? 'active' : '' }}">
                            <i class="fa-regular fa-user"></i>
                            <span>Profile</span>
                        </a>

                        <a href="{{ route('operator.settings') }}" class="profile-dropdown-link {{ request()->routeIs('operator.settings') ? 'active' : '' }}">
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>

                        <div class="profile-dropdown-divider"></div>

                        <button type="button" class="openLogoutModalBtn profile-dropdown-link logout">
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>

            <nav class="sidebar-content">
                @if($isPeluangInvestasi)
                <!-- SIDEBAR MENU PELUANG INVESTASI (IPRO) -->
                <div class="sidebar-section-title">
                    Peluang Investasi (IPRO)
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('operator.peluang-investasi') }}" class="sidebar-link {{ request()->routeIs('operator.peluang-investasi') ? 'active' : '' }}">
                            <i class="fa-solid fa-briefcase"></i>
                            <span>Dashboard Peluang</span>
                        </a>
                    </li>
                </ul>
                @else
                <!-- SIDEBAR MENU POTENSI UNGGULAN (MAKRO) -->
                <div class="sidebar-section-title">
                    Potensi Unggulan Daerah
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ route('operator.potensi-unggulan') }}" class="sidebar-link {{ request()->routeIs('operator.potensi-unggulan') ? 'active' : '' }}">
                            <i class="fa-solid fa-table-cells-large"></i>
                            <span>Dashboard Potensi</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('operator.lq.index') }}" class="sidebar-link {{ request()->routeIs('operator.lq.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i>
                            <span>Analisis LQ</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('operator.ss.index') }}" class="sidebar-link {{ request()->routeIs('operator.ss.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Analisis SS</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('operator.tipologi.index') }}" class="sidebar-link {{ request()->routeIs('operator.tipologi.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Analisis Tipologi Sektor</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('operator.klassen.index') }}" class="sidebar-link {{ request()->routeIs('operator.klassen.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Analisis Klassen</span>
                        </a>
                    </li>
                </ul>
                @endif

                <div class="sidebar-section-title">
                    Menu Utama
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a href="{{ url('/') }}" class="sidebar-link">
                            <i class="fa-solid fa-house"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        @endif

        <main class="admin-main {{ $isSelectionScreen ? 'selection-screen' : '' }}">
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

    <div id="logoutModal" class="logout-modal" hidden>
        <div class="logout-modal-backdrop" data-close-logout></div>
        <div class="logout-modal-card">
            <div class="logout-modal-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
            <h3>Keluar dari akun?</h3>
            <p>Anda akan keluar dari halaman operator dan perlu login kembali untuk mengakses dashboard.</p>
            <div class="logout-modal-actions">
                <button type="button" class="logout-cancel" data-close-logout>Batal</button>
                <button type="button" id="confirmLogout" class="logout-confirm">Ya, Keluar</button>
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
            }

            sidebarButton?.addEventListener('click', function() {
                sidebar?.classList.toggle('open');
                sidebarBackdrop?.classList.toggle('show');
            });

            sidebarBackdrop?.addEventListener('click', closeSidebar);

            function openLogoutModal() {
                if (!logoutModal) {
                    return;
                }

                logoutModal.hidden = false;
                document.body.classList.add('modal-open');
            }

            function closeLogoutModal() {
                if (!logoutModal) {
                    return;
                }

                logoutModal.hidden = true;
                document.body.classList.remove('modal-open');
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
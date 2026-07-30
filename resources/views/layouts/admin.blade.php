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
                radial-gradient(
                    circle at 20% 0%,
                    rgba(255, 255, 255, 0.08),
                    transparent 28%
                ),
                linear-gradient(
                    180deg,
                    #075735 0%,
                    #087849 48%,
                    #0c8d58 100%
                );
            box-shadow: 10px 0 30px rgba(5, 66, 40, 0.12);
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
    <button
        type="button"
        id="mobileSidebarButton"
        class="mobile-sidebar-button"
        aria-label="Buka menu"
    >
        <i class="fa-solid fa-bars"></i>
    </button>

    <div
        id="sidebarBackdrop"
        class="sidebar-backdrop"
    ></div>

    <div class="admin-shell">
        <aside
            id="adminSidebar"
            class="admin-sidebar"
        >
            <div class="sidebar-logo">
                <img
                    src="{{ asset('images/logo-dpmptsp.png') }}"
                    alt="Logo DPMPTSP Sumatera Utara"
                >
            </div>

            <div
                id="adminProfile"
                class="sidebar-profile"
            >
                <button
                    type="button"
                    id="adminProfileToggle"
                    class="profile-toggle"
                >
                    <span class="profile-avatar">
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

                    <span class="profile-copy">
                        <span class="profile-name">
                            {{ $adminName }}
                        </span>

                        <span class="profile-role">
                            {{ auth()->user()->role ?? 'admin' }}
                        </span>
                    </span>

                    <i class="fa-solid fa-chevron-down profile-chevron"></i>
                </button>

                <div class="profile-dropdown">
                    <div class="profile-dropdown-inner">
                        <a
                            href="{{ route('admin.profile.index') }}"
                            class="profile-dropdown-link
                                {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}"
                        >
                            <i class="fa-regular fa-user"></i>
                            <span>Profile</span>
                        </a>

                        <a
                            href="{{ route('admin.settings.index') }}"
                            class="profile-dropdown-link
                                {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-gear"></i>
                            <span>Settings</span>
                        </a>

                        <div class="profile-dropdown-divider"></div>

                        <button
                            type="button"
                            id="openLogoutModal"
                            class="profile-dropdown-link logout"
                        >
                            <i class="fa-solid fa-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </div>
                </div>
            </div>

            <nav class="sidebar-content">
                <div class="sidebar-section-title">
                    Menu Admin
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a
                            href="{{ route('admin.dashboard') }}"
                            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-table-cells-large"></i>
                            <span>Dashboard Admin</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.pengguna.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.pengguna.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-users"></i>
                            <span>Data Pengguna</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.proyek-ipro.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.proyek-ipro.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                            <span>Dokumen IPRO</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.data-wilayah.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.data-wilayah.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-location-dot"></i>
                            <span>Data Wilayah</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.data-kbli.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.data-kbli.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-table-cells"></i>
                            <span>Data KBLI</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.data-kbki.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.data-kbki.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Data KBKI</span>
                        </a>
                    </li>

                    <li>
                        <a
                            href="{{ route('admin.hs-code.index') }}"
                            class="sidebar-link {{ request()->routeIs('admin.hs-code.*') ? 'active' : '' }}"
                        >
                            <i class="fa-solid fa-link"></i>
                            <span>Data HS Code</span>
                        </a>
                    </li>
                    <li>
                        <a
                            href="{{ route('admin.money-currency.index') }}"
                            class="sidebar-link
                                {{ request()->routeIs('admin.money-currency.*')
                                    ? 'active'
                                    : '' }}"
                        >
                            <i class="fa-solid fa-circle-dollar-to-slot"></i>

                            <span>Konversi Mata Uang</span>
                        </a>
                    </li>
                </ul>

                <div class="sidebar-section-title">
                    Menu Utama
                </div>

                <ul class="sidebar-menu">
                    <li>
                        <a
                            href="{{ route('home') }}"
                            class="sidebar-link"
                        >
                            <i class="fa-solid fa-house"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>

        <main class="admin-main">
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

    <div
        id="logoutModal"
        class="logout-modal"
        hidden
    >
        <div
            class="logout-modal-backdrop"
            data-close-logout
        ></div>

        <div class="logout-modal-card">
            <div class="logout-modal-icon">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>

            <h3>Keluar dari akun?</h3>

            <p>
                Anda akan keluar dari halaman admin dan perlu login
                kembali untuk mengakses dashboard.
            </p>

            <div class="logout-modal-actions">
                <button
                    type="button"
                    class="logout-cancel"
                    data-close-logout
                >
                    Batal
                </button>

                <button
                    type="button"
                    id="confirmLogout"
                    class="logout-confirm"
                >
                    Ya, Keluar
                </button>
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
                document.body.classList.add('modal-open');
            }

            function closeLogoutModal() {
                if (! logoutModal) {
                    return;
                }

                logoutModal.hidden = true;
                document.body.classList.remove('modal-open');
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
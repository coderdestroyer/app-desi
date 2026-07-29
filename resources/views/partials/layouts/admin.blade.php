<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >
    <title>@yield('title', 'Admin') | DPMPTSP Sumatera Utara</title>

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

    <style>
        :root {
            --green-dark: #255d3e;
            --green-main: #2f6b48;
            --green-soft: #eaf7ef;
            --green-pale: #f6fbf7;
            --yellow-main: #f4cf63;
            --yellow-soft: #fff8e6;
            --navy: #14213d;
            --text-dark: #243042;
            --text-soft: #667085;
            --border-soft: #e5e7eb;
            --bg-main: #f8faf8;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background: var(--bg-main);
            color: var(--text-dark);
        }

        body {
            min-height: 100vh;
        }

        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 290px;
            background: var(--white);
            border-right: 1px solid var(--border-soft);
            padding: 24px 22px;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            overflow-y: auto;
            z-index: 20;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            margin-bottom: 26px;
        }

        .admin-logo img {
            width: 160px;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 28px;
            padding: 12px 0 6px;
        }

        .admin-avatar {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: var(--green-dark);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            flex-shrink: 0;
        }

        .admin-name {
            font-size: 15px;
            font-weight: 700;
            color: #101828;
            line-height: 1.3;
            margin-bottom: 2px;
        }

        .admin-role {
            font-size: 14px;
            font-weight: 400;
            color: var(--text-soft);
        }

        .menu-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--navy);
            margin: 22px 0 12px;
        }

        .sidebar-menu {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .sidebar-menu li {
            margin-bottom: 6px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            color: var(--navy);
            padding: 14px 14px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            width: 22px;
            text-align: center;
            font-size: 18px;
        }

        .sidebar-link:hover {
            background: var(--green-pale);
            color: var(--green-dark);
        }

        .sidebar-link.active {
            background: #e5f1e8;
            color: var(--green-dark);
        }

        .admin-main {
            margin-left: 290px;
            width: calc(100% - 290px);
            min-height: 100vh;
            background: var(--bg-main);
        }

        @media (max-width: 992px) {
            .admin-wrapper {
                flex-direction: column;
            }

            .admin-sidebar {
                position: relative;
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-soft);
            }

            .admin-main {
                margin-left: 0;
                width: 100%;
            }
        }

        .logout-sidebar-area {
            margin-top: 22px;
            padding-top: 18px;
            border-top: 1px solid #edf2f7;
        }

        .logout-form {
            margin: 0;
        }

        .logout-sidebar-button {
            width: 100%;
            border: none;
            background: transparent;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            text-align: left;
            color: #dc2626 !important;
        }

        .logout-sidebar-button i,
        .logout-sidebar-button span {
            color: #dc2626 !important;
        }

        .logout-sidebar-button:hover {
            background: #fee2e2 !important;
            color: #b91c1c !important;
        }

        .logout-sidebar-button:hover i,
        .logout-sidebar-button:hover span {
            color: #b91c1c !important;
        }

        /* POPUP LOGOUT */
        .logout-confirm-modal[hidden] {
            display: none !important;
        }

        .logout-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .logout-confirm-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(3px);
        }

        .logout-confirm-card {
            position: relative;
            width: min(430px, 100%);
            background: #ffffff;
            border-radius: 24px;
            padding: 30px 28px;
            text-align: center;
            border: 1px solid #e5e7eb;
            box-shadow: 0 30px 80px rgba(15, 23, 42, 0.24);
            animation: logoutPopupIn 0.18s ease-out;
        }

        .logout-confirm-icon {
            width: 72px;
            height: 72px;
            margin: 0 auto 18px;
            border-radius: 22px;
            background: #fee2e2;
            color: #dc2626;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
        }

        .logout-confirm-card h3 {
            margin: 0;
            color: #14213d;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .logout-confirm-card p {
            margin: 12px 0 0;
            color: #667085;
            font-size: 14px;
            line-height: 1.7;
        }

        .logout-confirm-actions {
            margin-top: 26px;
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .logout-cancel-button,
        .logout-submit-button {
            min-width: 130px;
            height: 46px;
            border-radius: 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .logout-cancel-button {
            border: 1px solid #d9dee8;
            background: #ffffff;
            color: #344054;
        }

        .logout-cancel-button:hover {
            background: #f8fafc;
        }

        .logout-submit-button {
            border: none;
            background: #dc2626;
            color: #ffffff;
            box-shadow: 0 12px 28px rgba(220, 38, 38, 0.22);
        }

        .logout-submit-button:hover {
            background: #b91c1c;
        }

        @keyframes logoutPopupIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.97);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <img
                    src="{{ asset('images/logo-dpmptsp.png') }}"
                    alt="Logo DPMPTSP"
                >
            </div>

            <div class="admin-profile">
                <div class="admin-avatar">
                    {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                </div>

                <div>
                    <div class="admin-name">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </div>
                    <div class="admin-role">
                        {{ ucfirst(auth()->user()->role ?? 'admin') }}
                    </div>
                </div>
            </div>

            <div class="menu-title">Menu Utama</div>
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

                <li>
                    <a
                        href="{{ route('about') }}"
                        class="sidebar-link"
                    >
                        <i class="fa-solid fa-circle-info"></i>
                        <span>Tentang</span>
                    </a>
                </li>
            </ul>

            <div class="menu-title">Menu Admin</div>
            <ul class="sidebar-menu">
                <li>
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="sidebar-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-chart-line"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('admin.pengguna.index') }}"
                        class="sidebar-link {{ request()->is('admin/pengguna*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('admin.proyek-ipro.index') }}"
                        class="sidebar-link {{ request()->is('admin/proyek-ipro*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-file-invoice-dollar text-[#0F8A5F]"></i>
                        <span>Dokumen IPRO</span>
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('admin.data-wilayah.index') }}"
                        class="sidebar-link {{ request()->is('admin/data-wilayah*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-map"></i>
                        <span>Data Wilayah</span>
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('admin.data-kbli.index') }}"
                        class="sidebar-link {{ request()->is('admin/data-kbli*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-table-cells"></i>
                        <span>Kode KBLI</span>
                    </a>
                </li>

                <li>
                    <a
                        href="{{ route('admin.hs-code.index') }}"
                        class="sidebar-link {{ request()->is('admin/hs-code*') ? 'active' : '' }}"
                    >
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Kode HS</span>
                    </a>
                    <div class="logout-sidebar-area">
                        <form
                            id="logoutForm"
                            action="{{ route('logout') }}"
                            method="POST"
                            class="logout-form"
                        >
                            @csrf

                            <button
                                type="button"
                                id="openLogoutModal"
                                class="sidebar-link logout-sidebar-button"
                            >
                                <i class="fa-solid fa-right-from-bracket"></i>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </li>
                
                <li style="margin-top: 32px;">
                    <form method="POST" action="{{ route('logout') }}" style="margin: 0; padding: 0;">
                        @csrf
                        <button type="submit" class="sidebar-link" style="width: 100%; text-align: left; background: #fee2e2; border: none; cursor: pointer; color: #b91c1c;">
                            <i class="fa-solid fa-arrow-right-from-bracket"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>
    <div
    id="logoutConfirmModal"
    class="logout-confirm-modal"
    hidden
>
    <div
        class="logout-confirm-backdrop"
        data-close-logout
    ></div>

    <div class="logout-confirm-card">
        <div class="logout-confirm-icon">
            <i class="fa-solid fa-right-from-bracket"></i>
        </div>

        <h3>Keluar dari akun?</h3>

        <p>
            Kamu akan keluar dari halaman admin dan perlu login kembali untuk mengakses dashboard.
        </p>

        <div class="logout-confirm-actions">
            <button
                type="button"
                class="logout-cancel-button"
                data-close-logout
            >
                Batal
            </button>

            <button
                type="button"
                id="confirmLogoutButton"
                class="logout-submit-button"
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
</body>
</html>
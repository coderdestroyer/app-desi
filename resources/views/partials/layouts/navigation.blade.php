<header class="main-header">

    <div class="container navbar-container">

        {{-- BRAND --}}
        <a href="{{ route('home') }}" class="brand">

            <img
                src="{{ asset('images/logo/logo-sumut.png') }}"
                alt="Logo Sumatera Utara"
                class="brand-logo"
            >

            <div class="brand-text">

                <strong>
                    DASHBOARD EXECUTIVE
                </strong>

                <span>
                    SUMATERA INVESTMENT
                </span>

            </div>

        </a>


        {{-- MOBILE MENU BUTTON --}}
        <button
            class="mobile-menu-button"
            id="mobileMenuButton"
            aria-label="Buka menu"
        >
            <i class="fa-solid fa-bars"></i>
        </button>


        {{-- NAVIGATION --}}
        <nav
            class="main-navigation"
            id="mainNavigation"
        >

            <a
                href="{{ route('home') }}"
                class="nav-link
                {{ request()->routeIs('home') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-house"></i>

                Beranda
            </a>


            <a
                href="{{ route('home') }}#overview"
                class="nav-link"
            >
                <i class="fa-solid fa-puzzle-piece"></i>

                Overview
            </a>


            <a
                href="{{ route('dashboard') }}"
                class="nav-link
                {{ request()->routeIs('dashboard') ? 'active' : '' }}"
            >
                <i class="fa-solid fa-grip"></i>

                Dashboard
            </a>


            <a
                href="{{ route('investment.map') }}"
                class="nav-link
                {{ request()->routeIs('investment.map') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-map"></i>

                Peta Investasi
            </a>


            <a
                href="{{ route('contact') }}"
                class="nav-link
                {{ request()->routeIs('contact') ? 'active' : '' }}"
            >
                <i class="fa-regular fa-envelope"></i>

                Kontak
            </a>

        </nav>


        {{-- ACTIONS --}}
        <div class="navbar-actions">

            <form class="search-box">

                <input
                    type="search"
                    placeholder="Cari informasi..."
                    aria-label="Cari informasi"
                >

                <button type="submit">

                    <i class="fa-solid fa-magnifying-glass"></i>

                </button>

            </form>


            @auth

                <a
                    href="{{ route('dashboard') }}"
                    class="login-button"
                >
                    <i class="fa-solid fa-grip"></i>

                    Dashboard
                </a>

            @else

                <a
                    href="{{ route('login') }}"
                    class="login-button"
                >
                    <i class="fa-regular fa-user"></i>

                    Login
                </a>

            @endauth

        </div>

    </div>

</header>
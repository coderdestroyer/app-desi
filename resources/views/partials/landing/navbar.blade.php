<header class="main-header" id="mainHeader">

    <div class="container navbar-container">

        {{-- BRAND --}}
        <a href="#hero" class="brand">

            <img
                src="{{ asset('images/logo-dpmptsp.png') }}"
                alt="Logo DPMPTSP Sumatera Utara"
                class="brand-logo"
            >

            {{-- <div class="brand-text">
                <strong>DPMPTSP</strong>
                <span>Provinsi Sumatera Utara</span>
            </div> --}}

        </a>

        {{-- MOBILE BUTTON --}}
        <button
            type="button"
            class="mobile-menu-button"
            id="mobileMenuButton"
            aria-label="Buka menu navigasi"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        {{-- NAVIGATION --}}
     <nav class="main-navigation" id="mainNavigation">

  <a
    id="nav-home"
    href="{{ request()->routeIs('home') ? '#hero' : route('home') }}"
    class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">
    Beranda
</a>

<a
    id="nav-about"
    href="{{ request()->routeIs('home') ? '#overview' : route('home') . '#overview' }}"
    class="nav-link">
    Overview
</a>

<a
    id="nav-map"
    href="{{ route('investment.map') }}"
    class="nav-link {{ request()->routeIs('investment.map') ? 'active' : '' }}">
    Peta Investasi
</a>

</nav>

        {{-- LOGIN --}}
       <div class="navbar-action">

    @auth

        <a
            href="{{ route('dashboard') }}"
            class="login-button"
        >
            <i class="fa-solid fa-user"></i>

            {{ ucfirst(strtolower(explode(' ', Auth::user()->name)[0])) }}

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
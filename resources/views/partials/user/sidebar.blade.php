<aside class="sidebar">

    <div class="profile-box">

        <img
            src="{{ asset('images/logo-dpmptsp.png') }}"
            class="sidebar-logo"
            alt="Logo DPMPTSP">

        <div class="avatar">
            {{ strtoupper(substr(Auth::user()->name,0,1)) }}
        </div>

        <h3>{{ Auth::user()->name }}</h3>

        <span>{{ ucfirst(Auth::user()->role) }}</span>

    </div>

    <hr>

    <p class="menu-title">MENU UTAMA</p>

    {{-- Beranda --}}
    <a href="{{ route('home') }}">
        <i class="fa-solid fa-house"></i>
        Beranda
    </a>

    {{-- Tentang --}}
    <a href="{{ route('home') }}#overview">
        <i class="fa-solid fa-circle-info"></i>
        Overview
    </a>

    {{-- Peta Investasi --}}
    <a href="{{ route('investment.map') }}">
        <i class="fa-solid fa-map-location-dot"></i>
        Peta Investasi
    </a>

    <p class="menu-title mt">PENGATURAN</p>

    {{-- Ganti Password --}}
    <a href="{{ route('user.password.edit') }}"
       class="{{ request()->routeIs('user.password.*') ? 'active' : '' }}">
        <i class="fa-solid fa-key"></i>
        Ganti Kata Sandi
    </a>

    {{-- Logout --}}
    <form method="POST" action="{{ route('logout') }}">
        @csrf

        <button class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i>
            Keluar
        </button>
    </form>

</aside>
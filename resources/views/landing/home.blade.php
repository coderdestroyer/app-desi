<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @vite([
        'resources/css/home.css',
        'resources/css/navbar.css',
        'resources/css/about.css',
        'resources/js/navbar.js',
        'resources/js/home.js',
        'resources/js/about.js',
    ])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

    {{-- NAVBAR --}}
    @include('partials.landing.navbar')

    {{-- HERO --}}
    <section id="hero" class="hero">

        <div class="hero-content">

            {{-- <h5>Selamat Datang</h5> --}}

            <h1>
                Dashboard Executive for Sumatera Investment
            </h1>

            <p>
                Dashboard eksekutif untuk investasi di Sumatera dirancang untuk
                memberikan gambaran cepat (high-level overview) bagi Gubernur,
                Bupati, Walikota, Investor dan Calon Investor serta para pengambil
                keputusan lain guna memantau Potensi Unggulan Daerah berdasarkan
                Produk Domestik Regional Bruto (PDRB) Atas Dasar Harga Konstan
                Menurut Lapangan Usaha, Realisasi Investasi, Realisasi Ekspor dan Impor,
                Realisasi Perdagangan Dalam Negeri di seluruh wilayah Pulau Sumatera.
            </p>

            <div class="hero-button">

                <a href="{{ route('analysis') }}" class="btn1">
                    Mulai Analisis
                </a>

                <a href="{{ route('comparison') }}" class="btn1">
                    Analisis Sektor
                </a>

            </div>

            <div class="hero-stats">

                <div>
                    <h3>{{ \App\Models\Kabupaten::count() }}</h3>
                    <span>Kabupaten/Kota</span>
                </div>

                <div>
                    <h3>4</h3>
                    <span>Metode Analisis</span>
                </div>

                <div>
                    <h3>GIS</h3>
                    <span>Pemetaan Investasi</span>
                </div>

            </div>

        </div>

        <div class="hero-image">

            <img src="{{ asset('images/gedung-dpmptsp.jpg') }}" alt="Gedung DPMPTSP">

        </div>

    </section>

    {{-- ABOUT --}}
    @include('landing.about-section')

    {{-- FOOTER --}}
    {{-- @include('partials.landing.footer') --}}

</body>

</html>
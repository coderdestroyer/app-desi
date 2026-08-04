<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Peta Investasi | DPMPTSP Pulau Sumatera</title>

    @vite([
        'resources/css/navbar.css',
        'resources/css/map.css',
        'resources/js/navbar.js',
        'resources/js/map.js',
        'resources/css/footer.css'
    ])

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
          rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <link rel="stylesheet"
          href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

</head>

<body>

    {{-- NAVBAR --}}
    @include('partials.landing.navbar')

    <section class="map-page">

        <div class="map-container">

            {{-- HEADER --}}
            <div class="map-header">

                <h1>Peta Investasi Sumatera</h1>

                <p>

                    Jelajahi persebaran potensi investasi setiap
                    Kabupaten/Kota dan Provinsi di Pulau Sumatera.

                    Klik marker pada peta untuk melihat informasi
                    daerah.

                </p>

            </div>

            {{-- SEARCH --}}
            <div class="search-box">

                <i class="fa-solid fa-magnifying-glass"></i>

                <input
                    type="text"
                    id="searchKabupaten"
                    placeholder="Cari Kabupaten/Kota atau Provinsi..."
                >

            </div>

            {{-- MAP + DETAIL --}}
            <div class="map-wrapper">

                {{-- MAP --}}
                <div class="map-card">

                    <div id="map"></div>

                </div>

                {{-- DETAIL --}}
                <div class="info-panel">

                    <h3>Informasi Daerah</h3>

                    {{-- Default --}}
                    <div id="detailKosong">

                        <i class="fa-solid fa-map-location-dot"></i>

                        <h4>

                            Belum Ada Daerah Dipilih

                        </h4>

                        <p>

                            Klik salah satu marker pada peta
                            untuk melihat informasi investasi.

                        </p>

                    </div>

                    {{-- Detail --}}
                    <div
                        id="detailDaerah"
                        style="display:none;"
                    >

                        <div class="detail-header">

                            <h2 id="namaDaerah"></h2>

                            <span id="provinsiDaerah">Memuat...</span>

                        </div>

                        <div class="info-item">

                            <label>

                                Sektor Unggulan

                            </label>

                            <strong id="sektorDaerah">

                                Memuat...

                            </strong>

                        </div>

                        <div class="info-item">

                            <label>

                                Status

                            </label>

                            <strong id="statusDaerah">

                                Sudah Dianalisis

                            </strong>

                        </div>

                        <div class="info-item">

                            <label>

                                Koordinat

                            </label>

                            <strong id="koordinatDaerah">

                                -

                            </strong>

                        </div>

                        {{-- <a
                            href="#"
                            id="detailButton"
                            class="detail-btn"
                        >

                            Lihat Detail Analisis

                        </a> --}}

                    </div>

                </div>

            </div>

        </div>
    </section>

    {{-- FOOTER --}}
     @include('partials.landing.footer')
      {{-- <button
    type="button"
        class="back-to-top"
        id="backToTop"
        aria-label="Kembali ke atas"
    >

        <i class="fa-solid fa-arrow-up"></i>

    </button> --}}

    <script>
    window.lokasi = @json($lokasi);
</script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

</body>

</html>
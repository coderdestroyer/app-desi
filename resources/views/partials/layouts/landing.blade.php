
<!DOCTYPE html>
<html lang="id">

<head>
<link
rel="stylesheet"
href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
/>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'DPMPTSP Provinsi Sumatera Utara')
    </title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">


    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    {{-- Font Awesome --}}
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    {{-- VITE --}}
    @vite([
        'resources/css/app.css',
        'resources/css/comparison.css',
        'resources/js/app.js',
        'resources/css/footer.css'
    ])


    @stack('styles')

</head>
<script
src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
</script>

<body>

    @include('partials.landing.navbar')

    <main>

        @yield('content')

    </main>

    @include('partials.landing.footer')

    <button
        type="button"
        class="back-to-top"
        id="backToTop"
        aria-label="Kembali ke atas"
    >

        <i class="fa-solid fa-arrow-up"></i>

    </button>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')

    <div class="summary-icon">

        <i class="{{ $card['icon'] }}"></i>

    </div>


</body>

</html>
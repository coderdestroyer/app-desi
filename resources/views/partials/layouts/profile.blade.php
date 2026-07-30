<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        @yield('title', 'Profil User | DPMPTSP Provinsi Sumatera Utara')
    </title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    @vite([
        'resources/css/user.css',
        'resources/js/app.js',
    ])

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>

<body>

    <div class="user-layout">

        {{-- SIDEBAR USER --}}
        @include('partials.user.sidebar')


        {{-- CONTENT --}}
        <main class="user-main">

            @yield('content')

        </main>

    </div>

</body>

</html>
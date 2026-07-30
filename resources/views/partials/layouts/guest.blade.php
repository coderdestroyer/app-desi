
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        @yield('title', config('app.name', 'DPMPTSP WebGIS'))
    </title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    {{-- Fonts --}}
    <link
        rel="preconnect"
        href="https://fonts.bunny.net"
    >

    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
        rel="stylesheet"
    >

    {{-- Vite --}}
    @vite([
        'resources/css/app.css',
        'resources/js/app.js'
    ])

    {{-- CSS tambahan halaman --}}
    @stack('styles')
</head>


<body class="font-sans text-gray-900 antialiased bg-white">

    <main class="min-h-screen flex flex-col items-center justify-center px-4 py-12">
        <div class="w-full max-w-[480px]">
            {{ $slot }}
        </div>
    </main>

    {{-- JavaScript tambahan halaman --}}
    @stack('scripts')

</body>

</html>
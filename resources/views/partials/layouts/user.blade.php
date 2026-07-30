<!DOCTYPE html>
<html lang="id">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title')</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('images/logo-sumut.png') }}">

    @vite([
        'resources/css/app.css',
        'resources/css/user.css',
        'resources/js/app.js'
    ])

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

</head>

<body>

<div class="user-wrapper">

    @include('partials.user.sidebar')

    <main class="content">

        @yield('content')

    </main>

</div>

</body>
</html>
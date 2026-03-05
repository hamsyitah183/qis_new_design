<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="QIS System Error">
    <meta name="Author" content="QIS System">

    <!-- Title-->
    <title>QIS SYSTEM | @yield('title', 'Error')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Main Theme JS -->
    <script src="{{ asset('build2/assets/main.js') }}"></script>

    <!-- ICONS CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/icon-fonts/icons.css') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/bootstrap/css/bootstrap.min.css') }}" id="style">

    <!-- Node Waves CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/node-waves/waves.min.css') }}">

    <!-- APP CSS & APP SCSS -->
    <link rel="preload" as="style" href="{{ asset('build2/assets/app-BXaKe1N-.css') }}">
    <link rel="stylesheet" href="{{ asset('build2/assets/app-BXaKe1N-.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/css/errors.css'])

    @stack('style')

    <style>
        /*error page centering*/
        .error-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--default-body-bg-color);
        }
    </style>

</head>

<body class="bg-white">

    <!-- Loader -->
    <div id="loader">
        <img src="https://laravelui.spruko.com/xintra/build2/assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->

    <div class="page error_page">
        <!-- QIS Logo -->
        <div style="position: absolute; top: 40px; left: 50%; transform: translateX(-50%); z-index: 10;">
            <img src="{{ asset('asset/doa-logo-black.png') }}" alt="QIS Logo" style="height: 80px; object-fit: contain;">
        </div>
        
        @yield('content')
    </div>

    <!-- Popper JS -->
    <script src="{{ asset('build2/assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('build2/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Node Waves JS -->
    <script src="{{ asset('build2/assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- APP JS -->
    <link rel="modulepreload" href="{{ asset('build2/assets/app-C4M4tSMb.js') }}" />
    <script type="module" src="{{ asset('build2/assets/app-C4M4tSMb.js') }}"></script>

    @stack('scripts')

</body>

</html>

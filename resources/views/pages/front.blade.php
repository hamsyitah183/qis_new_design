<!DOCTYPE html>
<html lang="en" dir="ltr" data-nav-layout="vertical" data-theme-mode="light" data-header-styles="light"
    data-menu-styles="dark" data-toggled="close">

<head>

    <!-- Meta Data -->
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="Description" content="Laravel Bootstrap Responsive Admin Web Dashboard Template">
    <meta name="Author" content="Spruko Technologies Private Limited">
    <meta name="keywords"
        content="laravel template, laravel, laravel admin, admin bootstrap, laravel admin template, dashboard, admin panel template, laravel framework, admin template, laravel admin panel, admin, laravel dashboard, dashboard for laravel, admin panel for laravel, bootstrap admin panel template.">

    <!-- Title-->
    <title> Xintra - Laravel Bootstrap 5 Premium Admin & Dashboard Template </title>


    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Main Theme JS -->
    <script src="{{ asset('build2/assets/main.js') }}"></script>

    <!-- ICONS CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/icon-fonts/icons.css') }}">

    <!-- Choices JS -->
    <script src="{{ asset('build2/assets/libs/choices.js/public/assets/scripts/choices.min.js') }}"></script>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/bootstrap/css/bootstrap.min.css') }}" id="style">

    <!-- Node Waves CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/node-waves/waves.min.css') }}">

    <!-- Simplebar CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/simplebar/simplebar.min.css') }}">

    <!-- Color Picker CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/flatpickr/flatpickr.min.css') }}">
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/@simonwep/pickr/themes/nano.min.css') }}">

    <!-- Choices CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/choices.js/public/assets/styles/choices.min.css') }}">

    <!-- FlatPickr CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/flatpickr/flatpickr.min.css') }}">

    <!-- Auto Complete CSS -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css') }}">

    <!-- APP CSS & APP SCSS -->
    <link rel="preload" as="style" href="{{ asset('build2/assets/app-BXaKe1N-.css') }}">
    <link rel="stylesheet" href="{{ asset('build2/assets/app-BXaKe1N-.css') }}">

    <!-- FlatPickr CSS (duplicate, can remove one if redundant) -->
    <link rel="stylesheet" href="{{ asset('build2/assets/libs/flatpickr/flatpickr.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('style')

</head>

<body class="">


    <!-- Loader -->
    <div id="loader">
        <img src="https://laravelui.spruko.com/xintra/build2/assets/images/media/loader.svg" alt="">
    </div>
    <!-- Loader -->

    <div class="page">

        @yield('content')






    </div>


    <!-- Popper JS -->
    <script src="{{ asset('build2/assets/libs/@popperjs/core/umd/popper.min.js') }}"></script>

    <!-- Bootstrap JS -->
    <script src="{{ asset('build2/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Node Waves JS -->
    <script src="{{ asset('build2/assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- Simplebar JS -->
    <script src="{{ asset('build2/assets/libs/simplebar/simplebar.min.js') }}"></script>

    <!-- Simplebar Module -->
    <link rel="modulepreload" href="{{ asset('build2/assets/simplebar-B35Aj-bA.js') }}" />
    <script type="module" src="{{ asset('build2/assets/simplebar-B35Aj-bA.js') }}"></script>

    <!-- Auto Complete JS -->
    <script src="{{ asset('build2/assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js') }}"></script>

    <!-- Color Picker JS -->
    <script src="{{ asset('build2/assets/libs/@simonwep/pickr/pickr.es5.min.js') }}"></script>

    <!-- Date & Time Picker JS -->
    <script src="{{ asset('build2/assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <!-- Vanilla-Wizard JS -->
    <script src="{{ asset('build2/assets/libs/vanilla-wizard/js/wizard.min.js') }}"></script>

    <!-- Internal Form Wizard JS -->
    <script src="{{ asset('build2/assets/form-wizard.js') }}"></script>

    <!-- Form Wizard Init -->
    {{-- <link rel="modulepreload" href="{{ asset('build2/assets/form-wizard-init-iKT7VXTT.js') }}" />
    <script type="module" src="{{ asset('build2/assets/form-wizard-init-iKT7VXTT.js') }}"></script> --}}

    <!-- Sticky JS -->
    <script src="{{ asset('build2/assets/sticky.js') }}"></script>

    <!-- Custom Switcher JS -->
    {{-- <link rel="modulepreload" href="{{ asset('build2/assets/custom-switcher-BayzdO2G.js') }}" />
    <script type="module" src="{{ asset('build2/assets/custom-switcher-BayzdO2G.js') }}"></script> --}}

    <!-- APP JS -->
    <link rel="modulepreload" href="{{ asset('build2/assets/app-C4M4tSMb.js') }}" />
    <script type="module" src="{{ asset('build2/assets/app-C4M4tSMb.js') }}"></script>
    <!-- END SCRIPTS -->

    @stack('scripts')

</body>

</html>

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
    <title>QIS SYSTEM | @yield('pageName')</title>
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

    <!-- Prism CSS -->
    <link rel="stylesheet"
        href="https://laravelui.spruko.com/xintra/build/assets/libs/prismjs/themes/prism-coy.min.css">

    <link rel="stylesheet" href="https://laravelui.spruko.com/xintra/build/assets/libs/filepond/filepond.min.css">
    <link rel="stylesheet"
        href="https://laravelui.spruko.com/xintra/build/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.css">
    <link rel="stylesheet"
        href="https://laravelui.spruko.com/xintra/build/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.css">
    <link rel="stylesheet" href="https://laravelui.spruko.com/xintra/build/assets/libs/dropzone/dropzone.css">

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

        <!-- Main-Header -->
        <header class="app-header sticky" id="header">

            <!-- Start::main-header-container -->
            @include('pages.includes.header')
            <!-- End::main-header-container -->

        </header>
        <!-- End Main-Header -->

        <!--Main-Sidebar-->
        @include('pages.includes.aside')
        <!-- End Main-Sidebar-->

        <!-- Start::app-content -->
        <div class="main-content app-content">
            <div class="container-fluid">

                @yield('breadcrumb')

                @yield('content')

            </div>
        </div>
        <!-- End::content  -->







    </div>

    <!-- SCRIPTS -->
    <!-- Scroll To Top -->
    {{-- <div class="scrollToTop">
        <span class="arrow"><i class="ti ti-arrow-narrow-up fs-20"></i></span>
    </div> --}}
    <div id="responsive-overlay"></div>
    <!-- Scroll To Top -->

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
    <link rel="modulepreload" href="{{ asset('build2/assets/custom-switcher-BayzdO2G.js') }}" />
    <script type="module" src="{{ asset('build2/assets/custom-switcher-BayzdO2G.js') }}"></script>

    <!-- APP JS -->
    <link rel="modulepreload" href="{{ asset('build2/assets/app-C4M4tSMb.js') }}" />
    <script type="module" src="{{ asset('build2/assets/app-C4M4tSMb.js') }}"></script>
    <!-- END SCRIPTS -->

    <!-- Prism JS -->
    <!-- -->
    <script src="{{ asset('build2/assets/libs/prismjs/prism.js') }}"></script>
    <link rel="modulepreload" href="{{ asset('build2/assets/prism-custom-DndhZ9SR.js') }}" />
    <script type="module" src="{{ asset('build2/assets/prism-custom-DndhZ9SR.js') }}"></script>

    <!-- Filepond JS -->
    <!-- -->
    <script src="{{ asset('build2/assets/libs/filepond/filepond.min.js') }}"></script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-image-preview/filepond-plugin-image-preview.min.js') }}">
    </script>
    <script
        src="{{ asset('build2/assets/libs/filepond-plugin-image-exif-orientation/filepond-plugin-image-exif-orientation.min.js') }}">
    </script>
    <script
        src="{{ asset('build2/assets/libs/filepond-plugin-file-validate-size/filepond-plugin-file-validate-size.min.js') }}">
    </script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-file-encode/filepond-plugin-file-encode.min.js') }}">
    </script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-image-edit/filepond-plugin-image-edit.min.js') }}"></script>
    <script
        src="{{ asset('build2/assets/libs/filepond-plugin-file-validate-type/filepond-plugin-file-validate-type.min.js') }}">
    </script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-image-crop/filepond-plugin-image-crop.min.js') }}"></script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-image-resize/filepond-plugin-image-resize.min.js') }}">
    </script>
    <script src="{{ asset('build2/assets/libs/filepond-plugin-image-transform/filepond-plugin-image-transform.min.js') }}">
    </script>

    <!-- Dropzone JS -->
    <script src="{{ asset('build2/assets/libs/dropzone/dropzone-min.js') }}"></script>

    <!-- Fileupload JS -->
    <link rel="modulepreload" href="{{ asset('build2/assets/fileupload-DSJ_d_h8.js') }}" />
    <script type="module" src="{{ asset('build2/assets/fileupload-DSJ_d_h8.js') }}"></script>

    <!-- Dropzone CSS -->
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" rel="stylesheet" /> -->

    <!-- Dropzone JS -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script> -->



    @stack('scripts')

</body>

</html>

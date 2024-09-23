<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="POS Dashboard">
    <meta name="author" content="Your Company">

    <title>POS Dashboard</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}"/>

    <!-- Core Stylesheets -->
    <link rel="stylesheet" href="{{ asset('assets/css/backend-plugin.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/backend.css?v=1.0.0') }}">

    <!-- Icon Fonts -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/line-awesome/dist/line-awesome/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/remixicon/fonts/remixicon.css') }}">

    <!-- Page Specific Styles -->
    @yield('specificpagestyles')
</head>
<body>
    <!-- Loader (optional, can be toggled as needed) -->
    <div id="loading" aria-hidden="true" style="display: none;">
        <div id="loading-center"></div>
    </div>

    <!-- Main Wrapper -->
    <div class="wrapper">
        <!-- Sidebar -->
        @include('dashboard.body.sidebar')

        <!-- Navbar -->
        @include('dashboard.body.navbar')

        <!-- Main Content -->
        <div class="content-page">
            @yield('container')
        </div>
    </div>
    <!-- End Main Wrapper -->

    <!-- Footer -->
    @include('dashboard.body.footer')

    <!-- Core JavaScript -->
    <script src="{{ asset('assets/js/backend-bundle.min.js') }}"></script>

    <!-- FontAwesome Script -->
    <script src="https://kit.fontawesome.com/4c897dc313.js" crossorigin="anonymous"></script>

    <!-- Page Specific Scripts -->
    @yield('specificpagescripts')

    <!-- App JS -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
</body>
</html>

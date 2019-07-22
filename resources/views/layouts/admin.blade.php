<!DOCTYPE html>
<html dir="ltr" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('backend/assets/images/favicon.png') }}">
    <title>YIFANG C.M.E. BACKEND</title>

    <link href="{{ asset('backend/assets/libs/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('backend/assets/libs/flot/css/float-chart.css')}}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('backend/dist/css/style.min.css')}}" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
        #navbarSupportedContent{
            background: #eeeeee !important;
        }

        .topbar .navbar-header{
            background: #fff !important;
        }

        .left-sidebar{
            background: #fff !important;
        }

        #sidebarnav{
            background: #fff !important;
        }

        .sidebar-nav ul .sidebar-item .sidebar-link{
            opacity: 1 !important;
            color: #000;
            padding: 10px 10px;
        }

        .navbar-dark .navbar-nav .nav-link{
            color: rgba(255, 255, 255, 1) !important;
        }

        .card-body h3{
            line-height: 1.6 !important;
        }

        .page-wrapper>.container-fluid{
            padding: 1em !important;
        }

        #main-wrapper .topbar .navbar-collapse[data-navbarbg=skin5], #main-wrapper .topbar[data-navbarbg=skin5]{
            background-color: initial;
        }

        .sidebar-nav ul .sidebar-item .sidebar-link i{
            color: #000;
        }

        .sidebar-nav .has-arrow:after{
            border-color: #000;
        }

        .sidebar-nav ul .sidebar-item.selected>.sidebar-link{
            background-color: #3DCC9C;
            color: #fff;
            border-radius: 4px;
        }

        .sidebar-nav ul .sidebar-item.selected>.sidebar-link i{
            color: #fff;
        }

        .sidebar-nav .selected .has-arrow:after{
            border-color: #fff;
        }

        .sidebar-nav .has-arrow:after{
            right: 8px;
            top: 23px;
            width: 6px;
            height: 6px;
        }

        #main-wrapper .left-sidebar[data-sidebarbg=skin5], #main-wrapper .left-sidebar[data-sidebarbg=skin5] ul{
            background-color: #fff;
        }

        .navbar-dark .navbar-nav .nav-link{
            color: #c32924 !important;
        }

        .topbar{
            box-shadow: initial !important;
        }

        .btn-success{
            background-color: #3DCC9C;
            border-color: #3DCC9C;
        }

        .left-sidebar{
            top: -16px !important;
        }
    </style>
    @yield('styles')
</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="lds-ripple">
        <div class="lds-pos"></div>
        <div class="lds-pos"></div>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper">

    @include('partials.admin._header')
    @include('partials.admin._sidebar')

    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper">
        <!-- ============================================================== -->
        <!-- Container fluid  -->
        <!-- ============================================================== -->
        <div class="container-fluid">
            @yield('content')
        </div>
        <!-- ============================================================== -->
        <!-- End Container fluid  -->
        <!-- ============================================================== -->
        @include('partials.admin._footer')
    </div>
    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="{{ asset('backend/assets/libs/jquery/dist/jquery.min.js') }}"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="{{ asset('backend/assets/libs/popper.js/dist/umd/popper.min.js') }}"></script>
<script src="{{ asset('backend/assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('backend/assets/libs/perfect-scrollbar/dist/perfect-scrollbar.jquery.min.js') }}"></script>
<script src="{{ asset('backend/assets/extra-libs/sparkline/sparkline.js') }}"></script>
<!--Wave Effects -->
<script src="{{ asset('backend/dist/js/waves.js') }}"></script>
<!--Menu sidebar -->
<script src="{{ asset('backend/dist/js/sidebarmenu.js') }}"></script>
<!--Custom JavaScript -->
<script src="{{ asset('backend/dist/js/custom.min.js') }}"></script>
<script src="{{ asset('backend/assets/libs/inputmask/dist/min/jquery.inputmask.bundle.min.js') }}"></script>
<script src="{{ asset('backend/dist/js/pages/mask/mask.init.js') }}"></script>

@yield('scripts')

</body>

</html>
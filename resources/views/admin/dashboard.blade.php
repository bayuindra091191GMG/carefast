@extends('layouts.admin')

@section('content')

    @php($projectAdminId = \Illuminate\Support\Facades\Auth::guard('admin')->user()->project_id)
    <div class="container-fluid">
        @if($projectAdminId != 0)
            <div class="row">
                <div class="col-12 mb-3">
                    <h3>Selamat Datang {{$userAdmin->first_name}} {{$userAdmin->last_name}}.</h3>
                </div>
            </div>
        @else
            <!-- ============================================================== -->
            <!-- Sales Cards  -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-12 mb-3">
                    <h3>Selamat Datang {{$userAdmin->first_name}} {{$userAdmin->last_name}}.</h3>
                </div>
                <!-- Column -->
                <div class="col-md-6 col-lg-2 col-xlg-3">
                    <div class="card card-hover">
                        <a href="{{ route('admin.project.information.index') }}" class="box bg-custom-dark-blue text-center">
                            <h1 class="font-light text-white"><i class="mdi mdi-book-open"></i></h1>
                            <h6 class="text-white">Daftar Project</h6>
                        </a>
                    </div>
                </div>
                <!-- Column -->
                <div class="col-md-6 col-lg-2 col-xlg-3">
                    <div class="card card-hover">
                        <a href="{{ route('admin.project.information.create') }}" class="box bg-custom-dark-blue text-center">
                            <h1 class="font-light text-white"><i class="mdi mdi-hospital-building"></i></h1>
                            <h6 class="text-white">Tambah Project</h6>
                        </a>
                    </div>
                </div>
                <!-- Column -->
                <div class="col-md-6 col-lg-2 col-xlg-3">
                    <div class="card card-hover">
                        <a href="{{ route('admin.employee.index') }}" class="box bg-custom-dark-blue text-center">
                            <h1 class="font-light text-white"><i class="mdi mdi-account-multiple"></i></h1>
                            <h6 class="text-white">Daftar Karyawan</h6>
                        </a>
                    </div>
                </div>
                <!-- Column -->
            </div>
            <!-- ============================================================== -->
            <!-- Sales chart -->
            <!-- ============================================================== -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-md-flex align-items-center">
                                <div>
                                    <h4 class="card-title">Data</h4>
                                    {{--                                <h5 class="card-subtitle">Overview of Latest Month</h5>--}}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-2">
                                    <a href="#">
                                        <div class="bg-dark p-10 text-white text-center">
                                            <i class="fa fa-check-circle m-b-5 font-16"></i>
                                            <h5 class="m-b-0 m-t-5">{{$totalAttendanceTodays}}</h5>
                                            <small class="font-light">Total Absensi Selesai Hari ini <br>&nbsp;</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-2">
                                    <a href="#">
                                        <div class="bg-dark p-10 text-white text-center">
                                            <i class="fa fa-check-circle m-b-5 font-16"></i>
                                            <h5 class="m-b-0 m-t-5">{{$totalAttendanceTodayNotDone}}</h5>
                                            <small class="font-light">Total Absensi Pending Hari ini <br> (Baru melakukan absen masuk)</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="#">
                                        <div class="bg-dark p-10 text-white text-center">
                                            <i class="fa fa-check-circle m-b-5 font-16"></i>
                                            <h5 class="m-b-0 m-t-5">{{$totalAttendanceMonths}}</h5>
                                            <small class="font-light">Total Absensi Periode<br>({{$filterDateStart}} - {{$filterDateEnd}})</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-4">
                                    <a href="#">
                                        <div class="bg-dark p-10 text-white text-center">
                                            <i class="fa fa-check-circle m-b-5 font-16"></i>
                                            <h5 class="m-b-0 m-t-5">{{$totalAttendances}}</h5>
                                            <small class="font-light">Total Absensi Sampai Saat ini<br>&nbsp;</small>
                                        </div>
                                    </a>
                                </div>
                            </div>
                            <div class="row pt-5">
                                <!-- column -->
                                {{--                            <div class="col-lg-9">--}}
                                {{--                                <div class="flot-chart">--}}
                                {{--                                    <div class="flot-chart-content" id="flot-line-chart"></div>--}}
                                {{--                                </div>--}}
                                {{--                            </div>--}}
                                <div class="col-lg-12">
                                    <div class="row">
                                        <div class="col-3">
                                            <div class="bg-dark p-10 text-white text-center">
                                                <i class="fa fa-building m-b-5 font-16"></i>
                                                <h5 class="m-b-0 m-t-5">{{$totalProjects}}</h5>
                                                <small class="font-light">Total Active Project<br>&nbsp;</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="bg-dark p-10 text-white text-center">
                                                <i class="fa fa-user m-b-5 font-16"></i>
                                                <h5 class="m-b-0 m-t-5">{{$totalEmployees}}</h5>
                                                <small class="font-light">Total Active Employee<br>&nbsp;</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <a href="{{route('admin.complaint.index')}}?type=customers&date_start={{$filterDateStart}}&date_end={{$filterDateEnd}}&project_id=0&category_id=0&status_id=10">
                                                <div class="bg-dark p-10 text-white text-center">
                                                    <i class="fa fa-table m-b-5 font-16"></i>
                                                    <h5 class="m-b-0 m-t-5">{{$totalComplaintCustomers}}</h5>
                                                    <small class="font-light"> Keluhan Pending Customer <br>({{$filterDateStart}} - {{$filterDateEnd}})</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-3">
                                            <a href="{{route('admin.complaint.index')}}?type=internals&date_start={{$filterDateStart}}&date_end={{$filterDateEnd}}&project_id=0&category_id=0&status_id=10">
                                                <div class="bg-dark p-10 text-white text-center">
                                                    <i class="fa fa-globe m-b-5 font-16"></i>
                                                    <h5 class="m-b-0 m-t-5">{{$totalComplaintInternals}}</h5>
                                                    <small class="font-light">Keluhan Pending Internal<br>({{$filterDateStart}} - {{$filterDateEnd}})</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <!-- column -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <div class="col-md-12">
                <br><br><br>
                <br><br><br>
            </div>
        </div>
    </div>

@endsection

@section('styles')
    <style>
        .text-black{
            color: #000;
        }

        .size-30{
            font-size: 30px;
            color: black;
        }
        .size-33{
            font-size: 33px;
            color: black !important;
        }
        .width-25{
            width: 25%;
        }
        .no-padding{
            padding-left: 1.25em;
            padding-top:5px;
            padding-bottom:5px;
        }
        .badge-success{
            border-radius: 20px;
            padding: 2% 5% 2% 5%;
        }
    </style>
@endsection

@section('scripts')
    <!--This page JavaScript -->
    <!-- <script src="../../dist/js/pages/dashboards/dashboard1.js"></script> -->
    <!-- Charts js Files -->
    <script src="{{ asset('backend/assets/libs/flot/excanvas.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot/jquery.flot.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot/jquery.flot.pie.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot/jquery.flot.time.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot/jquery.flot.stack.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot/jquery.flot.crosshair.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/flot.tooltip/js/jquery.flot.tooltip.min.js') }}"></script>
    <script src="{{ asset('backend/dist/js/pages/chart/chart-page-init.js') }}"></script>
@endsection

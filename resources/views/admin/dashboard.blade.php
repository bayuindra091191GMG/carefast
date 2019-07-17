@extends('layouts.admin')

@section('content')


    <div class="row">
        <div class="col-12">
            <h3>Selamat Datang Admin Yifang C.M.E.</h3>
{{--            @if($isSuperAdmin)--}}
{{--                <h3>Selamat Datang Admin DWS</h3>--}}
{{--            @else--}}
{{--                <h3>Selamat Datang Admin Pengolahan Sampah {{ $userAdmin->waste_bank->name }}</h3>--}}
{{--            @endif--}}

        </div>
    </div>

    <div class="row">

{{--    @foreach($dashboardDatas as $dashboardData)--}}
{{--        <!-- column -->--}}
{{--            <div class="col-lg-4">--}}
{{--                <!-- card new -->--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <span class="badge badge-success font-bold">{{ $dashboardData->get('month') }}</span>--}}
{{--                    </div>--}}

{{--                    <ul class="list-style-none">--}}
{{--                        <li class="d-flex no-block card-body">--}}
{{--                            <div class="col-lg-12">--}}
{{--                                <span class="text-muted">Total Nilai Sampah</span><br>--}}
{{--                                <span class="text-muted size-30">Rp </span><span class="text-muted font-bold size-33">{{ number_format($dashboardData->get('totalPrice'), 2, ",", ".") }}</span>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body border-top">--}}
{{--                            <div class="col-12">--}}
{{--                                <span class="text-muted">Total Berat</span>--}}
{{--                                <br>--}}
{{--                                @if($dashboardData->get('totalWeight') > 0)--}}
{{--                                    <span class="text-muted font-bold size-33">{{ number_format($dashboardData->get('totalWeight') / 1000, 2, ",", ".") }} </span><span class="text-muted size-30">Kg</span>--}}
{{--                                @else--}}
{{--                                    <span class="text-muted font-bold size-33">0 </span><span class="text-muted size-30">Kg</span>--}}
{{--                                @endif--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body border-top">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-5">--}}
{{--                                    <span class="font-weight-bold">Jenis Sampah</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3 text-right">--}}
{{--                                    <span class="font-weight-bold">Berat (Kg)</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-4 text-right">--}}
{{--                                    <span class="font-weight-bold">Harga (Rp)</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        @foreach($dashboardData->get('wasteCategoryItems') as $wasteCategoryItem)--}}
{{--                            <li class="d-flex no-block card-body no-padding">--}}
{{--                                <div class="row w-100">--}}
{{--                                    <div class="col-5">--}}
{{--                                        <span class="text-muted">{{ $wasteCategoryItem->get('name') }}</span>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-3 text-right">--}}
{{--                                        <span class="text-black font-weight-bold">{{ number_format($wasteCategoryItem->get('weight') / 1000, 2, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                    <div class="col-4 text-right">--}}
{{--                                        <span class="text-black font-weight-bold">{{ number_format($wasteCategoryItem->get('price'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </li>--}}
{{--                        @endforeach--}}
{{--                    </ul>--}}
{{--                </div>--}}
{{--            </div>--}}

{{--        @endforeach--}}
{{--    </div>--}}


{{--    <div class="row">--}}
{{--        @foreach($dashboardDatas as $dashboardData)--}}
{{--            <div class="col-lg-4">--}}
{{--                <!-- card new -->--}}
{{--                <div class="card">--}}
{{--                    <div class="card-body">--}}
{{--                        <span class="badge badge-success font-bold">{{ $dashboardData->get('month') }}</span>--}}
{{--                    </div>--}}
{{--                    <ul class="list-style-none">--}}
{{--                        <li class="d-flex no-block card-body">--}}
{{--                            <div class="col-lg-12">--}}
{{--                                <span class="text-muted">Total User</span><br>--}}
{{--                                <span class="text-muted size-30"></span><span class="text-muted font-bold size-33">{{ $isSuperAdmin ? $dashboardData->get('totalCustomer') : "-" }}</span>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body border-top">--}}
{{--                            <div class="col-12">--}}
{{--                                <span class="text-muted">Total Poin yang Didistribusikan</span>--}}
{{--                                <br>--}}
{{--                                <span class="text-muted font-bold size-33">{{ $dashboardData->get('totalDistributedPoint') }} </span>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body border-top">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-12">--}}
{{--                                    <span class="font-weight-bold">Data Operasional</span>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total penjemputan sampah</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalTransaction'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total penjemputan rutin</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalRutin'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total penjemputan sekarang</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalOnDemand'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total antar sendiri</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalAntarSendiri'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total rumah kosong</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalEmptyHouse'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="d-flex no-block card-body no-padding">--}}
{{--                            <div class="row w-100">--}}
{{--                                <div class="col-9">--}}
{{--                                    <span class="text-muted">Total tidak ada sampah</span>--}}
{{--                                </div>--}}
{{--                                <div class="col-3">--}}
{{--                                    <div class="text-right">--}}
{{--                                        <span class="text-black font-bold">{{ number_format($dashboardData->get('totalNoWaste'), 0, ",", ".") }}</span>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                    </ul>--}}

{{--                </div>--}}
{{--            </div>--}}
{{--        @endforeach--}}

{{--    </div>--}}

{{--    <div class="row">--}}
{{--        <div class="col-lg-6">--}}
{{--            <h3>Volume Sampah</h3>--}}
{{--        </div>--}}
{{--    </div>--}}
{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="card">--}}
{{--                <div class="card-body">--}}
{{--                    <div class="row">--}}
{{--                        <!-- column -->--}}
{{--                        <div class="col-lg-12">--}}
{{--                            <div class="flot-chart">--}}
{{--                                <div class="flot-chart-content" id="flot-line-chart"></div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                        <!-- column -->--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
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
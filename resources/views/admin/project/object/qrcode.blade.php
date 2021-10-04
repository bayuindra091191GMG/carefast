@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>DETIL DATA PROJECT {{ $project->name }}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
{{--                        <button class="btn btn-danger " data-toggle="modal" data-target="#deleteModal" data-id="{{$project->id}}">HAPUS</button>--}}
                    </div>
                </div>

                <form>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="information-tab" href="{{ route('admin.project.information.show', ['id' => $project->id]) }}" role="tab" aria-controls="home" aria-selected="true">INFORMASI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="object-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.activity.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card">

                                            <div id="print-section"  class="body">
{{--                                                <div class="col-md-12">--}}
{{--                                                    <h2>Project Qr-Code</h2>--}}
{{--                                                    <div class="col-md-6 p-t-20 text-center">--}}
{{--                                                        <h3 style="font-weight: bold">{{$project->name}}</h3>--}}

{{--                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$project->code}}&size=200x200" alt="" title="" />--}}
{{--                                                        <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{$project->code}}&choe=UTF-8" alt="" title="" />--}}

{{--                                                        <br>--}}
{{--                                                        <br>--}}
{{--                                                    </div>--}}
{{--                                                    <hr>--}}
{{--                                                </div>--}}
                                                <div class="col-md-12 p-t-20">
                                                    <h2>Project Place Qr-Code</h2>
                                                    <div class="row">
                                                        @if($projectObjects->count() > 0)
                                                            @php($count=1)
                                                            @foreach($placeArr as $projectObject)

                                                                <div class="col-md-4 text-center">
                                                                    <div class="form-group form-float form-group-lg">
                                                                        <h3 style="font-weight: bold">{{$projectObject["name"]}}</h3>

{{--                                                                        <img src="https://api.qrserver.com/v1/create-qr-code/?data={{$projectObject["qr_code"]}}&size=200x200" alt="" title="" />--}}
                                                                        <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{$projectObject["qr_code"]}}&choe=UTF-8" alt="" title="" />

                                                                        <br>
                                                                        <br>
                                                                    </div>
                                                                </div>
                                                                @php($count++)
                                                            @endforeach
                                                        @else
                                                            <h4>BELUM ADA PLACE DAN OBJECT TERPILIH</h4>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                </form>
            </div>
        </div>
    </div>
@endsection


@section('styles')
{{--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">--}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <style>
        .fancybox-viewer img{
            width: 150px;
            height: auto;
        }

        @media print {
            thead {display: table-header-group;}
            tfoot {display: table-footer-group;}
            #non-printable { display: none; }
            button {display: none;}

            body {margin: 0;}

            .col-md-6{
                -webkit-box-flex: 0 !important;
                -ms-flex: 0 0 50% !important;
                flex: 0 0 50% !important;
                max-width: 50% !important;
            }
            .col-md-6{
                -webkit-box-flex: 0 !important;
                -ms-flex: 0 0 50% !important;
                flex: 0 0 100% !important;
                max-width: 100% !important;
            }
            .text-center{
                text-align: center !important;
            }
        }
    </style>
@endsection
@section('scripts')
    <script>
        function print(){
            w=window.open();
            w.document.write($('#print-section').html());
            w.print();
            w.close();
        }
    </script>
@endsection

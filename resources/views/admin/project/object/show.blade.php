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
                                <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.set-schedule', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">JADWAL EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="object-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="plotting-tab" href="{{ route('admin.project.activity.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="col-md-12 col-12 text-right">
                                                @if($isCreate)
                                                    <a href="{{ route('admin.project.object.create', ['id' => $project->id]) }}" class="btn btn-success">TAMBAH OBJECT</a>
                                                @else
                                                    <a href="{{ route('admin.project.object.edit', ['id' => $project->id]) }}" class="btn btn-primary">UBAH</a>
                                                @endif
                                                <a href="{{ route('admin.project.object.qrcode', ['id' => $project->id]) }}" class="btn btn-primary">SHOW QR CODE</a>
                                            </div>
                                            <div class="body">

                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-hover" id="tab_logic">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center" style="width: 4%">
                                                                    No
                                                                </th>
                                                                <th class="text-center" style="width: 20%">
                                                                    Place
                                                                </th>
                                                                <th class="text-center" style="width: 16%">
                                                                    Place QR Code
                                                                </th>
                                                                <th class="text-center" style="width: 20%">
                                                                    Object (Jika ada)
                                                                </th>
                                                                <th class="text-center" style="width: 20%">
                                                                    Sub Object 1 (Jika ada)
                                                                </th>
                                                                <th class="text-center" style="width: 20%">
                                                                    Sub Object 2 (Jika ada)
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            @if($projectObjects->count() > 0)
                                                                @php($count=1)
                                                                @foreach($projectObjects as $projectObject)
                                                                    <tr id='sch0'>
                                                                        <td>
                                                                            {{$count}}
                                                                        </td>
                                                                        <td>
                                                                            <input type='text'class='form-control' value="{{$projectObject->place_name}}" disabled>
                                                                        </td>
                                                                        <td>
{{--                                                                            <img src="https://api.qrserver.com/v1/create-qr-code/?data={{}}&size=100x100" alt="" title="" />--}}
                                                                            <img src="https://chart.googleapis.com/chart?chs=150x150&cht=qr&chl={{$projectObject->place->qr_code}}&choe=UTF-8" alt="" title="" />

                                                                            <input type='text'class='form-control' value="{{$projectObject->place->qr_code}}" disabled>
                                                                        </td>
                                                                        <td>
                                                                            <input type='text'class='form-control' value="{{$projectObject->unit_name}}" disabled>
                                                                        </td>
                                                                        <td>
                                                                            <input type='text'class='form-control' value="{{$projectObject->sub1_unit_name}}" disabled>
                                                                        </td>
                                                                        <td>
                                                                            <input type='text'class='form-control' value="{{$projectObject->sub2_unit_name}}" disabled>
                                                                        </td>
                                                                    </tr>
                                                                    @php($count++)
                                                                @endforeach
                                                            @else
                                                                <tr id='sch0'>
                                                                    <td colspan="5" style="text-align:center;">
                                                                        <h4>BELUM ADA PLACE DAN OBJECT TERPILIH</h4>
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            </tbody>
                                                        </table>
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
    @include('partials._delete')
@endsection


@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <style>
        .fancybox-viewer img{
            width: 150px;
            height: auto;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>
    <script type="text/javascript">
        var Lat = '{{$project->latitude}}';
        var Long = '{{$project->longitude}}';
        var map = new google.maps.Map(document.getElementById('map-canvas'), {
            center:{
                lat: parseFloat(Lat),
                lng: parseFloat(Long)
            },
            zoom: 15
        });

        var marker = new google.maps.Marker({
            position:{
                lat: parseFloat(Lat),
                lng: parseFloat(Long)
            },
            map: map,
            draggable: true
        });

        $("a.fancybox-viewer").fancybox();

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
    </script>
    @include('partials._deletejs', ['routeUrl' => 'admin.project.information.destroy', 'redirectUrl' => 'admin.project.information.index'])
@endsection

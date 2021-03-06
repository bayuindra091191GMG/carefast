@extends('layouts.admin')

@section('content')
    <div style="display:none">
        {{ $projectId = \Illuminate\Support\Facades\Auth::guard('admin')->user()->project_id }}
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">

                        @if($projectId == 0)
                            <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary float-left mr-3">
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        @endif
{{--                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">--}}
{{--                            <i class="fas fa-arrow-left"></i>--}}
{{--                        </a>--}}
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
                                <a class="nav-link active" id="information-tab" data-toggle="tab" href="#basic" role="tab" aria-controls="information-tab" aria-selected="true">INFORMASI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.set-schedule', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">JADWAL EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="plotting-tab" href="{{ route('admin.project.activity.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="information-tab">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="col-md-12 col-12 text-right">
                                                @if($projectId == 0)
                                                <a href="{{ route('admin.project.information.edit', ['id' => $project->id]) }}" class="btn btn-primary">EDIT INFORMASI</a>
                                                @endif
                                            </div>
                                            <div class="body">
                                                @include('partials.admin._messages')
                                                @if(count($errors))
                                                    <div class="col-md-12">
                                                        <div class="form-group form-float form-group-lg">
                                                            <div class="form-line">
                                                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                                    <ul>
                                                                        @foreach($errors->all() as $error)
                                                                            <li>{{ $error }}</li>
                                                                        @endforeach
                                                                    </ul>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="image_main">Foto *</label>
                                                            <br>
                                                            @if(!empty($project->image_path))
                                                                <img src="{{ asset('storage/projects/'.$project->image_path) }}" width="500">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="name">Nama Project*</label>
                                                            <input id="name" type="text" class="form-control"
                                                                   name="name" value="{{ $project->name }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="phone">Nomor Telepon *</label>
                                                            <input id="phone" type="text" class="form-control"
                                                                   name="phone" value="{{ $project->phone }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="phone">Kode Project *</label>
                                                            <input id="code" type="text" class="form-control"
                                                                   name="code" value="{{ $project->code }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="address">Alamat *</label>
                                                            <textarea name="address" id="address" class="form-control" rows="3" readonly>{{ $project->address }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="phone">Kode Cabang *</label>
                                                            <input id="city" type="text" class="form-control"
                                                                   name="city" value="{{ $project->city }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="searchmap">Lokasi *</label>
                                                        </div>
                                                        <div id="map-canvas" style="height: 200px;"></div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="latitude">Latitude</label>
                                                                    <input type="text" name="latitude" id="latitude" class="form-control" value="{{$project->latitude}}" readonly/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="longitude">Longitude</label>
                                                                    <input type="text" name="longitude" id="longitude" class="form-control" value="{{$project->longitude}}" readonly/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_manday">Tanggal Dimulai Project*</label>
                                                                    <input id="start_date" name="start_date" type="text" class="form-control" value="{{$start_date}}" autocomplete="off" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_mp_onduty">Tanggal Selesai Project*</label>
                                                                    <input id="finish_date" name="finish_date" type="text" class="form-control" value="{{$finish_date}}" autocomplete="off" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_manday">Total Manday *</label>
                                                                    <input id="total_manday" type="number" class="form-control"
                                                                           name="total_manday" value="{{ $project->total_manday }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_mp_onduty">Total MP Onduty *</label>
                                                                    <input id="total_mp_onduty" type="number" class="form-control"
                                                                           name="total_mp_onduty" value="{{ $project->total_mp_onduty }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_mp_off">Total Mp Off *</label>
                                                                    <input id="total_mp_off" type="number" class="form-control"
                                                                           name="total_mp_off" value="{{ $project->total_mp_off }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="total_manpower">Total Manpower *</label>
                                                                    <input id="total_manpower" type="number" class="form-control"
                                                                           name="total_manpower" value="{{ $project->total_manpower }}" readonly>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

{{--                                                <div class="col-md-12">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label for="customer">Customer *</label>--}}
{{--                                                        <input type="text" name="customer" id="customer" class="form-control"--}}
{{--                                                               value="{{ $project->customer->name . ' - ' . $project->customer->email }}" readonly/>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="status">Status</label>
                                                        <input id="status" type="text" class="form-control"
                                                               name="status" value="{{ strtoupper($project->status->description) }}" readonly="">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <table class="table table-bordered table-hover" id="tab_logic">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="text-center" style="width: 75%">
                                                                            Customer*
                                                                        </th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    @php $idx = 1 @endphp

                                                                    @if($customerList->count() > 0)
                                                                        @foreach($customerList as $customer)
                                                                            <tr id='sch{{$idx}}'>
                                                                                <td><span>{{$customer->name}} - {{$customer->email}}</span></td>
                                                                            </tr>
                                                                            @php $idx++ @endphp
                                                                        @endforeach
                                                                        <tr id='sch{{$idx}}'></tr>
                                                                    @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <table class="table table-bordered table-hover" id="tab_logic">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="text-center" style="width: 30%">
                                                                            Keluhan Pending
                                                                        </th>
                                                                        <th class="text-center" style="width: 30%">
                                                                            Keluhan Diprocess
                                                                        </th>
                                                                        <th class="text-center" style="width: 30%">
                                                                            Keluhan Tutup
                                                                        </th>
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <tr>
                                                                        <td>{{$complaintPendingCt}}</td>
                                                                        <td>{{$complaintProcessCt}}</td>
                                                                        <td>{{$complaintCloseCt}}</td>
                                                                    </tr>
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
    <script src="https://maps.googleapis.com/maps/api/js?key={{env('GMAPS_KEY')}}&libraries=places"
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

@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-12">
                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>PLOTTING PROJECT - {{ $project->name }}</h3>
                    </div>
{{--                    <div class="col-md-4 col-12 text-right">--}}
{{--                        <button class="btn btn-danger " data-toggle="modal" data-target="#deleteModal" data-id="{{$project->id}}">HAPUS</button>--}}
{{--                    </div>--}}
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
                                <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="plotting-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
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
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="col-md-12 col-12 text-right">
                                                @if($project->id == 1 || $project->id == 803)
                                                    <a href="{{route('admin.project.activity.download-file', ['id' => $project->id])}}" class="btn btn-instagram" style="color: white;">DOWNLOAD DAC</a> &nbsp;
                                                    <a href="{{ route('admin.project.activity.show-schedule-plotting', ['id' => $project->id]) }}" class="btn btn-primary">JADWAL PLOTTING</a> &nbsp;
                                                @endif
                                                <a href="{{ route('admin.project.activity.create', ['id' => $project->id]) }}" class="btn btn-success">TAMBAH PLOTTING</a>
                                            </div>
                                            <div class="body">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Filter Tempat*</label>
                                                                    <select id='filter' class='form-control'>
                                                                        <option value='0' @if(0 == $placeId) selected @endif>Semua Place</option>
                                                                        @foreach($places as $place)
                                                                            <option value='{{ $place->id }}' @if($place->id == $placeId) selected @endif >{{ $place->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <br>
                                                                    <a onclick="filterSubmit()" class="btn btn-facebook" style="color: white;">Ganti Filter</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
{{--                                                <div class="col-md-12">--}}
{{--                                                    <div class="row">--}}
{{--                                                        <div class="col-md-4">--}}
{{--                                                            <div class="form-group form-float form-group-lg">--}}
{{--                                                                <div class="form-line">--}}
{{--                                                                    <select id='plotting1' class='form-control'>--}}
{{--                                                                        @foreach($places as $place)--}}
{{--                                                                            <option value='{{ $place->id }}' @if($place->id == $placeId) selected @endif >{{ $place->name }}</option>--}}
{{--                                                                        @endforeach--}}
{{--                                                                    </select>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-md-4">--}}
{{--                                                            <div class="form-group form-float form-group-lg">--}}
{{--                                                                <div class="form-line">--}}
{{--                                                                    <label class="form-label">Tempat Plotting Tujuan*</label>--}}
{{--                                                                    <select id='plotting2' class='form-control'>--}}
{{--                                                                        @foreach($places as $place)--}}
{{--                                                                            <option value='{{ $place->id }}' @if($place->id == $placeId) selected @endif >{{ $place->name }}</option>--}}
{{--                                                                        @endforeach--}}
{{--                                                                    </select>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                        <div class="col-md-6">--}}
{{--                                                            <div class="form-group form-float form-group-lg">--}}
{{--                                                                <div class="form-line">--}}
{{--                                                                    <br>--}}
{{--                                                                    <a onclick="copyPlotting()" class="btn btn-facebook" style="color: white;">Copy Plotting</a>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive-sm">
                                                        @if($activities->count() == 0)
                                                            <h4 class="text-center">BELUM ADA PLOTTING</h4>
                                                        @else
                                                            <table id="general_table" class="table table-striped table-bordered" style="width: 100%;">
                                                                <thead>
                                                                <tr>
                                                                    <th class="text-center" width="5%">Shift</th>
                                                                    <th class="text-center" width="20%">Tempat</th>
{{--                                                                    <th class="text-center" width="20%">Objek</th>--}}
                                                                    <th class="text-center" width="15%">Waktu</th>
                                                                    <th class="text-center" width="10%">Aktifitas</th>
                                                                    <th class="text-center" width="10%">Period</th>
                                                                    <th class="text-center" width="10%">CSO Bertugas</th>
                                                                    <th class="text-center" width="10%">Tindakan</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
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
    @include('partials._delete')
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <style>
        td {word-wrap: break-word}
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        function filterSubmit(){
            var filter = $('#filter').val();
            window.location.replace('{{ route('admin.project.activity.show', ['id' => $project->id]) }}?place=' + filter);
        }
        function copyPlotting(){
            var old_place = $('#plotting1').val();
            var new_place = $('#plotting2').val();
            window.location.replace('{{ route('admin.project.activity.show', ['id' => $project->id]) }}?old_place=' + old_place + '&new_place=' + new_place);
        }
        $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: {
                url: '{!! route('datatables.project-activity') !!}',
                data: {
                    'id': '{{ $project->id }}',
                    'place_id': '{{ $placeId }}'
                }
            },
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'shift', name: 'shift', class: 'text-center' },
                { data: 'place', name: 'place', class: 'text-left' },
                // { data: 'object_name', name: 'object_name', class: 'text-left' },
                { data: 'time', name: 'time', class: 'text-center' },
                { data: 'action_name', name: 'action_name', class: 'text-center' },
                { data: 'period_type', name: 'period_type', class: 'text-center' },
                { data: 'assigned_cso', name: 'period_type', orderable: false, searchable: false, class: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });

        let redirectUrl = '{{route('admin.project.activity.show',['id' => $project->id])}}';
        let routeUrl = '{{route('admin.project.activity.destroy')}}';
        $('.modal-footer').on('click', '.delete', function() {
            $.ajax({
                type: 'POST',
                url: routeUrl,
                data: {
                    '_token': '{{ csrf_token() }}',
                    'id': $('#deleted-id').val()
                },
                success: function(data) {
                    if ((data.errors)){
                        // setTimeout(function () {
                        //     toastr.error('Gagal menghapus data!!', 'Peringatan', {timeOut: 6000, positionClass: "toast-top-center"});
                        // }, 500);
                        window.location =  redirectUrl;
                    }
                    else{
                        window.location = redirectUrl;
                    }
                },
                error: function (data) {
                    alert("Error");
                }
            });
        });
    </script>
@endsection

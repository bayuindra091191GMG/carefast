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
                        <h3>DETIL ABSENSI PROJECT - {{ $project->name }}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
{{--                        <button class="btn btn-danger " data-toggle="modal" data-target="#deleteModal" data-id="{{$project->id}}">HAPUS</button>--}}
                    </div>
                </div>

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
                                <a class="nav-link" id="plotting-tab" href="{{ route('admin.project.activity.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="attendance-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="body">
                                                <div class="row">
                                                    <div class="col-12">

                                                        {{ Form::open(['route'=>'admin.project.attendance.download','method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}
                                                        <input type="hidden" name="project_id" value="{{$project->id}}">
                                                        <div class="row">
{{--                                                            <div class="col-md-3">--}}
{{--                                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                                    <div class="form-line">--}}
{{--                                                                        <label class="form-label">Filter Shift Type*</label>--}}
{{--                                                                        <select id='filter' class='form-control' name="shift_type">--}}
{{--                                                                            <option value='1'>Shift 1</option>--}}
{{--                                                                            <option value='2'>Shift 2</option>--}}
{{--                                                                            <option value='3'>Shift 3</option>--}}
{{--                                                                        </select>--}}
{{--                                                                    </div>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
                                                            <div class="col-md-6">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group form-float form-group-lg">
                                                                            <div class="form-line">
                                                                                <label class="form-label" for="total_manday">Tanggal Dimulai *</label>
                                                                                <input id="start_date" name="start_date" type="text" class="form-control" autocomplete="off" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group form-float form-group-lg">
                                                                            <div class="form-line">
                                                                                <label class="form-label" for="total_mp_onduty">Tanggal Selesai *</label>
                                                                                <input id="finish_date" name="finish_date" type="text" class="form-control" autocomplete="off" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <br>
                                                                        <button type="submit" class="btn btn-facebook" style="color: white;">Download Data</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{ Form::close() }}
                                                    </div>
                                                    <div class="col-12">
                                                        <div class="table-responsive-sm">
                                                            <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                                                <thead>
                                                                <tr>
                                                                    <th class="text-center">Code</th>
                                                                    <th class="text-center">Nama</th>
                                                                    <th class="text-center">Tanggal Absen</th>
                                                                    <th class="text-center">Status</th>
                                                                    <th class="text-center">Image</th>
                                                                    <th class="text-center">Tanggal Dibuat</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody></tbody>
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
        </div>
    </div>
    @include('partials._delete')
@endsection


@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <style>
        .fancybox-viewer img{
            width: 150px;
            height: auto;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: {
                url: '{!! route('datatables.project-attendance') !!}',
                data: {
                    'id': '{{ $project->id }}'
                }
            },
            order: [ [2, 'desc'] ],
            columns: [
                { data: 'employee_code', name: 'employee.code', class: 'text-center' },
                { data: 'first_name', name: 'employee.first_name', class: 'text-center' },
                { data: 'date', name: 'date', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY HH:mm');
                        }
                        return data;
                    }
                },
                { data: 'status', name: 'status', class: 'text-center', orderable: false, searchable: false},
                { data: 'image_path', name: 'image_path', class: 'text-center', orderable: false, searchable: false},
                { data: 'created_at', name: 'created_at', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                // { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
    </script>
    <script type="text/javascript">
        jQuery('#start_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
        jQuery('#finish_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
    </script>
    @include('partials._deletejs', ['routeUrl' => 'admin.project.information.destroy', 'redirectUrl' => 'admin.project.information.index'])
@endsection

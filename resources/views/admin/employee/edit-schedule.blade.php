@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH JADWAL KARYAWAN {{ $employee->code }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.employee.store-schedule', $employee->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
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
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="code">ID Karyawan</label>
                                                        <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="code" value="{{ $employee->code }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="first_name">Nama Depan </label>
                                                        <input id="first_name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               value="{{ $employee->first_name }} {{$employee->last_name}}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Nomor Ponsel Login </label>
                                                        <input id="phone" type="text" class="form-control"
                                                               value="{{ $employee->phone }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="status">Status</label>
                                                        <select class="form-control" id="status" disabled>
                                                            <option value="1" @if($employee->status_id === 1) selected @endif>ACTIVE</option>
                                                            <option value="2" @if($employee->status_id === 2) selected @endif>NON-ACTIVE</option>
                                                        </select>
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
                                                        <label class="form-label" for="phone">Project Saat ini </label>
                                                        <input type="text" class="form-control"
                                                               value="{{ $currentProject->project->name }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Kode Project </label>
                                                        <input type="text" class="form-control"
                                                               value="{{ $currentProject->project->code }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
{{--                                    <div class="col-md-12">--}}
{{--                                        <div class="table-responsive">--}}
{{--                                            <table class="table table-bordered table-hover" id="tab_logic">--}}
{{--                                                <thead>--}}
{{--                                                <tr>--}}
{{--                                                    <th class="text-center">--}}
{{--                                                        Tanggal--}}
{{--                                                    </th>--}}
{{--                                                @foreach($scheduleModel->where('day', '>=', 16) as $schedule)--}}
{{--                                                        <th class="text-center" style="width:50px;">--}}
{{--                                                            Tanggal {{$schedule["day"]}}--}}
{{--                                                        </th>--}}
{{--                                                @endforeach--}}
{{--                                                @foreach($scheduleModel->where('day', '<', 16) as $schedule)--}}
{{--                                                        <th class="text-center" style="width:50px;">--}}
{{--                                                            Tanggal {{$schedule["day"]}}--}}
{{--                                                        </th>--}}
{{--                                                @endforeach--}}
{{--                                                </tr>--}}
{{--                                                </thead>--}}
{{--                                                <tbody>--}}
{{--                                                <tr>--}}
{{--                                                    <td>Status Absensi</td>--}}

{{--                                                @foreach($scheduleModel->where('day', '>=', 16) as $schedule)--}}
{{--                                                        <td>--}}
{{--                                                            <select name="statuses[]" class='form-control'>--}}
{{--                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>--}}
{{--                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>--}}
{{--                                                            </select>--}}
{{--                                                        </td>--}}
{{--                                                @endforeach--}}
{{--                                                @foreach($scheduleModel->where('day', '<', 16) as $schedule)--}}
{{--                                                        <td>--}}
{{--                                                            <select name="statuses[]" class='form-control'>--}}
{{--                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>--}}
{{--                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>--}}
{{--                                                            </select>--}}
{{--                                                        </td>--}}
{{--                                                @endforeach--}}
{{--                                                </tr>--}}
{{--                                                </tbody>--}}
{{--                                            </table>--}}
{{--                                        </div>--}}
{{--                                    </div>--}}
                                    <div class="col-md-12">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="tab_logic">
                                                <thead>
                                                <tr>
                                                    <th class="text-center">
                                                        Tanggal
                                                    </th>
                                                    <th class="text-center">
                                                        Status Absensi
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($scheduleModel->where('day', '>=', 16) as $schedule)
                                                    <tr>
                                                        <td>
                                                            <input type='text' value='{{$schedule["day"]}}' name="days[]" class='form-control' readonly/>
                                                        </td>
                                                        <td>
                                                            <select name="statuses[]" class='form-control'>
                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>
                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                @foreach($scheduleModel->where('day', '<', 16) as $schedule)
                                                    <tr>
                                                        <td>
                                                            <input type='text' value='{{$schedule["day"]}}' name="days[]" class='form-control' readonly/>
                                                        </td>
                                                        <td>
                                                            <select name="statuses[]" class='form-control'>
                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>
                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.employee.index') }}" class="btn btn-danger">BATAL</a>
                                    <input type="submit" class="btn btn-success" value="UBAH">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection


@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
    <style>
        .select2-selection--multiple{
            overflow: hidden !important;
            height: auto !important;
        }
        hr {
            border-top: 3px solid rgba(0, 0, 0, 0.5);
        }
        table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        .tr-class{
            padding: 5px;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    {{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
@endsection

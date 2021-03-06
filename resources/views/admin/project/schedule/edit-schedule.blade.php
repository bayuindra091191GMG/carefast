@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH JADWAL KARYAWAN PROJECT {{ $project->name }}</h3>
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
                                <a class="nav-link active" id="schedule-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">JADWAL EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}"  role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
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

                                {{ Form::open(['route'=>['admin.project.store-schedule', $project->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                            <div class="col-md-6">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <label class="form-label" for="phone">Project </label>
                                                                        <input type="text" class="form-control"
                                                                               value="{{ $project->name }}" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <label class="form-label" for="phone">Kode Project </label>
                                                                        <input type="text" class="form-control"
                                                                               value="{{ $project->code }}" readonly>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group form-float form-group-lg">
                                                                            <div class="form-line">
                                                                                <label class="form-label" for="total_manday">Cut Off Dimulai *</label>
                                                                                <input id="start_date" name="start_date" type="text"
                                                                                       value="{{$start_date}}"
                                                                                       class="form-control" autocomplete="off" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group form-float form-group-lg">
                                                                            <div class="form-line">
                                                                                <label class="form-label" for="total_mp_onduty">Cut Off Selesai *</label>
                                                                                <input id="finish_date" name="finish_date" type="text"
                                                                                       value="{{$end_date}}"
                                                                                       class="form-control" autocomplete="off" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <br>
                                                                        <a id="refresh_date" class="btn btn-facebook" style="color: white;">Refresh</a>
                                                                        <p>*Klik tombol Refresh terlebih dahulu</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p>
                                                                    Keterangan = <br>
                                                                    - H = Hadir <br>
                                                                    - O = Off <br>
                                                                    - Angka pada table menunjukan tanggal
                                                                </p>
                                                        </div>
                                                    </div>
                                                    @if($isSelectDate)
                                                        <div class="col-md-12">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover" id="tab_logic">
                                                                    <thead>
                                                                    <tr>
                                                                        <th class="text-center">
                                                                            Nama
                                                                        </th>
                                                                        <th class="text-center">
                                                                            NUC
                                                                        </th>
                                                                        @foreach($days as $day)
                                                                            <th class="text-center" style="min-width:95px;">
                                                                                {{$day}}
                                                                            </th>
                                                                        @endforeach
                                                                    </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($projectScheduleModel as $schedule)
                                                                            <tr>
                                                                                <td>{{$schedule['employee_name']}}</td>
                                                                                <td>{{($schedule['employee_code'])}} </td>
                                                                                @foreach($schedule["days"] as $scheduleDay)
                                                                                <td>
{{--                                                                                    <input type="checkbox" id="status" name="statuses[]"--}}
{{--                                                                                           @if($scheduleDay["status"] == 1) checked @endif>--}}
                                                                                    <select name="statuses[]" class='form-control'>
                                                                                        <option value='1' @if($scheduleDay["status"] == 1) selected @endif>H</option>
                                                                                        <option value='0' @if($scheduleDay["status"] == 0) selected @endif>O</option>
                                                                                    </select>
                                                                                    <input type="hidden" id="days" name="days[]"  value="{{$scheduleDay['day']}}">
                                                                                    <input type="hidden" id="employeeId" name="employeeId[]"  value="{{$schedule['employee_id']}}">
                                                                                </td>
                                                                                @endforeach
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
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
                                                        {{--                                                    <th class="text-center">--}}
                                                        {{--                                                        Status Absensi--}}
                                                        {{--                                                    </th>--}}
                                                        {{--                                                </tr>--}}
                                                        {{--                                                </thead>--}}
                                                        {{--                                                <tbody>--}}
                                                        {{--                                                @foreach($scheduleModel->where('day', '>=', 16) as $schedule)--}}
                                                        {{--                                                    <tr>--}}
                                                        {{--                                                        <td>--}}
                                                        {{--                                                            <input type='text' value='{{$schedule["day"]}}' name="days[]" class='form-control' readonly/>--}}
                                                        {{--                                                        </td>--}}
                                                        {{--                                                        <td>--}}
                                                        {{--                                                            <select name="statuses[]" class='form-control'>--}}
                                                        {{--                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>--}}
                                                        {{--                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>--}}
                                                        {{--                                                            </select>--}}
                                                        {{--                                                        </td>--}}
                                                        {{--                                                    </tr>--}}
                                                        {{--                                                @endforeach--}}
                                                        {{--                                                @foreach($scheduleModel->where('day', '<', 16) as $schedule)--}}
                                                        {{--                                                    <tr>--}}
                                                        {{--                                                        <td>--}}
                                                        {{--                                                            <input type='text' value='{{$schedule["day"]}}' name="days[]" class='form-control' readonly/>--}}
                                                        {{--                                                        </td>--}}
                                                        {{--                                                        <td>--}}
                                                        {{--                                                            <select name="statuses[]" class='form-control'>--}}
                                                        {{--                                                                <option value='M' @if($schedule["status"] == 'M') selected @endif>Masuk</option>--}}
                                                        {{--                                                                <option value='O' @if($schedule["status"] == 'O') selected @endif>Off</option>--}}
                                                        {{--                                                            </select>--}}
                                                        {{--                                                        </td>--}}
                                                        {{--                                                    </tr>--}}
                                                        {{--                                                @endforeach--}}
                                                        {{--                                                </tbody>--}}
                                                        {{--                                            </table>--}}
                                                        {{--                                        </div>--}}
                                                        {{--                                    </div>--}}
                                                    @endif

                                                </div>

                                                @if($isSelectDate)
                                                    <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                                        <input type="submit" class="btn btn-success" value="UBAH">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{ Form::close() }}
                            </div>
                        </div>
                    </div>

                </div>


            </div>
        </div>
    </div>
@endsection


@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
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
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        select {
             font-size: 10px;
         }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    {{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script type="text/javascript">
        $("#refresh_date").click(function(){
            var start_date = $('#start_date').val();
            var finish_date = $('#finish_date').val();
            window.location.href = '{{route('admin.project.set-schedule', ['id'=>$project->id])}}?start_date=' + start_date + '&finish_date=' + finish_date;
        });
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
@endsection

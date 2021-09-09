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

                        {{ Form::open(['route'=>['admin.project.schedule-update-employee', $project->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                            <input type ="hidden" value="{{$project->id}}" name="projectId">
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
                                            </div>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <p>
                                                            Keterangan = <br>
                                                            - HP = Hadir Pagi<br>
                                                            - HS = Hadir Siang<br>
                                                            - HM = Hadir Malam<br>
                                                            - HM1 = Hadir Middle 1<br>
                                                            - HM2 = Hadir Middle 2<br>
                                                            - NS1 = No Shift 1<br>
                                                            - NS2 = No Shift 2<br>
                                                            - NS3 = No Shift 3<br>
                                                            - O = Off <br>
                                                            - Angka pada table menunjukan tanggal
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
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
                                                            @if($selectedEmployee == $schedule['employee_id'])
                                                                <input type="hidden" id="employeeId" name="employeeId" value="{{$schedule['employee_id']}}">
                                                            <tr>
                                                                <td>{{$schedule['employee_name']}}</td>
                                                                <td>{{($schedule['employee_code'])}} </td>
                                                                @foreach($schedule["days"] as $scheduleDay)
                                                                    <td class="text-center">
                                                                        <select name="statuses[]" class='form-control'>
                                                                            <option value='0'>O</option>
                                                                            @foreach($projectSchedules as $projectSchedule)
                                                                                @if($scheduleDay['status'] == $projectSchedule->shift_type)
                                                                                    <option value='{{$projectSchedule->shift_type}}' selected>
                                                                                        {{$projectSchedule->shift_type}}
                                                                                    </option>
                                                                                @else
                                                                                    <option value='{{$projectSchedule->shift_type}}'>
                                                                                        {{$projectSchedule->shift_type}}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                        </select>
                                                                        <input type="hidden" id="days" name="days[]"  value="{{$scheduleDay['day']}}">
                                                                    </td>
                                                                @endforeach
                                                            </tr>
                                                            @endif
                                                        @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <a href="{{ route('admin.project.set-schedule', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                                                <input type="submit" class="btn btn-success" value="SIMPAN">
                                            </div>
                                        </div>
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
@endsection


@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link href="{{ asset('css/fixed_table_rc.css') }}" rel="stylesheet"/>

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

    <script src="{{ asset('js/fixed_table_rc.js') }}"></script>

    <script type="text/javascript">
        $("#refresh_date").click(function(){
            var start_date = $('#start_date').val();
            var finish_date = $('#finish_date').val();
            {{--window.location.href = '{{route('admin.project.set-schedule', ['id'=>$project->id])}}?start_date=' + start_date + '&finish_date=' + finish_date;--}}
            window.location.href = '{{route('admin.project.set-schedule', ['id'=>$project->id])}}';
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

@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH PLOTTING PROJECT {{ $project->name }}</h3>
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
                                <a class="nav-link active" id="plotting-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">

                                {{ Form::open(['route'=>['admin.project.activity.update-schedule-plotting'],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}
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

                                                {{-- test freeze column --}}
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <p>
                                                                    Hover tulisan "detail" untuk melihat detail plotting
                                                                </p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type ="hidden" value="{{$project->id}}" name="projectId">
                                                    <div class="dwrapper">
                                                        <table id="fixed_hdr1">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center">
                                                                    Place
                                                                </th>
                                                                <th class="text-center">
                                                                    Shift
                                                                </th>
                                                                @foreach($days as $day)
                                                                    <th>
                                                                        {{$day}}
                                                                    </th>
                                                                @endforeach
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            @foreach($projectPlottingScheduleModel as $schedule)
                                                                @php($offCt = 0)
                                                                <tr>
                                                                    <td>{{$schedule['place']}}</td>
                                                                    <td>{{($schedule['shift'])}}
                                                                        <br>
                                                                        <a rel="tooltip" title="{{($schedule['project_activities_detail_description'])}}">
                                                                            detail
                                                                        </a>
                                                                    </td>
                                                                    @foreach($schedule["days"] as $scheduleDay)
                                                                        <td>
                                                                            <select name="employeeIds[]" class='form-control'>
                                                                            @foreach($employeeProjects as $employeeProject)
                                                                                <option value='{{$employeeProject->employee_id}}' selected="selected">
                                                                                    {{$employeeProject->employee->first_name}} {{$employeeProject->employee->last_name}}
                                                                                </option>
                                                                            @endforeach
                                                                            </select>
                                                                            <input type="hidden" id="projectActivitesHeaderId" name="projectActivitesHeaderIds[]"  value="{{$schedule['project_activities_header_id']}}">

                                                                            <input type="hidden" id="days" name="days[]"  value="{{$scheduleDay['day']}}">
                                                                        </td>
                                                                    @endforeach
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <a href="{{ route('admin.project.activity.show-schedule-plotting', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
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

        $(function () {
        // function loadTable() {
            let colums = [];
            let dayCount = '{{count($days)}}';
            console.log("dayCount = " + dayCount);
            let newDayCount = parseInt(dayCount) + 2;
            for(let a=0;a < newDayCount; a++){
                if(a===0){
                    let colum = {
                        "width": "300",
                        "align": "center",
                    };
                    colums.push(colum);
                }
                else if(a===1){
                    let colum = {
                        "width": "100",
                        "align": "center",
                    };
                    colums.push(colum);
                }
                else{
                    let colum = {
                        "width": "200",
                        "align": "center",
                    };
                    colums.push(colum);
                }
            }
            console.log(colums);
            $('#fixed_hdr1').fxdHdrCol({
                fixedCols   : 2,
                width       : '100%',
                height      : 250,
                // colModal    : colums

                colModal: [
                    { width: 300, align: 'center' },
                    { width: 100, align: 'center' },

                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },

                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },

                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },

                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                    { width: 200, align: 'center' },
                ],
            });
        // }
        });
    </script>
@endsection

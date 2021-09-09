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

                                {{ Form::open(['route'=>['admin.project.upload-schedule', $project->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                            @if($projectShifts->count() > 0)
                                                                <div class="col-md-6">
                                                                    <table>
                                                                        <tr>
                                                                            <td width="150">Tipe Shift</td>
                                                                            <td width="150">Start Time</td>
                                                                            <td width="150">Finish Time</td>
                                                                        </tr>
                                                                        @foreach($projectShifts as $projectShift)
                                                                            <tr>
                                                                                <td>{{$projectShift->shift_type}}</td>
                                                                                <td>{{$projectShift->start_time}}</td>
                                                                                <td>{{$projectShift->finish_time}}</td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </table>
                                                                </div>
                                                            @endif
                                                            <div class="col-md-6">
                                                                <a href="{{route('admin.project.edit-shift',['id' => $project->id])}}" class="btn btn-success">
                                                                    UBAH Waktu Shift
                                                                </a>
                                                            </div>
                                                        </div>

                                                        <div class="row mt-5">
{{--                                                            <div class="col-md-6">--}}
{{--                                                                <div class="row">--}}
{{--                                                                    <div class="col-md-6">--}}
{{--                                                                        <div class="form-group form-float form-group-lg">--}}
{{--                                                                            <div class="form-line">--}}
{{--                                                                                <label class="form-label" for="total_manday">Cut Off Dimulai *</label>--}}
{{--                                                                                <input id="start_date" name="start_date" type="text"--}}
{{--                                                                                       value="{{$start_date}}"--}}
{{--                                                                                       class="form-control" autocomplete="off" required>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                    <div class="col-md-6">--}}
{{--                                                                        <div class="form-group form-float form-group-lg">--}}
{{--                                                                            <div class="form-line">--}}
{{--                                                                                <label class="form-label" for="total_mp_onduty">Cut Off Selesai *</label>--}}
{{--                                                                                <input id="finish_date" name="finish_date" type="text"--}}
{{--                                                                                       value="{{$end_date}}"--}}
{{--                                                                                       class="form-control" autocomplete="off" required>--}}
{{--                                                                            </div>--}}
{{--                                                                        </div>--}}
{{--                                                                    </div>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
{{--                                                            <div class="col-md-3">--}}
{{--                                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                                    <div class="form-line">--}}
{{--                                                                        <br>--}}
{{--                                                                        <a id="refresh_date" class="btn btn-facebook" style="color: white;">Refresh</a>--}}
{{--                                                                        <p>*Klik tombol Refresh terlebih dahulu</p>--}}
{{--                                                                    </div>--}}
{{--                                                                </div>--}}
{{--                                                            </div>--}}
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
{{--                                                    <div class="col-md-12">--}}
{{--                                                        <div class="table-responsive">--}}
{{--                                                            <table class="table table-bordered table-hover" id="tab_logic">--}}
{{--                                                                <thead>--}}
{{--                                                                <tr>--}}
{{--                                                                    <th class="text-center">--}}
{{--                                                                        Nama--}}
{{--                                                                    </th>--}}
{{--                                                                    <th class="text-center">--}}
{{--                                                                        NUC--}}
{{--                                                                    </th>--}}
{{--                                                                    @foreach($days as $day)--}}
{{--                                                                        <th class="text-center" style="min-width:75px;">--}}
{{--                                                                            {{$day}}--}}
{{--                                                                        </th>--}}
{{--                                                                    @endforeach--}}
{{--                                                                </tr>--}}
{{--                                                                </thead>--}}
{{--                                                                <tbody>--}}
{{--                                                                @foreach($projectScheduleModel as $schedule)--}}
{{--                                                                    <tr>--}}
{{--                                                                        <td>{{$schedule['employee_name']}}</td>--}}
{{--                                                                        <td>{{($schedule['employee_code'])}} </td>--}}
{{--                                                                        @foreach($schedule["days"] as $scheduleDay)--}}
{{--                                                                            <td class="text-center">--}}
{{--                                                                                @if($scheduleDay["status"] == 'H')--}}
{{--                                                                                    H--}}
{{--                                                                                @else--}}
{{--                                                                                    O--}}
{{--                                                                                @endif--}}
{{--                                                                                <select name="statuses[]" class='form-control'>--}}
{{--                                                                                    <option value='1' @if($scheduleDay["status"] == 1) selected @endif>H</option>--}}
{{--                                                                                    <option value='0' @if($scheduleDay["status"] == 0) selected @endif>O</option>--}}
{{--                                                                                </select>--}}
{{--                                                                                --}}{{--                                                                                    <input type="hidden" id="days" name="days[]"  value="{{$scheduleDay['day']}}">--}}
{{--                                                                                --}}{{--                                                                                    <input type="hidden" id="employeeId" name="employeeId[]"  value="{{$schedule['employee_id']}}">--}}
{{--                                                                            </td>--}}
{{--                                                                        @endforeach--}}
{{--                                                                    </tr>--}}
{{--                                                                @endforeach--}}
{{--                                                                </tbody>--}}
{{--                                                            </table>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}

                                                {{-- test freeze column --}}
                                                    <div class="dwrapper">
                                                        <table id="fixed_hdr1">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center">
                                                                    Nama
                                                                </th>
                                                                <th class="text-center">
                                                                    NUC
                                                                </th>
                                                                @foreach($days as $day)
                                                                    <th>
                                                                        {{$day}}
                                                                    </th>
                                                                @endforeach
                                                                <th>
                                                                    Opsi
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>

                                                            @foreach($projectScheduleModel as $schedule)
                                                                @php($offCt = 0)
                                                                <tr>
                                                                    <td>{{$schedule['employee_name']}}</td>
                                                                    <td>{{($schedule['employee_code'])}} </td>
                                                                    @foreach($schedule["days"] as $scheduleDay)
                                                                        <td>
{{--                                                                            @foreach($projectShifts as $projectShift)--}}
{{--                                                                                @if($scheduleDay['status'] == $projectShift->shift_type)--}}
{{--                                                                                    {{$projectShift->shift_type}}--}}
{{--                                                                                @else--}}
{{--                                                                                    O--}}
{{--                                                                                    @php($offCt++)--}}
{{--                                                                                @endif--}}
{{--                                                                            @endforeach--}}
                                                                            {{$scheduleDay['status']}}
                                                                            @if($scheduleDay["status"] == 'O')
                                                                                @php($offCt++)
                                                                            @endif
{{--                                                                            @if($scheduleDay["status"] == 'H')--}}
{{--                                                                                H--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'HP')--}}
{{--                                                                                HP--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'HS')--}}
{{--                                                                                HS--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'HM')--}}
{{--                                                                                HM--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'HM1')--}}
{{--                                                                                HM1--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'HM2')--}}
{{--                                                                                HM2--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'NS1')--}}
{{--                                                                                NS1--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'NS2')--}}
{{--                                                                                NS2--}}
{{--                                                                            @elseif($scheduleDay["status"] == 'NS3')--}}
{{--                                                                                NS3--}}
{{--                                                                            @else--}}
{{--                                                                                O--}}
{{--                                                                            @php($offCt++)--}}
{{--                                                                            @endif--}}
                                                                            <input type="hidden" id="employeeId" name="employeeId[]"  value="{{$schedule['employee_id']}}">
                                                                        </td>
                                                                    @endforeach
                                                                    <td>
{{--                                                                        @if($offCt > 10)--}}
                                                                        <a href="{{route('admin.project.schedule-edit-employee', ['id'=>$schedule['employee_id']]).'?projectId='.$project->id }}"
                                                                           class="btn btn-primary">
                                                                            Ubah
                                                                        </a>
{{--                                                                        @endif--}}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="img_path">UPLOAD JADWAL EXCEL *</label>
                                                                    {!! Form::file('excel', array('id' => 'excel', 'class' => 'file-loading')) !!}
                                                                </div>
                                                                <p>
                                                                    Keterangan = <br>
                                                                    - Contoh Template excel dapat di <a href="{{route('admin.project.upload-template-download', ['id'=>$project->id])}}">download disini</a><br>
                                                                    - Setelah mengupload Excel jadwal yang baru, jadwal yang lama akan di ubah, pastikan data dalam excel sudah benar
                                                                </p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p>
                                                                DOWNLOAD DATA JADWAL SAAT INI &nbsp;&nbsp;
                                                                <a href="{{route('admin.project.download-schedule', ['id' => $project->id])}}" class="btn btn-primary">DOWNLOAD</a>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6 col-sm-6 col-xs-6" style="margin: 3% 0 3% 0;">
                                                            <input type="submit" class="btn btn-success" value="UPLOAD">
                                                        </div>
                                                    </div>
                                                </div>

                                                <hr>
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
                        "width": "50",
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

                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },

                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },

                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },

                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                    { width: 50, align: 'center' },
                ],
            });
        // }
        });
    </script>
@endsection

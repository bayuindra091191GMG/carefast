@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH SHIFT JADWAL KARYAWAN PROJECT</h3>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">

                        {{ Form::open(['route'=>['admin.project.update-shift', $project->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                    <div class="col-md-12">
                                                        <table class="table table-bordered table-hover" id="tab_logic">
                                                            <tr>
                                                                <td width="150">Tipe Shift</td>
                                                                <td width="150">Start Time</td>
                                                                <td width="150">Finish Time</td>
                                                            </tr>

                                                            @if($projectShifts->count() > 0)
                                                                @foreach($projectShifts as $projectShift)
                                                                    <tr>
                                                                        <td>
                                                                            <select name="shift_types[]" class='form-control'>
                                                                                <option value='HP' @if($projectShift->shift_type == "HP") selected @endif>HP</option>
                                                                                <option value='HS' @if($projectShift->shift_type == "HS") selected @endif>HS</option>
                                                                                <option value='HM' @if($projectShift->shift_type == "HM") selected @endif>HM</option>
                                                                                <option value='HM1' @if($projectShift->shift_type == "HM1") selected @endif>HM1</option>
                                                                                <option value='HM2' @if($projectShift->shift_type == "HM2") selected @endif>HM2</option>
                                                                                <option value='NS1' @if($projectShift->shift_type == "NS1") selected @endif>NS1</option>
                                                                                <option value='NS2' @if($projectShift->shift_type == "NS2") selected @endif>NS2</option>
                                                                                <option value='NS3' @if($projectShift->shift_type == "NS3") selected @endif>NS3</option>
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input class='form-control time-inputmask' name='start_time[]' placeholder='HH:MM'
                                                                                   value="{{$projectShift->start_time}}"/>
                                                                        </td>
                                                                        <td>
                                                                            <input class='form-control time-inputmask' name='finish_time[]' placeholder='HH:MM'
                                                                                   value="{{$projectShift->finish_time}}"/>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                            <tr id='sch1'></tr>
                                                        </table>
                                                        <a id="add_row" class="btn btn-success" style="color: #fff;">Tambah</a>
                                                        &nbsp;
                                                        <a id='delete_row' class="btn btn-danger" style="color: #fff;">Hapus</a>
                                                    </div>
                                            </div>
                                        </div>
                                        <hr>

                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6 col-sm-6 col-xs-6" style="margin: 3% 0 3% 0;">
                                                    <a href="{{ route('admin.project.set-schedule', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                                                    <input type="submit" class="btn btn-success" value="GANTI">
                                                </div>
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
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>

    <script type="text/javascript">
        $(".time-inputmask").inputmask("hh:mm", {
            placeholder: "HH:MM",
            insertMode: false,
            showMaskOnHover: false,

        });

        var i=1;
        $("#add_row").click(function(){
            var bufferID = i;
            $('#sch'+i).html(

                "<td>" +
                "<select class='form-control' name='shift_types[]'>" +
                "<option value='HP'>HP</option><option value='HS'>HS</option>" +
                "<option value='HM'>HM</option><option value='HM1'>HM1</option>" +
                "<option value='HM2'>HM2</option>" +
                "<option value='NS1'>NS1</option><option value='NS2'>NS2</option>" +
                "<option value='NS3'>NS3</option>" +
                "</select>" +
                "</td>" +

                "<td>" +
                "<input id='start"+ i +"' class='form-control time-inputmask' name='start_time[]' placeholder='HH:MM'/>" +
                "</td>" +

                "<td>" +
                "<input id='finish"+ i +"' class='form-control time-inputmask' name='finish_time[]' placeholder='HH:MM'/>" +
                "</td>"
            );
            $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');

            $(".time-inputmask").inputmask("hh:mm", {
                placeholder: "HH:MM",
                insertMode: false,
                showMaskOnHover: false
            });
            i++;
        });

        $("#delete_row").click(function(){
            if(i>1){
                $("#sch"+(i-1)).html('');
                i--;
            }
        });
    </script>
@endsection

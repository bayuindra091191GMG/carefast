@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.schedule.store'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU SCHEDULE DETAIL CSO</h3>
                        </div>
                        <div class="col-md-4 col-12 text-right">
                            <a href="{{ route('admin.project.schedule.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                            <input type="submit" class="btn btn-success" value="SIMPAN">
                        </div>
                    </div>

                    <div class="row">
                    <div class="col-md-12">
                        <div class="">
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

                                    <div class="col-md-12 p-t-20">
                                        <div class="accordion" id="accordionExample">
                                            <div class="m-b-0 border-top">
                                                <div class="card-header mb-3">
                                                    <h5 class="mb-0">
                                                        <img src="{{ asset('storage/employees/'.$projectEmployee->employee->image_path) }}" width="50">
                                                        &nbsp;
                                                        <span>{{$projectEmployee->employee->first_name}} {{$projectEmployee->employee->last_name}}</span>
                                                    </h5>
                                                </div>

                                                @foreach($projectSchedules as $projectSchedule)
                                                    <div class="card">
                                                        <!-- Nav tabs -->
                                                        <ul class="nav nav-tabs" role="tablist">
                                                            <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#schedule{{$projectSchedule->id}}" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Schedule</span></a> </li>
                                                            <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#detail{{$projectSchedule->id}}" role="tab"><span class="hidden-sm-up"></span> <span class="hidden-xs-down">Schedule Detail</span></a> </li>
                                                        </ul>
                                                        <!-- Tab panes -->
                                                        <div class="tab-content tabcontent-border">
                                                            <div class="tab-pane active" id="schedule{{$projectSchedule->id}}" role="tabpanel">
                                                                <div class="p-20">
                                                                    <div class="col-md-12 p-t-20">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-hover" id="tab_logic">
                                                                                <thead>
                                                                                <tr>
                                                                                    <th class="text-center">
                                                                                        Minggu
                                                                                    </th>
                                                                                    <th class="text-center">
                                                                                        Hari
                                                                                    </th>
                                                                                    <th class="text-center">
                                                                                        Jam Mulai
                                                                                    </th>
                                                                                    <th class="text-center">
                                                                                        Jam Berakhir
                                                                                    </th>
                                                                                </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                <tr>
                                                                                    <td>
                                                                                        <input type='text' value='{{\App\libs\Utilities::convertIntToWeek($projectSchedule->weeks)}}' class='form-control' readonly/>
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type='text' value='{{\App\libs\Utilities::convertIntToDay($projectSchedule->days)}}' class='form-control' readonly/>
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type='text' value='{{$projectSchedule->start}}' class='form-control' readonly/>
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type='text' value='{{$projectSchedule->finish}}' class='form-control' readonly/>
                                                                                    </td>
                                                                                    <input type="hidden" value="{{$projectSchedule->id}}" id="schedule-id{{$projectSchedule->id}}">
                                                                                </tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="tab-pane  p-20" id="detail{{$projectSchedule->id}}" role="tabpanel">
                                                                <div class="p-20">
                                                                    <div class="col-md-12 p-t-20">
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered table-hover" id="tab_logic{{$projectSchedule->id}}">
                                                                                <thead>
                                                                                <tr>
                                                                                    <th class="text-center">
                                                                                        Object
                                                                                    </th>
                                                                                    <th class="text-center">
                                                                                        Action
                                                                                    </th>
                                                                                    <th class="text-center">
                                                                                        Deksripsi
                                                                                    </th>
                                                                                </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                <tr id='sch-{{$projectSchedule->id}}-0'>
                                                                                    <input type="hidden" value="{{$projectSchedule->id}}" name="schedule_id[]">
                                                                                    <td>
                                                                                        <select id="project_object-{{$projectSchedule->id}}-0" name="project_objects[]" class='form-control'><option value='-1'>-</option></select>
                                                                                    </td>
                                                                                    <td>
                                                                                        <select id="action-{{$projectSchedule->id}}-0" name="actions[]" class='form-control'><option value='-1'>-</option></select>
                                                                                        <span><br>Atau tambah Baru</span>
                                                                                        <input type='text' id='actionNew0' name='action_new[]' class='form-control'>
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type='text' id='desc-{{$projectSchedule->id}}-0' class='form-control' name='description[]'/>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr id='sch-{{$projectSchedule->id}}-1'></tr>
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                        <a id="add_row" class="btn btn-success" style="color: #fff;" onclick="addRow({{$projectSchedule->id}})">Tambah</a>
                                                                        &nbsp;
                                                                        <a id='delete_row' class="btn btn-danger" style="color: #fff;" onclick="deleteRow({{$projectSchedule->id}})">Hapus</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
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

@endsection

@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/bootstrap-datepicker.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
{{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>

    <script type="text/javascript">
        let rowCount = '{{$projectSchedules->count()}}';
        let rowId = $('#schedule-id').val();

        for(let a=0;a<rowCount;a++){

            $('#project_object-'+rowId+'-0').select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Object - '
                },
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('select.projectObjects') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#action-'+rowId+'-0').select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Action - '
                },
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('select.actions') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });
        }

        var i=1;
        function addRow(rowId){
            var bufferID = i;
            $('#sch-'+ rowId + "-" + i).html(
                "<input type='hidden' value='" + rowId + "' name='schedule_id[]'>" +
                "<td>" +
                "<select id='project_object-" + rowId + "-"+ i +"' name='project_objects[]' class='form-control'><option value='-1'>-</option></select>" +
                "</td>" +

                "<td><select id='action-" + rowId + "-" + i +"' name='actions[]' class='form-control'><option value='-1'>-</option></select>" +
                "<span><br>Atau tambah Baru</span>" +
                "<input type='text' id='actionNew" + i +"' name='action_new[]' class='form-control'>" +
                "</td>" +

                "<td>" +
                "<input type='text' id='desc-"+ rowId+"-" + i +"' class='form-control' name='description'>" +
                "</td>"
            );
            $('#tab_logic' + rowId ).append('<tr id="sch-'+rowId+"-"+(i+1)+'"></tr>');

            $('#project_object-' + rowId + "-" + i).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Object - '
                },
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('select.projectObjects') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#action-' + rowId + "-" + i).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Action - '
                },
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('select.actions') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term)
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            i++;
        }

        function deleteRow(rowId){
            if(i>1){
                $("#sch-"+ rowId + "-" +(i-1)).html('');
                i--;
            }
        }
    </script>
@endsection

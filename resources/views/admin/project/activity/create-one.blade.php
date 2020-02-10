@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.activity.store-one'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU PLOTTING - {{$project->name}} - STEP 1</h3>
                        </div>
                        <div class="col-md-4 col-12 text-right">
                            <a href="{{ route('admin.project.activity.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                            <input type="submit" class="btn btn-success" value="SIMPAN">
                        </div>
                    </div>

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

                                    <div class="col-md-12 p-t-20">
                                        <div class="accordion" id="accordionExample">
                                            <div class="card m-b-0">

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Shift*</label>
                                                                    <select name='shift_type' class='form-control'>
                                                                        <option value='1'>SHIFT 1</option>
                                                                        <option value='2'>SHIFT 2</option>
                                                                        <option value='3'>SHIFT 3</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="place0">Place*</label>
                                                                    <select id='place0' name='places' class='form-control'><option value='-1'>-</option></select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="project_object0">Object / Sub Object*</label>
                                                                        <select id="project_object0" name="project_objects0[]"
                                                                                class='form-control' multiple="multiple"></select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Jam Mulai Shift*</label>
                                                                    <input id='start-shift' class='form-control time-inputmask' placeholder="HH:MM"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="place0">Jam Berakhir Shift*</label>
                                                                    <input id='finish-shift' class='form-control time-inputmask' placeholder="HH:MM"/>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="place0">Interval waktu*</label>
                                                                    <select id='interval' class='form-control'>
                                                                        <option value='15'>15 menit</option>
                                                                        <option value='30'>30 menit</option>
                                                                        <option value='60'>1 jam</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <a id="set_time" class="btn btn-primary">Buat Template Waktu</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive">
                                                        <input type="hidden" id="project_id" name="project_id" value="{{$project->id}}">
                                                        <table class="table table-bordered table-hover" id="tab_logic">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center" width="10">
                                                                    Jam Mulai
                                                                </th>
                                                                <th class="text-center"  width="10">
                                                                    Jam Berakhir
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr id='sch0'>
                                                                <td>
                                                                    <input id='start0' class='form-control time-inputmask' name='start_times[]' placeholder="HH:MM" required/>
                                                                </td>
                                                                <td>
                                                                    <input id='finish0' class='form-control time-inputmask' name='finish_times[]' placeholder="HH:MM" required/>
                                                                </td>
                                                            </tr>
                                                            <tr id='sch1'></tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <a id="add_row" class="btn btn-success" style="color: #fff;">Tambah</a>
                                                    &nbsp;
                                                    <a id='delete_row' class="btn btn-danger" style="color: #fff;">Hapus</a>
                                                </div>
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
    <style>
        .select2-selection--multiple{
            overflow: hidden !important;
            height: auto !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
{{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>

    <script type="text/javascript">
        $(".time-inputmask").inputmask("hh:mm", {
            placeholder: "HH:MM",
            insertMode: false,
            showMaskOnHover: false
        });

        $('#place0').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Place - '
            },
            width: '100%',
            ajax: {
                url: '{{ route('select.placeProjects') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        q: $.trim(params.term),
                        'project_id' : $('#project_id').val()
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
        $('#project_object0').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Object - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.projectObjectActivities') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        q: $.trim(params.term),
                        'project_id': $('#project_id').val(),
                        'place_id': $('#place0').val(),
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });

        $('#action0').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Action - '
            },
            width: '100%',
            minimumInputLength: 0,
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


        var i=1;
        $("#add_row").click(function(){
            var bufferID = i;
            $('#sch'+i).html(

                "<td>" +
                "<input id='start"+ i +"' class='form-control time-inputmask' name='start_times[]' placeholder='HH:MM' required/>" +
                "</td>" +

                "<td>" +
                "<input id='finish"+ i +"' class='form-control time-inputmask' name='finish_times[]' placeholder='HH:MM' required/>" +
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
        $("#set_time").click(function(){
            var startTime = $('#start-shift').val();
            var finishTime = $('#finish-shift').val();
            var interval = $('#finish-shift').val();
            alert(startTime);
            alert(finishTime);
            alert(interval);
            // var bufferID = i;
            // $('#sch'+i).html(
            //
            //     "<td>" +
            //     "<input id='start"+ i +"' class='form-control time-inputmask' name='start_times[]' placeholder='HH:MM' required/>" +
            //     "</td>" +
            //
            //     "<td>" +
            //     "<input id='finish"+ i +"' class='form-control time-inputmask' name='finish_times[]' placeholder='HH:MM' required/>" +
            //     "</td>"
            // );
            // $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');
            //
            // $(".time-inputmask").inputmask("hh:mm", {
            //     placeholder: "HH:MM",
            //     insertMode: false,
            //     showMaskOnHover: false
            // });
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

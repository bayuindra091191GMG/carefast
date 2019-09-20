@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.schedule.store'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU SCHEDULE CSO</h3>
                        </div>
                        <div class="col-md-4 col-12 text-right">
                            <a href="{{ route('admin.project.schedule.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
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
                                            <div class="card m-b-0 border-top">
                                                <div class="card-header">
                                                    <h5 class="mb-0">
                                                        <img src="{{ asset('storage/employees/'.$projectEmployee->employee->image_path) }}" width="50">
                                                        &nbsp;
                                                        <span>{{$projectEmployee->employee->first_name}} {{$projectEmployee->employee->last_name}}</span>
                                                    </h5>
                                                </div>
                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive">
                                                        <input type="hidden" id="project_id" name="project_id" value="{{$project->id}}">
                                                        <input type="hidden" name="project_employee_id" value="{{$projectEmployee->id}}">
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
                                                                <th class="text-center">
                                                                    Place
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr id='sch0'>
                                                                <td>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='week1' name='week[0][]' value='1'>
                                                                        <label class='custom-control-label' for='week1'>Minggu I</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='week2' name='week[0][]' value='2'>
                                                                        <label class='custom-control-label' for='week2'>Minggu II</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='week3' name='week[0][]' value='3'>
                                                                        <label class='custom-control-label' for='week3'>Minggu III</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='week4' name='week[0][]' value='4'>
                                                                        <label class='custom-control-label' for='week4'>Minggu IV</label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day1' name='day[0][]' value='1'>
                                                                        <label class='custom-control-label' for='day1'>Senin</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day2' name='day[0][]' value='2'>
                                                                        <label class='custom-control-label' for='day2'>Selasa</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day3' name='day[0][]' value='3'>
                                                                        <label class='custom-control-label' for='day3'>Rabu</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day4' name='day[0][]' value='4'>
                                                                        <label class='custom-control-label' for='day4'>Kamis</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day5' name='day[0][]' value='5'>
                                                                        <label class='custom-control-label' for='day5'>Jumat</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day6' name='day[0][]' value='6'>
                                                                        <label class='custom-control-label' for='day6'>Sabtu</label>
                                                                    </div>
                                                                    <div class='custom-control custom-checkbox mr-sm-2'>
                                                                        <input type='checkbox' class='custom-control-input' id='day7' name='day[0][]' value='7'>
                                                                        <label class='custom-control-label' for='day7'>Minggu</label>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input id='start0' class='form-control time-inputmask' name='start_times[]' placeholder="HH:MM" required/>
                                                                </td>
                                                                <td>
                                                                    <input id='finish0' class='form-control time-inputmask' name='finish_times[]' placeholder="HH:MM" required/>
                                                                </td>
                                                                <td>
                                                                    <select id='place0' name='places[]' class='form-control'><option value='-1'>-</option></select>
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
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
{{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>

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


        var i=1;
        $("#add_row").click(function(){
            var bufferID = i;
            $('#sch'+i).html(
                "<td>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='week1"+ i +"' name='week["+ i +"][]' value='1'>" +
                "<label class='custom-control-label' for='week1"+ i +"'>Minggu I</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='week2"+ i +"' name='week["+ i +"][]' value='2'>" +
                "<label class='custom-control-label' for='week2"+ i +"'>Minggu II</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='week3"+ i +"' name='week["+ i +"][]' value='3'>" +
                "<label class='custom-control-label' for='week3"+ i +"'>Minggu III</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='week4"+ i +"' name='week["+ i +"][]' value='4'>" +
                "<label class='custom-control-label' for='week4"+ i +"'>Minggu IV</label></div>" +
                "</td>" +

                "<td>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day1"+ i +"' name='day["+ i +"][]' value='1'>" +
                "<label class='custom-control-label' for='day1"+ i +"'>Senin</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day2"+ i +"' name='day["+ i +"][]' value='2'>" +
                "<label class='custom-control-label' for='day2"+ i +"'>Selasa</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day3"+ i +"' name='day["+ i +"][]' value='3'>" +
                "<label class='custom-control-label' for='day3"+ i +"'>Rabu</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day4"+ i +"' name='day["+ i +"][]' value='4'>" +
                "<label class='custom-control-label' for='day4"+ i +"'>Kamis</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day5"+ i +"' name='day["+ i +"][]' value='5'>" +
                "<label class='custom-control-label' for='day5"+ i +"'>Jumat</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day6"+ i +"' name='day["+ i +"][]' value='6'>" +
                "<label class='custom-control-label' for='day6"+ i +"'>Sabtu</label></div>" +
                "<div class='custom-control custom-checkbox mr-sm-2'>" +
                "<input type='checkbox' class='custom-control-input' id='day7"+ i +"' name='day["+ i +"][]' value='7'>" +
                "<label class='custom-control-label' for='day7"+ i +"'>Minggu</label></div>" +
                "</td>" +

                "<td>" +
                "<input id='start"+ i +"' class='form-control time-inputmask' name='start_times[]' placeholder='HH:MM' required/>" +
                "</td>" +

                "<td>" +
                "<input id='finish"+ i +"' class='form-control time-inputmask' name='finish_times[]' placeholder='HH:MM' required/>" +
                "</td>" +

                "<td>" +
                "<select id='place"+ i +"' name='places[]' class='form-control'><option value='-1'>-</option></select>" +
                "</td>"
            );
            $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');

            $(".time-inputmask").inputmask("hh:mm", {
                placeholder: "HH:MM",
                insertMode: false,
                showMaskOnHover: false
            });


            $('#place' + i).select2({
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

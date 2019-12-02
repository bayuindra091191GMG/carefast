@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.activity.update', 'id'=>$project->id],'method' => 'post','id' => 'general-form']) }}
                <div class="row">
                    <div class="col-md-8 col-12">
                        <h3>UBAH PLOTTING - {{$project->name}}</h3>
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
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="place0">Place*</label>
                                                                    <select id='place0' name='places' class='form-control'>
                                                                        <option value='{{$projectActivity->place_id}}'>{{$projectActivity->place->name}}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Shift*</label>
                                                                    <select name='shift_type' class='form-control'>
                                                                        <option value='1' @if($projectActivity->shift_type === 1) selected @endif>SHIFT 1</option>
                                                                        <option value='2' @if($projectActivity->shift_type === 2) selected @endif>SHIFT 2</option>
                                                                        <option value='3' @if($projectActivity->shift_type === 3) selected @endif>SHIFT 3</option>
                                                                    </select>
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
                                                                <th class="text-center" width="30">
                                                                    Object / Sub Object
                                                                </th>
                                                                <th class="text-center" width="30">
                                                                    Action
                                                                </th>
                                                                <th class="text-center" width="10">
                                                                    QT
                                                                </th>
                                                                <th class="text-center" width="10">
                                                                    Hari
                                                                </th>
                                                            </tr>
                                                            </thead>
                                                            <tbody>
                                                            <tr id='sch0'>
                                                                <td>
                                                                    <input id='start0' class='form-control time-inputmask' name='start_times[]' placeholder="HH:MM" required
                                                                           value='{{\Carbon\Carbon::parse($projectActivity->start)->format('H:i')}}'
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <input id='finish0' class='form-control time-inputmask' name='finish_times[]' placeholder="HH:MM" required
                                                                           value='{{\Carbon\Carbon::parse($projectActivity->finish)->format('H:i')}}'
                                                                    />
                                                                </td>
                                                                <td>
                                                                    <input class='form-control' value='{{ $projectActivity->plotting_name }}' disabled />
                                                                    <select id="project_object0" name="project_objects0[]" class='form-control' multiple="multiple"></select>
                                                                </td>
                                                                <td>
                                                                    <input type="hidden" id="selected-actions" value="{{$projectActivity->action_id}}">
                                                                    <select id="action0" name="actions0[]" class='form-control' multiple="multiple"></select>
                                                                    {{--                                                                    <span><br>Atau tambah Baru</span>--}}
                                                                    {{--                                                                    <input type='text' id='actionNew0' name='action_new[]' class='form-control'>--}}
                                                                </td>
                                                                <td>
                                                                    <select id="period0" name='period[]' class='form-control'>
                                                                        <option value='Daily' @if($projectActivity->period_type === "Daily") selected @endif>Daily</option>
                                                                        <option value='Weekly' @if($projectActivity->period_type === "Weekly") selected @endif>Weekly</option>
                                                                        <option value='Monthly' @if($projectActivity->period_type === "Monthly") selected @endif>Monthly</option>
                                                                    </select>
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
                                                            </tr>
                                                            <tr id='sch1'></tr>
                                                            </tbody>
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
                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
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

        var selectedValues = $("#selected-actions").val().split('#');
        $('#action0').select2('val',selectedValues);
    </script>
@endsection

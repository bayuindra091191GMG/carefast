@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.activity.store'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU PLOTTING - {{$project->name}} - STEP 2</h3>
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

                                    <div class="col-md-12 p-t-20" id="app">
                                        <div class="accordion" id="accordionExample">
                                            <div class="card m-b-0">
                                                <div>
                                                    <table class="scrollmenu">
                                                        <tr>
                                                            <td>Time</td>
                                                            @for($ct=1;$ct<=365;$ct++)
                                                                <td>Day {{$ct}}</td>
                                                            @endfor
                                                        </tr>
                                                        <tr v-for="time in times">
                                                            <td>@{{ time.time_string }}</td>
                                                            <td v-for="(day, index) in time.days" class="tr-class">
                                                                <input type="text"
                                                                       v-if="time.weekly_datas.length > 0" disabled/>
                                                                <input type="text"
                                                                       v-else disabled/>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <hr/>

                                                <input type="hidden" id="project_id" name="project_id" value="{{$project->id}}">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Object / Sub Object*</label>
                                                                    <select id="project_object0" name="project_objects0[]" class='form-control' multiple="multiple"></select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" for="place0">Action*</label>
                                                                    <select id="action0" name="actions0[]" class='form-control' multiple="multiple"></select>
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
                                                                    <label class="form-label" for="place0">Pilih Jam*</label>
                                                                    <select name='shift_type' class='form-control'>
                                                                        <option v-for="time in times">@{{ time.time_string }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
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
                                                    </div>
                                                </div>

                                                <div class="col-md-12 p-t-20">
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
        /*.scrollmenu {*/
        /*    background-color: #333;*/
        /*    overflow: auto;*/
        /*    white-space: nowrap;*/
        /*}*/
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
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
<script>
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

    //Create a new component for product-details with a prop of details.
    var data = '{{ $times }}';
    var subData = JSON.parse(data.replace(/&quot;/g,'"'));
    // console.log(subData);

    new Vue({
        el: '#app',
        data: {
            times: subData,
        },
        computed: {

        }
    });
</script>
@endsection

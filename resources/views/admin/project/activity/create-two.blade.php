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
                                                <input type="hidden" id="place0" value="{{$place->id}}">
                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label">Period*</label>
                                                            <select name='period' class='form-control' v-model="period">
                                                                <option value='1'>Daily</option>
                                                                <option value='2'>Weekly</option>
                                                                <option value='3'>Monthly</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr/>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label" >Pilih Jam*</label>
                                                                    <select name='shift_type' class='form-control' v-model="selected_time">
                                                                        <option v-for="time in times" :value="time.time_value">@{{ time.time_string }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">

                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Object / Sub Object*</label>
{{--                                                                    <select id="project_object0" name="project_objects0[]" v-model="project_objects"--}}
{{--                                                                            class='form-control' multiple="multiple"></select>--}}

                                                                    <select2 :disabled="isDisabled()" name="project_objects0[]" v-model="project_objects" url="{{route('select.projectObjectActivities') }}" placeholder=" -- Pilih Object -- ">
                                                                    </select2>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Action*</label>
{{--                                                                    <select id="action0" name="actions0[]" v-model="actions"--}}
{{--                                                                            class='form-control' multiple="multiple"></select>--}}
                                                                    <select2 :disabled="isDisabled()" name="actions0[]" v-model="actions" url="{{route('select.actions') }}" placeholder=" -- Pilih Action -- ">
                                                                    </select2>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12 p-t-20">
                                                    <a class="btn btn-success" style="color: #fff;" v-on:click="changePlotting">Ganti</a>
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
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
{{--    <script src="{{ asset('js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="{{ asset('js/jquery.inputmask.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
<script>
    //Create a new component for product-details with a prop of details.
    var data = '{{ $times }}';
    var subData = JSON.parse(data.replace(/&quot;/g,'"'));
    // console.log(subData);

    Vue.component('select2', {
        props: ['options','value', 'url' ,'placeholder','extra'],
        template: '#select2-template',
        data : function() {
            var thisVal = this;
            return {
                ajaxOptions: {
                    url: this.url,
                    dataType: 'json',
                    delay: 250,
                    tags: true,
                    data: function(params) {
                        if (params === undefined || params === null)return this.extra;
                        if (thisVal.extra !== undefined){
                            thisVal.extra.q = $.trim(params.term);
                            return thisVal.extra;
                        }
                        else {
                            return {
                                q: $.trim(params.term)
                            };
                        }
                    },
                    processResults: function(data) {

                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            };
        },
        mounted: function () {
            var vm = this;

            if (this.url != undefined){
                $(vm.$el)
                // init select2
                    .select2({
                        placeholder : {
                            id : "-1",
                            text : vm.placeholder
                        },
                        width: '100%',
                        ajax: vm.ajaxOptions
                    })
                    .val(this.value)
                    .trigger('change')
                    // emit event on change.
                    .on('change', function () {
                        vm.$emit('input', this.value)
                    });

                if (this.value !== 0 && this.value !== null){
                    //there is preselected value, we need to query right away.
                    var initData = axios.get(this.url,{
                        params : this.extra
                    }).then(function(val){
                        if (val.data.length > 0){
                            for(var idx = 0 ; idx < val.data.length; idx++){

                                if (val.data[idx].id == vm.value){
                                    var option = new Option(val.data[idx].text, val.data[idx].id, true, true);
                                    $(vm.$el).append(option).trigger('change');

                                    // manually trigger the `select2:select` event
                                    $(vm.$el).trigger({
                                        type: 'select2:select',
                                        params: {
                                            data: val.data[idx]
                                        }
                                    });
                                }
                            }
                        }
                    }).catch(function(err){
                        console.log(err);
                    });
                }
            }else if (vm.options !== undefined){
                var opt = vm.options;
                if (typeof(vm.options) === "object"){
                    opt = Object.entries(vm.options).map(function(v){
                        return {id : v[0], text : v[1]};
                    });
                }
                $(vm.$el)
                // init select2
                    .select2({ data: opt })
                    .val(this.value)
                    .trigger('change')
                    // emit event on change.
                    .on('change', function () {
                        vm.$emit('input', this.value)
                    });
            }

        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el)
                    .val(value)
                    .trigger('change');
            },
            options: function (value) {
                this.options = value;
                // update value
                $(this.$el)
                // init select2
                    .select2({ data: this.options })
                    .val(this.value)
                    .trigger('change')
            },
            url: function(value) {
                this.ajaxOptions.url = this.url;
                $(this.$el).select2({
                    placeholder : {
                        id : "-1",
                        text : value
                    },
                    width: '100%',
                    ajax: this.ajaxOptions
                });
            },
            placeholder : function(value){
                this.placeholder = value;
                $(this.$el).select2({
                    placeholder : {
                        id : "-1",
                        text : value
                    },
                    width: '100%',
                    ajax: this.ajaxOptions
                })
            },
            extra : function(value){
                this.extra = value;
                $(this.$el).select2({
                    placeholder : {
                        id : "-1",
                        text : value
                    },
                    width: '100%',
                    ajax: this.ajaxOptions
                });
            }

        },
        destroyed: function () {
            $(this.$el).off().select2('destroy')
        }
    });

    new Vue({
        el: '#app',
        data: {
            times: subData,
            period: "",
            selected_time: "",
            project_objects: "",
            actions: "",
        },
        methods:{
            testing(){
                alert('asdf');
            },
            changePlotting(){
                var period = this.period;
                var selected_time = this.selected_time;
                var project_objects = this.project_objects;
                var actions = this.actions;

                console.log(period);
                console.log(selected_time);
                console.log(project_objects);
                console.log(actions);
            }
        },
        computed: {

        }
    });
</script>
@endsection

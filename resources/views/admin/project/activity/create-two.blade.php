@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.activity.store'],'method' => 'post','id' => 'general-form','v-on:submit.prevent']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU PLOTTING - {{$project->name}} - STEP 2</h3>
                        </div>
                        <div class="col-md-4 col-12 text-right">
                            <a href="{{ route('admin.project.activity.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                            <button type="button" class="btn btn-success" onclick="root.submit();">SIMPAN</button>
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
                                                <div class="mb-4">
                                                    <table class="scrollmenu">
                                                        <tr>
                                                            <td>Time</td>
                                                            @for($ct=1;$ct<=365;$ct++)
                                                                <td>Day <br/>{{$ct}}</td>
                                                            @endfor
                                                        </tr>
                                                        <tr v-for="time in times">
                                                            <td>@{{ time.time_string }}</td>
                                                            <td v-for="(day, index) in time.days" class="tr-class">
                                                                <button type="button" class="btn btn-lg"
                                                                        v-if="day.action == '' "
                                                                        data-toggle="popover" title="Plot"
                                                                        data-content="Pilih Action Dibawah ini">
                                                                </button>
                                                                <button type="button" class="btn btn-lg"
                                                                        v-else :style="{backgroundColor: day.color}"
                                                                        data-toggle="popover" title="Plot"
                                                                        :data-content="day.action">
                                                                </button>
{{--                                                                <input type="text"--}}
{{--                                                                       v-if="day.action == null" v-model="day.action" disabled/>--}}
{{--                                                                <input type="text"--}}
{{--                                                                       v-else v-model="day.action" :style="{backgroundColor: day.color}" disabled/>--}}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <hr/>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Period*</label>
                                                                    <select name='period' class='form-control' v-model="period" @change="changePeriod($event)">
                                                                        <option value='1' selected>Daily</option>
                                                                        <option value='2'>Weekly</option>
                                                                        <option value='3'>Monthly</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-3" v-if="dayEnable">
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day1' name='day[0][]' value='1' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day1'>Day 1</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day2' name='day[0][]' value='2' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day2'>Day 2</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day3' name='day[0][]' value='3' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day3'>Day 3</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day4' name='day[0][]' value='4' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day4'>Day 4</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day5' name='day[0][]' value='5' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day5'>Day 5</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='day6' name='day[0][]' value='6' v-model="selectedDay">
                                                                <label class='custom-control-label' for='day6'>Day 6</label>
                                                            </div>
{{--                                                            <div class='custom-control custom-checkbox mr-sm-2'>--}}
{{--                                                                <input type='checkbox' class='custom-control-input' id='day7' name='day[0][]' value='7' v-model="selectedDay">--}}
{{--                                                                <label class='custom-control-label' for='day7'>Minggu</label>--}}
{{--                                                            </div>--}}
                                                        </div>

                                                        <div class="col-md-3" v-if="monthEnable">
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='week1' name='week[0][]' value='1' v-model="selectedWeeks">
                                                                <label class='custom-control-label' for='week1'>Minggu I</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='week2' name='week[0][]' value='2' v-model="selectedWeeks">
                                                                <label class='custom-control-label' for='week2'>Minggu II</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='week3' name='week[0][]' value='3' v-model="selectedWeeks">
                                                                <label class='custom-control-label' for='week3'>Minggu III</label>
                                                            </div>
                                                            <div class='custom-control custom-checkbox mr-sm-2'>
                                                                <input type='checkbox' class='custom-control-input' id='week4' name='week[0][]' value='4' v-model="selectedWeeks">
                                                                <label class='custom-control-label' for='week4'>Minggu IV</label>
                                                            </div>
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
                                                                        <option disabled value="-1">-- Pilih Jam --</option>
                                                                        <option v-for="time in times" :value="time.time_value" >@{{ time.time_string }}</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group form-float form-group-lg">
                                                                <div class="form-line">
                                                                    <label class="form-label">Action*</label>
                                                                    {{--                                                                    <select id="action0" name="actions0[]" v-model="actions"--}}
                                                                    {{--                                                                            class='form-control' multiple="multiple"></select>--}}
                                                                    <select2 name="actions0" v-model="actions" url="{{route('select.actions') }}" placeholder=" -- Pilih Action -- " @selected_action="receiveSelectedAction">
                                                                    </select2>
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
{{--                                                                    <label class="form-label">Object / Sub Object*</label>--}}
{{--                                                                    <select id="project_object0" name="project_objects0[]" v-model="project_objects"--}}
{{--                                                                            class='form-control' multiple="multiple"></select>--}}

{{--                                                                    <select2 name="project_objects0[]" v-model="project_objects" url="{{route('select.projectObjectActivities', ['project_id'=>$project->id, 'place_id'=>$place->id]) }}" placeholder=" -- Pilih Object -- " multiple="multiple">--}}
{{--                                                                    </select2>--}}
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/vue-toasted"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
<script>
    $(function () {
        $('[data-toggle="popover"]').popover()
    });

    //Create a new component for product-details with a prop of details.
    var data = '{{ $times }}';
    var subData = JSON.parse(data.replace(/&quot;/g,'"'));
    // console.log(subData);

    Vue.component('select2', {
        props: ['options','value', 'url' ,'placeholder','extra', 'selected_action'],
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
                        // vm.$emit('input', this.value)
                        vm.$emit('input', $(this).val());
                        vm.$emit('selected_action', $(this).val());
                        // debugger;
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
                                    // $(vm.$el).trigger({
                                    //     type: 'select2:select',
                                    //     params: {
                                    //         data: val.data[idx]
                                    //     }
                                    // });
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
                        // vm.$emit('input', this.value)
                        vm.$emit('input', $(this).val());
                        vm.$emit('selected_action', $(this).val());
                    });
            }

        },
        watch: {
            value: function (value) {
                if ([...value].sort().join(",") !== [...$(this.$el).val()].sort().join(","))
                    $(this.$el).val(value).trigger('change');
                // update value
                // $(this.$el)
                //     .val(value)
                //     .trigger('change');
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

    var root = new Vue({
        el: '#app',
        data: {
            times: subData,
            period: "1",
            dayEnable: false,
            selectedDay: [],
            monthEnable: false,
            selectedWeeks: [],
            selected_time: -1,
            project_objects: [],
            actions: null,
            selected_action: null,
            object: '{{$object}}',

            project_id : '{{$project->id}}',
            place_id : '{{$place->id}}',
            shift_type : '{{$shift}}',
        },
        methods:{
            changePeriod(event){
                //for daily period
                if(event.target.value === "1"){
                    this.dayEnable = false;
                    this.monthEnable = false;
                }
                //for weekly period
                else if(event.target.value === "2"){
                    this.dayEnable = true;
                    this.monthEnable = false;
                }
                //for monthly period
                else if(event.target.value === "3"){
                    this.dayEnable = true;
                    this.monthEnable = true;
                }
            },
            changePlotting(){
                var period = this.period;
                var selected_time = this.selected_time;
                var project_objects = this.object;
                var selected_day = this.selectedDay;
                var selected_weeks = this.selectedWeeks;
                var actions = this.actions;
                var selected_action = this.selected_action;
                var dayStringWeekly = "";
                var dayStringMonthlyDay = "";
                var dayStringMonthlyWeek = "";

                //get index of time
                let index=0;
                for(let j=0; j<this.times.length; j++){
                    if(this.times[j].time_value === selected_time){
                        index = j;
                    }
                }

                //for daily period
                if(period === "1"){
                    // this.times[index].action_daily =  project_objects + "/" + selected_action;

                    this.times[index].action_daily =  actions;
                    this.times[index].object_daily =  project_objects;

                    this.times[index].daily_datas.push({
                        TimeValue : selected_time,
                        Day : '1#2#3#4#5#6#7#',
                        Object : project_objects,
                        Action :  actions,
                    });
                    for(let i=0; i<this.times[index].days.length; i++){
                        this.selected_plot = "object = " + project_objects + " Action = " + selected_action;

                        this.times[index].days[i].action = this.selected_plot;
                        this.times[index].days[i].color = '#00ccff';
                        this.times[index].days[i].type = 1;
                        // if(this.times[index].days[i].action === ""){
                        // }
                    }
                }
                //for weekly period
                else if(period === "2"){
                    //add to time array data
                    this.times[index].weekly_datas = [];
                    for(let j=0; j<selected_day.length; j++){
                        dayStringWeekly = dayStringWeekly + "" + selected_day[j] + "#";
                    }
                    this.times[index].weekly_datas.push({
                        TimeValue : selected_time,
                        Day : dayStringWeekly,
                        Object : project_objects,
                        Action :  actions,
                        // Action : project_objects + "/" + selected_action,
                    });
                    for(let i=0; i<this.times[index].days.length; i++){
                        this.selected_plot = "object = " + project_objects + " Action = " + selected_action;
                        let tempI = i+1;
                        let tempIString = tempI.toString();
                        for(let j=0; j<selected_day.length; j++){
                            if(tempIString === selected_day[j]){
                                this.times[index].days[i].action = this.selected_plot;
                                this.times[index].days[i].color = '#00cc66';
                                this.times[index].days[i].type = 2;

                                let valueInt = parseInt(selected_day[j]);
                                selected_day[j] = (valueInt + 6).toString();
                            }
                        }
                    }
                    this.selectedDay = [];
                }
                //for monthly period
                else if(period === "3"){
                    let initialData = [];
                    for(let j=0; j<selected_weeks.length; j++){
                        for(let i=0; i<selected_day.length; i++){
                            let valueData = parseInt( selected_day[i]) + (6* (parseInt(selected_weeks[j])-1));
                            initialData.push(valueData.toString());
                        }
                    }
                    for(let j=0; j<selected_weeks.length; j++){
                        dayStringMonthlyWeek = dayStringMonthlyWeek + "" + selected_weeks[j] + "#";
                    }
                    for(let i=0; i<selected_day.length; i++){
                        dayStringMonthlyDay = dayStringMonthlyDay + "" + selected_day[i] + "#";
                    }
                    //add to time array data
                    this.times[index].monthly_datas.push({
                        TimeValue : selected_time,
                        Week : dayStringMonthlyWeek,
                        Day : dayStringMonthlyDay,
                        Object : project_objects,
                        Action :  actions,
                        // Action : project_objects + "/" + selected_action,
                    });

                    for(let i=0; i<this.times[index].days.length; i++){
                        this.selected_plot = "object = " + project_objects + " Action = " + selected_action;
                        let tempI = i+1;
                        let tempIString = tempI.toString();
                        for(let k=0; k<initialData.length; k++){
                            if(tempIString === initialData[k]){
                                this.times[index].days[i].action = this.selected_plot;
                                this.times[index].days[i].color = '#ff9933';
                                this.times[index].days[i].type = 3;

                                let valueInt = parseInt(initialData[k]);
                                initialData[k] = (valueInt + (6*4)).toString();
                            // debugger;
                            }
                        }
                    }
                }
            },
            receiveSelectedAction(val){
                var splitVar = this.actions.split('-');
                this.selected_action = splitVar[1];
            },
            validateSubmission : function(){
                return true;
            },
            submit : function(){
                var submittedItem = {
                    times : this.times,
                    object : this.object,
                    project_id : this.project_id,
                    place_id : this.place_id,
                    shift_type : this.shift_type,
                };
                // console.log(submittedItem);
                if (this.validateSubmission()){
                    axios.post('{{route('admin.project.activity.store')}}',submittedItem).then(response => {
                        console.log(response);
                        if (response.data.errors){
                            //there is error
                            var errors = Object.entries(response.data.errors);
                            this.errors = [];
                            //create new array.
                            for(var idx = 0 ; idx < errors.length; idx++){
                                this.errors.push({
                                    attribute : errors[idx][0],
                                    messages : errors[idx][1]
                                })
                            }
                        }else{
                            //here success, change the page to view the record. return the sales order id.
                            window.location.href = response.data.url;
                        }
                    })
                }

            },
        },
        computed: {

        }
    });
</script>
@endsection

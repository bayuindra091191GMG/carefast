@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.activity.store'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU PLOTTING - {{$project->name}}</h3>
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
                                                                    <select id='place0' name='places' class='form-control'><option value='-1'>-</option></select>
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
                                                <div id="app">
                                                    <table>
                                                        <tr>
                                                            <td>Time</td>
                                                            <td><input name="action[]" v-model="days.action" disabled/></td>
                                                        </tr>
                                                        <tr
                                                            v-for="data in datas"
                                                        >
                                                            <td>@{{ data.time }}</td>
                                                            <td
                                                                v-for="day in data.days"
                                                            >
                                                                <input type="text" v-model="data.weeklyDatas[day.day]"
                                                                       v-if="day.type == daily" disabled/>
                                                            </td>
                                                        </tr>
                                                    </table>
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
    //Create a new component for product-details with a prop of details.


    Vue.component('timeline-detail-row', {
        props: {
            detailRows: [{
                'id'    : null,
                'time'  : null
            }],
        },
        template: `
        <tr v-for="detailRow in detailRows">
           <td>{{ time }}</td>
          <timeline-detail-column></timeline-detail-column>
        </tr>
      `
    });

    Vue.component('timeline-detail-column', {
        props: {
            detailColumns: [{
                'id'            : null,
                'actions'       : null,
                'period_type'   : null,
                'color'         : null,
            }]
        },
        template: `
            <td v-for="detail in detailColumns">
              {{ detail }}
            </td>
          `
    });

    Vue.component('product', {
        props: {
            premium: {
                type: Boolean,
                required: true
            }
        },
        template: ` `,
        data() {
            return {
                product: 'Socks',
                brand: 'Vue Mastery',
                selectedVariant: 0,
                details: ['80% cotton', '20% polyester', 'Gender-neutral'],
                variants: [
                    {
                        variantId: 2234,
                        variantColor: 'green',
                        variantImage:  'https://www.vuemastery.com/images/challenges/vmSocks-green-onWhite.jpg',
                        variantQuantity: 10
                    },
                    {
                        variantId: 2235,
                        variantColor: 'blue',
                        variantImage: 'https://www.vuemastery.com/images/challenges/vmSocks-blue-onWhite.jpg',
                        variantQuantity: 0
                    }
                ],
                cart: 0
            }
        },
        methods: {
            addToCart: function() {
                this.cart += 1
            },
            updateProduct: function(index) {
                this.selectedVariant = index
            }
        },
        computed: {
            title() {
                return this.brand + ' ' + this.product
            },
            image(){
                return this.variants[this.selectedVariant].variantImage
            },
            inStock(){
                return this.variants[this.selectedVariant].variantQuantity
            },
            shipping() {
                if (this.premium) {
                    return "Free"
                }
                return 2.99
            }
        }
    });

    new Vue({
        el: '#app',
        data: {
            premium: true,
            headerId: 1,
            variants: [
                {
                    variantId: 2234,
                    variantColor: 'green',
                    variantImage:  'https://www.vuemastery.com/images/challenges/vmSocks-green-onWhite.jpg',
                    variantQuantity: 10
                },
                {
                    variantId: 2235,
                    variantColor: 'blue',
                    variantImage: 'https://www.vuemastery.com/images/challenges/vmSocks-blue-onWhite.jpg',
                    variantQuantity: 0
                }
            ]
        },
        computed: {
            headerCount(){
                return this.headerId = this.headerId + 1;
            }
        }
    });
</script>
@endsection

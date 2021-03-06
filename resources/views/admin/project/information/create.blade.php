@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>TAMBAH BARU PROJECT</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.project.information.store'],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_main">Foto *</label>
                                                {!! Form::file('photo', array('id' => 'photo', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="name">Nama Project*</label>
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ old('name') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Kode Project *</label>
                                                <input id="code" type="text" class="form-control"
                                                       name="code" value="{{ old('code') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Nomor Telepon *</label>
                                                <input id="phone" type="text" class="form-control"
                                                       name="phone" value="{{ old('phone') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="address">Alamat *</label>
                                                <textarea name="address" id="address" class="form-control" rows="3">{{ old('address') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="searchmap">Lokasi *</label>
                                                <input type="text" name="location" id="searchmap" class="form-control"/>
                                            </div>
                                            <div id="map-canvas" style="height: 200px;"></div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="latitude">Latitude</label>
                                                        <input type="text" name="latitude" id="latitude" class="form-control" readonly/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="longitude">Longitude</label>
                                                        <input type="text" name="longitude" id="longitude" class="form-control" readonly/>
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
                                                        <label class="form-label" for="total_manday">Tanggal Dimulai Project*</label>
                                                        <input id="start_date" name="start_date" type="text" class="form-control" autocomplete="off"  required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_mp_onduty">Tanggal Selesai Project*</label>
                                                        <input id="finish_date" name="finish_date" type="text" class="form-control" autocomplete="off" required>
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
                                                        <label class="form-label" for="total_manday">Total Manday *</label>
                                                        <input id="total_manday" type="number" class="form-control"
                                                               name="total_manday" value="{{ old('total_manday') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_mp_onduty">Total MP Onduty *</label>
                                                        <input id="total_mp_onduty" type="number" class="form-control"
                                                               name="total_mp_onduty" value="{{ old('total_mp_onduty') }}">
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
                                                        <label class="form-label" for="total_mp_off">Total Mp Off *</label>
                                                        <input id="total_mp_off" type="number" class="form-control"
                                                               name="total_mp_off" value="{{ old('total_mp_off') }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_manpower">Total Manpower *</label>
                                                        <input id="total_manpower" type="number" class="form-control"
                                                               name="total_manpower" value="{{ old('total_manpower') }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="1" selected>Aktif</option>
                                                <option value="2">Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="address">Deksripsi *</label>
                                                <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="col-md-12">
                                                        <div class="form-group form-float form-group-lg">
                                                            <div class="form-line">
                                                                <label for="customer">Customer *</label>
                                                                <select id="customer" name="customer[]" class="form-control" multiple></select>
                                                            </div>
                                                        </div>
                                                    </div>
{{--                                                    <input type="hidden" id="customer-selected" name="customer">--}}

{{--                                                    <div class="col-md-2">--}}
{{--                                                        <div class="form-group form-float form-group-lg">--}}
{{--                                                            <div class="form-line">--}}
{{--                                                                <label for="customer">&nbsp;</label>--}}
{{--                                                                <a class="form-control btn btn-success" id="add-customer" onclick="addCustomer()"--}}
{{--                                                                   style="color: white;">Tambah</a>--}}
{{--                                                            </div>--}}
{{--                                                        </div>--}}
{{--                                                    </div>--}}
                                                </div>
                                            </div>
{{--                                            <div class="col-md-6">--}}
{{--                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                    <table class="table table-bordered table-hover" id="tab_logic">--}}
{{--                                                        <thead>--}}
{{--                                                        <tr>--}}
{{--                                                            <th class="text-center" style="width: 75%">--}}
{{--                                                                Customer*--}}
{{--                                                            </th>--}}
{{--                                                            <th class="text-center" style="width: 25%">--}}
{{--                                                                Action--}}
{{--                                                            </th>--}}
{{--                                                        </tr>--}}
{{--                                                        </thead>--}}
{{--                                                        <tbody>--}}
{{--                                                        <tr id='sch1'></tr>--}}
{{--                                                        </tbody>--}}
{{--                                                    </table>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}
                                        </div>
                                    </div>
                                    <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-danger">BATAL</a>
                                        <input type="submit" class="btn btn-success" value="SIMPAN">
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
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
    <style>
        .select2-selection--multiple{
            overflow: hidden !important;
            height: auto !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key={{env('GMAPS_KEY')}}&libraries=places"
            type="text/javascript"></script>

    <script type="text/javascript">
        $("#photo").fileinput({
            theme: 'fas',
            showUpload: false,
            allowedFileExtensions: ["jpg", "png", "gif"],
            maxFileCount: 1
        });

        jQuery('#start_date').  datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
        jQuery('#finish_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });


        $('#city').select2();

        $('#customer').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Customer - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.customers') }}',
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
        function addCustomer(){
            let customerID = $('#customer').val();
            let selectedCustomer = $('#customer-selected').val();

            if(customerID === null){

            }
            else{
                if(!(selectedCustomer.includes(customerID))){
                    let newSelectedCustomer = selectedCustomer + customerID + "#" ;
                    $('#customer-selected').val(newSelectedCustomer);

                    $('#sch'+i).html(
                        "<td><span>" + $('#select2-customer-container').text() + "</span></td>" +
                        "<td><a class='form-control btn btn-danger' onclick='deleteCustomer(" + i + ")' style='color: white;'>Delete</a></td>"
                    );
                    $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');
                    i++;
                }
            }
        }

        function deleteCustomer(rowId){
            let selectedCustomer = $('#customer-selected').val();
            let selectedCustomerSplit = selectedCustomer.split("#");
            let deletedCustomer = selectedCustomerSplit[parseInt(rowId)-1];
            let newSelectedCustomer = selectedCustomer.replace(deletedCustomer + "#", "");

            $('#customer-selected').val(newSelectedCustomer);
            $('#sch' + rowId).remove();
        }

        var map = new google.maps.Map(document.getElementById('map-canvas'), {
            center:{
                lat: -6.180495,
                lng: 106.82834149999996
            },
            zoom: 15
        });

        var marker = new google.maps.Marker({
            position:{
                lat: -6.180495,
                lng: 106.82834149999996
            },
            map: map,
            draggable: true
        });

        var searchBox = new google.maps.places.SearchBox(document.getElementById('searchmap'));

        google.maps.event.addListener(searchBox, 'places_changed', function(){
            var places = searchBox.getPlaces();
            var bounds = new google.maps.LatLngBounds();
            var i, place;

            for(i=0; place=places[i]; i++){
                bounds.extend(place.geometry.location);
                marker.setPosition(place.geometry.location);
            }

            map.fitBounds(bounds);
            map.setZoom(15);
        });

        google.maps.event.addListener(marker, 'position_changed', function(){
            var lat = marker.getPosition().lat();
            var lng = marker.getPosition().lng();

            $('#latitude').val(lat);
            $('#longitude').val(lng);
        });

    </script>
@endsection

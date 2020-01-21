@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA PROJECT {{ $project->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.project.information.update', $project->id],'method' => 'post','id' => 'general-form']) }}

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
                                                       name="name" value="{{ $project->name }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Nomor Telepon *</label>
                                                <input id="phone" type="text" class="form-control"
                                                       name="phone" value="{{ $project->phone }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Kode Project *</label>
                                                <input id="code" type="text" class="form-control"
                                                       name="code" value="{{ $project->code }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="address">Alamat *</label>
                                                <textarea name="address" id="address" class="form-control" rows="3">{{ $project->address }}</textarea>
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
                                                        <input type="text" name="latitude" id="latitude" class="form-control" value="{{$project->latitude}}" readonly/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="longitude">Longitude</label>
                                                        <input type="text" name="longitude" id="longitude" class="form-control" value="{{$project->longitude}}" readonly/>
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
                                                        <input id="start_date" name="start_date" type="text" class="form-control" value="{{$start_date}}" autocomplete="off" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_mp_onduty">Tanggal Selesai Project*</label>
                                                        <input id="finish_date" name="finish_date" type="text" class="form-control" value="{{$finish_date}}" autocomplete="off" required>
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
                                                               name="total_manday" value="{{ $project->total_manday }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_mp_onduty">Total MP Onduty *</label>
                                                        <input id="total_mp_onduty" type="number" class="form-control"
                                                               name="total_mp_onduty" value="{{ $project->total_mp_onduty }}">
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
                                                               name="total_mp_off" value="{{ $project->total_mp_off }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="total_manpower">Total Manpower *</label>
                                                        <input id="total_manpower" type="number" class="form-control"
                                                               name="total_manpower" value="{{ $project->total_manpower }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="1" @if($project->status_id === 1) selected @endif>Aktif</option>
                                                <option value="2" @if($project->status_id === 2) selected @endif>Non-Aktif</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="address">Deksripsi *</label>
                                                <textarea name="description" id="description" class="form-control" rows="3">{{ $project->description }}</textarea>
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
                                                                <select id="customer" name="customer[]" class="form-control" multiple>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="customer-selected" name="customer" value="{{$project->customer_id}}">

                                                    <div class="col-md-2">
                                                        <div class="form-group form-float form-group-lg">
                                                            <div class="form-line">
                                                                <label for="customer">&nbsp;</label>
                                                                <a class="form-control btn btn-success" onclick="addCustomer()">Tambah</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <table class="table table-bordered table-hover" id="tab_logic">
                                                        <thead>
                                                        <tr>
                                                            <th class="text-center" style="width: 75%">
                                                                Customer*
                                                            </th>
                                                            <th class="text-center" style="width: 25%">
                                                                Action
                                                            </th>
                                                        </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php $idx = 1 @endphp

                                                        @if($customerList->count() > 0)
                                                            @foreach($customerList as $customer)
                                                                <tr id='sch{{$idx}}'>
                                                                    <td><span>{{$customer->name}} - {{$customer->email}}</span></td>
                                                                    <td><a class='form-control btn btn-danger' onclick='deleteCustomer({{ $idx }})'>Delete</a></td>
                                                                </tr>
                                                                @php $idx++ @endphp
                                                            @endforeach
                                                            <tr id='sch{{$idx}}'></tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                        <a href="{{ route('admin.project.information.show', ['id' => $project->id]) }}" class="btn btn-danger">BATAL</a>
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
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCfqCBxMgWfWHG-6pOHG2aehW3HJJNcXp4&libraries=places"
            type="text/javascript"></script>

    <script type="text/javascript">

        // $(document).ready(function() {
            // var selectedValues = $("#customer-selected").val().split('#');
            // alert(selectedValues[1]);
            // $('#customer').select2('val',selectedValues);
            // $('#customer').trigger('change');

            // var data = { id: 3, text: 'Barn owl' };
            // var newOption = new Option(data.text, data.id, false, false);
            // $('#customer').append(newOption).trigger('change');

            {{--var append = "";--}}
            {{--    @foreach($customerList as $customer)--}}
            {{--    append += "<option value='{{$customer->id}}' data-select2-id='81'>{{$customer->name}} - {{$customer->email}}</option>";--}}
            {{--    @endforeach--}}
            {{--$('#customer').html(append);--}}
        // });

        @if(!empty($employee->image_path))
            var photoUrl = '{{ asset('storage/projects/'. $employee->image_path) }}';
            $("#photo").fileinput({
                theme: 'fas',
                initialPreview : [photoUrl],
                initialPreviewAsData: true,
                overwriteInitial: true,
                showUpload: false,
                allowedFileExtensions: ["jpg", "png", "gif"],
                maxFileCount: 1
            });
        @else
            $("#photo").fileinput({
                theme: 'fas',
                showUpload: false,
                allowedFileExtensions: ["jpg", "png", "gif"],
                maxFileCount: 1
            });
        @endif
        jQuery('#start_date').datepicker({
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

        var i = '{{$idx}}';
        function addCustomer(){
            let customerID = $('#customer').val();
            let selectedCustomer = $('#customer-selected').val();

            if(!(selectedCustomer.includes(customerID))){
                let newSelectedCustomer = selectedCustomer + customerID + "#" ;
                $('#customer-selected').val(newSelectedCustomer);

                $('#sch'+i).html(
                    "<td><span>" + $('#select2-customer-container').text() + "</span></td>" +
                    "<td><a class='form-control btn btn-danger' onclick='deleteCustomer(" + i + ")'>Delete</a></td>"
                );
                $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');
                i++;
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

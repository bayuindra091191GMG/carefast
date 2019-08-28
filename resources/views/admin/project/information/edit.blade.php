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

                {{ Form::open(['route'=>['admin.project.update', $project->id],'method' => 'post','id' => 'general-form']) }}

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
                                                <label class="form-label" for="address">Alamat *</label>
                                                <textarea name="address" id="address" class="form-control" rows="10">{{ $project->address }}</textarea>
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
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="latitude">Latitude</label>
                                                <input type="text" name="latitude" id="latitude" class="form-control" value="{{$project->latitude}}" readonly/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="longitude">Longitude</label>
                                                <input type="text" name="longitude" id="longitude" class="form-control" value="{{$project->longitude}}" readonly/>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="customer">Customer *</label>
                                            <select id="customer" name="customer" class="form-control">
                                                <option value="{{ $project->customer_id }}">{{ $project->customer->name . ' - ' . $project->customer->email }}</option>
                                            </select>
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
                                    <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                        <a href="{{ route('information') }}" class="btn btn-danger">BATAL</a>
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
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>

    <script type="text/javascript">

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

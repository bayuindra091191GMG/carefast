@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>DETIL DATA PROJECT {{ $project->name }}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
{{--                        <button class="btn btn-danger " data-toggle="modal" data-target="#deleteModal" data-id="{{$project->id}}">HAPUS</button>--}}
                    </div>
                </div>

                <form>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="information-tab" href="{{ route('admin.project.information.show', ['id' => $project->id]) }}" role="tab" aria-controls="home" aria-selected="true">INFORMASI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="object-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.schedule.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">JADWAL</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="col-md-12 col-12 text-right">
                                                <a href="{{ route('admin.project.information.edit', ['id' => $project->id]) }}" class="btn btn-primary">EDIT OBJECT</a>
                                            </div>
                                            <div class="body">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                </form>
            </div>
        </div>
    </div>
    @include('partials._delete')
@endsection


@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <style>
        .fancybox-viewer img{
            width: 150px;
            height: auto;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>
    <script type="text/javascript">
        var Lat = '{{$project->latitude}}';
        var Long = '{{$project->longitude}}';
        var map = new google.maps.Map(document.getElementById('map-canvas'), {
            center:{
                lat: parseFloat(Lat),
                lng: parseFloat(Long)
            },
            zoom: 15
        });

        var marker = new google.maps.Marker({
            position:{
                lat: parseFloat(Lat),
                lng: parseFloat(Long)
            },
            map: map,
            draggable: true
        });

        $("a.fancybox-viewer").fancybox();

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
    </script>
    @include('partials._deletejs', ['routeUrl' => 'admin.project.information.destroy', 'redirectUrl' => 'admin.project.information.index'])
@endsection

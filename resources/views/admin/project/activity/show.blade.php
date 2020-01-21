@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-12">
                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>PLOTTING PROJECT - {{ $project->name }}</h3>
                    </div>
{{--                    <div class="col-md-4 col-12 text-right">--}}
{{--                        <button class="btn btn-danger " data-toggle="modal" data-target="#deleteModal" data-id="{{$project->id}}">HAPUS</button>--}}
{{--                    </div>--}}
                </div>

                <form>

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" id="information-tab" href="{{ route('admin.project.information.show', ['id' => $project->id]) }}" role="tab" aria-controls="home" aria-selected="true">INFORMASI</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="schedule-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
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
                                    <div class="card">
                                        <div class="card-body b-b">
                                            <div class="col-md-12 col-12 text-right">
                                                <a href="{{ route('admin.project.activity.create', ['id' => $project->id]) }}" class="btn btn-success">TAMBAH PLOTTING</a>
                                            </div>
                                            <div class="body">
                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive-sm">
                                                        @if($activities->count() == 0)
                                                            <h4 class="text-center">BELUM ADA PLOTTING</h4>
                                                        @else
                                                            <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                                                <thead>
                                                                <tr>
                                                                    <th class="text-center">Waktu</th>
                                                                    <th class="text-center">Aktifitas</th>
                                                                    <th class="text-center">Period</th>
                                                                    <th class="text-center">Nama Place</th>
                                                                    <th class="text-center">Nama Objek</th>
                                                                    <th class="text-center">Shift</th>
                                                                    <th class="text-center">Tindakan</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                </tbody>
                                                            </table>
                                                        @endif
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

                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <style></style>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: {
                url: '{!! route('datatables.project-activity') !!}',
                data: {
                    'id': '{{ $project->id }}'
                }
            },
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'time', name: 'time', class: 'text-center' },
                { data: 'action_name', name: 'action_name', class: 'text-center' },
                { data: 'period_type', name: 'period_type', class: 'text-center' },
                { data: 'place_name', name: 'place_name', class: 'text-center' },
                { data: 'plotting_name', name: 'plotting_name', class: 'text-center' },
                { data: 'shift', name: 'shift', class: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

    </script>
@endsection
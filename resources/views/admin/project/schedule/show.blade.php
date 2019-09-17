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
                        <h3>DETIL SCHEDULE EMPLOYEE PROJECT {{ $project->name }}</h3>
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
                                <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" id="schedule-tab" href="#" role="tab" aria-controls="profile" aria-selected="false">JADWAL / SCHEDULE</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="object" role="tabpanel" aria-labelledby="object-tab">
                                <div class="col-md-12">
                                    <div class="card">
                                        <div class="card-body b-b">
                                            {{--<div class="col-md-12 col-12 text-right">--}}
                                                {{--@if($isCreate)--}}
                                                    {{--<a href="{{ route('admin.project.schedule.create', ['id' => $project->id]) }}" class="btn btn-success">TAMBAH SCHEDULE</a>--}}
                                                {{--@else--}}
                                                    {{--<a href="{{ route('admin.project.schedule.edit', ['id' => $project->id]) }}" class="btn btn-primary">UBAH</a>--}}
                                                {{--@endif--}}
                                            {{--</div>--}}
                                            <div class="body">
                                                <div class="col-md-12 p-t-20">
                                                    <div class="table-responsive-sm">
                                                        <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                                            <thead>
                                                            <tr>
                                                                <th class="text-center">Nama</th>
                                                                <th class="text-center">Gambar</th>
                                                                <th class="text-center">Jabatan</th>
                                                                <th class="text-center">Tindakan</th>
                                                            </tr>
                                                            </thead>
                                                            <tbody></tbody>
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

                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
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
                url: '{!! route('datatables.project_schedule_employees') !!}',
                data: {
                    'id': '{{ $project->id }}'
                }
            },
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'name', name: 'name', class: 'text-center' },
                { data: 'picture', name: 'picture', class: 'text-center' },
                { data: 'employee_role', name: 'employee_role', class: 'text-center'},
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

    </script>
@endsection

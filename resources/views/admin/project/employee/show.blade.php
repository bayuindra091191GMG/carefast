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
                                    <a class="nav-link" id="information-tab" href="{{ route('admin.project.information.show', ['id' => $project->id]) }}" role="tab" aria-controls="information-tab" aria-selected="true">INFORMASI</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link active" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.schedule.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">JADWAL</a>
                                </li>
                            </ul>

                            <div class="tab-content" id="myTabContent">
                                <div class="tab-pane fade show active" id="basic" role="tabpanel" aria-labelledby="information-tab">
                                    <div class="col-md-12">
                                        <div class="card">
                                            <div class="card-body b-b">
                                                <div class="body">
                                                    @include('partials.admin._messages')

                                                    <div class="col-12 text-right">
                                                        <a href="{{ route('admin.project.employee.edit', ['project_id' => $project->id]) }}" class="btn btn-danger">UBAH</a>
                                                    </div>

                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <label class="form-label" for="code">Nama Project</label>
                                                                        <input type="text" id="code" name="code" class="form-control" value="{{ $project->name }}" readonly/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group form-float form-group-lg">
                                                                    <div class="form-line">
                                                                        <label class="form-label" for="manpower">Manpower</label>
                                                                        <input type="text" id="manpower_string" class="form-control" value="{{ $project->total_manpower ?? 0 }}" readonly/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr/>

                                                    <div class="col-12 mb-3">
                                                        <h3>UPPER MANAGEMENT</h3>
                                                    </div>

                                                    <div class="col-12">
                                                        @if($upperEmployees->count() > 0)
                                                            <table id="upper_employee_table" class="table table-striped table-bordered nowrap">
                                                                <thead>
                                                                <tr>
                                                                    <th class="text-center" style="width: 25%">ID</th>
                                                                    <th class="text-center" style="width: 45%">NAMA</th>
                                                                    <th class="text-center" style="width: 30%">ROLE/POSISI</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>

                                                                @foreach($upperEmployees as $upperEmployee)
                                                                    <tr>
                                                                        <td>
                                                                            <a href="{{ route('admin.employee.show', ['id' => $upperEmployee->employee_id]) }}">{{ $upperEmployee->employee->code }}</a>
                                                                        </td>
                                                                        <td>{{ $upperEmployee->employee->first_name. ' '. $upperEmployee->employee->last_name }}</td>
                                                                        <td class="text-center">{{ $upperEmployee->employee_role->name }}</td>
                                                                    </tr>
                                                                @endforeach

                                                                </tbody>
                                                            </table>
                                                        @else
                                                            <h4>BELUM ADA PENUGASAN EMPLOYEE</h4>
                                                        @endif
                                                    </div>

                                                    <hr/>

                                                    <div class="col-12 mb-3">
                                                        <h3>CLEANERS</h3>
                                                    </div>

                                                    <div class="col-12">
                                                        @if($cleanerEmployees->count() > 0)
                                                            <table id="upper_employee_table" class="table table-striped table-bordered nowrap">
                                                                <thead>
                                                                <tr>
                                                                    <th class="text-center" style="width: 25%">ID</th>
                                                                    <th class="text-center" style="width: 75%">NAMA</th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>

                                                                @foreach($cleanerEmployees as $cleanerEmployee)
                                                                    <tr>
                                                                        <td>
                                                                            <a href="{{ route('admin.employee.show', ['id' => $cleanerEmployee->employee_id]) }}">{{ $cleanerEmployee->employee->code }}</a>
                                                                        </td>
                                                                        <td>{{ $cleanerEmployee->employee->first_name. ' '. $cleanerEmployee->employee->last_name }}</td>
                                                                    </tr>
                                                                @endforeach

                                                                </tbody>
                                                            </table>
                                                        @else
                                                            <h4>BELUM ADA PENUGASAN EMPLOYEE</h4>
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
                </form>>
            </div>
        </div>
    </div>
@endsection


@section('styles')
@endsection

@section('scripts')
    <script type="text/javascript">
    </script>
@endsection

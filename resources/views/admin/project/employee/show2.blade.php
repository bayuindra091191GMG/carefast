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
                                    <a class="nav-link active" id="employee-tab" href="{{ route('admin.project.employee.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR EMPLOYEE</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="object-tab" href="{{ route('admin.project.object.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">DAFTAR OBJECT</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="schedule-tab" href="{{ route('admin.project.activity.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">PLOTTING</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="attendance-tab" href="{{ route('admin.project.attendance.show', ['id' => $project->id]) }}" role="tab" aria-controls="profile" aria-selected="false">ABSENSI</a>
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
                                                        @if($isCreate)
                                                            <a href="{{ route('admin.project.employee.create', ['project_id' => $project->id]) }}" class="btn btn-success">TAMBAH EMPLOYEE</a>
                                                        @else
                                                            <a href="{{ route('admin.project.employee.edit', ['project_id' => $project->id]) }}" class="btn btn-primary">UBAH</a>
{{--                                                            &nbsp;--}}
{{--                                                            <a href="{{ route('admin.project.employee.set', ['project_id' => $project->id]) }}" class="btn btn-success">UBAH EMPLOYEE</a>--}}
                                                        @endif
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
{{--                                                                        <input type="text" id="manpower_string" class="form-control" value="{{ 'Sisa '. $manpowerLeft. ' dari '. $project->total_manpower }}" readonly/>--}}
                                                                        <input type="text" id="manpower_string" class="form-control" value="{{ $project->total_manpower }}" readonly/>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <hr/>
                                                    <table id="upper_employee_table" class="table table-striped table-bordered nowrap">
                                                        <thead>
                                                            <tr>
                                                                <th class="text-center" style="width: 30%">ROLE/POSISI</th>
                                                                <th class="text-center" style="width: 25%">JUMLAH</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        @php($count=0)
                                                        @foreach($employeeRoles as $employeeRole)
                                                            @if($employeeRoleAssigned[$count] > 0)
                                                                <tr id="upper_employee_row_{{$count}}">
                                                                    <td class="text-center">
                                                                        <span id="upper_employee_role_">{{$employeeRole->name}}</span>
                                                                    </td>
                                                                    <td class="text-center">
                                                                        <input name="employee_total[]" class="form-control" value="{{ $employeeRoleAssigned[$count] }}" readonly>
                                                                        <input type="hidden" name="employee_role_id[]" value="{{$employeeRole->id}}">
                                                                    </td>
                                                                </tr>
                                                            @endif
                                                            @php($count++)
                                                        @endforeach
                                                        </tbody>
                                                    </table>


                                                    <hr/>

                                                    <div class="col-12 mb-3">
                                                        <h3>UPPER MANAGEMENT</h3>
                                                    </div>

                                                    <div class="col-12 text-right">
                                                        @if($isCreate)
                                                            <a href="{{ route('admin.project.employee.create', ['project_id' => $project->id]) }}" class="btn btn-success">TAMBAH EMPLOYEE</a>
                                                        @else
                                                            <a href="{{ route('admin.project.employee.edit-employee', ['project_id' => $project->id]) }}" class="btn btn-primary">UBAH</a>
                                                        @endif
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
                                                                    @if($upperEmployee->employee_roles_id != 11)
                                                                        <tr>
                                                                            <td>
                                                                                <a href="{{ route('admin.employee.show', ['id' => $upperEmployee->employee_id]) }}">{{ $upperEmployee->employee->code }}</a>
                                                                            </td>
                                                                            <td>{{ $upperEmployee->employee->first_name. ' '. $upperEmployee->employee->last_name }}</td>
                                                                            <td class="text-center">{{ $upperEmployee->employee_role->name }}</td>
                                                                        </tr>
                                                                    @endif
                                                                @endforeach
                                                                @foreach($upperEmployees as $upperEmployee)
                                                                    @if($upperEmployee->employee_roles_id == 11)
                                                                        <tr>
                                                                            <td>
                                                                                <a href="{{ route('admin.employee.show', ['id' => $upperEmployee->employee_id]) }}">{{ $upperEmployee->employee->code }}</a>
                                                                            </td>
                                                                            <td>{{ $upperEmployee->employee->first_name. ' '. $upperEmployee->employee->last_name }}</td>
                                                                            <td class="text-center">{{ $upperEmployee->employee_role->name }}</td>
                                                                        </tr>
                                                                    @endif
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
                                                            <table id="cleaner_employee_table" class="table table-striped table-bordered nowrap">
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
                </form>
            </div>
        </div>
    </div>
@endsection


@section('styles')
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript">
        $('#cleaner_employee_table').DataTable();
    </script>
@endsection

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
                                                                        <input type="text" id="manpower_string" class="form-control" value="{{ 'Sisa '. $manpowerLeft. ' dari '. $project->total_manpower }}" readonly/>
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
                                                            <tr id="upper_employee_row_{{$count}}">
                                                                <td class="text-center">
                                                                    <span id="upper_employee_role_">{{$employeeRole->name}}</span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <input name="employee_total[]" class="form-control" value="{{ $employeeRoleAssigned[$count] }}" readonly>
                                                                    <input type="hidden" name="employee_role_id[]" value="{{$employeeRole->id}}">
                                                                </td>
                                                            </tr>
                                                            @php($count++)
                                                        @endforeach
                                                        </tbody>
                                                    </table>

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
@endsection

@section('scripts')
    <script type="text/javascript">
    </script>
@endsection

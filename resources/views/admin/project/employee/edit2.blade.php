@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <h3>UBAH DATA PENUGASAN EMPLOYEE PROJECT</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.project.employee.show', ['project_id' => $project->id]) }}" class="btn btn-danger">BATAL</a>
                        <a id="btn_submit" class="btn btn-success text-white">SIMPAN</a>
                        <a id="btn_loading" class="btn btn-success text-white" style="display: none"><i class="fas fa-sync-alt fa-spin"></i>&nbsp;&nbsp;MENYIMPAN DATA EMPLOYEE...</a>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.project.employee.update', $project->id],'method' => 'post','id' => 'general-form']) }}

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
                                                        <input type="text" id="manpower_string" class="form-control" value="{{ $manpowerLeft }}" readonly/>
                                                        <input type="hidden" id="manpower" name="manpower" value="{{ $manpowerLeft }}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>

                                    <div class="col-12 mb-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <h3>ROLE EMPLOYEE PROJECT</h3>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
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
                                                        <input name="employee_total[]" class="form-control"
                                                               min="0" step="1" pattern="\d+"
                                                               value="{{ $employeeRoleAssigned[$count] }}">
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

                {{ Form::close() }}
            </div>
        </div>
    </div>
@endsection


@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    {{--    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">--}}
@endsection

@section('scripts')
    {{--    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
@endsection

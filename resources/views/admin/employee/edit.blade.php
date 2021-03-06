@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA KARYAWAN {{ $employee->code }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.employee.update', $employee->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                        <label class="form-label" for="phone">Project Saat ini </label>
                                                        @if(empty($currentProject))
                                                            <input id="phone" type="text" class="form-control"
                                                                   value="-" readonly>
                                                        @else
                                                            <input id="phone" type="text" class="form-control"
                                                                   value="{{ $currentProject->project->name }}" readonly>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Kode Project </label>
                                                        @if(empty($currentProject))
                                                            <input id="phone" type="text" class="form-control"
                                                                   value="-" readonly>
                                                        @else
                                                            <input id="phone" type="text" class="form-control"
                                                                   value="{{ $currentProject->project->code }}" readonly>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_main">Foto</label>
                                                {!! Form::file('photo', array('id' => 'photo', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Nomor Ponsel Login *</label>
                                                <input id="phone" type="text" class="form-control"
                                                       name="phone" value="{{ $employee->phone }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="password">Ganti Kata Sandi</label>
                                                        <input id="password" type="password" class="form-control"
                                                               name="password">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi</label>
                                                        <input id="password_confirmation" type="password" class="form-control"
                                                               name="password_confirmation">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="code">ID Karyawan</label>
                                                <input id="code" type="text" class="form-control" style="text-transform: uppercase;"
                                                       name="code" value="{{ $employee->code }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="first_name">Nama Depan *</label>
                                                        <input id="first_name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="first_name" value="{{ $employee->first_name }}" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="last_name">Nama Belakang </label>
                                                        <input id="last_name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="last_name" value="{{ $employee->last_name }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="address">Alamat</label>
                                                <textarea id="address" class="form-control"
                                                          name="address" rows="3">{{ $employee->address ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="email">Alamat Email</label>
                                                        <input id="email" type="text" class="form-control"
                                                               name="email" value="{{ $email }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="telephone">Nomor Telpon Rumah</label>
                                                        <input id="telephone" type="text" class="form-control"
                                                               name="telephone" value="{{ $employee->telephone }}">
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
                                                        <label class="form-label" for="dob">Tanggal Lahir</label>
                                                        <input id="dob" type="text" class="form-control"
                                                               name="dob" value="{{ !empty($employee->dob) ? $employee->dob_string : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="nik">KTP</label>
                                                        <input id="nik" type="text" class="form-control"
                                                               name="nik" value="{{ $employee->nik ?? '' }}" pattern="\d+">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="notes">Keterangan Tambahan</label>
                                                <textarea id="notes" class="form-control"
                                                          name="notes" rows="3">{{ $employee->notes ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    @if($employee->employee_role_id > 1)
                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="role">Role/Posisi</label>
                                                    <select id="role" class="form-control" name="role">
                                                        <option value="-1"> - Pilih Role - </option>
                                                        @foreach($employeeRoles as $role)
                                                            <option value="{{ $role->id }}" @if($employee->employee_role_id === $role->id) selected @endif>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="status">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="1" @if($employee->status_id === 1) selected @endif>ACTIVE</option>
                                                    <option value="2" @if($employee->status_id === 2) selected @endif>NON-ACTIVE</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.employee.index') }}" class="btn btn-danger">BATAL</a>
                                    <input type="submit" class="btn btn-success" value="UBAH">
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
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/themes/fas/theme.js') }}"></script>
    <script type="text/javascript">

        @if(!empty($employee->image_path))
        var photoUrl = '{{ asset('storage/employees/'. $employee->image_path) }}';
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

        jQuery('#dob').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
    </script>
@endsection

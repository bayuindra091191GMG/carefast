@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <a href="{{ route('admin.employee.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>DETIL DATA KARYAWAN {{ $employee->code }}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.employee.set-schedule', ['employee_id' => $employee->id]) }}" class="btn btn-primary">ATUR JADWAL</a>
                        <a href="{{ route('admin.employee.edit', ['id' => $employee->id]) }}" class="btn btn-primary">EDIT</a>
                        <button class="btn btn-danger delete-modal" data-toggle="modal" data-target="#deleteModal" data-id="{{$employee->id}}">HAPUS</button>
                    </div>
                </div>

                <form>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="body">
                                    @include('partials.admin._messages')


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
                                                <label class="form-label" for="status">Status</label>
                                                <input id="status" type="text" class="form-control"
                                                       name="status" value="{{ strtoupper($employee->status->description) }}" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="photo">Foto *</label>
                                                @if(!empty($employee->image_path))
                                                    <a class="fancybox-viewer" href="{{ asset('storage/employees/'. $employee->image_path) }}"><img src="{{ asset('storage/employees/'. $employee->image_path) }}" alt=""/></a>
                                                @else
                                                    <h4>Tidak ada foto</h4>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="role">Role/Posisi</label>
                                                <input id="role" type="text" class="form-control"
                                                       name="role" value="{{ $employee->employee_role->name }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="phone">Nomor Ponsel Login</label>
                                                <input id="phone" type="text" class="form-control"
                                                       name="phone" value="{{ $employee->phone }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="code">ID Karyawan</label>
                                                <input id="code" type="text" class="form-control"
                                                       name="code" value="{{ $employee->code }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="first_name">Nama Depan</label>
                                                        <input id="first_name" type="text" class="form-control"
                                                               name="first_name" value="{{ $employee->first_name }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="last_name">Nama Belakang</label>
                                                        <input id="last_name" type="text" class="form-control"
                                                               name="last_name" value="{{ $employee->last_name }}" readonly>
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
                                                          name="address" rows="3" readonly>{{ $employee->address ?? '' }}</textarea>
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
                                                               name="email" value="{{ $email  }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="telephone">Nomor Telpon Rumah</label>
                                                        <input id="telephone" type="text" class="form-control"
                                                               name="telephone" value="{{ $employee->telephone }}" readonly>
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
                                                               name="dob" value="{{ !empty($employee->dob) ? $employee->dob_string : '' }}" readonly="">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="nik">KTP</label>
                                                        <input id="nik" type="text" class="form-control"
                                                               name="nik" value="{{ $employee->nik }}" readonly="">
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
                                                          name="notes" rows="3" readonly>{{ $employee->notes ?? '' }}</textarea>
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
    <script type="text/javascript">
        $("a.fancybox-viewer").fancybox();

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
    </script>
    @include('partials._deletejs', ['routeUrl' => 'admin.employee.destroy', 'redirectUrl' => 'admin.employee.index'])
@endsection

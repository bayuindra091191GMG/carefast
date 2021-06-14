@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <h2 class="card-title m-b-0">TAMBAH BARU ADMIN USER</h2>

                {{ Form::open(['route'=>['admin.admin-users.store'],'method' => 'post','id' => 'general-form']) }}
                {{--<form method="POST" action="{{ route('admin-users.store') }}">--}}
                {{--{{ csrf_field() }}--}}
                <div class="container-fluid relative animatedParent animateOnce">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body b-b">
                                    <div class="tab-content pb-3" id="v-pills-tabContent">
                                        <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                            @include('partials.admin._messages')
                                            @foreach($errors->all() as $error)
                                                <ul>
                                                    <li>
                                            <span class="help-block">
                                                <strong style="color: #ff3d00;"> {{ $error }} </strong>
                                            </span>
                                                    </li>
                                                </ul>
                                        @endforeach
                                        <!-- Input -->
                                            <div class="body">
{{--                                                <div class="col-sm-12">--}}
{{--                                                    <div class="form-check mb-2 mr-sm-2">--}}
{{--                                                        <input type="checkbox" id="is_super_admin" name="is_super_admin" class="form-check-input"/>--}}
{{--                                                        <label class="form-check-label" for="is_super_admin">--}}
{{--                                                            Super Admin?--}}
{{--                                                        </label>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="email">Email *</label>
                                                            <input id="email" type="email" class="form-control"
                                                                   name="email" value="{{ old('email') }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password">Kata Sandi *</label>
                                                            <input id="password" type="password" class="form-control"
                                                                   name="password" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi *</label>
                                                            <input id="password_confirmation" type="password" class="form-control"
                                                                   name="password_confirmation" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="first_name">Nama Depan *</label>
                                                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="last_name">Nama Belakang *</label>
                                                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

{{--                                                <div class="col-md-12">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label for="waste_bank">Assign Waste Bank</label>--}}
{{--                                                        <select id="waste_bank" name="waste_bank" class="form-control">--}}
{{--                                                            <option value="-1" @if(old('waste_bank') == null) selected @endif> - Pilih Waste Bank - </option>--}}
{{--                                                            @foreach($wasteBanks as $wasteBank)--}}
{{--                                                                <option value="{{ $wasteBank->id }}" @if(old('waste_bank') == $wasteBank->id) selected @endif>{{ $wasteBank->name }}</option>--}}
{{--                                                            @endforeach--}}
{{--                                                        </select>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">Role</label>
                                                        <select id="role" name="role" class="form-control">
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}" @if(old('role') == $role->id) selected @endif>{{ $role->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="status">Status *</label>
                                                        <select id="status" name="status" class="form-control">
                                                            <option value="1">Active</option>
                                                            <option value="2">Not Active</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">Project (Jika memilih Role = Admin Project)</label>
                                                        <select id="project_id" name="project_id" class='form-control'><option value='0'>Semua</option></select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">FM (jika memilih Role = FM)</label>
                                                        <select id="fm_id" name="fm_id" class='form-control'></select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">FM (Jika memilih Role = OM)</label>
                                                        <select id="multi_fm_id" name="multi_fm_id[]" class='form-control' multiple></select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                                <a href="{{ route('admin.admin-users.index') }}" class="btn btn-danger">Kembali</a>
                                                <input type="submit" class="btn btn-success" value="Simpan">
                                            </div>
                                            <!-- #END# Input -->
                                        </div>
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
    <style>
        .select2-selection--multiple{
            overflow: hidden !important;
            height: auto !important;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script type="text/javascript">
        $('#role').select2();
        // $('#waste_bank').select2();

        $('#project_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Project - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.projects') }}',
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

        $('#fm_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Project - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.fms') }}',
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

        $('#multi_fm_id').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih FM - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.fms') }}',
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
    </script>
@endsection

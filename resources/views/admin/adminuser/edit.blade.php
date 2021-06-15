@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <h2 class="card-title m-b-0">UBAH DATA ADMIN USER</h2>

                {{ Form::open(['route'=>['admin.admin-users.update'],'method' => 'post','id' => 'general-form']) }}
                {{--<form method="POST" action="{{ route('admin-users.store') }}">--}}
                {{--{{ csrf_field() }}--}}
                <div class="container-fluid relative animatedParent animateOnce">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body b-b">
                                    <div class="tab-content pb-3" id="v-pills-tabContent">
                                        <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
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
{{--                                                        @if($adminUser->is_super_admin == 1)--}}
{{--                                                            <input type="checkbox" id="is_super_admin" name="is_super_admin" value="true" class="form-check-input" checked/>--}}
{{--                                                        @else--}}
{{--                                                            <input type="checkbox" id="is_super_admin" name="is_super_admin" value="true" class="form-check-input"/>--}}
{{--                                                        @endif--}}
{{--                                                        <label class="form-check-label" for="is_super_admin">--}}
{{--                                                            Super Admin--}}
{{--                                                        </label>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
                                                <input type="hidden" name="id" value="{{ $adminUser->id }}">

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password">Kata Sandi Baru</label>
                                                            <input id="password" type="password" class="form-control"
                                                                   name="password">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru</label>
                                                            <input id="password_confirmation" type="password" class="form-control"
                                                                   name="password_confirmation">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="first_name">Nama Depan *</label>
                                                            <input id="first_name" name="first_name" type="text" value="{{ $adminUser->first_name }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="last_name">Nama Belakang *</label>
                                                            <input id="last_name" name="last_name" type="text" value="{{ $adminUser->last_name }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

{{--                                                <div class="col-md-12">--}}
{{--                                                    <div class="form-group">--}}
{{--                                                        <label for="waste_bank">Assign Waste Bank</label>--}}
{{--                                                        <select id="waste_bank" name="waste_bank" class="form-control">--}}
{{--                                                            <option value="-1" @if($adminUser->waste_bank == null) selected @endif> - Pilih Waste Bank - </option>--}}
{{--                                                            @foreach($wasteBanks as $wasteBank)--}}
{{--                                                                <option value="{{ $wasteBank->id }}" @if($adminUser->waste_bank_id === $wasteBank->id) selected @endif>{{ $wasteBank->name }}</option>--}}
{{--                                                            @endforeach--}}
{{--                                                        </select>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">Role</label>
                                                        <select id="role" name="role" class="form-control">
                                                            @foreach($roles as $role)
                                                                <option value="{{ $role->id }}" @if($adminUser->role_id === $role->id) selected @endif>{{ $role->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="status">Status *</label>
                                                        <select id="status" name="status" class="form-control">
                                                            @if($adminUser->status_id == 1)
                                                                <option value="1" selected>Active</option>
                                                                <option value="2">Not Active</option>
                                                            @else
                                                                <option value="1">Active</option>
                                                                <option value="2" selected>Not Active</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">Project (Jika memilih Role = Admin Project)</label>
                                                        <select id="project_id" name="project_id" class='form-control'>
                                                            <option value='{{$projectId}}'>{{$projectName}}</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">FM (jika memilih Role = FM)</label>
                                                        <select id="project_id" name="project_id" class='form-control'>
                                                            @if($adminUser->role_id == 4)
                                                                <option value='{{$fmId}}'>{{$fmName}}</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="role">OM (Jika memilih Role = OM)</label>
                                                        <select id="multi_fm_id" name="multi_fm_id[]" class='form-control' multiple>
                                                            @if($adminUser->role_id == 5)
                                                                @foreach($fmList as $fmData)
                                                                    <option value="{{$fmData->id}}">{{$fmData->code}} - {{$fmData->first_name}} {{$fmData->last_name}}</option>
                                                                @endforeach
                                                            @endif
                                                        </select>
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
        .select2-container--default .select2-search--dropdown::before {
            content: "";
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
        $( document ).ready(function() {
            $('#multi_fm_id').trigger('change');
        });
    </script>
@endsection

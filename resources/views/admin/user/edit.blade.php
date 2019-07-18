@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <h2 class="card-title m-b-0">Edit MD</h2>

                {{ Form::open(['route'=>['admin.users.update'],'method' => 'post','id' => 'general-form']) }}
                {{--<form method="POST" action="{{ route('admin-users.store') }}">--}}
                {{--{{ csrf_field() }}--}}
                <div class="container-fluid relative animatedParent animateOnce">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body b-b">
                                    <div class="tab-content pb-3" id="v-pills-tabContent">
                                        <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                            <!-- Input -->
                                            <div class="body">

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="email">Email *</label>
                                                            <input id="email" type="email" class="form-control"
                                                                   name="email" value="{{ $user->email }}">
                                                            <input type="hidden" value="{{ $user->id }}" name="id"/>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password">Password *</label>
                                                            <input id="password" type="password" class="form-control"
                                                                   name="password">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="password_confirmation">Password Confirmation *</label>
                                                            <input id="password_confirmation" type="password" class="form-control"
                                                                   name="password_confirmation">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="first_name">First Name *</label>
                                                            <input id="first_name" name="first_name" type="text" value="{{ $user->first_name }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="last_name">Last Name *</label>
                                                            <input id="last_name" name="last_name" type="text" value="{{ $user->last_name }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="phone">Phone *</label>
                                                            <input id="phone" name="phone" type="text" value="{{ $user->phone }}"
                                                                   class="form-control" required>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="status">Status *</label>
                                                        <select id="status" name="status" class="form-control">
                                                            @if($user->status_id == 1)
                                                                <option value="1" selected>Active</option>
                                                                <option value="2">Not Active</option>
                                                            @else
                                                                <option value="1">Active</option>
                                                                <option value="2" selected>Not Active</option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                                <a href="{{ route('admin.users.index') }}" class="btn btn-danger">Kembali</a>
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
                {{--</form>--}}
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
@endsection
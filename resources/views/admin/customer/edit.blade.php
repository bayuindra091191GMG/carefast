@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA CUSTOMER {{ $customer->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.customer.update', $customer->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_main">Foto</label>
                                                {!! Form::file('photo', array('id' => 'photo', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="first_name">Nama Customer *</label>
                                                        <input id="name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="name" value="{{ $customer->name }}" required>
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
                                                        <label class="form-label" for="password">Kata Sandi Baru*</label>
                                                        <input id="password" type="password" class="form-control"
                                                               name="password">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi Baru*</label>
                                                        <input id="password_confirmation" type="password" class="form-control"
                                                               name="password_confirmation">
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
                                                        <label class="form-label" for="telephone">Email Customer</label>
                                                        <input id="email" type="text" class="form-control"
                                                               name="email" value="{{ $customer->email ?? '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Nomor Ponsel</label>
                                                        <input id="phone" type="text" class="form-control"
                                                               name="phone" value="{{ $customer->phone ?? '' }}" pattern="\d+">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="status">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="1" @if($customer->status_id === 1) selected @endif>ACTIVE</option>
                                                    <option value="2" @if($customer->status_id === 2) selected @endif>NON-ACTIVE</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.customer.index') }}" class="btn btn-danger">BATAL</a>
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

        @if(!empty($customer->image_path))
        var photoUrl = '{{ asset('storage/employees/'. $customer->image_path) }}';
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

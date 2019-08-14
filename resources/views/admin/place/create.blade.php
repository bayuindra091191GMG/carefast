@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>TAMBAH BARU PLACE</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.place.store'],'method' => 'post','id' => 'general-form']) }}

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
                                                <label class="form-label" for="name">Nama Place *</label>
                                                <input id="name" type="text" class="form-control" style="text-transform: uppercase;"
                                                       name="name" value="{{ old('name') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="description">Keterangan</label>
                                                <textarea id="description" class="form-control"
                                                          name="description" rows="4">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="status">Status</label>
                                                <select class="form-control" id="status" name="status">
                                                    <option value="1">ACTIVE</option>
                                                    <option value="2">NON-ACTIVE</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.place.index') }}" class="btn btn-danger">BATAL</a>
                                    <input type="submit" class="btn btn-success" value="SIMPAN">
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
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script type="text/javascript">
    </script>
@endsection

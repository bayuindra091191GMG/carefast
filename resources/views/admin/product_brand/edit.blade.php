@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH BRAND PRODUK {{ $productBrand->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.product.brand.update', $productBrand->id],'method' => 'post','id' => 'general-form',     'enctype' => 'multipart/form-data']) }}

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

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="name">Nama Brand *</label>
                                                        <input id="name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="name" value="{{ $productBrand->name }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="image_main">Gambar Brand</label>
                                                        {!! Form::file('brand_image', array('id' => 'brand_image', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="description">Keterangan</label>
                                                        <textarea id="description" class="form-control"
                                                                  name="description" rows="4">{{ $productBrand->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="status">Status</label>
                                                        <select class="form-control" id="status" name="status">
                                                            <option value="1" @if($productBrand->status_id === 1) selected @endif>ACTIVE</option>
                                                            <option value="2" @if($productBrand->status_id === 2) selected @endif>NON-ACTIVE</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div> --}}
                                        </div>
                                        <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                            <a href="{{ route('admin.product.brand.index') }}" class="btn btn-danger">BATAL</a>
                                            <input type="submit" class="btn btn-success" value="SIMPAN">
                                        </div>
                                        <!-- #END# Input -->
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
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
<script type="text/javascript">
    $("#brand_image").fileinput({
        showUpload: false,
        allowedFileExtensions: ["jpg", "png", "gif"],
        maxFileCount: 1
    });

    new AutoNumeric('#price', {
        minimumValue: '0',
        maximumValue: '999999',
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        modifyValueOnWheel: false,
        emptyInputBehavior: 'zero'
    });

    new AutoNumeric('#weight', {
        minimumValue: '0',
        maximumValue: '999999',
        digitGroupSeparator: '.',
        decimalCharacter: ',',
        decimalPlaces: 0,
        modifyValueOnWheel: false,
        emptyInputBehavior: 'zero'
    });
</script>
@endsection
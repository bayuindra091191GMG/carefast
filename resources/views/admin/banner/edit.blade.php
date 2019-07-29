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

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="name">Nama banner *</label>
                                                    <input id="name" type="text" class="form-control" style="text-transform: uppercase;"
                                                           name="name" value="{{ $banner->name }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="name">Deskripsi banner *</label>
                                                    <input id="description" type="text" class="form-control"
                                                           name="description" value="{{ $banner->alt_text }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="name">Link URL banner </label>
                                                    <input id="url" type="text" class="form-control"
                                                           name="url" value="{{ $banner->url }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="name">Pilih Brand </label>
                                                    <select id="brand_id" name="brand_id" class="form-control">
                                                        @if(!empty($product))
                                                            <option value="{{ $product->id }}">{{ $product->name }} - {{ $product->sku }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="name">Pilih Produk </label>
                                                    <select id="product_id" name="product_id" class="form-control">
                                                        @if(!empty($brand))
                                                            <option value="{{ $brand->id }}">{{ $brand->name }} }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="description">Keterangan</label>
                                                    <textarea id="description" class="form-control"
                                                              name="description" rows="4">{{ old('description') }}</textarea>
                                                </div>
                                            </div>
                                        </div> --}}

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="brand_image">Gambar Banner</label>
                                                    {!! Form::file('banner_image', array('id' => 'banner_image', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                                </div>
                                            </div>
                                        </div>

                                        {{--<div class="col-md-12">--}}
                                        {{--<div class="form-group form-float form-group-lg">--}}
                                        {{--<div class="form-line">--}}
                                        {{--<label class="form-label" for="status">Status</label>--}}
                                        {{--<select class="form-control" id="status" name="status">--}}
                                        {{--<option value="1">ACTIVE</option>--}}
                                        {{--<option value="2">NON-ACTIVE</option>--}}
                                        {{--</select>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}
                                        {{--</div>--}}
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
@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>TAMBAH BARU PRODUK</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.product.store'],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                <label class="form-label" for="image_main">Gambar Utama *</label>
                                                {!! Form::file('image_main', array('id' => 'image_main', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_secondary">Gambar Lain</label>
                                                {!! Form::file('image_secondary[]', array('id' => 'image_secondary', 'class' => 'file-loading', 'multiple' => 'multiple', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="name">Nama Produk *</label>
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ old('name') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="category">Kategori *</label>
                                                <select class="form-control" id="category" name="category">
                                                    <option value="-1"> - Pilih Kategori Produk - </option>
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="brand">Merek *</label>
                                                <select class="form-control" id="brand" name="brand">
                                                    <option value="-1"> - Pilih Merek - </option>
                                                    @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="sku">SKU *</label>
                                                <input id="sku" type="text" class="form-control" style="text-transform: uppercase;"
                                                       name="sku" value="{{ old('sku') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="price">Harga *</label>
                                                        <input id="price" type="text" class="form-control"
                                                               name="price">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="weight">Berat (gram)</label>
                                                        <input id="weight" type="text" class="form-control"
                                                               name="weight">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <select id="status" name="status" class="form-control">
                                                <option value="1">Active</option>
                                                <option value="2">Not Active</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="description">Keterangan</label>
                                                <textarea id="description" class="form-control"
                                                          name="description" rows="3">{{ old('description') }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" id="is_start_customize" name="is_start_customize">

                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.product.index') }}" class="btn btn-danger">BATAL</a>
                                    <a id="btn_submit" class="btn btn-success text-white">SIMPAN</a>
                                    <a id="btn_submit_customize" class="btn btn-success text-white">SIMPAN DAN BUAT KUSTOMISASI</a>
                                    <a id="btn_loading" class="btn btn-success text-white" style="display: none"><i class="fas fa-sync-alt fa-spin"></i>&nbsp;&nbsp;MENGUNGGAH PRODUK...</a>
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
    <link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet"/>
    <style>
        .select2-container--default .select2-search--dropdown::before {
            content: "";
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
    <script type="text/javascript">
        $("#image_main").fileinput({
            showUpload: false,
            allowedFileExtensions: ["jpg", "png", "gif"],
            maxFileCount: 1
        });

        $("#image_secondary").fileinput({
            showUpload: false,
            allowedFileExtensions: ["jpg", "png", "gif"]
        });

        new AutoNumeric('#price', {
            minimumValue: '0',
            maximumValue: '9999999999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 6,
            modifyValueOnWheel: false,
            emptyInputBehavior: 'zero',
            allowDecimalPadding: false,
        });

        new AutoNumeric('#weight', {
            minimumValue: '0',
            maximumValue: '999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            modifyValueOnWheel: false,
            emptyInputBehavior: 'zero',
            allowDecimalPadding: false,
        });

        $(document).on('click', '#btn_submit', function() {
            $('#btn_submit').hide(500);
            $('#btn_submit_customize').hide(500);
            $('#btn_loading').show(500);
            $('#is_start_customize').val(0);
            $('#general-form').submit();
        });

        $(document).on('click', '#btn_submit_customize', function() {
            $('#btn_submit').hide(500);
            $('#btn_submit_customize').hide(500);
            $('#btn_loading').show(500);
            $('#is_start_customize').val(1);
            $('#general-form').submit();
        });
    </script>
@endsection
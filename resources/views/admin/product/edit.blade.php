@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA PRODUK {{ $product->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.product.update', $product->id],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                                <label class="form-label">Gambar Lain</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 mb-3">
                                        @if($secondaryImages->count() > 0)
                                            <div class="row">
                                                @foreach($secondaryImages as $image)
                                                    <div class="col-md-2 col-6 text-center" id="image_box_{{ $image->id }}">
                                                        <a class="fancybox-viewer" href="{{ asset('storage/products/'. $image->path) }}"><img src="{{ asset('storage/products/'. $image->path) }}" alt=""/></a>
                                                        <br/>
                                                        <br/>
                                                        <a class="btn btn-sm btn-danger delete-image text-white" style="cursor: pointer;" data-image-id="{{ $image->id }}">HAPUS</a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <h3>Tidak Ada Gambar Lain</h3>
                                        @endif
                                    </div>

                                    <input type="hidden" name="deleted_image_ids" id="deleted_image_ids">

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_others">Tambah Gambar Lain</label>
                                                {!! Form::file('image_secondary[]', array('id' => 'image_secondary', 'class' => 'file-loading', 'multiple' => 'multiple', 'accept' => 'image/*')) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="name">Nama Produk *</label>
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ $product->name }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="category">Kategori</label>
                                                <select class="form-control" id="category" name="category">
                                                    @foreach($categories as $category)
                                                        <option value="{{ $category->id }}" @if($product->category_id === $category->id) selected @endif>{{ $category->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="brand">Merek</label>
                                                <select class="form-control" id="brand" name="brand">
                                                    @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}" @if($product->brand_id === $brand->id) selected @endif>{{ $brand->name }}</option>
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
                                                       name="sku" value="{{ $product->sku }}" required>
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
                                                <option value="1" @if($product->status_id === 1) selected @endif>ACTIVE</option>
                                                <option value="2" @if($product->status_id === 2) selected @endif>NOT ACTIVE</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="description">Keterangan</label>
                                                <textarea id="description" class="form-control"
                                                          name="description" rows="3">{{ $product->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                    <a href="{{ route('admin.product.index') }}" class="btn btn-danger">BATAL</a>
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <style>
        .fancybox-viewer img{
            width: 100px;
            height: auto;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.js"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
    <script src="{{ asset('kartik-v-bootstrap-fileinput/themes/fas/theme.js') }}"></script>
    <script type="text/javascript">
        var mainImageUrl = '{{ asset('storage/products/'. $mainImage->path) }}';
        $("#image_main").fileinput({
            theme: 'fas',
            initialPreview : [mainImageUrl],
            initialPreviewAsData: true,
            overwriteInitial: true,
            showUpload: false,
            allowedFileExtensions: ["jpg", "png", "gif"],
            maxFileCount: 1
        });

        $("#image_secondary").fileinput({
            theme: 'fas',
            showUpload: false,
            overwriteInitial: false,
            allowedFileExtensions: ["jpg", "png", "gif"]
        });

        new AutoNumeric('#price','{{ $product->price }}', {
            minimumValue: '0',
            maximumValue: '9999999999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 6,
            modifyValueOnWheel: false,
            emptyInputBehavior: 'zero',
            allowDecimalPadding: false,
        });

        new AutoNumeric('#weight','{{ $product->weight }}', {
            minimumValue: '0',
            maximumValue: '999999',
            digitGroupSeparator: '.',
            decimalCharacter: ',',
            decimalPlaces: 0,
            modifyValueOnWheel: false,
            emptyInputBehavior: 'zero',
            allowDecimalPadding: false,
        });

        $(document).on('click', '.delete-image', function() {
            let deletedImageId = $(this).data('image-id');
            let deletedImageIds = $('#deleted_image_ids').val();
            if(!deletedImageIds  || deletedImageIds === ''){
                deletedImageIds += + deletedImageId;
            }
            else{
                deletedImageIds += ',' + deletedImageId;
            }

            $('#deleted_image_ids').val(deletedImageIds);

            // Remove image box
            $('#image_box_' + deletedImageId).fadeOut(500, function(){ $(this).remove();});
        });
    </script>
@endsection
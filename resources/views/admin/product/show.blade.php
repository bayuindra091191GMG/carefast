@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <a href="{{ route('admin.product.index') }}" class="btn btn-outline-primary float-left">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3 class="float-left ml-3">DETIL PRODUK</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.product.edit', ['id' => $product->id]) }}" class="btn btn-primary">EDIT</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="body">
                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_main">Gambar Utama</label>
                                                <a class="fancybox-viewer" href="{{ asset('storage/products/'. $mainImage->path) }}"><img src="{{ asset('storage/products/'. $mainImage->path) }}" alt=""/></a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="image_others">Gambar Lain</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        @if($secondaryImages->count() > 0)
                                            <div class="row">
                                                @foreach($secondaryImages as $image)
                                                    <div class="col-md-2 col-6">
                                                        <a class="fancybox-viewer" href="{{ asset('storage/products/'. $image->path) }}"><img src="{{ asset('storage/products/'. $image->path) }}" alt=""/></a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <h3>Tidak ada Foto Lain</h3>
                                        @endif
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="name">Nama Produk</label>
                                                <input id="name" type="text" class="form-control"
                                                       name="name" value="{{ $product->name }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="category">Kategori</label>
                                                <input id="category" type="text" class="form-control"
                                                       name="category" value="{{ $product->product_category->name }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="brand">Merek</label>
                                                <input id="brand" type="text" class="form-control"
                                                       name="brand" value="{{ $product->product_brand->name }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="sku">SKU</label>
                                                <input id="sku" type="text" class="form-control"
                                                       name="sku" value="{{ $product->sku }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="status">Status</label>
                                            <input id="status" type="text" class="form-control"
                                                   name="status" value="{{ $product->status->description }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="price">Harga</label>
                                                <input id="price" type="text" class="form-control"
                                                       name="price" value="{{ $product->price_string }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="weight">Berat (gram)</label>
                                                <input id="weight" type="text" class="form-control"
                                                       name="weight" value="{{ $product->weight_string }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="weight">Tanggal Dibuat</label>
                                                <input id="created_at" type="text" class="form-control"
                                                       name="created_at" value="{{ $product->created_at_string }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="description">Keterangan</label>
                                                <textarea id="description" class="form-control"
                                                          name="description" rows="3" readonly>{{ $product->description }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
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
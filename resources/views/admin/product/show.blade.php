@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>DETIL PRODUK</h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="tab-content pb-3" id="v-pills-tabContent">
                                    <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                        @include('partials.admin._messages')

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
                                                        {!! Form::file('image_secondary', array('id' => 'image_secondary', 'class' => 'file-loading', 'accept' => 'image/*')) !!}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                @if($secondaryImages->count() > 0)
                                                    @foreach($secondaryImages as $image)
                                                        <div class="col-md-3">
                                                            <a class="fancybox-viewer" href="{{ asset('storage/products/'. $image->path) }}"><img src="{{ asset('storage/products/'. $image->path) }}" alt=""/></a>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <span>Tidak Ada Foto Lain</span>
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
                                        <!-- #END# Input -->
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
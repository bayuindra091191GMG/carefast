@extends('layouts.admin')

@section('content')

    <header class="blue accent-3 relative nav-sticky">
        <div class="container-fluid text-white">
            <div class="row p-t-b-10 ">
                <div class="col">
                    <h4> <i class="icon-table"></i> New Product</h4>
                </div>
            </div>
        </div>
    </header>


    <div class="content-wrapper animatedParent animateOnce">
        <div class="container">
            <section class="paper-card">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <h3 class="my-3">
                                    Step 1 (Product Information)
                                </h3>

                                <div class="container-fluid animatedParent animateOnce my-3">
                                    <div class="animated fadeInUpShort">
                                        <!-- Input -->
                                        {{ Form::open(['route'=>['admin.product.store'],'method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

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
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label">Upload Main Image *</label>
                                                            {{--<input type="file" name="PhotoPosted" id="PhotoPosted" class="file-loading">--}}
                                                            {!! Form::file('main_image', array('id' => 'main_image', 'class' => 'file-loading', 'accept' => 'image/*,application/pdf')) !!}
                                                        </div>
                                                        <div class="col-md-12 mb-3">
                                                            <label class="form-label">Upload Detail Image *</label>
                                                            {!! Form::file('detail_image[]', array('id' => 'detail_image', 'class' => 'file-loading', 'multiple' => 'multiple')) !!}
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="validationCustom01">Product Name</label>
                                                            <input type="text" name="name" class="form-control" value="{{old('name')}}" required>
                                                        </div>
                                                        <div class="col-md-6 mb-3">
                                                            <label for="sku">SKU</label>
                                                            <input type="text" class="form-control" id="sku" name="sku" value="{{old('sku')}}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6 mb-3">
                                                            <label for="category">Category</label>
                                                            <select id="category" name="category" class="custom-select form-control">
                                                                <option value="-1">Select Product Category</option>
                                                                @foreach($categories as $category)
                                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label for="validationCustom04">Price</label>
                                                            <input type="number" class="form-control" id="price"  name="price" value="{{old('price')}}" required>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label for="sku">Quantity</label>
                                                            <input type="number" class="form-control" id="qty" name="qty" value="{{old('qty')}}" required>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3 mb-3">
                                                            <label>Weight</label>
                                                            <input type="number" class="form-control" id="weight" name="weight" value="{{old('weight')}}" required>
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label>Width</label>
                                                            <input type="number" class="form-control" id="width" name="width" value="{{old('width')}}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label>Height</label>
                                                            <input type="number" class="form-control" id="height" name="height" value="{{old('height')}}">
                                                        </div>
                                                        <div class="col-md-3 mb-3">
                                                            <label>Length</label>
                                                            <input type="number" class="form-control" id="length" name="length" value="{{old('length')}}">
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="productDetails">Product Details</label>
                                                        <textarea class="form-control p-t-40" id="description" name="description"
                                                                  placeholder="Write Something..." rows="7" required>{{old('description')}}</textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="tags">Product Tags</label><br>
                                                        <input type="text" class="tags-input" id="tags" name="tags" placeholder="Add New"
                                                               value="{{old('tags')}}">
                                                    </div>
                                                    {{--<div class="row">--}}
                                                        {{--<div class="col-md-12 mb-3">--}}
                                                            {{--<label>Meta Title</label>--}}
                                                            {{--<input type="text" name="meta_title" class="form-control">--}}
                                                        {{--</div>--}}
                                                        {{--<div class="col-md-12 mb-3">--}}
                                                            {{--<label>Meta Description</label>--}}
                                                            {{--<textarea id="meta_description" rows="2" class="form-control" name="meta_description"></textarea>--}}
                                                        {{--</div>--}}
                                                    {{--</div>--}}
                                                    <div class="row">
                                                        <a href="{{ route('admin.product.index') }}" class="btn btn-danger">Exit</a>
                                                        <button class="btn btn-primary" type="submit">Publish</button>
                                                    </div>
                                                </div>
                                            </div>
                                        {{ Form::close() }}
                                    <!-- #END# Input -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection


@section('styles')
    <link href="{{ URL::asset('css/fileinput.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ URL::asset('js/fileinput.js') }}"></script>

    <script>
        // FILEINPUT
        $("#main_image")
            .fileinput({
                allowedFileExtensions: ["jpg", "jpeg", "png"],
                showUpload: false,
            });
        $("#detail_image")
            .fileinput({
                allowedFileExtensions: ["jpg", "jpeg", "png"],
                showUpload: false,
            });
    </script>
@endsection
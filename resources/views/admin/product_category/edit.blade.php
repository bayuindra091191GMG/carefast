@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH KATEGORI PRODUK {{ $productCategory->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.product.category.update', $productCategory->id],'method' => 'post','id' => 'general-form']) }}

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
                                                        <label class="form-label" for="name">Nama Kategori *</label>
                                                        <input id="name" type="text" class="form-control" style="text-transform: uppercase;"
                                                               name="name" value="{{ $productCategory->name }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="description">Keterangan</label>
                                                        <textarea id="description" class="form-control"
                                                                  name="description" rows="4">{{ $productCategory->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="status">Status</label>
                                                        <select class="form-control" id="status" name="status">
                                                            <option value="1" @if($productCategory->status_id === 1) selected @endif>ACTIVE</option>
                                                            <option value="2" @if($productCategory->status_id === 2) selected @endif>NON-ACTIVE</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                            <a href="{{ route('admin.product.category.index') }}" class="btn btn-danger">BATAL</a>
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
    <script type="text/javascript">
        $('#wastebank').select2({
            placeholder: {
                id: '-1',
                text: 'Pilih Wastebank...'
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.wastebanks') }}',
                dataType: 'json',
                data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                }
            }
        });
    </script>
@endsection
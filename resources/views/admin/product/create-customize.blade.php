@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-9">
                        <h3>KUSTOMISASI HARGA PRODUK</h3>
                    </div>
                    <div class="col-3">
                        <div class="float-right">
                            <button class="btn btn-danger" id="btn_cancel">BATAL</button>
                            <button class="btn btn-success" id="btn_save">SIMPAN</button>
                        </div>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.transactions.antar_sendiri.store'],'method' => 'post','id' => 'general-form']) }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <!-- Input -->
                                <div class="body">

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
                                                <label class="form-label" for="product">Produk</label>
                                                <select id="product" name="product" class="form-control">
                                                    @if(!empty($product))
                                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <div class="text-right w-100">
                                            <a class="btn btn-success" id="btn-add-row" style="color: #fff !important; cursor: pointer;" onclick="addRow();">TAMBAH KUSTOMISASI</a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <table id="category_table" class="table">
                                        <thead>
                                        <tr>
                                            <th scope="col">Kategori MD</th>
                                            <th scope="col">Harga</th>
                                            <th scope="col">Tindakan</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- #END# Input -->
                        </div>
                    </div>
                </div>
            </div>

            {{ Form::close() }}
        </div>
    </div>


@endsection

@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <style>
        .select2-container--default .select2-search--dropdown::before {
            content: "";
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
    <script>
        var categories = [];
        @foreach($userCategories as $category)
            categories.push({ id: '{{ $category->id }}', name: '{{ $category->name }}'})
        @endforeach

        jQuery('#date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });

        var i = 0;

        function addRow(){
            var sbAdd = "<tr id='row_" + i + "'>";
            sbAdd += "<td><select id='category_" + i + "' name='categories[]' class='form-control'>";
            sbAdd += "<option value='-1'> - Pilih Kategori MD - </option>";

            if(categories.length > 0){
                for(var j = 0; j< categories.length; j++){
                    sbAdd += "<option value='" + categories[j].id + "'>" + categories[j].name + "</option>";
                }
            }

            sbAdd += "<select/></td>";
            sbAdd += "<td><input type='text' id='price_" + i + "' name='prices[]' class='form-control text-right' /></td>";
            sbAdd += "<td class='text-center'><a class='btn btn-danger' style='cursor: pointer;' onclick='deleteRow(" + i + ")'><i class='fas fa-minus-circle text-white'></a></td>";

            $('#category_table').append(sbAdd);

            new AutoNumeric('#price_' + i, {
                minimumValue: '0',
                maximumValue: '9999999999',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 6,
                modifyValueOnWheel: false,
                emptyInputBehavior: 'zero'
            });

            i++;
        }

        function deleteRow(rowIdx){
            $('#row_' + rowIdx).remove();
        }

        $('#product').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Produk - '
            },
            width: '100%',
            minimumInputLength: 0,
            ajax: {
                url: '{{ route('select.products') }}',
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

        $('#product').on('select2:select', function (e) {
            let data = e.params.data;
            console.log(data);

            let redirectUrl = '{{ route('admin.product.customize.create') }}';
            window.location.href = redirectUrl + '?id=' + data;
        });

    </script>
@endsection
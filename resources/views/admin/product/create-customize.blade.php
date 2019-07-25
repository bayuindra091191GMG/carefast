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
                            <a href="{{ route('admin.product.customize.index') }}" class="btn btn-danger" id="btn_cancel">BATAL</a>
                            <button class="btn btn-success" id="btn_save">SIMPAN</button>
                        </div>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.product.customize.store'],'method' => 'post','id' => 'general-form']) }}

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
                                                <input type="text" id="product" name="product" class="form-control" value="{{ $product->name }}" readonly=""/>
                                                <input type="hidden" id="product_id" name="product_id" value="{{ $product->id }}" readonly=""/>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12 mb-3">
                                        <div class="text-center w-100">
                                            <h3>KUSTOMISASI HARGA PRODUK</h3>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <table id="customize_table" class="table">
                                        <thead>
                                        <tr>
                                            <th class="text-center">Kategori MD</th>
                                            <th class="text-center">Harga</th>
{{--                                            <th class="text-center">Tindakan</th>--}}
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php( $idx = 0 )
                                        @foreach($userCategories as $userCategory)
                                            <tr>
                                                <td class="text-center">
                                                    <input type="hidden" name="user_categories[]" value="{{ $userCategory->id }}">
                                                    <span>{{ $userCategory->name }}</span>
                                                </td>
                                                <td>
                                                    <input type="text" id="price_{{ $idx }}" name="prices[]" class="form-control text-right">
                                                </td>
{{--                                                <td>--}}
{{--                                                    <a class="btn btn-danger" style="cursor: pointer;" onclick="deleteRow('{{ $idx }}')"><i class="fas fa-minus-circle text-white"></i></a>--}}
{{--                                                </td>--}}
                                            </tr>
                                            @php( $idx++ )
                                        @endforeach
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
        @php( $idxScript = 0 )
        @foreach($userCategories as $category)
            categories.push({ id: '{{ $category->id }}', name: '{{ $category->name }}'});

            new AutoNumeric('#price_' + '{{ $idxScript }}', {
                minimumValue: '0',
                maximumValue: '9999999999',
                digitGroupSeparator: '.',
                decimalCharacter: ',',
                decimalPlaces: 6,
                modifyValueOnWheel: false,
                allowDecimalPadding: false,
                emptyInputBehavior: 'zero'
            });

            @php( $idxScript++ )
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

            $('#customize_table').append(sbAdd);

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

        $(document).on('click', '#btn_save', function() {
            $('#general-form').submit();
        });

    </script>
@endsection
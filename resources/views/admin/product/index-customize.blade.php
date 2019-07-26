@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR KUSTOMISASI HARGA PRODUK PER KATEGORI MD</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <form class="form-horizontal" style="margin-bottom: 10px;">
                            <div class="row">
                                <div class="form-group">
                                    <label for="filter_product">Produk:</label>
                                    <select id="filter_product" name="filter_product" class="form-control">
                                        @if($filterProductId !== -1)
                                            <option value="{{ $filterProductId }}" selected>{{ $filterProduct->name }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="filter_user_category">Kategori MD:</label>
                                    <select id="filter_user_category" name="filter_user_category" class="form-control">
                                        <option value="-1" @if($filterUserCategoryId === -1) selected @endif>Semua</option>
                                        @foreach($userCategories as $category)
                                            <option value="{{ $category->id }}" @if($filterUserCategoryId === $category->id) selected @endif>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group pt-4 ml-3">
                                    <a id="btn_filter" class="btn btn-primary mt-2 text-white" style="cursor: pointer;">FILTER</a>
                                </div>
                                <div class="form-group pt-4 ml-3">
                                    <a id="btn_reset" class="btn btn-primary mt-2 text-white" style="cursor: pointer;">RESET</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive-sm">
                            <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                <thead>
                                <tr>
                                    <th class="text-center">Nama Produk</th>
                                    <th class="text-center">SKU</th>
                                    <th class="text-center">MD</th>
                                    <th class="text-center">Harga Satuan</th>
                                    <th class="text-center">Tanggal Dibuat</th>
                                    <th class="text-center">Tanggal Diubah</th>
                                    <th class="text-center">Tindakan</th>
                                </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{--    @include('partials._delete')--}}
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script>
        $('#filter_product').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Product - '
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

        $('#filter_user_category').select2({
            width: '100%'
        });

        $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: {
                url: '{!! route('datatables.product.customizations') !!}',
                data: {
                    'user_category_id': '{{ $filterUserCategoryId }}',
                    'product_id': '{{ $filterProductId }}'
                }
            },
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'name', name: 'name', orderable: false, searchable: false },
                { data: 'sku', name: 'sku', orderable: false, searchable: false},
                { data: 'md_category', name: 'md_category', orderable: false, searchable: false },
                { data: 'price', name: 'price', class: 'text-right',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return data.toLocaleString(
                                "de-DE",
                                {minimumFractionDigits: 2}
                            );
                        }
                        return data;
                    }
                },
                { data: 'created_at', name: 'created_at', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'updated_at', name: 'updated_at', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

        $(document).on("click", "#btn_filter", function(){
            let userCategoryId = $('#filter_user_category').val();
            let productId = $('#filter_product').val();

            let url = '{{ route('admin.product.customize.index') }}';
            //alert(userCategoryId);
            if(!productId || productId == ''){
                window.location = url + '?user_category_id=' + userCategoryId;
            }
            else{
                window.location = url + '?product_id=' + productId + '&user_category_id=' + userCategoryId;
            }


        });

        $(document).on("click", "#btn_reset", function(){
            window.location = '{{ route('admin.product.customize.index') }}';
        });

        // $(document).on('click', '.delete-modal', function(){
        //     $('#deleteModal').modal({
        //         backdrop: 'static',
        //         keyboard: false
        //     });
        //
        //     $('#deleted-id').val($(this).data('id'));
        // });
    </script>
    {{--    @include('partials._deletejs', ['routeUrl' => 'admin.product.destroy', 'redirectUrl' => 'admin.product.index'])--}}
@endsection
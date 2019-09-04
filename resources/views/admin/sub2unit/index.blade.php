@extends('layouts.admin')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <h3>DAFTAR Sub Unit 2</h3>
                    @include('partials.admin._messages')
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-12 text-right">
                    <a href="{{ route('admin.sub2unit.create') }}" class="btn btn-success">
                        <i class="fas fa-plus text-white"></i>
                        <br />
                        <span>TAMBAH</span>
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive-sm">
                        <table id="general_table" class="table table-striped table-bordered nowrap"
                            style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center">Object</th>
                                    <th class="text-center">Nama Sub Object 1</th>
                                    <th class="text-center">Nama Sub Object 2</th>
                                    <th class="text-center">Keterangan</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Tanggal Dibuat</th>
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
@include('partials._delete')
@endsection

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
<link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endsection

@section('scripts')
<script src="{{ asset('js/datatables.js') }}"></script>
<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
<script>
    $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: '{!! route('datatables.sub2_units') !!}',
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'unit', name: 'unit', class: 'text-center'},
                { data: 'sub_unit_1', name: 'sub_unit_1', class: 'text-center'},
                { data: 'name', name: 'name', class: 'text-center'},
                { data: 'description', name: 'description'},
                { data: 'status', name: 'status', class: 'text-center'},
                { data: 'created_at', name: 'created_at', class: 'text-center',
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

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
</script>
@include('partials._deletejs', ['routeUrl' => 'admin.product.category.destroy', 'redirectUrl' =>
'admin.product.category.index'])
@endsection

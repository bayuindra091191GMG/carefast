@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR PROJECT</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <a href="{{ route('admin.project.information.create') }}" class="btn btn-success">
                            <i class="fas fa-plus text-white"></i>
                            <br/>
                            <span>TAMBAH</span>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive-sm">
                            <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                <thead>
                                <tr>
                                    <th class="text-center">Nama</th>
                                    <th class="text-center">Nama Customer</th>
                                    <th class="text-center">Nomor Telpon/Ponsel</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Alamat</th>
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
            ajax: '{!! route('datatables.projects') !!}',
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'name', name: 'name', class: 'text-center' },
                { data: 'customer_name', name: 'customer_name', class: 'text-center' },
                { data: 'phones', name: 'phones', class: 'text-center'},
                { data: 'address', name: 'address', class: 'text-center'},
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
    @include('partials._deletejs', ['routeUrl' => 'admin.customer.destroy', 'redirectUrl' => 'admin.customer.index'])
@endsection

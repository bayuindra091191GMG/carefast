@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR ORDER REQUEST</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12">
                        <div class="table-responsive-sm">
                            <table id="order-requests" class="table table-striped table-bordered dt-responsive nowrap" width="100%" cellspacing="0">
                                <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Order Number</th>
                                    <th>Customer</th>
                                    <th>Customer Email</th>
                                    <th>Sub total</th>
                                    <th>Grand Total</th>
                                    <th>Status</th>
                                    <th></th>
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
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <style>
        .text-uppercase{
            text-transform: uppercase;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $('#order-requests').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: '{!! route('datatables.orders') !!}',
            order: [ [1, 'desc'] ],
            columns: [
                { data: 'created_at', name: 'created_at', class: 'text-center', orderable: false, searchable: false,
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'order_number', name: 'order_number' },
                { data: 'customer', name: 'customer' },
                { data: 'email', name: 'email', class: 'text-center'},
                { data: 'sub_total', name: 'sub_total', class: 'text-right'},
                { data: 'grand_total', name: 'grand_total', class: 'text-right'},
                { data: 'status', name: 'status', class: 'text-center'},
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
    @include('partials._deletejs', ['routeUrl' => 'admin.admin-users.destroy', 'redirectUrl' => 'admin.admin-users.index'])
@endsection
@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR MASTER DEALER</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table id="user" class="table table-striped table-bordered dt-responsive nowrap" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th>Email</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Created At</th>
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
@endsection

@section('styles')
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script>
        $('#user').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            ajax: '{!! route('datatables.users') !!}',
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'email', name: 'email'},
                { data: 'name', name: 'name', orderable: false, searchable: false},
                { data: 'phone', name: 'phone', class: 'text-center'},
                { data: 'status', name: 'status', class: 'text-center', orderable: false, searchable: false},
                { data: 'created_at', name: 'created_at', class: 'text-center', orderable: false, searchable: false,
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
            language: {
                url: "{{ asset('indonesian.json') }}"
            }
        });

        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
    </script>
    @include('partials._deletejs', ['routeUrl' => 'admin.users.destroy', 'redirectUrl' => 'admin.users.index'])
@endsection
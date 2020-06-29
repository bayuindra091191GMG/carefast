@extends('layouts.admin')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <h3>DETAIL ABSEN KARYAWAN</h3>
                    @include('partials.admin._messages')
                </div>
            </div>
            {{-- <div class="row mb-3">
                <div class="col-12 text-right">
                    <a href="{{ route('admin.employee.create') }}" class="btn btn-success">
            <i class="fas fa-plus text-white"></i>
            <br />
            <span>TAMBAH</span>
            </a>
        </div>
    </div> --}}
    <div class="row">
        <div class="col-12">
            <div class="table-responsive-sm">
                <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                    <thead>
                        <tr>
                            <th class="text-center">Employee</th>
                            <th class="text-center">Schedule id</th>
                            <th class="text-center">Place</th>
                            <th class="text-center">Date</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Image path</th>
                            {{-- <th class="text-center">Tanggal Dibuat</th> --}}
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
            ajax: {
                url: '{!! route('datatables.attendances') !!}',
                data: {
                    'id': '{{ $employee->id }}'
                }
            },
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'employee', name: 'employee', class: 'text-center' },
                { data: 'schedule_id', name: 'schedule_id', class: 'text-center' },
                { data: 'place_name', name: 'place_name'},
                { data: 'date', name: 'date', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'status', name: 'status', class: 'text-center'},
                { data: 'image_path', name: 'image_path', class: 'text-center'},
                // { data: 'created_at', name: 'created_at', class: 'text-center',
                //     render: function ( data, type, row ){
                //         if ( type === 'display' || type === 'filter' ){
                //             return moment(data).format('DD MMM YYYY');
                //         }
                //         return data;
                //     }
                // },
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });

        // $(document).on('click', '.delete-modal', function(){
        //     $('#deleteModal').modal({
        //         backdrop: 'static',
        //         keyboard: false
        //     });

        //     $('#deleted-id').val($(this).data('id'));
        // });
</script>
{{-- @include('partials._deletejs', ['routeUrl' => 'admin.employee.destroy', 'redirectUrl' => 'admin.employee.index']) --}}
@endsection

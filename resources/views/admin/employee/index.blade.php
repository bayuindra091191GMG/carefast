@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR KARYAWAN</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
{{--                <div class="row mb-3">--}}
{{--                    <div class="col-12 text-right">--}}
{{--                        <a href="{{ route('admin.employee.create') }}" class="btn btn-success">--}}
{{--                            <i class="fas fa-plus text-white"></i>--}}
{{--                            <br/>--}}
{{--                            <span>TAMBAH</span>--}}
{{--                        </a>--}}
{{--                    </div>--}}
{{--                </div>--}}

                <div class="row mb-3">

                    {{ Form::open(['route'=>'admin.employee.download-nucphone','method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}
                    <div class="col-12 text-right">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-download text-white"></i>
                            <br/>
                            <span>DOWNLOAD NUC-PHONE</span>
                        </button>
                    </div>

                    {{ Form::close() }}
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive-sm">
                            <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                                <thead>
                                <tr>
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Posisi</th>
                                    <th class="text-center">Nama Depan</th>
                                    <th class="text-center">Nama Belakang</th>
                                    <th class="text-center">NIK</th>
                                    <th class="text-center">Nomor Telpon/Ponsel</th>
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
            ajax: '{!! route('datatables.employees') !!}',
            order: [ [0, 'asc'] ],
            columns: [
                { data: 'code', name: 'code'},
                { data: 'role', name: 'employee_role.name'},
                { data: 'first_name', name: 'first_name', class: 'text-center' },
                { data: 'last_name', name: 'last_name', class: 'text-center' },
                { data: 'nik', name: 'nik'},
                { data: 'phones', name: 'phone', class: 'text-center'},
                { data: 'status', name: 'status.description', class: 'text-center'},
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
    @include('partials._deletejs', ['routeUrl' => 'admin.employee.destroy', 'redirectUrl' => 'admin.employee.index'])
@endsection

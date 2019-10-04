@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DAFTAR KELUHAN</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-9">
                        <form class="form-horizontal" style="margin-bottom: 10px;">
                            <div class="row">
                                <div class="form-group pt-2 ml-3">
                                    <label for="date_start">Tanggal Mulai:</label>
                                    <input id="date_start" type="text" class="form-control" value="{{ $filterDateStart }}">
                                </div>
                                <div class="form-group pt-2 ml-3">
                                    <label for="date_end">Tanggal Berakhir:</label>
                                    <input id="date_end" type="text" class="form-control" value="{{ $filterDateEnd }}">
                                </div>
                                <div class="form-group ml-3" style="padding-top: 1.8rem !important;">
                                    <a id="btn_filter" class="btn btn-primary mt-2 text-white" style="cursor: pointer;">FILTER</a>
                                </div>
                                <div class="form-group ml-3" style="padding-top: 1.8rem !important;">
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
                                    <th class="text-center">Kode</th>
                                    <th class="text-center">Tanggal</th>
                                    <th class="text-center">Project</th>
                                    <th class="text-center">Jenis</th>
                                    <th class="text-center">Keluhan Oleh</th>
                                    <th class="text-center">Diproses Oleh</th>
                                    <th class="text-center">Status</th>
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
@endsection

@section('styles')
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        // Date Picker
        jQuery('#date_start').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });

        jQuery('#date_end').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });

        $(document).on("click", "#btn_filter", function(){
            let dateStart = $('#date_start').val();
            let dateEnd = $('#date_end').val();

            let url = '{{ route('admin.complaint.index') }}';
            window.location = url + '?date_start=' + dateStart + "&date_end=" + dateEnd;
        });

        $(document).on("click", "#btn_reset", function(){
            let url = '{{ route('admin.complaint.index') }}';
            window.location = url;
        });

        $('#general_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            responsive: true,
            ajax: {
                url: '{!! route('datatables.complaints') !!}',
                data: {
                    'date_start': '{{ $filterDateStart }}',
                    'date_end': '{{ $filterDateEnd }}'
                }
            },
            order: [ [1, 'desc'] ],
            columns: [
                { data: 'code', name: 'code'},
                { data: 'date', name: 'date', class: 'text-center',
                    render: function ( data, type, row ){
                        if ( type === 'display' || type === 'filter' ){
                            return moment(data).format('DD MMM YYYY');
                        }
                        return data;
                    }
                },
                { data: 'project', name: 'project', class: 'text-center' },
                { data: 'type', name: 'type', class: 'text-center' },
                { data: 'complainer', name: 'complainer'},
                { data: 'handled_by', name: 'handled_by', class: 'text-center'},
                { data: 'status', name: 'status', class: 'text-center'},
                { data: 'action', name: 'action', orderable: false, searchable: false, class: 'text-center'}
            ],
        });
    </script>
@endsection

@extends('layouts.admin')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    <h3>DETAIL ABSEN KARYAWAN {{$employee->first_name}} {{$employee->last_name}}</h3>
                    @include('partials.admin._messages')
                </div>
            </div>
    <div class="row">
        <div class="col-12">
            <div class="table-responsive-sm">
                <table id="general_table" class="table table-striped table-bordered nowrap" style="width: 100%;">
                    <thead>
                        <tr>
{{--                            <th class="text-center">Employee</th>--}}
                            <th class="text-center">Project</th>
{{--                            <th class="text-center">Place</th>--}}
                            <th class="text-center">Check In</th>
                            <th class="text-center">Check Out</th>
                            {{-- <th class="text-center">Tanggal Dibuat</th> --}}
{{--                            <th class="text-center">Tindakan</th>--}}
                        </tr>
                    </thead>
                    <tbody>
                    @if(!empty($attendanceModels))
                        @foreach($attendanceModels as $attendanceModel)
                        <tr>
                            <td>{{$attendanceModel["project_name"]}}</td>
                            <td>{{$attendanceModel["attendance_in_date"]}}</td>
                            <td>{{$attendanceModel["attendance_out_date"]}}</td>
                        </tr>
                        @endforeach
                    @endif
                    </tbody>
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
{{-- @include('partials._deletejs', ['routeUrl' => 'admin.employee.destroy', 'redirectUrl' => 'admin.employee.index']) --}}
<script type="text/javascript">
    $('#general_table').DataTable();
</script>
@endsection

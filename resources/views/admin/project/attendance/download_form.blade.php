@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <h3>DOWNLOAD PROJECT ABSENSI</h3>
                        @include('partials.admin._messages')
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12">

                        {{ Form::open(['route'=>'admin.project.attendance.download-all','method' => 'post','id' => 'general-form', 'enctype' => 'multipart/form-data']) }}

                        <div class="row">
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="total_manday">Tanggal Dimulai *</label>
                                                <input id="start_date" name="start_date" type="text" class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="total_mp_onduty">Tanggal Selesai *</label>
                                                <input id="finish_date" name="finish_date" type="text" class="form-control" autocomplete="off" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group form-float form-group-lg">
                                    <div class="form-line">
                                        <br>
                                        <a id="btn_submit" class="btn btn-facebook" style="color: white;">Download Data Absensi</a>
                                        <a id="btn_loading" class="btn btn-success text-white" style="display: none"><i class="fas fa-sync-alt fa-spin"></i>&nbsp;&nbsp;MEMPROSES DATA ABSENSI...</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if($filename != "")
                            <div class="row">
                                File sudah dapat di download. Klik link di bawah ini
                                <a href="{{route('admin.project.attendance.download-file', ['filename' => $filename])}}">{{$filename}}</a>
                            </div>
                        @endif

                        {{ Form::close() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/datatables.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('js/datatables.js') }}"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>

    <script type="text/javascript">
        $(document).on('click', '#btn_submit', function() {
            $('#btn_submit').hide(500);
            $('#btn_loading').show(500);
            $('#general-form').submit();
        });

        jQuery('#start_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
        jQuery('#finish_date').datepicker({
            autoclose: true,
            todayHighlight: true,
            format: "dd M yyyy"
        });
    </script>
@endsection

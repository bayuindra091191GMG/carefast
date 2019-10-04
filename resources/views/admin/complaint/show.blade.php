@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 col-12">
                        <a href="{{ route('admin.complaint.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>DETIL DATA KELUHAN {{ $complaint->code }}</h3>
                    </div>
{{--                    <div class="col-md-4 col-12 text-right">--}}
{{--                        <a href="{{ route('admin.employee.edit', ['id' => $employee->id]) }}" class="btn btn-primary">EDIT</a>--}}
{{--                        <button class="btn btn-danger delete-modal" data-toggle="modal" data-target="#deleteModal" data-id="{{$employee->id}}">HAPUS</button>--}}
{{--                    </div>--}}
                </div>

                <form>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body b-b">
                                    <div class="body">
                                        @include('partials.admin._messages')

                                        <div class="col-md-12">
                                            <div class="form-group form-float form-group-lg">
                                                <div class="form-line">
                                                    <label class="form-label" for="subject">SUBJECT</label>
                                                    <input id="subject" type="text" class="form-control"
                                                           name="subject" value="{{ $complaint->subject }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="project">Project</label>
                                                            <input id="project" type="text" class="form-control"
                                                                   name="project" value="{{ $complaint->project->name }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-12">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="date">Nama Belakang</label>
                                                            <input id="date" type="text" class="form-control"
                                                                   name="date" value="{{ $complaint->date_string }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h3 class="card-title">HISTORI RESPON KELUHAN</h3>
                                @if($complaint->complaint_details->count() > 0)
                                    <div class="chat-box scrollable" style="height:475px;">
                                        <!--chat Row -->
                                        <ul class="chat-list">
                                        @foreach($complaint->complaint_details as $detail)
                                            <!--chat Row -->
                                                <li class="chat-item">
                                                    {{--<div class="chat-img"><img src="../../assets/images/users/1.jpg" alt="user"></div>--}}
                                                    <div class="chat-content">
                                                        @if(!empty($detail->customer_id) && empty($detail->employee_id))
                                                            <h6 class="font-medium">{{ $detail->customer_name }} - Customer</h6>
                                                        @else
                                                            <h6 class="font-medium">{{ $detail->employee->first_name. ' '. $detail->employee->last_name }} - {{ $detail->employee->employee_role->name }}</h6>
                                                        @endif

                                                        <div class="box bg-light-info">{{ $detail->message }}</div>
                                                    </div>
                                                    <div class="chat-time">{{ $detail->created_at_string }}</div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @else
                                    <h4>TIDAK ADA HISTORI!</h4>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('partials._delete')
@endsection


@section('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.css" type="text/css" media="screen" />
    <style>
        .fancybox-viewer img{
            width: 150px;
            height: auto;
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.js"></script>
    <script type="text/javascript">
        $("a.fancybox-viewer").fancybox();

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

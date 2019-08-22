@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <a href="{{ route('admin.customer.index') }}" class="btn btn-outline-primary float-left mr-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h3>DETIL DATA CUSTOMER {{ $customer->name }}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.customer.edit', ['id' => $customer->id]) }}" class="btn btn-primary">EDIT</a>
                        <button class="btn btn-danger" data-toggle="modal" data-target="#deleteModal">HAPUS</button>
                    </div>
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
                                                <label class="form-label" for="photo">Foto *</label>
                                                @if(!empty($customer->image_path))
                                                    <a class="fancybox-viewer" href="{{ asset('storage/customers/'. $customer->image_path) }}"><img src="{{ asset('storage/employees/'. $customer->image_path) }}" alt=""/></a>
                                                @else
                                                    <h4>Tidak ada foto</h4>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="role">Role/Posisi</label>
                                                <input id="role" type="text" class="form-control"
                                                       name="role" value="{{ $customer->user_category->name }}" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="name">Nama Customer</label>
                                                        <input id="name" type="text" class="form-control"
                                                               name="name" value="{{ $customer->name }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="telephone">Email Customer</label>
                                                        <input id="email" type="text" class="form-control"
                                                               name="email" value="{{ $customer->email }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Nomor Ponsel</label>
                                                        <input id="phone" type="text" class="form-control"
                                                               name="phone" value="{{ $customer->phone }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group form-float form-group-lg">
                                            <div class="form-line">
                                                <label class="form-label" for="status">Status</label>
                                                <input id="status" type="text" class="form-control"
                                                       name="status" value="{{ strtoupper($customer->status->description) }}" readonly="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                </form>
            </div>
        </div>
    </div>
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
    </script>
@endsection

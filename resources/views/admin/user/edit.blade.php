@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA MASTER DEALER {{ $user->first_name }} {{ $user->last_name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.users.update'],'method' => 'post','id' => 'general-form']) }}
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="tab-content pb-3" id="v-pills-tabContent">
                                    <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                        <!-- Input -->
                                        <div class="body">

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="email">Email *</label>
                                                        <input id="email" type="email" class="form-control"
                                                               name="email" value="{{ $user->email }}">
                                                        <input type="hidden" value="{{ $user->id }}" name="id"/>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="password">Kata Sandi *</label>
                                                        <input id="password" type="password" class="form-control"
                                                               name="password">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="password_confirmation">Konfirmasi Kata Sandi *</label>
                                                        <input id="password_confirmation" type="password" class="form-control"
                                                               name="password_confirmation">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="name">Nama *</label>
                                                        <input id="name" name="name" type="text" value="{{ $user->name }}"
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="phone">Telepon/Fax *</label>
                                                        <input id="phone" name="phone" type="text" value="{{ $user->phone }}"
                                                               class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="address">Alamat *</label>
                                                        <textarea id="address" name="address" rows="3"
                                                                  class="form-control" required>{{ $address->description }}</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="postal_code">Kodepos</label>
                                                        <input id="postal_code" name="postal_code" type="text"
                                                               class="form-control" value="{{ $address->postal_code }}">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="role">Kategori MD *</label>
                                                    <select id="role" name="category" class="form-control">
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}" @if($user->category_id == $category->id) selected @endif>{{ $category->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="status">Status *</label>
                                                    <select id="status" name="status" class="form-control">
                                                        @if($user->status_id == 1)
                                                            <option value="1" selected>Aktif</option>
                                                            <option value="2">Tidak Aktif</option>
                                                        @else
                                                            <option value="1">Aktif</option>
                                                            <option value="2" selected>Tidak Aktif</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                            <a href="{{ route('admin.users.index') }}" class="btn btn-danger">Kembali</a>
                                            <input type="submit" class="btn btn-success" value="Simpan">
                                        </div>
                                        <!-- #END# Input -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>


@endsection

@section('styles')
    <link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet"/>
    <style>
        .select2-container--default .select2-search--dropdown::before {
            content: "";
        }
    </style>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
@endsection

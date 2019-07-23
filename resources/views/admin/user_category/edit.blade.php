@extends('layouts.admin')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>UBAH DATA KATEGORI MASTER DEALER {{ $category->name }}</h3>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.user_categories.update', $category->id],'method' => 'post', 'id' => 'general-form']) }}

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="tab-content pb-3" id="v-pills-tabContent">
                                    <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                        @include('partials.admin._messages')
                                        @foreach($errors->all() as $error)
                                            <ul>
                                                <li>
                                                    <span class="help-block">
                                                        <strong style="color: #ff3d00;"> {{ $error }} </strong>
                                                    </span>
                                                </li>
                                            </ul>
                                        @endforeach

                                        <input type="hidden" value="{{ $category->id }}" name="id"/>
                                        <div class="body">
                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="name">Nama *</label>
                                                        <input id="name" type="text" class="form-control"
                                                               name="name" value="{{ $category->name }}" required>
                                                    </div>
                                                </div>
                                            </div>

{{--                                            <div class="col-md-12">--}}
{{--                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                    <div class="form-line">--}}
{{--                                                        <label class="form-label" for="slug">Slug *</label>--}}
{{--                                                        <input id="slug" type="text" class="form-control"--}}
{{--                                                               name="slug" value="{{ $category->slug  }}" required>--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

{{--                                            <div class="col-md-12">--}}
{{--                                                <div class="form-group form-float form-group-lg">--}}
{{--                                                    <div class="form-line">--}}
{{--                                                        <label class="form-label" for="meta_title">Meta Title</label>--}}
{{--                                                        <input id="meta_title" type="text" class="form-control"--}}
{{--                                                               name="meta_title" value="{{ $category->meta_title }}">--}}
{{--                                                    </div>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

                                            <div class="col-md-12">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="meta_description">Keterangan</label>
                                                        <input id="meta_description" type="text" class="form-control"
                                                               name="meta_description" value="{{ $category->meta_description }}">
                                                    </div>
                                                </div>
                                            </div>

{{--                                            <div class="col-md-12">--}}
{{--                                                <div class="form-group">--}}
{{--                                                    <label for="parent">Parent</label>--}}
{{--                                                    <select id="parent" name="parent" class="form-control">--}}
{{--                                                        @if($parent != null)--}}
{{--                                                            <option value="{{ $parent->id }}"> {{ $parent->name }}</option>--}}
{{--                                                        @else--}}
{{--                                                            <option value="0">-</option>--}}
{{--                                                        @endif--}}
{{--                                                    </select>--}}
{{--                                                </div>--}}
{{--                                            </div>--}}

                                        </div>
                                        <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                            <a href="{{ route('admin.user_categories.index') }}" class="btn btn-danger">Kembali</a>
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
@endsection

@section('scripts')
@endsection
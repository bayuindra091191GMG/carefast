@extends('layouts.admin')

@section('content')

<div class="row">
    <div class="col-12">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <h3>UBAH Sub Unit 1</h3>
                </div>
            </div>

            {{ Form::open(['route'=>['admin.sub1unit.update', $sub1unit->id],'method' => 'post','id' => 'general-form']) }}

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body b-b">
                            <div class="body">
                                @include('partials.admin._messages')
                                @foreach($errors->all() as $error)
                                <div class="col-md-12">
                                    <div class="form-group form-float form-group-lg">
                                        <div class="form-line">
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <ul>
                                                    @foreach($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                                <!-- Input -->
                                <div class="col-md-12">
                                    <div class="form-group form-float form-group-lg">
                                        <div class="form-line">
                                            <label class="form-label" for="name">Nama Sub Unit 1 *</label>
                                            <input id="name" type="text" class="form-control"
                                                style="text-transform: uppercase;" name="name"
                                                value="{{ $sub1unit->name }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group form-float form-group-lg">
                                        <div class="form-line">
                                            <label class="form-label" for="description">Keterangan</label>
                                            <textarea id="description" class="form-control" name="description"
                                                rows="4">{{ $sub1unit->description }}</textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="unit_id">Units *</label>
                                        <select id="unit_id" name="unit_id" class="form-control">
                                            <option value="{{ $unit->id }}">
                                                {{ $unit->name . ' - ' . $unit->description }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group form-float form-group-lg">
                                        <div class="form-line">
                                            <label class="form-label" for="status">Status</label>
                                            <select class="form-control" id="status" name="status">
                                                <option value="1" @if($sub1unit->status_id === 1) selected @endif>ACTIVE
                                                </option>
                                                <option value="2" @if($sub1unit->status_id === 2) selected
                                                    @endif>NON-ACTIVE</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                <a href="{{ route('admin.sub1unit.index') }}" class="btn btn-danger">BATAL</a>
                                <input type="submit" class="btn btn-success" value="SIMPAN">
                            </div>
                            <!-- #END# Input -->
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
<link href="{{ asset('kartik-v-bootstrap-fileinput/css/fileinput.min.css') }}" rel="stylesheet" />
<link href="{{ asset('css/select2-bootstrap4.min.css') }}" rel="stylesheet" />
@endsection

@section('scripts')
<script src="{{ asset('kartik-v-bootstrap-fileinput/js/fileinput.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
    type="text/javascript"></script>

<script type="text/javascript">
    $('#city').select2();

    $('#unit_id').select2({
        placeholder: {
            id: '-1',
            text: ' - Pilih Object - '
        },
        width: '100%',
        minimumInputLength: 0,
        ajax: {
            url: '{{ route('select.units') }}',
            dataType: 'json',
            data: function (params) {
                return {
                    q: $.trim(params.term)
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            }
        }
    });
</script>
@endsection

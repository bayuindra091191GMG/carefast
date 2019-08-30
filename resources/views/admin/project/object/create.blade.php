@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.object.store'],'method' => 'post','id' => 'general-form']) }}
                    <div class="row">
                        <div class="col-md-8 col-12">
                            <h3>TAMBAH BARU OBJECT PROJECT</h3>
                        </div>
                        <div class="col-md-4 col-12 text-right">
                            <a href="{{ route('admin.project.object.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
                            <input type="submit" class="btn btn-success" value="SIMPAN">
                        </div>
                    </div>

                    <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="body">
                                    @include('partials.admin._messages')
                                    @if(count($errors))
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
                                    @endif

                                    <div class="col-md-12 p-t-20">
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-hover" id="tab_logic">
                                                <thead>
                                                <tr >
                                                    <th class="text-center" style="width: 30%">
                                                        Pilih Place*
                                                    </th>
                                                    <th class="text-center" style="width: 30%">
                                                        Pilih Object (Jika ada)
                                                    </th>
                                                    <th class="text-center" style="width: 30%">
                                                        Pilih Sub Object 1 (Jika ada)
                                                    </th>
                                                    <th class="text-center" style="width: 30%">
                                                        Pilih Sub Object 2 (Jika ada)
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr id='sch0'>
                                                    <td class='field-item'>
                                                        <select id="place0" name="places[]" class='form-control'></select>
                                                    </td>
                                                    <td>
                                                        <select id="unit0" name="units[]" class='form-control'></select>
                                                    </td>
                                                    <td>
                                                        <select id="sub_1_unit0" name="sub_1_units[]" class='form-control' style="display: none"></select>
                                                    </td>
                                                    <td>
                                                        <select id="sub_2_unit0" name="sub_2_units[]" class='form-control' style="display: none"></select>
                                                    </td>
                                                </tr>
                                                <tr id='sch1'></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <a id="add_row" class="btn btn-success" style="color: #fff;">Tambah</a>
                                        &nbsp;
                                        <a id='delete_row' class="btn btn-danger" style="color: #fff;">Hapus</a>
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
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCqhoPugts6VVh4RvBuAvkRqBz7yhdpKnQ&libraries=places"
            type="text/javascript"></script>

    <script type="text/javascript">
        // select2 for initial Data

        $('#place0').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Place - '
            },
            width: '100%',
            minimumInputLength: 1,
            ajax: {
                url: '{{ route('select.places') }}',
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

        $('#unit0').select2({
            placeholder: {
                id: '-1',
                text: ' - Pilih Object - '
            },
            width: '100%',
            minimumInputLength: 1,
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

        $('#unit0').on('select2:select', function(){
            var objVal = $('#unit0').val();
            // alert(objVal);

            $.ajax({
                url: '{{ route('select.units') }}',
                dataType: 'json',
                data: {
                    'id': objVal
                },
                success: function (data) {
                    $('#sub_1_unit0').empty();
                    for(let j=0; j<data.length; j++){
                        $('#sub_1_unit0')
                            .append($("<option></option>")
                                .attr("value",data[j])
                                .text(data[j]));
                    }
                    $('#sub_1_unit0').show();
                }
            });
        });

        $('#sub_1_object' + i).on('select2:select', function(){
            {{--var objId = 'sub_1_object' + i;--}}
            {{--$.ajax({--}}
            {{--    url: '{{ route('select.units') }}',--}}
            {{--    dataType: 'json',--}}
            {{--    data: {--}}
            {{--        'id': $('select[id=objId]').val()--}}
            {{--    },--}}
            {{--    success: function (data) {--}}
            {{--        var i;--}}
            {{--        $('#sub_2_object' + i).empty();--}}
            {{--        for(i=0; i<data.length; i++){--}}
            {{--            $('#sub_2_object' + i)--}}
            {{--                .append($("<option></option>")--}}
            {{--                    .attr("value",data[i])--}}
            {{--                    .text(data[i]));--}}
            {{--        }--}}
            {{--        $('#sub_2_object' + i).show();--}}
            {{--    }--}}
            {{--});--}}
        });

        var i=1;
        $("#add_row").click(function(){
            var bufferID = i;
            $('#sch'+i).html(
                "<td><select id='place" + i +"' name='places[]' class='form-control'></select></td>" +
                "<td><select id='unit" + i +"' name='units[]' class='form-control'></select></td>" +
                "<td><select id='sub_1_unit" + i +"' name='sub_1_units[]' class='form-control' style='display: none'></select></td>" +
                "<td><select id='sub_2_unit" + i +"' name='sub_2_units[]' class='form-control' style='display: none'></select></td>"
            );
            $('#tab_logic').append('<tr id="sch'+(i+1)+'"></tr>');

            $('#place' + i).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Place - '
                },
                width: '100%',
                minimumInputLength: 1,
                ajax: {
                    url: '{{ route('select.places') }}',
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

            $('#unit' + i).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Object - '
                },
                width: '100%',
                minimumInputLength: 1,
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

            $('#unit' + i).on('select2:select', function(){
                var objVal = '#unit' + bufferID;

                $.ajax({
                    url: '{{ route('select.units') }}',
                    dataType: 'json',
                    data: {
                        'id': $(objVal).val()
                    },
                    success: function (data) {
                        $('#sub_1_unit' + bufferID).empty();
                        for(let j=0; j<data.length; j++){
                            $('#sub_1_unit' + bufferID)
                                .append($("<option></option>")
                                    .attr("value",data[j])
                                    .text(data[j]));
                        }
                        $('#sub_1_unit' + bufferID).show();
                    }
                });
            });

            $('#sub_2_unit' + bufferID).on('change', function() {
                var objVal = '#sub_1_unit' + bufferID;
                $.ajax({
                    url: '{{ route('select.units') }}',
                    dataType: 'json',
                    data: {
                        'id': $(objVal).val()
                    },
                    success: function (data) {
                        $('#sub_2_unit' + bufferID).empty();
                        for(let j=0; j<data.length; j++){
                            $('#sub_2_unit' + bufferID)
                                .append($("<option></option>")
                                    .attr("value",data[j])
                                    .text(data[j]));
                        }
                        $('#sub_2_unit' + bufferID).show();
                    }
                });
            });

            i++;
        });

        $("#delete_row").click(function(){
            if(i>1){
                $("#sch"+(i-1)).html('');
                i--;
            }
        });
    </script>
@endsection

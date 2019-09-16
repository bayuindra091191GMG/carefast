@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                {{ Form::open(['route'=>['admin.project.schedule.update', 'id'=>$project->id],'method' => 'post','id' => 'general-form']) }}
                <div class="row">
                    <div class="col-md-8 col-12">
                        <h3>UBAH SCHEDULE PROJECT {{$project->name}}</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.project.schedule.show', ['id'=>$project->id]) }}" class="btn btn-danger">BATAL</a>
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
                                            <input type="hidden" name="project_id" value="{{$project->id}}">
                                            <table class="table table-striped table-bordered table-hover" id="tab_logic">
                                                <thead>
                                                <tr>
                                                    <th class="text-center" style="width: 20%">
                                                        Pilih Place*
                                                    </th>
                                                    <th class="text-center" style="width: 20%">
                                                        Pilih Object (Jika ada)
                                                    </th>
                                                    <th class="text-center" style="width: 25%">
                                                        Pilih Sub Object 1 (Jika ada)
                                                    </th>
                                                    <th class="text-center" style="width: 25%">
                                                        Pilih Sub Object 2 (Jika ada)
                                                    </th>
                                                    <th class="text-center" style="width: 10%">
                                                        Tindakan
                                                    </th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                @php($count=0)
                                                @foreach($projectObjects as $projectObject)
                                                    <tr id='sch{{$count}}'>
                                                        <input type='hidden' name='id[]' value='{{$projectObject->id}}'>
                                                        <td class='field-item'>
                                                            <select id="place{{$count}}" name="places[]" class='form-control'>
                                                                <option value='{{$projectObject->place_id}}'>{{$projectObject->place_name}}</option>
                                                            </select>
                                                            <span><br>Atau tambah Baru</span>
                                                            <input type='text' id='placeNew{{$count}}' name='place_new[]' class='form-control'>
                                                        </td>
                                                        <td>
                                                            <select id="unit{{$count}}" name="units[]" class='form-control'>
                                                                <option value='{{$projectObject->unit_id}}'>{{$projectObject->unit_name}}</option>
                                                            </select>
                                                            <span><br>Atau tambah Baru</span>
                                                            <input type="text" id="unitNew{{$count}}" name="unit_new[]" class='form-control'>
                                                        </td>
                                                        <td>
                                                            <div id='sub_1_unit_div{{$count}}'>
                                                                <select id="sub_1_unit{{$count}}" name="sub_1_units[]" class='form-control'>
                                                                    <option value='{{$projectObject->sub1_unit_id}}'>{{$projectObject->sub1_unit_name}}</option>
                                                                </select>
                                                                <span><br>Atau tambah Baru</span>
                                                                <input type="text" id="sub_1_unitNew{{$count}}" name="sub_1_unit_new[]" class='form-control'>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div id='sub_2_unit_div{{$count}}'>
                                                                <select id="sub_2_unit{{$count}}" name="sub_2_units[]" class='form-control'>
                                                                    <option value='{{$projectObject->sub2_unit_id}}'>{{$projectObject->sub2_unit_name}}</option>
                                                                </select>
                                                                <span><br>Atau tambah Baru</span>
                                                                <input type="text" id="sub_2_unitNew{{$count}}" name="sub_2_unit_new[]" class='form-control'>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a class='edit-modal btn btn-xs btn-info' data-id='{{$count}}'><i class='fas fa-info'></i></a>
                                                            <a class='delete-modal btn btn-xs btn-danger' data-id='{{$count}}' ><i class='fas fa-trash-alt text-white'></i></a>
                                                        </td>
                                                    </tr>
                                                    @php($count++)
                                                @endforeach
                                                <tr id='sch{{$count}}'>
                                                    <input type='hidden' name='id[]' value='-1'>
                                                    <td class='field-item'>
                                                        <select id="place{{$count}}" name="places[]" class='form-control'><option value='-1'>-</option></select>
                                                        <span><br>Atau tambah Baru</span>
                                                        <input type='text' id='placeNew{{$count}}' name='place_new[]' class='form-control'>
                                                    </td>
                                                    <td>
                                                        <select id="unit{{$count}}" name="units[]" class='form-control'><option value='-1'>-</option></select>
                                                        <span><br>Atau tambah Baru</span>
                                                        <input type="text" id="unitNew{{$count}}" name="unit_new[]" class='form-control'>
                                                    </td>
                                                    <td>
                                                        <div id='sub_1_unit_div{{$count}}'>
                                                            <select id="sub_1_unit{{$count}}" name="sub_1_units[]" class='form-control'><option value='-1'>-</option></select>
                                                            <span><br>Atau tambah Baru</span>
                                                            <input type="text" id="sub_1_unitNew{{$count}}" name="sub_1_unit_new[]" class='form-control'>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div id='sub_2_unit_div{{$count}}'>
                                                            <select id="sub_2_unit{{$count}}" name="sub_2_units[]" class='form-control'><option value='-1'>-</option></select>
                                                            <span><br>Atau tambah Baru</span>
                                                            <input type="text" id="sub_2_unitNew{{$count}}" name="sub_2_unit_new[]" class='form-control'>
                                                        </div>
                                                    </td>
                                                    <td>

                                                    </td>
                                                </tr>
                                                @php($count++)
                                                <tr id='sch{{$count}}'></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <a id="add_row" class="btn btn-success" style="color: #fff;">Tambah</a>
                                        &nbsp;
                                        <a id='delete_row' class="btn btn-danger" style="color: #fff;">Hapus</a>
                                        <input type="hidden" id="project-count" value="{{$projectObjectCount}}">
                                        <input type="hidden" id="project-id" name="project-id" value="{{$project->id}}">
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
    <div id="deleteModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center">Apakah anda yakin ingin menghapus data ini?</h3>
                    <br />
                    <input type="hidden" id="deleted-id" name="deleted-id"/>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">
                            <span class='glyphicon glyphicon-remove'></span> No
                        </button>
                        <button type="button" class="btn btn-danger" onclick="deleteRow()">
                            <span class='glyphicon glyphicon-trash'></span> Yes
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="editModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                </div>
                <div class="modal-body">
                    <h3 class="text-center">Apakah anda yakin ingin mengubah data ini?</h3>
                    <br />
                    <form role="form">
                        <input type="hidden" id="edited-id" name="edited-id"/>
                        <div class="row mb-5">
                            <div class="col-6">
                                <h4>Place</h4>
                                <select id="place_edit" class='form-control'>
                                </select>
                                <span><br>Atau tambah Baru</span>
                                <input type='text' id='placeNew_edit'  class='form-control'>
                            </div>
                            <div class="col-6">
                                <h4>Object (Jika ada)</h4>
                                <select id="unit_edit" class='form-control'>
                                </select>
                                <span><br>Atau tambah Baru</span>
                                <input type='text' id='unitNew_edit' class='form-control'>
                            </div>
                        </div>
                        <div class="row mb-5">
                            <div class="col-6">
                                <h4>Sub Object 1 (Jika ada)</h4>
                                <select id="sub_1_unit_edit" class='form-control'>
                                </select>
                                <span><br>Atau tambah Baru</span>
                                <input type='text' id='sub_1_unitNew_edit' class='form-control'>
                            </div>
                            <div class="col-6">
                                <h4>Sub Object 2 (Jika ada)</h4>
                                <select id="sub_2_unit_edit" class='form-control'>
                                </select>
                                <span><br>Atau tambah Baru</span>
                                <input type='text' id='sub_2_unitNew_edit'  class='form-control'>
                            </div>
                        </div>
                    </form>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">
                            <span class='glyphicon glyphicon-remove'></span> No
                        </button>
                        <button type="button" class="btn btn-danger" onclick="editRow()">
                            <span class='glyphicon glyphicon-trash'></span> Yes
                        </button>
                    </div>
                </div>
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
        var countRow = $('#project-count').val();

        $('#place' + countRow).select2({
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

        $('#unit' + countRow).select2({
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

        $('#unit' + countRow).on('select2:select', function(){
            var objVal = '#unit' + countRow;

            $.ajax({
                url: '{{ route('select.sub1unit-dropdown') }}',
                dataType: 'json',
                data: {
                    'id': $(objVal).val()
                },
                success: function (data) {
                    $('#sub_1_unit' + countRow).empty();
                    if(data.length == 0){
                        $('#sub_1_unit' + countRow)
                            .append($("<option></option>")
                                .attr("value","-1")
                                .text("-"));
                    }
                    else{
                        for(let j=0; j<data.length; j++){
                            $('#sub_1_unit' + countRow)
                                .append($("<option></option>")
                                    .attr("value",data[j].id)
                                    .text(data[j].text));
                        }
                    }
                    $('#sub_2_unit' + countRow).empty();
                    $('#sub_2_unit' + countRow)
                        .append($("<option></option>")
                            .attr("value","-1")
                            .text("-"));
                }
            });
        });

        $('#sub_1_unit' + countRow).on('change', function() {
            var objVal = '#sub_1_unit' + countRow;
            $.ajax({
                url: '{{ route('select.units') }}',
                dataType: 'json',
                data: {
                    'id': $(objVal).val()
                },
                success: function (data) {
                    $('#sub_2_unit' + countRow).empty();
                    for(let j=0; j<data.length; j++){
                        $('#sub_2_unit' + countRow)
                            .append($("<option></option>")
                                .attr("value",data[j].id)
                                .text(data[j].text));
                    }
                }
            });
        });

        var i= parseInt(countRow) + parseInt(1);
        $("#add_row").click(function(){
            var bufferID = i;
            $('#sch'+i).html(
                "<input type='hidden' name='id[]' value='-1'>" +
                "<td><select id='place" + i +"' name='places[]' class='form-control'><option value='-1'>-</option></select>" +
                "<span><br>Atau tambah Baru</span>" +
                "<input type='text' id='placeNew" + i +"' name='place_new[]' class='form-control'>" +
                "</td>" +

                "<td><select id='unit" + i +"' name='units[]' class='form-control'><option value='-1'>-</option></select>" +
                "<span><br>Atau tambah Baru</span>" +
                "<input type='text' id='unitNew" + i +"' name='unit_new[]' class='form-control'>" +
                "</td>" +

                "<td><div id='sub_1_unit_div" + i +"' >" +
                "<select id='sub_1_unit" + i +"' name='sub_1_units[]' class='form-control'><option value='-1'>-</option></select>" +
                "<span><br>Atau tambah Baru</span>" +
                "<input type='text' id='sub_1_unitNew" + i +"' name='sub_1_unit_new[]' class='form-control'>" +
                "</div></td>" +

                "<td><div id='sub_2_unit_div" + i +"'>" +
                "<select id='sub_2_unit" + i +"' name='sub_2_units[]' class='form-control'><option value='-1'>-</option></select>" +
                "<span><br>Atau tambah Baru</span>" +
                "<input type='text' id='sub_2_unitNew" + i +"' name='sub_2_unit_new[]' class='form-control'>" +
                "</div></td><td></td>"
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
                    url: '{{ route('select.sub1unit-dropdown') }}',
                    dataType: 'json',
                    data: {
                        'id': $(objVal).val()
                    },
                    success: function (data) {
                        $('#sub_1_unit' + bufferID).empty();
                        if(data.length == 0){
                            $('#sub_1_unit' + bufferID)
                                .append($("<option></option>")
                                    .attr("value","-1")
                                    .text("-"));
                        }
                        else{
                            for(let j=0; j<data.length; j++){
                                $('#sub_1_unit' + bufferID)
                                    .append($("<option></option>")
                                        .attr("value",data[j].id)
                                        .text(data[j].text));
                            }
                        }
                        $('#sub_2_unit' + bufferID).empty();
                        $('#sub_2_unit' + bufferID)
                            .append($("<option></option>")
                                .attr("value","-1")
                                .text("-"));
                    }
                });
            });

            $('#sub_1_unit' + bufferID).on('change', function() {
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
                                    .attr("value",data[j].id)
                                    .text(data[j].text));
                        }
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
    <script>

        //delete already saved object
        $(document).on('click', '.delete-modal', function(){
            $('#deleteModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#deleted-id').val($(this).data('id'));
        });
        function deleteRow(){
            let rowIdx = $('#deleted-id').val();
            let deletedIdx = parseInt(rowIdx);

            $('#sch' + deletedIdx).remove();
            $('#deleteModal').modal('hide');
        }

        //edit already saved object
        $(document).on('click', '.edit-modal', function(){
            $('#editModal').modal({
                backdrop: 'static',
                keyboard: false
            });

            let a = $(this).data('id');
            $('#edited-id').val(a);

            $('#place_edit').select2({
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

            $('#unit_edit').select2({
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
            $('#unit_edit').on('select2:select', function(){
                bufferID = parseInt(a) - parseInt(1);
                var objVal = $(this).val();

                $.ajax({
                    url: '{{ route('select.sub1unit-dropdown') }}',
                    dataType: 'json',
                    data: {
                        'id': objVal
                    },
                    success: function (data) {
                        $('#sub_1_unit_edit').empty();
                        if(data.length == 0){
                            $('#sub_1_unit_edit')
                                .append($("<option></option>")
                                    .attr("value","-1")
                                    .text("-"));
                        }
                        else{
                            for(let j=0; j<data.length; j++){
                                $('#sub_1_unit_edit')
                                    .append($("<option></option>")
                                        .attr("value",data[j].id)
                                        .text(data[j].text));
                            }
                        }
                        $('#sub_2_unit_edit').empty();
                        $('#sub_2_unit_edit')
                            .append($("<option></option>")
                                .attr("value","-1")
                                .text("-"));
                    }
                });
            });

            $('#sub_1_unit_edit').on('change', function() {
                var objVal = $('#sub_1_unit_edit'+a).val();
                $.ajax({
                    url: '{{ route('select.units') }}',
                    dataType: 'json',
                    data: {
                        'id': objVal
                    },
                    success: function (data) {
                        $('#sub_2_unit_edit').empty();
                        for(let j=0; j<data.length; j++){
                            $('#sub_2_unit_edit')
                                .append($("<option></option>")
                                    .attr("value",data[j].id)
                                    .text(data[j].text));
                        }
                    }
                });
            });

        });

        function editRow(){
            let rowIdx = $('#edited-id').val();
            let a = parseInt(rowIdx);

            let placeNew = $('#placeNew_edit').val();
            $('#placeNew_edit').val("");
            $('#place' + a).empty();
            if(placeNew !== ""){
                $('#placeNew' + a).val(placeNew);
            }
            else{
                $('#place' + a)
                    .append($("<option></option>")
                        .attr("value",$('#place_edit').val())
                        .text($('#place_edit option:selected').text()));
            }

            let unitNew = $('#unitNew_edit').val();
            $('#unitNew_edit').val("");
            $('#unit' + a).empty();
            if(unitNew !== ""){
                $('#unitNew' + a).val(placeNew);
            }
            else{
                $('#unit' + a)
                    .append($("<option></option>")
                        .attr("value",$('#unit_edit').val())
                        .text($('#unit_edit option:selected').text()));
            }

            let sub1unitNew = $('#sub_1_unitNew_edit').val();
            $('#sub_1_unitNew_edit').val("");
            $('#sub_1_unit' + a).empty();
            if(sub1unitNew !== ""){
                $('#sub_1_unitNew' + a).val(placeNew);
            }
            else{
                $('#sub_1_unit' + a)
                    .append($("<option></option>")
                        .attr("value",$('#sub_1_unit_edit').val())
                        .text($('#sub_1_unit_edit option:selected').text()));
            }

            let sub2unitNew = $('#sub_2_unitNew_edit').val();
            $('#sub_2_unitNew_edit').val("");
            $('#sub_2_unit' + a).empty();
            if(sub2unitNew !== ""){
                $('#sub_2_unitNew' + a).val(placeNew);
            }
            else{
                $('#sub_2_unit' + a)
                    .append($("<option></option>")
                        .attr("value",$('#sub_2_unit_edit').val())
                        .text($('#sub_2_unit_edit option:selected').text()));
            }
            $('#editModal').modal('hide');
        }
    </script>
@endsection

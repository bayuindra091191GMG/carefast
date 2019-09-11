@extends('layouts.admin')

@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8 col-12">
                        <h3>UBAH DATA PENUGASAN EMPLOYEE PROJECT</h3>
                    </div>
                    <div class="col-md-4 col-12 text-right">
                        <a href="{{ route('admin.project.information.index') }}" class="btn btn-danger">BATAL</a>
                        <a id="btn_submit" class="btn btn-success text-white">SIMPAN</a>
                        <a id="btn_loading" class="btn btn-success text-white" style="display: none"><i class="fas fa-sync-alt fa-spin"></i>&nbsp;&nbsp;MENYIMPAN DATA EMPLOYEE...</a>
                    </div>
                </div>

                {{ Form::open(['route'=>['admin.project.employee.update', $project->id],'method' => 'post','id' => 'general-form']) }}

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

                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="code">Nama Project</label>
                                                        <input type="text" id="code" name="code" class="form-control" value="{{ $project->name }}" readonly/>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group form-float form-group-lg">
                                                    <div class="form-line">
                                                        <label class="form-label" for="manpower">Manpower</label>
                                                        <input type="text" id="manpower_string" class="form-control" value="{{ $manpowerLeft }}" readonly/>
                                                        <input type="hidden" id="manpower" name="manpower" value="{{ $manpowerLeft }}"/>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <hr/>

                                    <div class="col-12 mb-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <h3>UPPER MANAGEMENT</h3>
                                            </div>
                                            <div class="col-4 text-right">
                                                <a class="btn btn-primary text-white" style="cursor: pointer;" onclick="addRow();">TAMBAH</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <table id="upper_employee_table" class="table table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th class="text-center" style="width: 45%">EMPLOYEE</th>
                                                <th class="text-center" style="width: 30%">ROLE/POSISI</th>
                                                <th class="text-center" style="width: 25%">TINDAKAN</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php $idx = $upperEmployees->count() @endphp
                                            @if($upperEmployees->count() > 0)
                                                @foreach($collectUpperEmployees as $collectUpperEmployee)
                                                    <tr id="upper_employee_row_{{ $idx }}">
                                                        <td>
                                                            <select id="upper_employee_id_{{ $idx }}" name="upper_employee_ids[]" class="form-control">
                                                                <option value="{{ $collectUpperEmployee['employee_id']. '#'. $collectUpperEmployee['employee_role_name'] }}" selected>
                                                                    {{ $collectUpperEmployee['employee_code']. ' - '. $collectUpperEmployee['employee_name'] }}
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center">
                                                            <span id="upper_employee_role_{{ $idx }}">{{ $collectUpperEmployee['employee_role_name'] }}</span>
                                                        </td>
                                                        <td class="text-center">
                                                            @if($collectUpperEmployee['is_created_schedule'] === false)
                                                                <a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow('{{ $idx }}', 'upper');">HAPUS</a>
                                                            @else
                                                                <button type="button" class="btn btn-danger" disabled>HAPUS</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $idx++ @endphp
                                                @endforeach
                                            @else
                                                <tr id="upper_employee_row_0">
                                                    <td>
                                                        <select id="upper_employee_id_0" name="upper_employee_ids[]" class="form-control"></select>
                                                    </td>
                                                    <td class="text-center">
                                                        <span id="upper_employee_role_0"></span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow(0, 'upper');">HAPUS</a>
                                                    </td>
                                                </tr>
                                            @endif

                                            </tbody>
                                        </table>
                                    </div>

                                    <hr/>

                                    <div class="col-12 mb-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <h3>CLEANERS</h3>
                                            </div>
                                            <div class="col-4 text-right">
                                                <a class="btn btn-primary text-white" style="cursor: pointer;" onclick="addCleanerRow();">TAMBAH</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <table id="cleaner_employee_table" class="table table-striped table-bordered nowrap">
                                            <thead>
                                            <tr>
                                                <th class="text-center" style="width: 75%">EMPLOYEE</th>
                                                <th class="text-center" style="width: 25%">TINDAKAN</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @php $cleanerIdx = $cleanerEmployees->count() @endphp
                                            @if($cleanerEmployees->count() > 0)
                                                @foreach($collectCleanerEmployees as $collectCleanerEmployee)
                                                    <tr id="cleaner_employee_row_{{ $cleanerIdx }}">
                                                        <td >
                                                            <select id="cleaner_employee_id_{{ $cleanerIdx }}" name="cleaner_employee_ids[]" class="form-control">
                                                                <option value="{{ $collectCleanerEmployee['employee_id'] }}" selected>
                                                                    {{ $collectCleanerEmployee['employee_code']. ' - '. $collectCleanerEmployee['employee_name'] }}
                                                                </option>
                                                            </select>
                                                        </td>
                                                        <td class="text-center" style="width: 75%">
                                                            @if($collectCleanerEmployee['is_created_schedule'] === false)
                                                                <a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow('{{ $cleanerIdx }}', 'cleaner');">HAPUS</a>
                                                            @else
                                                                <button type="button" class="btn btn-danger" disabled>HAPUS</button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @php $cleanerIdx++ @endphp
                                                @endforeach
                                            @else
                                                <tr id="cleaner_employee_row_0">
                                                    <td >
                                                        <select id="cleaner_employee_id_0" name="cleaner_employee_ids[]" class="form-control"></select>
                                                    </td>
                                                    <td class="text-center" style="width: 75%">
                                                        <a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow(0, 'cleaner');">HAPUS</a>
                                                    </td>
                                                </tr>
                                            @endif

                                            </tbody>
                                        </table>
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
    {{--    <link rel="stylesheet" type="text/css" href="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">--}}
@endsection

@section('scripts')
    {{--    <script src="{{ asset('backend/assets/libs/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>--}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.6-rc.0/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/autonumeric@4.2.0"></script>
    <script type="text/javascript">
        var employeeIds = JSON.parse('{{ $includeIds }}');

        $(document).on('click', '#btn_submit', function() {
            $('#btn_submit').hide(500);
            $('#btn_loading').show(500);
            $('#general-form').submit();
        });

        @php $scriptIdx = $upperEmployees->count() @endphp
        @if($upperEmployees->count() > 0)
            @foreach($upperEmployees as $upperEmployee)
                $('#upper_employee_id_{{ $scriptIdx }}').select2({
                    placeholder: {
                        id: '-1',
                        text: ' - Pilih Employee - '
                    },
                    width: '100%',
                    minimumInputLength: 0,
                    ajax: {
                        url: '{{ route('select.upper.employees') }}',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                q: $.trim(params.term),
                                ids: employeeIds
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        }
                    }
                });

                $('#upper_employee_id_{{ $scriptIdx }}').on('select2:select', function (e) {
                    let data = e.params.data;
                    let valueArr = data.id.split('#');

                    employeeIds.push(parseInt(valueArr[0]));

                    $('#upper_employee_role_{{ $scriptIdx }}').html(valueArr[1]);
                });
                @php $scriptIdx++ @endphp
            @endforeach
        @else
            $('#upper_employee_id_0').select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Employee - '
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('select.upper.employees') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term),
                            ids: employeeIds
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#upper_employee_id_0').on('select2:select', function (e) {
                let data = e.params.data;
                let valueArr = data.id.split('#');

                employeeIds.push(parseInt(valueArr[0]));

                $('#upper_employee_role_0').html(valueArr[1]);
            });
        @endif

        @php $scriptCleanerIdx = $cleanerEmployees->count() @endphp
        @if($cleanerEmployees->count() > 0)
            @foreach($cleanerEmployees as $cleanerEmployee)
                $('#cleaner_employee_id_{{ $scriptCleanerIdx }}').select2({
                    placeholder: {
                        id: '-1',
                        text: ' - Pilih Cleaner - '
                    },
                    width: '100%',
                    minimumInputLength: 0,
                    ajax: {
                        url: '{{ route('select.cleaner.employees') }}',
                        dataType: 'json',
                        data: function (params) {
                            return {
                                q: $.trim(params.term),
                                ids: employeeIds
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        }
                    }
                });

                $('#cleaner_employee_id_{{ $scriptCleanerIdx }}').on('select2:select', function (e) {
                    let data = e.params.data;
                    let value = parseInt(data.id);

                    employeeIds.push(value);
                });

                @php $scriptCleanerIdx++ @endphp
            @endforeach
        @else
            $('#cleaner_employee_id_0').select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Cleaner - '
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('select.cleaner.employees') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term),
                            ids: employeeIds
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#cleaner_employee_id_0').on('select2:select', function (e) {
                let data = e.params.data;
                let value = parseInt(data.id);

                employeeIds.push(value);
            });
        @endif


        var tmp = '{{ $idx }}';
        var idx = parseInt(tmp) + 1;
        function addRow(){
            let bufferIdx = idx;

            // Validate manpower
            let manpower = parseInt($('#manpower').val());
            if(manpower === 0){
                alert('MANPOWER SUDAH MENCAPAI KAPASITAS MAKSIMUM!');
                return false;
            }

            // Update manpower
            manpower--;
            $('#manpower').val(manpower);
            let manpowerStr = rupiahFormat(manpower);
            $('#manpower_string').val(manpowerStr);

            let deleteType = 'upper';
            let newRow = '<tr id="upper_employee_row_' + idx + '">' +
                '<td><select id="upper_employee_id_' + idx + '" name="upper_employee_ids[]" class="form-control"></select></td>' +
                '<td class="text-center"><span id="upper_employee_role_' + idx + '"></span></td>' +
                '<td class="text-center"><a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow(' + idx + ',\'upper\')">HAPUS</a></td>' +
                '</tr>';

            $('#upper_employee_table').append(newRow);

            $('#upper_employee_id_' + idx).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Employee - '
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('select.upper.employees') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term),
                            ids: employeeIds
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#upper_employee_id_' + idx).on('select2:select', function (e) {
                let data = e.params.data;
                let valueArr = data.id.split('#');

                employeeIds.push(parseInt(valueArr[0]));

                $('#upper_employee_role_' + bufferIdx).html(valueArr[1]);
            });

            idx++;
        }
        var tmp2 = '{{ $cleanerIdx }}';
        var cleanerIdx = parseInt(tmp2) + 1;
        function addCleanerRow(){
            let bufferIdx = cleanerIdx;

            // Validate manpower
            let manpower = parseInt($('#manpower').val());
            if(manpower === 0){
                alert('MANPOWER SUDAH MENCAPAI KAPASITAS MAKSIMUM!');
                return false;
            }

            // Update manpower
            manpower--;
            $('#manpower').val(manpower);
            let manpowerStr = rupiahFormat(manpower);
            $('#manpower_string').val(manpowerStr);

            let newRow = '<tr id="cleaner_employee_row_' + cleanerIdx + '">' +
                '<td><select id="cleaner_employee_id_' + cleanerIdx + '" name="cleaner_employee_ids[]" class="form-control"></select></td>' +
                '<td class="text-center"><a class="btn btn-danger text-white" style="cursor: pointer;" onclick="deleteRow(' + cleanerIdx + ',\'cleaner\')">HAPUS</a></td>' +
                '</tr>';

            $('#cleaner_employee_table').append(newRow);

            $('#cleaner_employee_id_' + cleanerIdx).select2({
                placeholder: {
                    id: '-1',
                    text: ' - Pilih Cleaner - '
                },
                width: '100%',
                minimumInputLength: 0,
                ajax: {
                    url: '{{ route('select.cleaner.employees') }}',
                    dataType: 'json',
                    data: function (params) {
                        return {
                            q: $.trim(params.term),
                            ids: employeeIds
                        };
                    },
                    processResults: function (data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('#cleaner_employee_id_' + cleanerIdx).on('select2:select', function (e) {
                let data = e.params.data;
                let value = data.id;

                employeeIds.push(parseInt(value));
            });

            cleanerIdx++;
        }

        function deleteRow(rowIdx, type){
            let deletedIdx = parseInt(rowIdx);

            let deletedEmpVal = '';
            let deletedEmpId = 0;
            if(type === 'upper'){
                deletedEmpVal = $('#upper_employee_id_' + deletedIdx).val();
                let deletedEmpArr = deletedEmpVal.split('#');
                deletedEmpId = parseInt(deletedEmpArr[0]);
            }
            else{
                deletedEmpVal = $('#cleaner_employee_id_' + deletedIdx).val();
                deletedEmpId = parseInt(deletedEmpVal);
            }

            if(deletedEmpVal != null){
                for(let i = 0; i < employeeIds.length; i++){
                    if ( employeeIds[i] === deletedEmpId) {
                        employeeIds.splice(i, 1);
                    }
                }
            }

            $('#' + type + '_employee_row_' + deletedIdx).remove();

            // Update manpower
            let manpower = parseInt($('#manpower').val());
            manpower++;

            $('#manpower').val(manpower);
            let manpowerStr = rupiahFormat(manpower);
            $('#manpower_string').val(manpowerStr);
        }

        // Auto onfocusout when enter is pressed
        $('.auto-blur').keypress(function (e) {
            if (e.which == 13) {
                $(this).blur();
            }
        });

        function rupiahFormat(nStr) {
            let valueStr = nStr.toLocaleString(
                "de-DE"
            );

            return valueStr;
        }
    </script>
@endsection

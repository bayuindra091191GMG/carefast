@extends('layouts.admin')

@section('content')
<div class="row">

        <div class="col-12">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <h3>DETIL ORDER REQUEST</h3>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body b-b">
                                <div class="tab-content pb-3" id="v-pills-tabContent">
                                    <div class="tab-pane animated fadeInUpShort show active" id="v-pills-1">
                                        <!-- Input -->

                                        <div class="body">
                                            <div class="col-md-6">
                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="created_at">Tanggal Dibuat</label>
                                                            <input id="created_at" type="text" class="form-control"
                                                                   name="created_at" value="{{ $order->created_at_string }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="customer_name">Nama Customer</label>
                                                            <input id="customer_name" type="text" class="form-control"
                                                                   name="customer_name" value="{{ $order->user->first_name . ' ' . $order->user->last_name }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="customer_email">Email Customer</label>
                                                            <input id="customer_email" type="text" class="form-control"
                                                                   name="customer_email" value="{{ $order->user->email }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="customer_phone">Telepon Customer</label>
                                                            <input id="customer_phone" type="text" class="form-control"
                                                                   name="customer_phone" value="{{ $order->user->phone }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="customer_address">Alamat Customer</label>
                                                            <textarea id="customer_address" type="text" class="form-control"
                                                                      name="customer_address" readonly>{{ $order->user->addresses[0]->description }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="customer_country">Nama Sales</label>
                                                            <input id="customer_country" type="text" class="form-control"
                                                                   name="customer_country" value="{{ $order->sales_name }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="sub_total">Sub Total</label>
                                                            <input id="sub_total" type="text" class="form-control"
                                                                   name="sub_total" value="Rp{{ $order->sub_total_string }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="shipping_charge">Diskon</label>
                                                            <input id="shipping_charge" type="text" class="form-control"
                                                                   name="shipping_charge" value="Rp{{ $order->discount }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="grand_total">Grand Total</label>
                                                            <input id="grand_total" type="text" class="form-control"
                                                                   name="grand_total" value="Rp{{ $order->grand_total_string }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="status">Status</label>
                                                            <input id="status" type="text" class="form-control"
                                                                   name="status" value="{{ $order->order_status->name }}" readonly>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="col-md-6 text-right">
                                                    <div class="form-group form-float form-group-lg">
                                                        <div class="form-line">
                                                            <label class="form-label" for="track_code">Pilih Status</label>
                                                            <select id="status_dropdown" class="form-control">
                                                                <option value="process">Order Request Diproses</option>
                                                                <option value="ship">Dalam Pengiriman</option>
                                                                <option value="done">Sampai dan Selesai</option>
                                                                <option value="cancel">Order Request dibatalkan</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-right">
                                                    <div class="form-group form-float form-group-lg">
                                                        <a class="btn btn-error" data-id="{{$order->id}}" data-order_number="{{$order->order_number}}">
                                                            Ganti
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <table class="table table-striped table-bordered dt-responsive">
                                                    <thead>
                                                    <tr>
                                                        <td>Nama Produk</td>
                                                        <td>Info Produk</td>
                                                        <td>Qty</td>
                                                        <td>Harga</td>
                                                        <td>Total Harga</td>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($order->order_products as $product)
                                                        <tr>
                                                            <td>{{ $product->product->name }}</td>
                                                            <td>{!! $product->product_info !!}</td>
                                                            <td>{{ $product->qty }}</td>
                                                            <td>{{ $product->price_string }}</td>
                                                            <td>{{ $product->grand_total_string }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="col-md-11 col-sm-11 col-xs-12" style="margin: 3% 0 3% 0;">
                                            <a href="{{ route('admin.orders.index') }}" class="btn btn-danger">Keluar</a>
                                        </div>
                                        <!-- #END# Input -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal form to delete a form -->
    <div id="statusModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    <h3 class="text-center">Apakah anda yakin ingin mengganti status detail ini?</h3>
                    <br />
                    <form class="form-horizontal" role="form">
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="order_number">Nomor Order:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="order_number" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2" for="status_selected">Status pilihan:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="status_selected" readonly>
                            </div>
                        </div>

                        <div id="cancel_reason_div" class="form-group" style="display: none">
                            <label class="control-label col-sm-2" for="cancel_reason">Alasan pembatalan:</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control" id="cancel_reason">
                            </div>
                        </div>

                        <input type="hidden" name="order_id"/>
                    </form>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" data-dismiss="modal">
                            <span class='glyphicon glyphicon-remove'></span> Batal
                        </button>
                        <button type="button" class="btn btn-danger save" data-dismiss="modal">
                            <span id="" class='glyphicon glyphicon-trash'></span> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        // Delete detail
        var orderId = "0";
        var statusSelected = "";
        var cancelReason = "";
        $(document).on('click', '.status-modal', function() {
            $('.modal-title').text('Ganti Status');
            orderId = $(this).data('id');
            statusSelected = $('#status_dropdown').val();

            if(statusSelected === 'cancel'){
                $('#cancel_reason_div').show();

            }
            else{
                $('#cancel_reason_div').hide();
            }

            $('#order_number').val(orderId);
            $('#status_selected').val(statusSelected);
            $('#statusModal').modal('show');
        });
        $('.modal-footer').on('click', '.save', function() {
            cancelReason = $('#cancel_reason').val();
            $.ajax({
                type: 'POST',
                url: '{{ route('admin.orders.processing') }}',
                data: {
                    '_token': $('input[name=_token]').val(),
                    'order_id': orderId,
                    'process_type': statusSelected,
                    'cancel_reason': cancelReason,
                },
                success: function(data) {
                    if ((data.errors)){
                        setTimeout(function () {
                            toastr.error('Gagal mengganti status!', 'Peringatan', {timeOut: 5000});
                        }, 500);
                    }
                    else{
                        toastr.success('Berhasil mengganti status!', 'Sukses', {timeOut: 5000});
                    }
                }
            });
        });

    </script>
@endsection
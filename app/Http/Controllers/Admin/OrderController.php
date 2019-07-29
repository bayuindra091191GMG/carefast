<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ProductImage;
use App\Models\User;
use App\Transformer\OrderTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Yajra\DataTables\DataTables;

class OrderController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request){
        $users = Order::where('order_status_id', '>', 0)
            ->orderBy('order_number', 'desc')->get();
        return DataTables::of($users)
            ->setTransformer(new OrderTransformer())
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.order.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTracking(Request $request)
    {
        $orderid = $request->input('order-id');
        $orderDB = Order::find($orderid);
        $orderDB->track_code = $request->input('track_code');
        $orderDB->order_status_id = 4;
        $orderDB->save();

        return redirect()->route('admin.orders.detail', ['id'=>$orderid]);
        //
    }

    public function confirmOrderProcess(Request $request)
    {
        try{
            $processType = $request->input('process_type');
            $orderid = $request->input('order_id');
            $cancelReason = $request->input('cancel_reason');
            $orderDB = Order::find($orderid);

            //change order status (Order Request Diproses)
            if($processType == 'process'){
                $orderDB->status_id = 6;
                $orderDB->save();
            }
            //change order status (Dalam Pengiriman)
            if($processType == 'ship'){
                $orderDB->status_id = 7;
                $orderDB->save();
            }
            //change order status (Sampai dan Selesai)
            if($processType == 'done'){
                $orderDB->status_id = 8;
                $orderDB->save();
            }
            //change order status (Order Request dibatalkan)
            if($processType == 'cancel'){
                if(!empty($cancelReason)){
                    $orderDB->status_id = 9;
                    $orderDB->notes = $cancelReason;
                    $orderDB->save();
                }
                else{
                    return Response::json(array('errors' => 'INVALID'));
                }
            }
            return Response::json(array('success' => 'VALID'));
        }
        catch (\Exception $ex){
            Log::error('Admin/OrderController - confirmOrderProcess error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }

        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order = Order::find($id);

        return view('admin.order.show', compact('order'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

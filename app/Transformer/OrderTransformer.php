<?php
/**
 * Created by PhpStorm.
 * User: GMG-Developer
 * Date: 13/02/2018
 * Time: 11:34
 */

namespace App\Transformer;


use App\Models\AdminUser;
use App\Models\Order;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Log;

class OrderTransformer extends TransformerAbstract
{
    public function transform(Order $order){

        try{
            $createdDate = Carbon::parse($order->created_at)->toIso8601String();

            $url = route('admin.orders.detail', ['item'=>$order->id]);
            $action = "<a class='btn btn-xs btn-info' href='".$url."' data-toggle='tooltip' data-placement='top'><i class='icon-info'></i></a>";


            return[
                'order_number'      => $order->order_number ?? '',
                'created_at'        => $createdDate,
                'customer'          => $order->user->first_name . ' ' . $order->user->last_name,
                'email'             => $order->user->email,
                'sub_total'         => 'Rp'.$order->total_price_string,
                'discount'          => 'Rp'.$order->discount,
                'grand_total'       => 'Rp'.$order->grand_total_string,
                'status'            => $order->status->name,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("OrderTransformer.php > transform ".$exception);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    /**
     * Function to get the Order Request Data Details.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getTransactionData(Request $request)
    {
        $order = Order::where('order_number', $request->input('order_number'))->with('order_products')->first();

        return Response::json([
            'message'   => 'success',
            'model'    => json_encode($order)
        ], 200);
    }

    /**
     * Function to get all the Order Request.
     *
     * @return JsonResponse
     */
    public function getTransactions(Request $request)
    {
        $user = auth('api')->user();
        $skip = intval($request->input('skip'));
        $statusId = $request->input('order_status');
        $orderingType = $request->input('ordering_type');


        $transactions = Order::with(['order_products'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', $orderingType);

        // 0 default, get all order history
        if($statusId == 0) {
            $transactions = $transactions->where('status_id', $statusId)
                ->skip($skip)
                ->limit(10)
                ->get();
        }
        //get order history by status_id
        else{
            $transactions = $transactions
                ->skip($skip)
                ->limit(10)
                ->get();
        }

        return Response::json([
            'message'   => 'success',
            'model'    => json_encode($transactions)
        ], 200);
//        return Response::json(array('data' => $transactions));
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Function to confirm transaction Antar Sendiri by Admin Wastebank.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function addToCart(Request $request)
    {
        try{
            $rules = array(
                'product_id' => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);

            $user = auth('api')->user();
            $product = Product::find($request->input('product_id'));
            $totalPrice = $product->price * $request->input('qty');

            $tmpCart = Cart::where('user_id', $user->id)->where('product_id', $product->id)->get();
            if($tmpCart != null){
                $totalPriceTmp = $tmpCart->total_price;
                $totalPrice += $totalPriceTmp;
                $tmpCart->qty += $request->input('qty');
                $tmpCart->total_price = $totalPrice;

                $tmpCart->save();
            }
            else{
                $cart = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'description'   => $request->input('description'),
                    'qty'           => $request->input('qty'),
                    'price'         => $product->price,
                    'total_price'   => $totalPrice,
                    'created_at'    => Carbon::now('Asia/Jakarta')
                ]);
            }

            return Response::json("Added to Cart!", 200);
        }
        catch (\Exception $ex){
            return Response::json("Sorry Something went Wrong!", 500);
        }
    }

    /**
     * Used for Antar Sendiri Transaction when Admin Scan the User QR Code
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setTransactionToUser(Request $request)
    {
        try{
            $rules = array(
                'transaction_no'    => 'required',
                'email'             => 'required'
            );

            $data = $request->json()->all();

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = User::where('email', $data['email'])->first();
            $header = TransactionHeader::where('transaction_no', $data['transaction_no'])->first();
            $header->user_id = $user->id;
            $header->save();

            //send notification to admin browser and user device
            $userName = $header->user->first_name." ".$header->user->last_name;
            $title = "Digital Waste Solution";
            $body = "Admin Scan QR Code User";
            $data = array(
                'type_id' => '2',
                'transaction_no' => $header->transaction_no,
                'name' => $userName
            );

//            FCMNotification::SendNotification($header->created_by_admin, 'browser', $title, $body, $data);
            FCMNotification::SendNotification($user->id, 'app', $title, $body, $data);

            return Response::json([
                'message' => "Success assign " . $user->email . " to " . $header->transaction_no . "!",
            ], 200);
        }
        catch (\Exception $ex){
            Log::error("AdminController - setTransactionToUser error: ". $ex);
            return Response::json([
                'ex' => "ex " . $ex
            ], 500);
        }

    }

    /**
     * Function to return Transaction Data Related to the Admin Wastebank.
     *
     * @return JsonResponse
     */
    public function getTransactionList()
    {
        try{
            $adminWb = auth('admin_wastebank')->user();
            $admin = AdminUser::find($adminWb->id);
            $header = TransactionHeader::where('transaction_type_id', 1)->where('waste_bank_id', $admin->waste_bank_id)->get();

            return Response::json($header, 200);
        }
        catch (\Exception $ex){
            return Response::json("Sorry Something went Wrong!" . $ex, 500);
        }
    }
}

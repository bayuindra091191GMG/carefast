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
     * Function to Add item or Update Cart.
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
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

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
                Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $product->id,
                    'description'   => $request->input('description'),
                    'qty'           => $request->input('qty'),
                    'price'         => $product->price,
                    'total_price'   => $totalPrice,
                    'created_at'    => Carbon::now('Asia/Jakarta')
                ]);
            }

            return Response::json("Berhasil menambahkan ke keranjang belanja!", 200);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to Update item in Cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCart(Request $request)
    {
        try{
            $rules = array(
                'product_id'    => 'required',
                'qty'           => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('api')->user();
            $product = Product::find($request->input('product_id'));
            $totalPrice = $product->price * $request->input('qty');

            $tmpCart = Cart::where('user_id', $user->id)->where('product_id', $product->id)->get();
            if($tmpCart == null){
                return response()->json("Cart tidak ditemukan!", 404);
            }
            $tmpCart->total_price = $totalPrice;
            $tmpCart->qty = $request->input('qty');
            $tmpCart->save();

            return Response::json("Berhasil mengubah jumlah barang di keranjang belanja!", 200);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to Add item or Update Cart.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteCart(Request $request)
    {
        try{
            $rules = array(
                'product_id' => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('api')->user();

            $tmpCart = Cart::where('user_id', $user->id)->where('product_id', $request->input('product_id'))->get();
            if($tmpCart == null){
                return response()->json("Cart tidak ditemukan!", 404);
            }
            $tmpCart->delete();

            return Response::json("Berhasil menghapus dari keranjang belanja!", 200);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

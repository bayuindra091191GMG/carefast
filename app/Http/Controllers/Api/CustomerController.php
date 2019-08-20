<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\FCMNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return User[]|\Exception|\Illuminate\Database\Eloquent\Collection
     */
    public function getCustomers()
    {
        error_log("exception");
        try{

            $customers = Customer::all();

            return $customers;
        }
        catch(\Exception $ex){
            error_log($ex);
            return $ex;
        }
    }

    /**
     * Function to save user token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function saveCustomerToken(Request $request)
    {
        try{
            $data = $request->json()->all();
            $customer = auth('customer')->user();

            //Save user deviceID
            FCMNotification::SaveToken($customer->id, $data['device_id'], "customer");

            return Response::json([
                'message' => "Success Save Customer Token!",
            ], 200);
        }
        catch(\Exception $ex){
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @return JsonResponse
     */
    public function show()
    {
        try{
            $customerLogin = auth('customer')->user();
            $customer = Customer::where('id', $customerLogin->id)->first();

            $customerModel = collect([
                'id'                => $customer->id,
                'name'              => $customer->name,
                'email'             => $customer->email,
                'image_path'        => $customer->image_path,
                'phone'             => $customer->phone
            ]);

            return Response::json([
                'message'       => 'SUCCESS',
                'model'         => json_encode($customerModel)
            ]);
        }
        catch(\Exception $ex){
            return Response::json([
                'error'   => $ex,
            ], 500);
        }
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Project;
use App\Models\User;
use App\Notifications\FCMNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            FCMNotification::SaveToken($customer->id, $request->input('device_id'), "customer");

            return Response::json([
                'message' => "Success Save Customer Token!",
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/CustomerController - saveCustomerToken error EX: '. $ex);
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

            $project = Project::where('customer_id', 'like', '%'.$customer->id.'%')->first();
            $projectName = empty($project) ? "-" : $project->name;

            if(empty($project)){
                return Response::json("Customer tidak ada di project ini", 482);
            }

            $imagePath = asset('storage/customers/'. $customer->image_path);

            $customerModel = collect([
                'id'                => $customer->id,
                'project_name'      => $projectName,
                'project_id'      => $project->id,
                'name'              => $customer->name,
                'email'             => $customer->email,
                'image_path'        => $imagePath,
                'phone'             => $customer->phone
            ]);

            return Response::json($customerModel, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/CustomerController - show error EX: '. $ex);
            return Response::json([
                'error'   => $ex,
            ], 500);
        }
    }
}

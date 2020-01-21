<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\Configuration;
use App\Models\ProjectEmployee;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return User[]|\Exception|\Illuminate\Database\Eloquent\Collection
     */
    public function index()
    {
        error_log("exception");
        try{

            $users = User::all();

            return $users;
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
    public function saveUserToken(Request $request)
    {
        try{
            $data = $request->json()->all();
            $user = auth('api')->user();

            //Save user deviceID
            FCMNotification::SaveToken($user->id, $request->input('device_id'), "user");

            return Response::json([
                'message' => "Success Save User Token!",
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - saveUserToken error EX: '. $ex);
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
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            // Get dob
            $dob = '';
            if(!empty($employee->dob)){
                $dob = Carbon::parse($employee->dob, 'Asia/Jakarta')->format('d M Y');
            }

            //    accessible_menus =
            //    1. checkin,
            //    2. checkout,
            //    3. lihat jadwal (login sbg CSO)
            //    4. lihat jadwal cso (login sbg upper management)
            //    5. beri penilaian cso
            //    6. complain management
            //    7. create MR
            //    11. Plotting oleh leader
            $accessible_menus = "";
            if($user->employee->employee_role_id == 9){
                $accessible_menus = "1,2,3,4,5,6,7";
            }
            else if($user->employee->employee_role_id == 1){
                $accessible_menus = "1,2,3";
            }
            else{
                $accessible_menus = "1,4,5,6,11";
            }
            //pengecekan jika employee adalah pembuat MR pada suatu project

            //mengambil data project_id
            $employeeDB = ProjectEmployee::where('employee_id', $employee->id)
                ->where('employee_roles_id', '>', 1)
                ->where('status_id', 1)
                ->first();

            $employeeImage = empty($employee->image_path) ? "" : asset('storage/employees/'. $employee->image_path);
            $userModel = collect([
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role_id'           => $employee->employee_role_id,
                'role_name'         => $employee->employee_role->name,
                'project_id'        => empty($employeeDB) ? 0 : $employeeDB->project_id,
                'image_path'        => $employeeImage,
                'telephone'         => $employee->telephone ?? '',
                'phone'             => $employee->phone ?? '',
                'dob'               => $dob,
                'nik'               => $employee->nik ?? '',
                'address'           => $employee->address ?? '',
                'accessible_menus'  => $accessible_menus,
                'employee_id'       => $employee->id
            ]);

            return Response::json($userModel, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - show error EX: '. $ex);
            return Response::json([
                'error'   => $ex,
            ], 500);
        }
    }

    /**
     * Function to get user Address with Email Posted.
     *
     * @return JsonResponse
     */
    public function getAddress()
    {
        try{
            $user = auth('api')->user();
            $user = User::where('email', $user->email)->first();

            $address = Address::where('user_id', $user->id)
                ->where('primary', 1)
                ->first();

            if(empty($address)){
                return Response::json([
                    'message' => "Anda belum punya alamat.",
                ], 482);
            }

            return Response::json($address,200);
        }
        catch (\Exception $ex){
            return Response::json([
                'error'   => $ex,
            ], 500);
        }
    }

    /**
     * Function to Set Address with Parameters like Register.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function setAddress(Request $request)
    {
        try{
            $rules = array(
                'description'    => 'required',
                'latitude'       => 'required',
                'longitude'      => 'required',
                'city'           => 'required',
                'province'       => 'required',
                'postal_code'    => 'required'
            );

            Log::info("UserController - setAddress Content: ". $request);

            $data = $request->json()->all();

            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('api')->user();

            // Disable routine pickup
            if($user->routine_pickup === 1){
                $userDb = User::find($user->id);
                $userDb->routine_pickup = 0;
                $userDb->save();

                $userWasteBanks = UserWasteBank::where('user_id', $user->id)->get();
                if($userWasteBanks->count() > 0){
                    foreach($userWasteBanks as $userWasteBank){
                        $userWasteBank->status_id = 2;
                        $userWasteBank->save();
                    }
                }

                $userWasteCollectors = WasteCollectorUser::where('user_id', $user->id)->get();
                if($userWasteCollectors->count() > 0){
                    foreach ($userWasteCollectors as $userWasteCollector){
                        $userWasteCollector->status_id = 2;
                        $userWasteCollector->save();
                    }
                }
            }

            $addresses = Address::where('user_id', $user->id)->get();
            if($addresses->count() === 0){
                // Create new address
                $nAddress = Address::create([
                    'user_id'       => $user->id,
                    'primary'       => 1,
                    'description'   => $data['description'],
                    'latitude'      => $data['latitude'],
                    'longitude'     => $data['longitude'],
                    'city'          => (int)$data['city'],
                    'province'      => (int)$data['province'],
                    'postal_code'   => $data['postal_code'],
                    'notes'         => $data['notes'] ?? null,
                    'created_at'    => Carbon::now('Asia/Jakarta')
                ]);

                return Response::json($nAddress, 200);
            }
            else{
                // Assume edited address is always primary
                $address = Address::where('user_id', $user->id)
                    ->first();

                $address->description = $data['description'];
                $address->latitude = $data['latitude'];
                $address->longitude = $data['longitude'];
                $address->city = (int)$data['city'];
                $address->province = (int)$data['province'];
                $address->postal_code = $data['postal_code'];
                $address->notes = $data['notes'] ?? null;
                $address->save();

                return Response::json($address, 200);
            }



        }
        catch (\Exception $ex){
            Log::error("Api/UserController - setAddress error: ". $ex);
            return Response::json([
                'error'   => $ex,
            ], 500);
        }
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

    public function testingAuthToken(){
        $user = auth('waste_collector')->user();
        return $user;
    }

    // Update customer profile

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfile(Request $request)
    {
        try{
            $rules = array(
                'first_name'    => 'required',
                'last_name'     => 'required',
                'phone'         => 'required'
            );

            Log::info("UserController - updateProfile Content: ". $request);
            $data = json_decode($request->input('json_string'));
            //$jsonData = $request->input('apiEditProfileModel');

            //Log::info("First Name: ". $data->json_string->first_name);

            //$data = $request->json()->all();
            //$validator = Validator::make($data, $rules);

//            if ($validator->fails()) {
//                return response()->json($validator->messages(), 400);
//            }

            $user = auth('api')->user();
            $profile = User::with(['addresses', 'company'])->where('id', $user->id)->first();
            $profile->first_name = $data->first_name;
            $profile->last_name = $data->last_name;
            $profile->phone = $data->phone;
            $profile->save();

            // Update avatar
            if($request->hasFile('avatar')){
                if(!empty($profile->image_path)){
                    $tempImg = public_path('storage/avatars/'. $profile->image_path);
                    if(file_exists($tempImg)){
                        unlink($tempImg);
                    }
                }

                $avatar = Image::make($request->file('avatar'));
//                $filename = $profile->id. "_". Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $avatar->mime();
                $extension = $request->file('avatar')->extension();
                $filename = $profile->id. "_". Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $avatar->save(public_path('storage/avatars/'. $filename));
                $profile->image_path = $filename;
                $profile->save();
            }

            return Response::json($profile, 200);
        }
        catch (\Exception $ex){
            Log::error("UserController - updateProfile Error: ". $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'error'   => $ex,
            ], 500);
        }
    }
}

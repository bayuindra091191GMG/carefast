<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\FcmTokenUser;
use App\Models\ImeiHistory;
use App\Models\ProjectEmployee;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

    public function checkUserNUC(Request $request){
        try{
//            $employeeNUC = Employee::where('code', $request->input('employee_code'))->first();
            $employeeNUC = DB::table('employees')->where('code', $request->input('employee_code'))->first();

            if(!empty($employeeNUC)){
                if(empty($employeeNUC->phone) || $employeeNUC->phone == ""){

                    Log::channel('user_activity')
                        ->info("\tApi/UserController - checkUserNUC\tPhone number kosong ".$employeeNUC->phone."\tBelum ada nomor handphone");
                    return Response::json("Belum ada nomor handphone", 482);
                }
                else{
                    Log::channel('user_activity')
                        ->info("\tApi/UserController - checkUserNUC\tPhone number Sudah terisi ".$employeeNUC->phone."\tSudah ada nomor handphone");
                    return Response::json("Sudah ada nomor handphone", 200);
                }
            }
            else{
                Log::channel('user_activity')
                    ->info("\tApi/UserController - checkUserNUC\tEmployee Code tidak ditemukan | request code = ".$request->input('employee_code')."\tSudah ada nomor handphone");
                return Response::json("Sudah ada nomor handphone", 200);
            }
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - checkUserNUC error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    public function saveUserPhone(Request $request){
        try{
            $employee = Employee::where('code', $request->input('employee_code'))->first();

            if(!empty($employee)){
                if($employee->phone != "") {
                    Log::channel('user_activity')
                        ->info("\tApi/UserController - saveUserPhone\tNo Handphone di user ini sudah ada (".$employee->phone.")\tEmployee status = ".$employee->status_id."\tSudah ada nomor handphone");

                    return Response::json("Sudah ada nomor handphone", 482);
                }
                else{
                    if(DB::table('employees')->where('phone', $request->input('phone'))->exists()){
                        Log::channel('user_activity')
                            ->info("\tApi/UserController - saveUserPhone\tNo Handphone employee ".$request->input('employee_code')." sudah digunakan employee lain (".$employee->phone.")\tEmployee status = ".$employee->status_id."\tSudah ada nomor handphone");
                        return Response::json("Sudah ada nomor handphone", 482);
                    }

                    $employee->phone = $request->input('phone');
                    $employee->save();

                    $user = User::where('employee_id', $employee->id)->first();
                    $user->phone = $request->input('phone');
                    $user->save();

                    Log::channel('user_activity')
                        ->info("\tApi/UserController - saveUserPhone\tEmployee ".$employee->first_name.''.$employee->last_name."(".$employee->code.") mengganti nomor handphone ke ".$request->input('phone')."\tEmployee status = ".$employee->status_id."\tSuccess Save User new Phone");
                    return Response::json("Success Save User new Phone", 200);
                }
            }
            else{
                return Response::json([
                    'message' => "Sorry Something went Wrong!",
                ], 500);
            }
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - saveUserPhone error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    public function ChangePhone(Request $request){
        try{

            $employee = Employee::where('code', $request->input('employee_code'))->first();
            if(empty($employee)){
                return Response::json([
                    'message' => "NUC not found!",
                ], 484);
            }
            $user = User::where('employee_id', $employee->id)->first();

            if(!empty($user->android_id)){
                if($user->android_id != $request->input('android_id') ){
                    Log::channel('user_activity')
                        ->info("\tApi/UserController - ChangePhone\tAndroid Id tidak sesuai, database = ".$user->android_id.", request = ".$request->input('android_id')."\tEmployee status = ".$employee->status_id."\tAndroid ID Handphone tidak terdaftar");

                    return Response::json("Android ID Handphone tidak terdaftar", 483);
                }
            }

            if(!empty($user->first_imei)){
                if($user->first_imei != $request->input('imei_no')){
                    Log::channel('user_activity')
                        ->info("\tApi/UserController - ChangePhone\tImei No tidak sesuai, database = ".$user->first_imei.", request = ".$request->input('imei_no')."\tEmployee status = ".$employee->status_id."\tIMEI Handphone tidak terdaftar");
                    return Response::json("IMEI Handphone tidak terdaftar", 483);
                }
            }

            if(DB::table('employees')->where('phone', $request->input('phone'))->exists()){
                Log::channel('user_activity')
                    ->info("\tApi/UserController - ChangePhone\tNomor Handphone sudah ada pada employee lain, request = ".$request->input('phone')."\tEmployee status = ".$employee->status_id."\tSudah ada nomor handphone");

                return Response::json("Sudah ada nomor handphone", 482);
            }

            if(!empty($employee)){
//                Log::channel('in_sys')
//                    ->error('API/UserController - saveUserPhone : CODE = '.$request->input('employee_code').", Phone = ".$employee->phone);

                $employee->phone = $request->input('phone');
                $employee->save();

                $user->phone = $request->input('phone');
                $user->save();

                Log::channel('user_activity')
                    ->info("\tApi/UserController - ChangePhone\tEmployee ".$user->name."(".$employee->code.") sukses mengganti nomor handphone ke ".$request->input('phone')."\tEmployee status = ".$employee->status_id."\tSuccess Save User new Phone");
                return Response::json("Success Save User new Phone", 200);
        }
            else{
                return Response::json([
                    'message' => "NUC not found!",
                ], 484);
            }
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - saveUserPhone error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
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
            $user = auth('api')->user();

            //Save user deviceID
            FCMNotification::SaveToken($user->id, $request->input('device_id'), "user");


            //save user IMEI
            $userDB = User::where('id', $user->id)->first();
            $employeeDB = Employee::where('id', $userDB->employee_id)->first();

            if($user->status_id == 2 || $employeeDB->status_id == 2){
                return Response::json("Imei tidak terdaftar", 484);
            }

            if(empty($request->input('android_id')) && empty($request->input('imei_no'))){
                return Response::json("Imei tidak terdaftar", 484);
            }
            if(empty($userDB->phone) || $userDB->phone == " "){
                return Response::json("Handphone masih kosong", 483);
            }
            if(!empty($request->input('phone_no'))){
                if($userDB->phone != $request->input('phone_no')){
                    return Response::json("Handphone masih kosong", 483);
                }
            }

            if(empty($userDB->android_id)){
                $userDB->android_id = $request->input('android_id');
                $userDB->save();
            }
            else{
                if($userDB->android_id != $request->input('android_id')){
                    Log::channel('user_activity')
                        ->info("\tApi/UserController - saveUserToken\tAndroid ID dari ".$userDB->name."(".$employeeDB->code.") tidak sama, database=". $userDB->android_id." | request=".$request->input('android_id')."\tEmployee status = ".$employeeDB->status_id."\tImei tidak terdaftar");
                    return Response::json("Imei tidak terdaftar", 484);
                }
            }

//                $isNotValid = false;
//                if(empty($userDB->first_imei)){
//                    $userDB->first_imei = $request->input('imei_no');
//                    $userDB->save();
//                }
//                else{
//                    if($userDB->first_imei != $request->input('imei_no')){
//                        $isNotValid = true;
//
//                        if(empty($userDB->second_imei)){
//                            $userDB->second_imei = $request->input('imei_no');
//                            $userDB->save();
//                            $isNotValid = false;
//                        }
//                        else{
//                            if($userDB->second_imei != $request->input('imei_no')){
//                                $isNotValid = true;
//                            }
//                        }
//                    }
//                    else{
//                        $isNotValid = false;
//                    }
//                }

//                if($isNotValid){
//                    return Response::json("IMEI tidak terdaftar", 483);
//                }


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
     * Function to save user token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetImei(Request $request)
    {
        try{
            $user = auth('api')->user();
            $employee_id =  $user->employee_id;

            //save user IMEI
            $userDB = User::where('id', $user->id)->first();
            if(empty($request->input('android_id'))){
                return Response::json("Imei tidak terdaftar", 483);
            }

            $newHistory = ImeiHistory::create([
                'employee_id'   => $employee_id,
                'nuc'           => $request->input('employee_code'),
                'phone_type_old'=> $userDB->phone_type,
                'imei_old'      => $userDB->android_id,
                'phone_type_new'=> $request->input('phone_type'),
                'imei_new'      => $request->input('android_id'),
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'    => $employee_id,
            ]);

            $userDB->android_id = $request->input('android_id');
            $userDB->phone_type = $request->input('phone_type');
            $userDB->save();

            Log::channel('user_activity')
                ->info("\tApi/UserController - resetImei\tEmployee ".$userDB->name." mengganti nomor handphone ke ".$request->input('android_id')."\tEmployee status = ".$userDB->status_id."\tSuccess Reset User Imei");
            return Response::json([
                'message' => "Success Reset User Imei!",
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - resetImei error EX: '. $ex);
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
            if(empty($user->phone) || $user->phone == " " || $user->phone == ""){
                return Response::json([
                    'error'   => "",
                ], 452);
            }
            if($user->status_id == 2){
                return Response::json([
                    'error'   => "",
                ], 484);
            }
            $employee = Employee::find($user->employee_id);

            // Get dob
            $dob = '';
//            if(!empty($employee->dob)){
//                $dob = Carbon::parse($employee->dob, 'Asia/Jakarta')->format('d M Y');
//            }

            //    accessible_menus =
            //    1. checkin,
            //    2. checkout,
            //    3. lihat jadwal (login sbg CSO)
            //    4. lihat jadwal cso (login sbg upper management)
            //    5. beri penilaian cso
            //    6. complain management
            //    7. create MR
            //    8. attendance log
            //    11. Plotting oleh leader

            //    301. sick leave form
            //    302. sick leave list
            //    311. permission form
            //    312. permission list
            //    321. overtime list

            //  601. shift

            $accessible_menus = "";
            if($user->employee->employee_role_id > 4){
                $accessible_menus = "1,601,2,8,4,5,6,301,302,311,312,321";
            }
            else if($user->employee->employee_role_id == 1){
                $accessible_menus = "1,601,2,8,3,301,311";
            }
            else{
                $accessible_menus = "1,601,4,8,5,6,11,301,302,311,312,321";
            }
            //pengecekan jika employee adalah pembuat MR pada suatu project

            //mengambil data project_id
            $employeeDB = ProjectEmployee::where('employee_id', $employee->id)
                ->where('employee_roles_id', '>', 0)
                ->where('status_id', 1)
                ->first();
            $projectModels = collect();
            if($user->employee->employee_role_id > 1){
//                $employeeDBs = ProjectEmployee::where('employee_id', $employee->id)
//                    ->where('employee_roles_id', '>', 0)
//                    ->where('status_id', 1)
//                    ->chunk(100);
//                foreach ($employeeDBs as $employeedb){
//                    $projectModel = [
//                        'id'    => $employeedb->project_id,
//                        'name'    => $employeedb->project->name,
//                    ];
//                    $projectModels->push($projectModel);
//                }

                ProjectEmployee::where('employee_id', $employee->id)
                    ->where('employee_roles_id', '>', 0)
                    ->where('status_id', 1)
                    ->chunk(100, function ($employeeDBs) use ($projectModels){
                        foreach ($employeeDBs as $employeedb){
                            $projectModel = [
                                'id'    => $employeedb->project_id,
                                'name'    => $employeedb->project->name,
                            ];
                            $projectModels->push($projectModel);
                        }
                    });
            }


            $employeeImage = empty($employee->image_path) ? asset('storage/employees/1_photo_20190822050856.png') : asset('storage/employees/'. $employee->image_path);
            $userModel = collect([
                'id'                => $user->id,
                'name'              => $user->name,
                'email'             => $user->email,
                'role_id'           => $employee->employee_role_id,
                'role_name'         => $employee->employee_role->name,
                'project_id'        => empty($employeeDB) ? 0 : $employeeDB->project_id,
                'project_name'      => empty($employeeDB) ? "" : $employeeDB->project->name,
                'image_path'        => $employeeImage,
                'telephone'         => $employee->telephone ?? '',
                'phone'             => $employee->phone ?? '',
                'dob'               => $dob,
                'nik'               => $employee->nik ?? '',
                'address'           => $employee->address ?? '',
                'accessible_menus'  => $accessible_menus,
//                'employee_id'       => $employee->id,
                'employee_id'       => $user->employee_id,
                'employee_code'     => $employee->code,
                'projects'          => $projectModels,
                'job_name'          => $employee->notes ?? ''
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


    public function logout(Request $request){
        try{

            Log::info($request->getContent());

            //$token = $request->input('fmc_token');
            $json = json_decode($request->getContent());

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $token = $json->fcm_token;

            if(empty($user)){
                return Response::json('INVALID', 400);
            }

            $fcmToken = FcmTokenUser::where('user_id', $user->id)
                ->where('token', $token)
                ->first();

            if(!empty($fcmToken)){
                $fcmToken->delete();
            }
            else{
                Log::info('TOKEN NOT FOUND: '. $token);
            }

            return Response::json('SUCCESS', 200);
        }
        catch(\Exception $ex){
            Log::error('Api/UserController - logout error EX: '. $ex);
            return Response::json([
                'error'       => $ex,
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
     * Function to save user token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function changePassword(Request $request)
    {
        try{
            $user = auth('api')->user();

            $userDB = User::find($user->id);

            if($request->input('new_password') == $request->input('retype_password')){
                $userDB->password = Hash::make($request->input('new_password'));
                $userDB->updated_at = Carbon::now('Asia/Jakarta');
                $userDB->save();

                return Response::json([
                    'message' => "Success Change Customer Password!",
                ], 200);
            }
            return Response::json([
                'message' => "Password and Retype Password not match!",
            ], 500);

        }
        catch(\Exception $ex){
            Log::error('Api/CustomerController - changePassword error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
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

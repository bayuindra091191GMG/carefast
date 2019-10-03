<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Complaint;
use App\Models\ComplaintDetail;
use App\Models\Employee;
use App\Models\CustomerComplaintDetail;
use App\Models\Customer;
use App\Models\CustomerComplaint;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ComplainController extends Controller
{
    /**
     * Function to Submit Attendance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCustomer(Request $request)
    {
        try{
            $rules = array(
                'project_id'      => 'required',
                'subject'   => 'required',
                'message'   => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            //create customer complaint
            $newComplaint = Complaint::create([
                'project_id'   => $request->input('project_id'),
                'customer_id'   => $user->id,
                'customer_name'   => $customer->name,
                'subject'     => $request->input('subject'),
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            //get employee ID
//            $employeeID = 1;
//            $employeeDB = Employee::where('project_id', $request->input('project_id'))
//                        ->where('employee_roles_id', '>', 1)
//                        ->orderBy('employee_roles_id', 'asc')
//                        ->first();
//            $employeeID = $employeeDB->employee_id;

            //create complaint detail
            $newComplaint = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => $request->input('customer_id'),
                'employee_id'         => 0,
                'message'             => $request->input('message'),
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            return Response::json("Sukses menyimpan complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function submitEmployee(Request $request)
    {
        try{
            $rules = array(
                'project_id'    => 'required',
                'subject'       => 'required',
                'message'       => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();

            $customerComplaint = Complaint::where('project_id', $request->input('project_id'))->first();
            if(empty($customerComplaint)){

                //create customer complaint
                $newComplaint = Complaint::create([
                    'project_id'   => $request->input('project_id'),
                    'customer_id'   => $user->id,
                    'customer_name'   => $user->name,
                    'subject'     => $request->input('subject'),
                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'          => $user->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);

                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $newComplaint->id,
                    'customer_id'         => 0,
                    'employee_id'         => $user->id,
                    'message'             => $request->input('message'),
                    'created_by'          => $user->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);
            }
            else{
                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $customerComplaint->id,
                    'customer_id'         => 0,
                    'employee_id'         => $user->id,
                    'message'             => $request->input('message'),
                    'created_by'          => $user->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);
            }

            return Response::json("Sukses menyimpan complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submit error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaint(){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $customerComplaints =  Complaint::where('customer_id', $customer->id)->get();

            if($customerComplaints->count() == 0){
                return Response::json("Saat ini belum Ada complaint", 482);
            }

            return Response::json($customerComplaints, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

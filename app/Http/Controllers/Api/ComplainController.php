<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Complaint;
use App\Models\ComplaintDetail;
use App\Models\Employee;
use App\Models\Customer;
use App\Models\ProjectEmployee;
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
//            $rules = array(
//                'project_id'      => 'required',
//                'subject'   => 'required',
//                'message'   => 'required'
//            );
//
//            $data = $request->json()->all();
//            $validator = Validator::make($data, $rules);
//            if ($validator->fails()) {
//                return response()->json($validator->messages(), 400);
//            }
            if(!$request->filled('project_id')){
                return response()->json("Project ID harus terisi", 400);
            }
            if(!$request->filled('subject')){
                return response()->json("Subject harus terisi", 400);
            }
            if(!$request->filled('message')){
                return response()->json("Message harus terisi", 400);
            }
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            //checking if complain more than 5 or not
            $customerComplaintCount = Complaint::where('project_id', $request->input('project_id'))
                ->where('customer_id', $user->id)
                ->where('status_id', '!=', 12)
                ->count();
            if($customerComplaintCount >= 5){
                return response()->json("Quota complaint anda sudah mencapai maksimal", 482);
            }

            //create first complaint
            if($request->input('complaint_id') == 0){

                //get employee ID
                $employeeDB = ProjectEmployee::where('project_id', $request->input('project_id'))
                            ->where('employee_roles_id', '>', 1)
                            ->orderBy('employee_roles_id', 'asc')
                            ->first();

                //create customer complaint
                $newComplaint = Complaint::create([
                    'project_id'        => $request->input('project_id'),
                    'customer_id'       => $customer->id,
                    'customer_name'     => $customer->name,
                    'subject'           => $request->input('subject'),
                    'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'status_id'          => 10,
                    'employee_handler_role_id'  => $employeeDB->employee_roles_id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);

                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $newComplaint->id,
                    'customer_id'         => $customer->id,
                    'employee_id'         => null,
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
                    'complaint_id'        => $request->input('complaint_id'),
                    'customer_id'         => $customer->id,
                    'employee_id'         => null,
                    'message'             => $request->input('message'),
                    'created_by'          => $user->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);

                $complaint = Complaint::find($request->input('complaint_id'));
                $complaint->status_id = 11;
                $complaint->save();
            }

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

            if(!$request->filled('project_id')){
                return response()->json("Project ID harus terisi", 400);
            }
            if(!$request->filled('subject')){
                return response()->json("Subject harus terisi", 400);
            }
            if(!$request->filled('message')){
                return response()->json("Message harus terisi", 400);
            }
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            //checking if complain more than 5 or not
            $customerComplaintCount = Complaint::where('project_id', $request->input('project_id'))
                ->where('employee_id', $employee->id)
                ->where('status_id', '!=', 12)
                ->count();
            if($customerComplaintCount >= 5){
                return response()->json("Quota complaint anda sudah mencapai maksimal", 482);
            }

            //create first complaint
            if($request->input('complaint_id') == 0){

                //get employee ID
                $employeeDB = ProjectEmployee::where('project_id', $request->input('project_id'))
                    ->where('employee_roles_id', '>', 1)
                    ->orderBy('employee_roles_id', 'asc')
                    ->first();

                //create customer complaint
                $newComplaint = Complaint::create([
                    'project_id'        => $request->input('project_id'),
                    'employee_id'       => $employee->id,
                    'customer_name'     => $employee->name,
                    'subject'           => $request->input('subject'),
                    'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'status_id'          => 10,
                    'employee_handler_role_id'  => $employeeDB->employee_roles_id,
                    'created_by'          => $user->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $user->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);

                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $newComplaint->id,
                    'customer_id'         => null,
                    'employee_id'         => $employee->id,
                    'message'             => $request->input('message'),
                    'created_by'          => $employee->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $employee->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);
            }
            else{
                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $request->input('complaint_id'),
                    'customer_id'         => null,
                    'employee_id'         => $employee->id,
                    'message'             => $request->input('message'),
                    'created_by'          => $employee->id,
                    'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'          => $employee->id,
                    'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);

                $complaint = Complaint::find($request->input('complaint_id'));
                $complaint->status_id = 11;
                $complaint->save();

            }

            return Response::json("Sukses menyimpan complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaint(){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $customerComplaints =  Complaint::where('customer_id', $customer->id)->where('status_id', )->with('complaint_details')->get();

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

    public function getComplaintDetail(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }
            $user = auth('customer')->user();
//            $customer = Customer::find($user->id);

            $complaint =  Complaint::find($request->input('complaint_id'));
            $complaintDetails = collect();

            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            else{
                $customerComplaintDetails = $complaint->complaint_details;

                foreach($customerComplaintDetails as $customerComplaintDetail){
                    $customerComplaintDetailModel = ([
                        'customer_id'       => $customerComplaintDetail->customer_id,
                        'customer_name'     => $customerComplaintDetail->customer->name,
                        'customer_image'    => asset('storage/customers/'. $customerComplaintDetail->customer->image_path),
                        'employee_id'       => $customerComplaintDetail->employee_id,
                        'employee_name'     => $customerComplaintDetail->employee->first_name." ".$customerComplaintDetail->employee->last_name,
                        'employee_image'    => asset('storage/employees/'. $customerComplaintDetail->employee->image_path),
                        'message'           => $customerComplaintDetail->message,
                        'date'              => Carbon::parse($customerComplaintDetail->created_at, 'Asia/Jakarta')->format('d-m-Y H:i:s'),
                    ]);
                    $complaintDetails->push($customerComplaintDetailModel);
                }
            }
            $customerComplaintModel = collect([
                'id'                    => $complaint->id,
                'project_id'            => $complaint->project_id,
                'project_name'          => $complaint->project->name,
                'customer_id'           => $complaint->customer_id,
                'employee_id'           => $complaint->employee_id,
                'employee_handler_id'   => $complaint->employee_handler_id,
                'customer_name'         => $complaint->customer_name,
                'subject'               => $complaint->subject,
                'date'                  => Carbon::parse($complaint->date, 'Asia/Jakarta')->format('d-m-Y H:i:s'),
                'status_id'             => $complaint->status_id,
                'complaint_details'     => $complaintDetails,
            ]);

            return Response::json($customerComplaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

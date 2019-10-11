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
    public function createComplaintCustomer(Request $request)
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

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            //create first complaint
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
                    'response_limit_date'  => $datetimenow,
                    'created_at'          => $datetimenow,
                    'updated_by'          => $user->id,
                    'updated_at'          => $datetimenow,
                ]);

                //create complaint detail
                $newComplaint = ComplaintDetail::create([
                    'complaint_id'        => $newComplaint->id,
                    'customer_id'         => $customer->id,
                    'employee_id'         => null,
                    'message'             => $request->input('message'),
                    'created_by'          => $user->id,
                    'created_at'          => $datetimenow,
                    'updated_by'          => $user->id,
                    'updated_at'          => $datetimenow,
                ]);

            return Response::json($newComplaint->id, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function replyComplaintCustomer(Request $request)
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
            if(!$request->filled('message')){
                return response()->json("Message harus terisi", 400);
            }
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();

            $customer = Customer::find($user->id);

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();

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
            $complaint->response_limit_date = $datetimenow;
            $complaint->save();

            return Response::json("Sukses menyimpan complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitCustomer error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function createComplaintEmployee(Request $request)
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

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            //create first complaint

            //get employee ID
            $employeeDB = ProjectEmployee::where('project_id', $request->input('project_id'))
                ->where('employee_roles_id', '>', 1)
                ->orderBy('employee_roles_id', 'asc')
                ->first();

            //create customer complaint
            $newComplaint = Complaint::create([
                'project_id'        => $request->input('project_id'),
                'code'              => "test",
                'employee_id'       => $employee->id,
                'customer_name'     => $employee->name,
                'subject'           => $request->input('subject'),
                'date'              => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'          => 10,
                'employee_handler_role_id'  => $employeeDB->employee_roles_id,
                'response_limit_date'  => $datetimenow->addHours(6),
                'created_by'          => $user->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $user->id,
                'updated_at'          => $datetimenow,
            ]);

            //create complaint detail
            $newComplaint = ComplaintDetail::create([
                'complaint_id'        => $newComplaint->id,
                'customer_id'         => null,
                'employee_id'         => $employee->id,
                'message'             => $request->input('message'),
                'created_by'          => $employee->id,
                'created_at'          => $datetimenow,
                'updated_by'          => $employee->id,
                'updated_at'          => $datetimenow,
            ]);

            return Response::json($newComplaint->id, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function replyComplaintEmployee(Request $request)
    {
        try{
            if(!$request->filled('message')){
                return response()->json("Message harus terisi", 400);
            }
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $datetimenow = Carbon::now('Asia/Jakarta')->toDateTimeString();

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
            $complaint->response_limit_date = $datetimenow;
            $complaint->save();

            return Response::json("Sukses menyimpan complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - submitEmployee error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaint(Request $request){
        try{
            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $skip = intval($request->input('skip'));
            $statusId = intval($request->input('compliant_status'));
            $orderingType = $request->input('ordering_type');

//            Log::info('skip: '. $skip);
//            Log::info('order_status: '. $statusId);
//            Log::info('ordering_type: '. $orderingType);

            $customerComplaints =  Complaint::where('customer_id', $customer->id);
            if($statusId !== 0) {
                $customerComplaints = $customerComplaints->where('status_id', $statusId);
            }

            $customerComplaints = $customerComplaints
                ->orderBy('date', $orderingType)
                ->skip($skip)
                ->limit(10)
                ->get();

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

    public function getComplaintHeader(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }

            $complaint =  Complaint::find($request->input('complaint_id'));

            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
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
            ]);

            return Response::json($customerComplaintModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function getComplaintDetail(Request $request){
        try{
            if(empty($request->input('complaint_id'))){
                return response()->json("Bad Request", 400);
            }
            $skip = intval($request->input('skip'));

            $complaintDetails =  ComplaintDetail::where('complaint_id', $request->input('complaint_id'));
            $complaintDetailModels = collect();

            $complaintDetails = $complaintDetails
                ->skip($skip)
                ->limit(10)
                ->get();

            if(empty($complaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            else{
                foreach($complaintDetails as $customerComplaintDetail){
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
                    $complaintDetailModels->push($customerComplaintDetailModel);
                }
            }

            return Response::json($complaintDetailModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaintDetail error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function closeComplaint(Request $request){
        try{
            if(!$request->filled('complaint_id')){
                return response()->json("Complaint harus terisi", 400);
            }

            $user = auth('customer')->user();
            $customer = Customer::find($user->id);

            $customerComplaint =  Complaint::find($request->input('complaint_id'));
            if(empty($customerComplaint)){
                return Response::json("Complaint tidak ditemukan", 482);
            }
            $customerComplaint->status_id = 12;
            $customerComplaint->save();

            return Response::json("Berhasil menutup complaint", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ComplainController - getComplaint error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

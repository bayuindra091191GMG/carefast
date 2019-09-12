<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\CustomerComplaintDetail;
use App\Models\Customer;
use App\Models\CustomerComplaint;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
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
    public function submit(Request $request)
    {
        try{
            $rules = array(
                'project_id'      => 'required',
                'customer_id'   => 'required',
                'subject'   => 'required',
                'message'   => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('customer')->customer();

            $customer = Customer::find($request->input('customer_id'));
            
            //create customer complaint
            $newComplaint = CustomerComplaint::create([
                'project_id'   => $request->input('project_id'),
                'customer_id'   => $request->input('customer_id'),
                'customer_name'   => $customer->name,
                'subject'     => $request->input('subject'),
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            //get employee ID
            $employeeID = 1;
            $employeeList = Employee::where('project_id', $request->input('project_id'))
                        ->where('employee_roles_id', '>', 1)
                        ->orderBy('employee_roles_id', 'asc')
                        ->first();
            $employeeID = $employeeList->employee_id;

            //create complaint detail
            $newComplaint = CustomerComplaintDetail::create([
                'complaint_id'   => $newComplaint->id,
                'customer_id'   => $request->input('customer_id'),
                'employee_id'   => $employeeID,
                'message'             => $request->input('message'),
                'created_by'          => $user->id,
                'created_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'          => $user->id,
                'updated_at'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
            ]);

            return Response::json([
                'message'   => 'Sukses menyimpan complaint!',
                'model'     => ''
            ]);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function getComplaint(){
        try{
            $user = auth('customer')->customer();
            $customer = Customer::find($user->id);
            
            $customerComplaints =  CustomerComplaint::where('customer_id', $customer->id)
            
            -> get();
            
            return Response::json($customerComplaints, 200);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function employeeList(Request $request){
        try{
            $rules = array(
                'qr_code'   => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            //Submit Data
            $place = Place::where('qr_code', $request->input('qr_code'))->first();
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $schedule = Schedule::where('place_id', $place->id)->where('finish' <= $time)->with('employee')->get();

            return Response::json([
                'message'   => 'Sukses mengambil data Schedule!',
                'model'     => $schedule
            ]);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function supervisorSubmit(Request $request){
        try {
            $rules = array(
                'attendance_id' => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            //Submit Data
            $place = Place::where('qr_code', $request->input('qr_code'))->first();

            if($place->qr_code != Crypt::decryptString($request->input('qr_code'))){
                return Response::json("Tempat yang discan tidak tepat!", 400);
            }
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $schedule = Schedule::where('place_id', $place->id)->where('start' >= $time)->where('finish' <= $time)->first();
//            $attendance = Attendance::where('schedule_id', $schedule->id)->where('employee_id', )
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

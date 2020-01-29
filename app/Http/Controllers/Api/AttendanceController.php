<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\AttendanceProcess;
use App\libs\Utilities;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
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

class AttendanceController extends Controller
{
    /**
     * Function to Submit Attendance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCheckin(Request $request)
    {
        try{

            $data = json_decode($request->input('checkin_model'));
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $result = AttendanceProcess::checkinProcess($employee, $request, $data);

            return Response::json($result["desc"], $result["status_code"]);

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckin error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /**
     * Function to Submit Attendance by leader.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCheckinByLeader(Request $request)
    {
        try{

            $data = json_decode($request->input('checkin_model'));

            if($data->cso_id != "0"){
                $employee = Employee::find($data->cso_id);

                $result = AttendanceProcess::checkinProcess($employee, $request, $data);

                return Response::json($result["desc"], $result["status_code"]);
            }
            else{
                $userLogin = auth('api')->user();
                $user = User::where('phone', $userLogin->phone)->first();
                $employee = $user->employee;

                $result = AttendanceProcess::checkinLeaderProcess($employee, $request, $data);

                return Response::json($result["desc"], $result["status_code"]);
            }
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckin error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /**
     * Function to Submit Attendance checkout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCheckout(Request $request)
    {
        try{

//            Log::info('qr_code: '. $request->input('qr_code'));
//            Log::info('notes: '. $request->input('notes'));

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $result = AttendanceProcess::checkoutProcess($employee, $request);

            return Response::json($result["desc"], $result["status_code"]);

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckout error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /**
     * Function to Submit Attendance checkout by leader.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submitCheckoutByLeader(Request $request)
    {
        try{

//            Log::info('qr_code: '. $request->input('qr_code'));
//            Log::info('notes: '. $request->input('notes'));

            if($request->input('cso_id') != "0"){
                $type = 1;
                $employee = Employee::find($request->input('cso_id'));

                $result = AttendanceProcess::checkoutProcess($employee, $request, $type);

                return Response::json($result["desc"], $result["status_code"]);
            }
            else{
                $type = 2;
                $userLogin = auth('api')->user();
                $user = User::where('phone', $userLogin->phone)->first();
                $employee = $user->employee;

                $result = AttendanceProcess::checkoutProcess($employee, $request, $type);

                return Response::json($result["desc"], $result["status_code"]);
            }
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckout error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to checking employee already checkin or not.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkinChecking(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();

            //pengecekan harus di ganti dengan pengecekan weeks dan days dan finish
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
                ->first();

            if(empty($schedule)){
                return Response::json("Tidak ada schedule saat ini!", 482);
            }
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->where('start' >= $time)
//                ->where('finish' <= $time)->first();

            //checking checkin with attendance
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->where('is_done', 0)
                ->first();

            if(empty($attendance)){
                return Response::json("Tidak ada Attendance!", 482);
            }
            else{
                $place = Place::find($attendance->place_id);
                Log::info('checkinChecking place id = '.$attendance->place_id);
                if(empty($place)){
                    return Response::json("Place Tidak ditemukan!", 482);
                }

                $placeModel = collect([
                    'id'                => $place->id,
                    'place_name'        => $place->name,
                    'project_name'      => $projectEmployee->project->name,
                ]);
                return Response::json($placeModel, 200);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - checkinChecking error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }


    /**
     * Function to assestment employee works.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function leaderSubmit(Request $request){
        try {
            $rules = array(
                'attendance_id' => 'required'
            );

            $type = 1;
            $employee = Employee::find($request->input('cso_id'));

            $result = AttendanceProcess::leaderAssessment($employee, $request, $type);

            return Response::json($result["desc"], $result["status_code"]);


            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            //Submit Data
            $place = Place::where('qr_code', $request->input('qr_code'))->first();

//            if($place->qr_code != Crypt::decryptString($request->input('qr_code'))){
            if($place->qr_code != $request->input('qr_code')){
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

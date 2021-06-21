<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AttendanceAbsentTestController extends Controller
{
    /**
     * Function to Submit Attendance Absent Checkin or checkout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceIn(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            $employeeLogin = $user->employee;
            $employee_id =  $user->employee_id;
            $leader_id = $user->employee_id;

            $data = json_decode($request->input('attendance_model'));
            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;
                $leader_id = $user->employee_id;
            }
//            Log::info('Api/AttendanceAbsentController - attendanceIn $employee_id : '. $employee_id.', cso_id : '.$data->cso_id);
            $employee = DB::table('employees')->where('id', $employee_id)->first();

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $data->qr_code;
//            $project = Project::where('code', $projectCode)->first();
            $project = DB::table('projects')->where('code', $projectCode)->first();
            if(empty($project)){
                return Response::json("Project Tidak ditemukan!", 400);
            }

            //checking if employee on project
            $projectEmployeeExistence = ProjectEmployee::where('project_id', $project->id)
                ->where('employee_id', $employee->id)
                ->first();
            if(empty($projectEmployeeExistence)){
                return Response::json("Bukan pada project yang sesuai", 482);
            }
//            if($projectEmployeeExistence->status_id == 0){
//                return Response::json("Bukan pada project yang sesuai", 482);
//            }

//            $schedule = Schedule::where('project_id', $project->id)
//                ->where('project_employee_id', $employee->id)
//                ->first();
//            if(empty($schedule)){
//                return Response::json("Tidak pada schedule penempatan", 482);
//            }


//            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
//                ->where('project_id', $project->id)
//                ->where('status_id', 6)
//                ->where('is_done', 0)
//                ->first();

            //validasi attendance oleh leader
            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;
                $leader_id = $user->employee_id;

                $datenow = Carbon::now();
                $datenow2 = Carbon::now();
//            $datenowMonth = Carbon::parse($datenow)->format('m');
//            $datenowYear = Carbon::parse($datenow)->format('Y');
                $firstDate = $datenow->firstOfMonth();
                $lastDate = $datenow2->lastOfMonth();
//                $firstDate = $datenowYear.'-'.$datenowMonth.'-'.$firstDay;
//                $lastDate = $datenowYear.'-'.$datenowMonth.'-'.$lastDay;

                $existDate = AttendanceAbsent::where('employee_id', $employee_id)
//                        ->where('created_by', $projectUpperEmployee->employee_id)
                    ->where('status_id', 6)
                    ->where('created_by', '!=', $employee_id)
                    ->whereBetween('created_at', array($firstDate, $lastDate))
                    ->first();

//                    Log::error('Api/AttendanceAbsentController - attendanceIn firstDate : '. $firstDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn lastDate : '. $lastDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn employee_id : '. $employee_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn leader_id : '. $leader_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn AttendanceAbsent : '. json_encode($existDate));

                if(!empty($existDate)){
                    return Response::json("Leader sudah pernah absensi CSO tersebut", 484);
                }
            }

            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();

            //if not exist, checkin absent
            $result = 500;
            if(!empty($attendanceData)){
//                if(!empty($data->attendance_type)){
//                    if($data->attendance_type == 'off'){
//                        $result = $this->attendandeOutProcess($attendanceData, $employee, $employee->id, $project->id, $request, $data);
//                    }
//                    else{
//                        return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
//                    }
//                }
//                else{
//                    return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
//                }
                return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
            }
            else{
                $result = $this->attendandeInProcess($employee, $leader_id, $project->id, $request, $data);
            }
            if($result== 200){
                //Push Notification to customer App.
                if(!empty($project->customer_id) && $employee->employee_role_id >= 5){
                    //Send notification to
                    //Customer
                    $title = "ICare";
                    $body = "Manager sedang meninjau project";
                    $data = array(
                        "type_id" => 501,
                        "project_id" => $project->id,
                        "project_name" => $project->name,
                        "employee_name" => $employee->first_name. " " .$employee->last_name,
                    );
                    if(strpos($project->customer_id, '#') !== false){
                        $cusArr = explode('#', $project->customer_id);
                        foreach ($cusArr as $custId){
                            if(!empty($custId)){
                                FCMNotification::SendNotification($custId, 'customer', $title, $body, $data);
                            }
                        }
                    }
                    else{
                        FCMNotification::SendNotification($project->customer_id, 'customer', $title, $body, $data);
                    }
                }
                return Response::json("Berhasil Proses Absen Keluar", 200);
            }
            else{
                return Response::json([
                    'message' => "Sorry Something went Wrong!",
                    'ex' => "Please check log for further information",
                ], 500);
            }
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - attendanceIn error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to Submit Attendance Absent Checkin v2.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceInV2(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            $employeeLogin = $user->employee;
            $employee_id =  $user->employee_id;
            $leader_id = $user->employee_id;

            $data = json_decode($request->input('attendance_model'));
            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;
                $leader_id = $user->employee_id;
            }

            $employee = DB::table('employees')->where('id', $employee_id)->first();


            $projectCode = $data->qr_code;

            $project = DB::table('projects')->where('code', $projectCode)->first();
            if(empty($project)){
                return Response::json("Project Tidak ditemukan!", 400);
            }

            //for testing offline queue, project TSC only
            if($project->id == 1){
                Log::info('Api/AttendanceAbsentTestController - attendanceIn data : '.
                    json_encode($request->input('attendance_model')));
            }

            //checking if employee on project
            $projectEmployeeExistence = ProjectEmployee::where('project_id', $project->id)
                ->where('employee_id', $employee->id)
                ->first();
            if(empty($projectEmployeeExistence)){
                return Response::json("Bukan pada project yang sesuai", 482);
            }

            //validasi attendance oleh leader
            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;
                $leader_id = $user->employee_id;

                $datenow = Carbon::now();
                $datenow2 = Carbon::now();
                $firstDate = $datenow->firstOfMonth();
                $lastDate = $datenow2->lastOfMonth();

                $existDate = AttendanceAbsent::where('employee_id', $employee_id)
//                        ->where('created_by', $projectUpperEmployee->employee_id)
                    ->where('created_by', '!=', $employee_id)
                    ->whereBetween('created_at', array($firstDate, $lastDate))
                    ->first();

//                    Log::error('Api/AttendanceAbsentController - attendanceIn firstDate : '. $firstDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn lastDate : '. $lastDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn employee_id : '. $employee_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn leader_id : '. $leader_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn AttendanceAbsent : '. json_encode($existDate));

                if(!empty($existDate)){
                    return Response::json("Leader sudah pernah absensi CSO tersebut", 484);
                }
            }

            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();

            //if not exist, checkin absent
            $result = 500;
            if(!empty($attendanceData)){
//                if(!empty($data->attendance_type)){
//                    if($data->attendance_type == 'off'){
//                        $result = $this->attendandeOutProcess($attendanceData, $employee, $employee->id, $project->id, $request, $data);
//                    }
//                    else{
//                        return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
//                    }
//                }
//                else{
//                    return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
//                }
                return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
            }
            else{
                $result = $this->attendandeInProcess($employee, $leader_id, $project->id, $request, $data);
            }
            if($result== 200){
                //Push Notification to customer App.
                if(!empty($project->customer_id) && $employee->employee_role_id >= 5){
                    //Send notification to
                    //Customer
                    $title = "ICare";
                    $body = "Manager sedang meninjau project";
                    $data = array(
                        "type_id" => 501,
                        "project_id" => $project->id,
                        "project_name" => $project->name,
                        "employee_name" => $employee->first_name. " " .$employee->last_name,
                    );
                    if(strpos($project->customer_id, '#') !== false){
                        $cusArr = explode('#', $project->customer_id);
                        foreach ($cusArr as $custId){
                            if(!empty($custId)){
                                FCMNotification::SendNotification($custId, 'customer', $title, $body, $data);
                            }
                        }
                    }
                    else{
                        FCMNotification::SendNotification($project->customer_id, 'customer', $title, $body, $data);
                    }
                }
                return Response::json("Berhasil Proses Absen Keluar", 200);
            }
            else{
                return Response::json([
                    'message' => "Sorry Something went Wrong!",
                    'ex' => "Please check log for further information",
                ], 500);
            }
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - attendanceIn error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to Submit Attendance Absent Checkin or checkout.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceOut(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            $employeeLogin = $user->employee;
            $employee_id =  $user->employee_id;

            $data = json_decode($request->input('attendance_model'));
            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;
            }
            //Log::info('Api/AttendanceAbsentController - attendanceOut $employee_id : '. $employee_id.', cso_id : '.$data->cso_id);
            $employee = DB::table('employees')->where('id', $employee_id)->first();

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $data->qr_code;

//            $project = Project::where('code', $projectCode)->first();
            $project = DB::table('projects')->where('code', $projectCode)->first();

            if(empty($project)){
                return Response::json("Project Tidak ditemukan!", 400);
            }

            //checking if employee on project
            $projectEmployeeExistence = ProjectEmployee::where('project_id', $project->id)
                ->where('employee_id', $employee->id)
                ->first();
            if(empty($projectEmployeeExistence)){
                return Response::json("Bukan pada project yang sesuai", 482);
            }
//            if($projectEmployeeExistence->status_id == 0){
//                return Response::json("Bukan pada project yang sesuai", 482);
//            }

//            $schedule = Schedule::where('project_id', $project->id)
//                ->where('project_employee_id', $employee->id)
//                ->first();
//            if(empty($schedule)){
//                return Response::json("Tidak pada schedule penempatan", 482);
//            }

            if($data->cso_id != "0"){
                $employee_id = $data->cso_id;

                $datenow = Carbon::now();
                $datenow2 = Carbon::now();
//            $datenowMonth = Carbon::parse($datenow)->format('m');
//            $datenowYear = Carbon::parse($datenow)->format('Y');
                $firstDate = $datenow->firstOfMonth();
                $lastDate = $datenow2->lastOfMonth();
//                $firstDate = $datenowYear.'-'.$datenowMonth.'-'.$firstDay;
//                $lastDate = $datenowYear.'-'.$datenowMonth.'-'.$lastDay;

                $existDate = AttendanceAbsent::where('employee_id', $employee_id)
//                        ->where('created_by', $projectUpperEmployee->employee_id)
                    ->where('status_id', 7)
                    ->where('created_by', '!=', $employee_id)
                    ->whereBetween('created_at', array($firstDate, $lastDate))
                    ->first();

//                    Log::error('Api/AttendanceAbsentController - attendanceIn firstDate : '. $firstDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn lastDate : '. $lastDate);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn employee_id : '. $employee_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn leader_id : '. $leader_id);
//                    Log::error('Api/AttendanceAbsentController - attendanceIn AttendanceAbsent : '. json_encode($existDate));

                if(!empty($existDate)){
                    return Response::json("Leader sudah pernah absensi CSO tersebut", 484);
                }
            }



            //for testing offline queue, project TSC only
            if($project->id == 1){
                Log::info('Api/AttendanceAbsentTestController - attendanceOut data : '.
                    json_encode($request->input('attendance_model')));
            }

            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            $result = 500;
            if(empty($attendanceData)){
//                if(!empty($data->attendance_type)){
//                    if($data->attendance_type == 'off'){
//                        $result = $this->attendandeInProcess($employee, $employee->id, $project->id, $request, $data);
//                    }
//                    else{
//                        return Response::json("Anda Belum Absen Masuk", 483);
//                    }
//                }
//                else{
//                    return Response::json("Anda Belum Absen Masuk", 483);
//                }
                return Response::json("Anda Belum Absen Masuk", 483);
            }
            else{
//                if(!empty($data->attendance_type)){
//                    if($data->attendance_type != 'off'){
//                        $temp = Carbon::now('Asia/Jakarta');
//                        $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));
//
//                        $trxDate = Carbon::parse(date_format($attendanceData->created_at, 'j-F-Y H:i:s'));
//                        $intervalMinute = $trxDate->diffInMinutes($now);
//                        if($intervalMinute < 480){
//                            return Response::json("Absensi dilakukan kurang dari 8 jam yang lalu!", 484);
//                        }
//                    }
//                }
                $result = $this->attendandeOutProcess($attendanceData, $employee, $employee->id, $project->id, $request, $data);
            }

            if($result== 200){
                return Response::json("Berhasil Proses Absen Keluar", 200);
            }
            else{
                return Response::json([
                    'message' => "Sorry Something went Wrong!",
                    'ex' => "Please check log for further information",
                ], 500);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - attendanceOut error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to checking employee already checkin or not.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceChecking(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();

            if($projectEmployee->employee_roles_id  == 1){
                //pengecekan harus di ganti dengan pengecekan weeks dan days dan finish
//                $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                    ->where('project_employee_id', $projectEmployee->id)
//                    ->first();
//
//                if(empty($schedule)){
//                    return Response::json("Tidak ada schedule saat ini!", 482);
//                }
            }
//            $date = Carbon::now('Asia/Jakarta');
//            $time = $date->format('H:i:s');
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->where('start' >= $time)
//                ->where('finish' <= $time)->first();

            //checking checkin with attendance
//            $attendance = AttendanceAbsent::where('employee_id', $employee->id)
////                ->where('schedule_id', $schedule->id)
//                ->where('status_id', 6)
//                ->where('is_done', 0)
//                ->first();
            $attendance = DB::table('attendance_absents')
                ->where('employee_id', $employee->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();

            if(empty($attendance)){
                return Response::json("Tidak ada Attendance!", 482);
            }
            else{
//                $place = Place::find($attendance->place_id);
//                Log::info('checkinChecking place id = '.$attendance->place_id);
//                if(empty($place)){
//                    return Response::json("Place Tidak ditemukan!", 482);
//                }

                $placeModel = collect([
                    'id'                => $projectEmployee->project_id,
                    'place_name'        => $projectEmployee->project->name,
                    'project_name'      => $projectEmployee->project->name,
                    'attendance_time'      => Carbon::parse($attendance->created_at)->format('d M Y H:i:s'),
                    'attendance_out_time'      => "",
                ]);
                return Response::json($placeModel, 200);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - checkinChecking error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to checking employee already checkin or not.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceCheckingV2(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();

            if($projectEmployee->employee_roles_id  == 1){
                //pengecekan harus di ganti dengan pengecekan weeks dan days dan finish
//                $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                    ->where('project_employee_id', $projectEmployee->id)
//                    ->first();
//
//                if(empty($schedule)){
//                    return Response::json("Tidak ada schedule saat ini!", 482);
//                }
            }
//            $date = Carbon::now('Asia/Jakarta');
//            $time = $date->format('H:i:s');
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->where('start' >= $time)
//                ->where('finish' <= $time)->first();

            //checking checkin with attendance
//            $attendance = AttendanceAbsent::where('employee_id', $employee->id)
////                ->where('schedule_id', $schedule->id)
//                ->where('status_id', 6)
//                ->where('is_done', 0)
//                ->first();
            $attendance = DB::table('attendance_absents')
                ->where('employee_id', $employee->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();

            if(empty($attendance)){
                return Response::json("Tidak ada Attendance!", 482);
            }
            else{
//                $place = Place::find($attendance->place_id);
//                Log::info('checkinChecking place id = '.$attendance->place_id);
//                if(empty($place)){
//                    return Response::json("Place Tidak ditemukan!", 482);
//                }

                $placeModel = collect([
                    'id'                => $projectEmployee->project_id,
                    'place_name'        => $projectEmployee->project->name,
                    'project_name'      => $projectEmployee->project->name,
                    'attendance_time'      => Carbon::parse($attendance->created_at)->format('d m Y H:i:s'),
                    'attendance_out_time'      => "",
                ]);
                return Response::json($placeModel, 200);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - checkinChecking error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to get employee attendance log.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceLog(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d 00:00:00');

            $finishDate = Carbon::parse($request->input('finish_date'))->format('Y-m-d 00:00:00');
            $finishDate2 = Carbon::parse($finishDate)->addDay();

            $attendances = DB::table('attendance_absents')
                ->where('employee_id', $employee->id)
                ->whereBetween('created_at', [$startDate, $finishDate2])
                ->where('status_id', 6)
                ->orderByDesc('date')
                ->get();

            if($attendances->count() == 0){
                return Response::json("Tidak ada Attendance!", 482);
            }
            else{
                $attendanceModels = collect();
                foreach ($attendances as $attendance){
                    $attIn = Carbon::parse($attendance->date)->format('d M Y H:i:s');
//                    $attIn = $attendance->date->format('Y-m-d H:i:s');
                    if(empty($attendance->date_checkout)){
                        $attOut = "";
                    }
                    else{
                        $attOut = Carbon::parse($attendance->date_checkout)->format('d M Y H:i:s');
//                        $attOut = $attendance->date_checkout->format('Y-m-d H:i:s');
                    }
                    $attendanceModel = collect([
                        'attendance_in_date'    => $attIn,
                        'attendance_out_date'   => $attOut,
                    ]);
                    $attendanceModels->push($attendanceModel);
                }
                return Response::json($attendanceModels, 200);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - attendanceLog error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex->getMessage(),
            ], 500);
        }
    }
    /**
     * Function to get employee attendance log.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function attendanceLogV2(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d 00:00:00');

            $finishDate = Carbon::parse($request->input('finish_date'))->format('Y-m-d 00:00:00');
            $finishDate2 = Carbon::parse($finishDate)->addDay();

            $attendances = DB::table('attendance_absents')
                ->where('employee_id', $employee->id)
                ->whereBetween('created_at', [$startDate, $finishDate2])
                ->where('status_id', 6)
                ->orderByDesc('date')
                ->get();

            if($attendances->count() == 0){
                return Response::json("Tidak ada Attendance!", 482);
            }
            else{
                $attendanceModels = collect();
                foreach ($attendances as $attendance){
                    $attIn = Carbon::parse($attendance->date)->format('d m Y H:i:s');
//                    $attIn = $attendance->date->format('Y-m-d H:i:s');
                    if(empty($attendance->date_checkout)){
                        $attOut = "";
                    }
                    else{
                        $attOut = Carbon::parse($attendance->date_checkout)->format('d m Y H:i:s');
//                        $attOut = $attendance->date_checkout->format('Y-m-d H:i:s');
                    }
                    $projectName = "";
                    $project = Project::where('id', $attendance->project_id)->first();

                    if(!empty($project)){
                        $projectName = $project->name;
                    }

                    $attendanceModel = collect([
                        'attendance_in_date'    => $attIn,
                        'attendance_out_date'   => $attOut,
                        'project_name'          => $projectName,
                    ]);
                    $attendanceModels->push($attendanceModel);
                }
                return Response::json($attendanceModels, 200);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - attendanceLog error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex->getMessage(),
            ], 500);
        }
    }

    /**
     * Function to Submit Attendance Absent Check out.
     *
     * @param Request $request
     * @return JsonResponse
     */
//    public function submitCheckout(Request $request)
//    {
//        try{
//
//            $userLogin = auth('api')->user();
//            $user = User::where('phone', $userLogin->phone)->first();
//            $employee = $user->employee;
//
//            $projectCode = Crypt::decryptString($request->input('qr_code'));
//            $project = Project::where('code', $projectCode)->first();
//            if(empty($project)){
//                return Response::json("Project Tidak ditemukan!", 400);
//            }
//
//            $newAttendance = AttendanceAbsent::create([
//                'employee_id'   => $employee->id,
//                'project_id'   => $project->id,
//                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
//                'status_id'     => 7
//            ]);
//            return Response::json("Berhasil Check out Absensi", 200);
//        }
//        catch (\Exception $ex){
//            Log::error('Api/AttendanceAbsentController - submitCheckout error EX: '. $ex);
//            return Response::json("Maaf terjadi kesalahan!", 500);
//        }
//    }

    public function getProjectCodeEncrypted(Request $request)
    {
        try{
            $projectCode = $request->input('project_code');

            $codeEncrypted = Crypt::encryptString($projectCode);

            return Response::json($codeEncrypted, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - getProjectCodeEncrypted error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function attendandeInProcess($employee, $employeeId, $projectId, $request, $data){
        try{
            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $employee->id,
                'project_id'    => $projectId,
//                    'shift_type'    => $schedule->shift_type ?? 0,
                'shift_type'    => 1,
                'is_done'       => 0,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 6,
                'created_by'     => $employeeId,
            ]);
            //get latitude and longitude
//                if($user->id < 30){
//                    if(!empty($data->location->latitude) && !empty($data->location->longitude)){
//                        $newAttendance->latitude = $data->location->latitude;
//                        $newAttendance->longitude = $data->location->longitude;
//                        $newAttendance->save();
//                    }
//                }

            //check if on/off request
            if(!empty($data->attendance_type)){
                if($data->attendance_type == 'off'){
                    $attTime = Carbon::parse($data->attendance_time)->format('Y-m-d H:i:s');
                    $newAttendance->date = $attTime;
                    $newAttendance->save();
                }
            }

            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
//                $todayStr = $today->format('l d-m-y');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendances/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employee->first_name . ' ' . $employee->last_name . '_attendancein_'. $newAttendance->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendance->image_path = $todayStr.'/'.$filename;
                $newAttendance->save();
            }
            return 200;
        }
        catch (\Exception $exception){
//            dd($exception);
            error_log($exception);
            Log::error("Api/AttendanceAbsentController - attendandeInProcess Error: ". $exception->getMessage());
            return 500;
        }
    }

    public function attendandeOutProcess($attendanceData, $employee, $employeeId, $projectId, $request, $data){
        try{

            // checkout absent
            //for development comment this code
//            $temp = Carbon::now('Asia/Jakarta');
//            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

//            $trxDate = Carbon::parse(date_format($attendanceData->created_at, 'j-F-Y H:i:s'));
//            $intervalMinute = $now->diffInMinutes($trxDate);
//            if($intervalMinute < 480){
//                return Response::json("Absensi dilakukan kurang dari 1 jam yang lalu!", 483);
//            }
            $attendanceData->is_done = 1;
            $attendanceData->date_checkout = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $attendanceData->save();

            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $employeeId,
                'project_id'    => $projectId,
//                    'shift_type'    => $schedule->shift_type ?? 0,
                'shift_type'    => 1,
                'is_done'       => 1,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 7,
                'created_by'     => $employeeId,
            ]);
            //get latitude and longitude
//            if($user->id < 30){
//                if(!empty($data->location->latitude) && !empty($data->location->longitude)){
//                    $newAttendance->latitude = $data->location->latitude;
//                    $newAttendance->longitude = $data->location->longitude;
//                    $newAttendance->save();
//                }
//            }

            //check if on/off request
            if(!empty($data->attendance_type)){
                if($data->attendance_type == 'off'){
                    $attTime = Carbon::parse($data->attendance_time)->format('Y-m-d H:i:s');
                    $newAttendance->date = $attTime;
                    $newAttendance->save();

                    $attendanceData->date_checkout = $attTime;
                    $attendanceData->save();
                }
            }

            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
//                $todayStr = $today->format('l d-m-y');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendances/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employee->first_name . ' ' . $employee->last_name . '_attendanceout_'. $newAttendance->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendance->image_path = $todayStr.'/'.$filename;
                $newAttendance->save();
            }
            return 200;
        }
        catch (\Exception $exception){
//            dd($exception);
            error_log($exception);
            Log::error("Api/AttendanceAbsentController - attendandeOutProcess Error: ". $exception->getMessage());
            return 500;
        }
    }
}

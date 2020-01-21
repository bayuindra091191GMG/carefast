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
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class AttendanceAbsentController extends Controller
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

            if($request->input('cso_id') != "0"){
                $employee = Employee::find($request->input('cso_id'));
            }
            else{
                $userLogin = auth('api')->user();
                $user = User::where('phone', $userLogin->phone)->first();
                $employee = $user->employee;
            }

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $request->input('qr_code');
            $project = Project::where('code', $projectCode)->first();
            if(empty($project)){
                return Response::json("Project Tidak ditemukan!", 400);
            }
            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();

            //if not exist, checkin absent
            if(empty($attendanceData)){
                $newAttendance = AttendanceAbsent::create([
                    'employee_id'   => $employee->id,
                    'project_id'    => $project->id,
                    'is_done'       => 0,
                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'status_id'     => 6
                ]);
                return Response::json("Berhasil Proses Absensi", 200);
            }
            else{
                return Response::json("Sudah pernah melakukan absen di tempat ini", 483);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - absentProcess error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
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
            if($request->input('cso_id') != "0"){
                $employee = Employee::find($request->input('cso_id'));
            }
            else{
                $userLogin = auth('api')->user();
                $user = User::where('phone', $userLogin->phone)->first();
                $employee = $user->employee;
            }

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $request->input('qr_code');
            $project = Project::where('code', $projectCode)->first();
            if(empty($project)){
                return Response::json("Project Tidak ditemukan!", 400);
            }
            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            if(empty($attendanceData)){
                return Response::json("Belum pernah melakukan Absen di tempat ini", 483);
            }

            // checkout absent
            $temp = Carbon::now('Asia/Jakarta');
            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

            $trxDate = Carbon::parse(date_format($attendanceData->created_at, 'j-F-Y H:i:s'));
            $intervalMinute = $now->diffInMinutes($trxDate);
            if($intervalMinute < 60){
                return Response::json("Absensi dilakukan kurang dari 1 jam yang lalu!", 483);
            }
            $attendanceData->is_done = 1;
            $attendanceData->save();

            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $employee->id,
                'project_id'    => $project->id,
                'is_done'       => 1,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 7
            ]);


            return Response::json("Berhasil Proses Absensi", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - absentProcess error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
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

}

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
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employeeLogin = $user->employee;

            $data = json_decode($request->input('attendance_model'));
            if($data->cso_id != "0"){
                $employee = Employee::find($data->cso_id);
            }
            else{
                $employee =  $employeeLogin;
            }

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $data->qr_code;
            $project = Project::where('code', $projectCode)->first();
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
            $schedule = Schedule::where('project_id', $project->id)
                ->where('project_employee_id', $employee->id)
                ->first();
            if(empty($schedule)){
                return Response::json("Tidak pada schedule penempatan", 482);
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
                    'shift_type'    => $schedule->shift_type ?? 0,
                    'is_done'       => 0,
                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'status_id'     => 6,
                    'created_by'     => $employeeLogin->id,
                ]);

                if($request->hasFile('image')){
                    //Upload Image
                    //Creating Path Everyday
                    $today = Carbon::now('Asia/Jakarta');
                    $todayStr = $today->format('l d-m-y');
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

                //Send notification to
                //Customer
                $title = "ICare";
                $body = "Manager sedang meninjau project";
                $data = array(
                    "type_id" => 501,
                );
                //Push Notification to customer App.
                if(!empty($project->customer_id)){
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
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employeeLogin = $user->employee;

            $data = json_decode($request->input('attendance_model'));
            if($data->cso_id != "0"){
                $employee = Employee::find($data->cso_id);
            }
            else{
                $employee =  $employeeLogin;
            }

//            $projectCode = Crypt::decryptString($request->input('qr_code'));
            $projectCode = $data->qr_code;
            $project = Project::where('code', $projectCode)->first();
//            Log::error('Api/AttendanceAbsentController - project code : '. $projectCode);
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

            $schedule = Schedule::where('project_id', $project->id)
                ->where('project_employee_id', $employee->id)
                ->first();
            if(empty($schedule)){
                return Response::json("Tidak pada schedule penempatan", 482);
            }

            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
                ->where('project_id', $project->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            if(empty($attendanceData)){
                return Response::json("Anda Belum Absen Masuk", 483);
            }

            // checkout absent
            $temp = Carbon::now('Asia/Jakarta');
            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

            $trxDate = Carbon::parse(date_format($attendanceData->created_at, 'j-F-Y H:i:s'));
            $intervalMinute = $now->diffInMinutes($trxDate);
            //for development comment this code
//            if($intervalMinute < 300){
//                return Response::json("Absensi dilakukan kurang dari 1 jam yang lalu!", 483);
//            }
            $attendanceData->is_done = 1;
            $attendanceData->date_checkout = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $attendanceData->save();

            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $employee->id,
                'project_id'    => $project->id,
                'shift_type'    => $schedule->shift_type ?? 0,
                'is_done'       => 1,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 7,
                'created_by'     => $employeeLogin->id,
            ]);

            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('l d-m-y');
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

            return Response::json("Berhasil Proses Absen Keluar", 200);
        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceAbsentController - absentProcess error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
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

            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();

            if($projectEmployee->employee_roles_id  == 1){
                //pengecekan harus di ganti dengan pengecekan weeks dan days dan finish
                $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                    ->where('project_employee_id', $projectEmployee->id)
                    ->first();

                if(empty($schedule)){
                    return Response::json("Tidak ada schedule saat ini!", 482);
                }
            }
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->where('start' >= $time)
//                ->where('finish' <= $time)->first();

            //checking checkin with attendance
            $attendance = AttendanceAbsent::where('employee_id', $employee->id)
//                ->where('schedule_id', $schedule->id)
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
                    'attendance_time'      => Carbon::parse($attendance->created_at)->format('d M Y H:i'),
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

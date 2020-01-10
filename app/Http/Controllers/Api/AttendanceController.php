<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Checkout;
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

//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->first();
            //Check Schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();


            //checking kalau leader yg checkin atau cso langsung yg checkin
            $checkinEmployeeId = $projectEmployee->id;
            if($projectEmployee->employee_roles_id != 1){
                $checkinEmployeeId = $data->cso_id;
            }

            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $checkinEmployeeId)
//                ->whereTime('start', '<=', $time)
//                ->whereTime('finish', '>=', $time)
                ->first();
            $place = Place::find($schedule->place_id);

//            $isPlace = Utilities::checkingQrCode($data->qr_code);
//            if(!$isPlace){

            if($place->qr_code != $data->qr_code){
                return Response::json("Tempat yang discan tidak tepat!", 400);
            }

            if($schedule == null){
                return Response::json("Jadwal Tidak ditemukan!", 400);
            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            $message = "";
            if($request->hasFile('image')){
                $newAttendance = Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => $schedule->id,
                    'place_id'      => $place->id,
                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'       => 0,
                    'status_id'     => 6
                ]);

                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('l d-m-y');
                $publicPath = 'storage/checkins/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employee->first_name . ' ' . $employee->last_name . '_checkin_'. $newAttendance->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendance->image_path = $filename;
                $newAttendance->save();
                $message = "Berhasil Check in";
                return Response::json($message, 200);
            }
            else{
                return Response::json('Harus mengupload Gambar!', 400);
            }

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckin error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
//    public function submitCheckin(Request $request)
//    {
//        try{
//
//            $data = json_decode($request->input('checkin_model'));
//            $userLogin = auth('api')->user();
//            $user = User::where('phone', $userLogin->phone)->first();
//            $employee = $user->employee;
//
////            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
////                ->where('project_employee_id', $projectEmployee->id)
////                ->first();
//            //Check Schedule
//            $date = Carbon::now('Asia/Jakarta');
//            $time = $date->format('H:i:s');
//            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
////                ->whereTime('start', '<=', $time)
////                ->whereTime('finish', '>=', $time)
//                ->first();
//            $place = Place::find($schedule->place_id);
//
////            $isPlace = Utilities::checkingQrCode($data->qr_code);
////            if(!$isPlace){
//
//            if($place->qr_code != $data->qr_code){
//                return Response::json("Tempat yang discan tidak tepat!", 400);
//            }
//
//            if($schedule == null){
//                return Response::json("Jadwal Tidak ditemukan!", 400);
//            }
//
//            //Check if Check in or Check out
//            //Check in  = 1
//            //Check out = 2
//            $message = "";
//            if($request->hasFile('image')){
//                $newAttendance = Attendance::create([
//                    'employee_id'   => $employee->id,
//                    'schedule_id'   => $schedule->id,
//                    'place_id'      => $place->id,
//                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
//                    'is_done'       => 0,
//                    'status_id'     => 6
//                ]);
//
//                //Upload Image
//                //Creating Path Everyday
//                $today = Carbon::now('Asia/Jakarta');
//                $todayStr = $today->format('l d-m-y');
//                $publicPath = 'storage/checkins/'. $todayStr;
//                if(!File::isDirectory($publicPath)){
//                    File::makeDirectory(public_path($publicPath), 0777, true, true);
//                }
//
//                $image = $request->file('image');
//                $avatar = Image::make($image);
//                $extension = $image->extension();
//                $filename = $employee->first_name . ' ' . $employee->last_name . '_checkin_'. $newAttendance->id . '_' .
//                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
//                $avatar->save(public_path($publicPath ."/". $filename));
//
//                $newAttendance->image_path = $filename;
//                $newAttendance->save();
//                $message = "Berhasil Check in";
//                return Response::json($message, 200);
//            }
//            else{
//                return Response::json('Harus mengupload Gambar!', 400);
//            }
//
//        }
//        catch (\Exception $ex){
//            Log::error('Api/AttendanceController - submitCheckin error EX: '. $ex);
//            return Response::json("Maaf terjadi kesalahan!", 500);
//        }
//    }
    public function submitCheckout(Request $request)
    {
        try{

//            Log::info('qr_code: '. $request->input('qr_code'));
//            Log::info('notes: '. $request->input('notes'));

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $result = Checkout::checkoutProcess($employee, $request);

            return Response::json($result["desc"], $result["status_code"]);

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckout error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    public function submitCheckoutByLeader(Request $request)
    {
        try{

//            Log::info('qr_code: '. $request->input('qr_code'));
//            Log::info('notes: '. $request->input('notes'));

            $employee = Employee::find($request->input('employee_id'));

            $result = Checkout::checkoutProcess($employee, $request);

            return Response::json($result["desc"], $result["status_code"]);

        }
        catch (\Exception $ex){
            Log::error('Api/AttendanceController - submitCheckout error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

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

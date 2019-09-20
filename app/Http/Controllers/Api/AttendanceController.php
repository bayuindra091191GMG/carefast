<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
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
    public function submit(Request $request)
    {
        try{
            $rules = array(
                'type'      => 'required',
                'qr_code'   => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $userLogin = auth('api')->user();
            $user = User::where('email', $userLogin->email)->first();
            $employee = $user->employee;

            //Check Schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
                ->where('start' >= $time)->where('finish' <= $time)->first();
            $place = Place::find($schedule->place_id);

            if($place->qr_code != Crypt::decryptString($request->input('qr_code'))){
                return Response::json("Tempat yang discan tidak tepat!", 400);
            }

            if($schedule == null){
                return Response::json("Jadwal Tidak ditemukan!", 400);
            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            $message = "";
            if($request->input('type') == 1){
                if($request->hasFile('image')){
                    $newAttendance = Attendance::create([
                        'employee_id'   => $employee->id,
                        'schedule_id'   => $schedule->id,
                        'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'status_id'     => 6
                    ]);

                    //Upload Image
                    //Creating Path Everyday
                    $today = Carbon::now('Asia/Jakarta');
                    $todayStr = $today->format('l d-m-y');
                    if(!File::exists($todayStr)){
                        File::makeDirectory(public_path('storage/checkins/'. $todayStr));
                    }

                    $image = $request->file('image');
                    $avatar = Image::make($image);
                    $extension = $image->extension();
                    $filename = $employee->first_name . ' ' . $employee->last_name . '_checkin_'. $newAttendance->id . '_' .
                        Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                    $avatar->save(public_path('storage/checkins/'. $todayStr . $filename));

                    $newAttendance->image_path = $filename;
                    $newAttendance->save();
                    $message = "Berhasil Check in";
                }
                else{
                    return Response::json([
                        'message'   => 'Harus mengupload Gambar!',
                        'model'     => ''
                    ], 400);
                }
            }
            else if($request->input('type') == 2){
                if($request->input('dac') == null){
                    return Response::json("Tidak ada data Dac yang diterima!", 500);
                }

                $newAttendance = Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => $schedule->id,
                    'place_id'   => $place->id,
                    'date'          => Carbon::now('Asia/Jakarta'),
                    'status_id'     => 7
                ]);

                //Create Attendance Detail
                $submittedDac = $request->input('dac');
                $i=0;

                //Done = 8
                //Not Done =9
                foreach ($schedule->schedule_details as $dac){
                    AttendanceDetail::create([
                        'attendance_id' => $newAttendance->id,
                        'unit'          => $dac->unit->name,
                        'action'        => $dac->action->description,
                        'status_id'     => $submittedDac[$i]->status
                    ]);
                    $i++;
                }

                //Add to the DAC work
                $message = "Berhasil Check out";
            }

            return Response::json([
                'message'   => $message,
                'model'     => ''
            ]);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function checkinChecking(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('email', $userLogin->email)->first();
            $employee = $user->employee;

            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
                ->where('start' >= $time)->where('finish' <= $time)->first();

            //checking checkin with attendance
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->first();

            if(empty($attendance)){
                return Response::json("Jadwal Tidak ditemukan!", 482);
            }
            else{
                $place = Place::find($attendance->place_id);

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

<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\libs;

use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\AutoNumber;
use App\Models\Place;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class AttendanceProcess
{
    public static function checkinProcess($employee, $request, $data){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check in",
            ];
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->first();
            //Check Schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
//                ->whereTime('start', '<=', $time)
//                ->whereTime('finish', '>=', $time)
                ->first();

            if($schedule == null){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Jadwal Tidak ditemukan",
                ];
                return $returnData;
            }
            $place = Place::find($schedule->place_id);

//            $isPlace = Utilities::checkingQrCode($data->qr_code);
//            if(!$isPlace){

            if($place->qr_code != $data->qr_code){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
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

                return $returnData;
            }
            else{
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Harus mengupload Gambar",
                ];
                return $returnData;
            }

        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkoutProcess - checkoutProcess error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
    public static function checkinLeaderProcess($employee, $request, $data){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check in",
            ];
//            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
//                ->where('project_employee_id', $projectEmployee->id)
//                ->first();
            //Check Schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');

            $place = Place::where('qr_code', $data->qr_code)->first();

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            $message = "";
            if($request->hasFile('image')){
                $newAttendance = Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => null,
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

                return $returnData;
            }
            else{
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Harus mengupload Gambar",
                ];
                return $returnData;
            }

        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkoutProcess - checkoutProcess error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }

    public static function checkoutProcess($employee, $request, $type){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            if(empty($attendance)){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tidak ditemukan Jadwal Sesuai",
                ];
                return $returnData;
            }
//            $schedule = Schedule::find($attendance->schedule_id);
            $place = Place::find($attendance->place_id);

//            $isPlace = Utilities::checkingQrCode($request->input('qr_code'));
//            if(!$isPlace){
            if($type == 1){
                if($place->qr_code != $request->input('qr_code')){
                    $returnData = [
                        'status_code'           => 400,
                        'desc'                  => "Tempat yang discan tidak tepat",
                    ];
                    return $returnData;
                }
            }

//            if($schedule == null){
//                $returnData = [
//                    'status_code'           => 400,
//                    'desc'                  => "Jadwal Tidak ditemukan",
//                ];
//                return $returnData;
//            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            if(!$request->filled('schedule_details')){
                $returnData = [
                    'status_code'           => 500,
                    'desc'                  => "Tidak ada data Dac yang diterima",
                ];
                return $returnData;
            }

            $newAttendance = Attendance::create([
                'employee_id'   => $employee->id,
                'schedule_id'   => $attendance->schedule_id,
                'place_id'      => $place->id,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'is_done'       => 1,
                'notes'         => $request->input('notes'),
                'status_id'     => 7
            ]);
            $attendance->is_done = 1;
            $attendance->save();

            //type 1 = checkout cso, type 2 = checkout leader
            if($type == 1){
                //Create Attendance Detail
                $submittedDac = $request->input('schedule_details');
                $i=0;

                //Done = 8
                //Not Done =9
//            $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();
                foreach ($submittedDac as $dac){

                    AttendanceDetail::create([
                        'attendance_id' => $newAttendance->id,
                        'unit'          => $dac['object_name'],
                        'action'        => $dac['action_name'],
                        'status_id'     => $dac['status'],
                        'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    ]);
                    $i++;
                }
            }

            //get location
//            $location = $request->input('location');
//            $lat = $location["latitude"];
//            $long = $location["longitude"];

            return $returnData;
        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkoutProcess - checkoutProcess error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
    public static function leaderAssessment($employee, $request, $type){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('status_id', 7)
                ->where('is_done', 1)
                ->first();
            if(empty($attendance)){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tidak ditemukan checkout",
                ];
                return $returnData;
            }
            $place = Place::find($attendance->place_id);

            if($type == 1){
                if($place->qr_code != $request->input('qr_code')){
                    $returnData = [
                        'status_code'           => 400,
                        'desc'                  => "Tempat yang discan tidak tepat",
                    ];
                    return $returnData;
                }
            }

            if(!$request->filled('schedule_details')){
                $returnData = [
                    'status_code'           => 500,
                    'desc'                  => "Tidak ada data Dac yang diterima",
                ];
                return $returnData;
            }

            $attendance->assessment_leader = $request->input('assessment_leader');
            $attendance->save();

            return $returnData;
        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkoutProcess - checkoutProcess error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
}

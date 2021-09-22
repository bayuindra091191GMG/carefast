<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\libs;

use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\AttendanceDetail;
use App\Models\AutoNumber;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
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
                    'status_code'           => 482,
                    'desc'                  => "Jadwal Tidak ditemukan",
                ];
                return $returnData;
            }

            //Check Place
            $place = Place::find($schedule->place_id);
//            $isPlace = Utilities::checkingQrCode($data->qr_code);
//            if(!$isPlace){

            if($place->qr_code != $data->qr_code){
                $returnData = [
                    'status_code'           => 483,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            $message = "";
            if($request->hasFile('image')){
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('status_id', 6)
                    ->where('schedule_detail_id', $data->schedule_detail_id)
                    ->where('is_done', 0)
                    ->first();
                if(!empty($attendance)){
                    $returnData = [
                        'status_code'           => 452,
                        'desc'                  => "Sudah Pernah melakukan Checkin",
                    ];
                }

                $newAttendance = Attendance::create([
                    'employee_id'           => $employee->id,
                    'schedule_id'           => $schedule->id,
                    'schedule_detail_id'    => $data->schedule_detail_id,
                    'place_id'              => $place->id,
                    'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'               => 0,
                    'assessment_leader'     => 0,
                    'status_id'             => 6
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

                $newAttendance->image_path = $todayStr.'/'.$filename;
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
                    'status_code'           => 482,
                    'desc'                  => "Jadwal Tidak ditemukan",
                ];
                return $returnData;
            }

            //Check Place
            $place = Place::where('qr_code', $data->qr_code)->first();
//            $isPlace = Utilities::checkingQrCode($data->qr_code);
//            if(!$isPlace){

            if($place->qr_code != $data->qr_code){
                $returnData = [
                    'status_code'           => 483,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

            $message = "";
            if($request->hasFile('image')){
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('status_id', 6)
                    ->where('schedule_detail_id', $data->schedule_detail_id)
                    ->where('is_done', 0)
                    ->first();
                if(!empty($attendance)){
                    $returnData = [
                        'status_code'           => 452,
                        'desc'                  => "Sudah Pernah melakukan Checkin",
                    ];
                }

                $newAttendance = Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => null,
                    'schedule_detail_id'    => $data->schedule_detail_id,
                    'place_id'      => $place->id,
                    'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'       => 0,
                    'assessment_leader'     => 0,
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

                $newAttendance->image_path = $todayStr.'/'.$filename;
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

    public static function checkoutProcess($employee, $request, $type, $data){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('status_id', 6)
//                ->where('schedule_detail_id', $request->input('schedule_detail_id'))
                ->where('is_done', 0)
                ->first();
            if(empty($attendance)){
                $returnData = [
                    'status_code'           => 482,
                    'desc'                  => "Belum melakukan Checkin",
                ];
                return $returnData;
            }
//            $schedule = Schedule::find($attendance->schedule_id);
            $place = Place::find($attendance->place_id);

//            $isPlace = Utilities::checkingQrCode($request->input('qr_code'));
//            if(!$isPlace){
            if($type == 1){
                Log::info('Api/AttendanceController - $data->qr_code: '. $data->qr_code);
                if($place->qr_code != $data->qr_code){
                    $returnData = [
                        'status_code'           => 483,
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

            if(empty($data->schedule_detail)){
                $returnData = [
                    'status_code'           => 500,
                    'desc'                  => "Tidak ada data Dac yang diterima",
                ];
                return $returnData;
            }
            $submittedDac = $data->schedule_detail;
//            Log::info('Api/AttendanceController - $data->schedule_detail->is_action_checked: '. $submittedDac->is_action_checked);

            $attendance->is_done = 1;
            $attendance->save();

            //type 1 = checkout cso, type 2 = checkout leader
//            if($type == 1){
//                //Create Attendance Detail
//                $submittedDac = $data->schedule_detail;
//                $i=0;
//
//                //Done = 8
//                //Not Done =9
////            $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();
//                foreach ($submittedDac as $dac){
//
//                    AttendanceDetail::create([
//                        'attendance_id' => $newAttendance->id,
//                        'unit'          => $dac['object_name'],
//                        'action'        => $dac['action_name'],
//                        'status_id'     => $dac['status'],
//                        'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
//                    ]);
//                    $i++;
//                }
//            }

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

    public static function checkinProcessV2($employee, $request, $data){
        try{
            Log::error('libs/AttendanceProcess/checkinProcessV2 - data : '. json_encode($data));
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check in",
            ];

            $place = Place::where('qr_code', $data->qr_code)->first();
            if(empty($place)){
                $returnData = [
                    'status_code'           => 483,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

            $message = "";
//            if($request->hasFile('image')){
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('status_id', 6)
//                    ->where('schedule_detail_id', $data->schedule_detail_id)
                    ->where('is_done', 0)
                    ->first();
                if(!empty($attendance)){
                    $returnData = [
                        'status_code'           => 452,
                        'desc'                  => "Sudah Pernah melakukan Checkin",
                    ];
                    return $returnData;
                }
            $attendanceData = AttendanceAbsent::where('employee_id', $employee->id)
//                ->where('project_id', $data->project_id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            $result = 500;
            if(empty($attendanceData)){
                return Response::json("Anda Belum Absen Masuk", 483);
            }

                $newAttendance = Attendance::create([
                    'employee_id'           => $employee->id,
                    'schedule_id'           => null,
                    'schedule_detail_id'    => null,
                    'place_id'              => empty($place) ? 0 : $place->id,
                    'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'               => 0,
                    'assessment_leader'     => 0,
                    'status_id'             => 6
                ]);

                //Upload Image
                //Creating Path Everyday
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
//                $newAttendance->image_path = $todayStr.'/'.$filename;
//                $newAttendance->save();

                return $returnData;
//            }
//            else{
//                $returnData = [
//                    'status_code'           => 400,
//                    'desc'                  => "Harus mengupload Gambar",
//                ];
//                return $returnData;
//            }

        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkinProcessV2 - checkinProcessV2 error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
    public static function checkinLeaderProcessV2($employee, $request, $data){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check in",
            ];

            //Check Place
            $place = Place::where('qr_code', $data->qr_code)->first();

            $message = "";
//            if($request->hasFile('image')){
                $attendance = Attendance::where('employee_id', $employee->id)
                    ->where('status_id', 6)
//                    ->where('schedule_detail_id', $data->schedule_detail_id)
                    ->where('is_done', 0)
                    ->first();
                if(!empty($attendance)){
                    $returnData = [
                        'status_code'           => 452,
                        'desc'                  => "Sudah Pernah melakukan Checkin",
                    ];
                    return $returnData;
                }

                $newAttendance = Attendance::create([
                    'employee_id'           => $employee->id,
                    'schedule_id'           => null,
                    'schedule_detail_id'    => null,
                    'place_id'              => empty($place) ? 0 : $place->id,
                    'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'               => 0,
                    'assessment_leader'     => 0,
                    'status_id'             => 6
                ]);

                //Upload Image
                //Creating Path Everyday
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
//                $newAttendance->image_path = $todayStr.'/'.$filename;
//                $newAttendance->save();

                return $returnData;
//            }
//            else{
//                $returnData = [
//                    'status_code'           => 400,
//                    'desc'                  => "Harus mengupload Gambar",
//                ];
//                return $returnData;
//            }

        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkinLeaderProcessV2 - checkinLeaderProcessV2 error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }

    public static function checkoutProcessV2($employee, $request, $type, $data){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];
//            Log::info('submitCheckout - checkoutProcessV2 : data checkin_model: '. json_encode($data));

            $place = Place::where('qr_code', $data->qr_code)->first();
            if(empty($place)){
                $returnData = [
                    'status_code'           => 483,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('status_id', 6)
                ->where('place_id', $place->id)
//                ->where('schedule_detail_id', $request->input('schedule_detail_id'))
                ->where('is_done', 0)
                ->first();
            if(empty($attendance)){
                $returnData = [
                    'status_code'           => 482,
                    'desc'                  => "Belum melakukan Checkin",
                ];
                return $returnData;
            }

            $place = Place::where('qr_code', $data->qr_code)->first();
//            $place = Place::find($attendance->place_id);

            $attendance->is_done = 1;
            $attendance->date_checkout = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $attendance->save();

            $newAttendance = Attendance::create([
                'employee_id'           => $employee->id,
                'schedule_id'           => null,
                'schedule_detail_id'    => null,
                'place_id'              => empty($place) ? 0 : $place->id,
                'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'is_done'               => 1,
                'assessment_leader'     => 0,
                'status_id'             => 7,
                'notes'                 => $data->notes,
            ]);

            return $returnData;
        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/checkoutProcessV2 - checkoutProcessV2 error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }

    public static function leaderAssessment($employee, $request){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];


            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
//                ->whereTime('start', '<=', $time)
//                ->whereTime('finish', '>=', $time)
                ->first();

            if($schedule == null){
                $returnData = [
                    'status_code'           => 482,
                    'desc'                  => "Jadwal Tidak ditemukan",
                ];
                return $returnData;
            }
            $place = Place::find($schedule->place_id);

            if($place->qr_code != $request->input('qr_code')){
                $returnData = [
                    'status_code'           => 483,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

            if(!$request->filled('schedule_details')){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tidak ada data Dac yang diterima",
                ];
                return $returnData;
            }
            $submittedDetails = $request->input('schedule_details');
//            Log::info('libs/AttendanceProcess/leaderAssessment $submittedDetails: '. gettype($submittedDetails));
//            Log::info('libs/AttendanceProcess/leaderAssessment $submittedDetails: '. json_decode($submittedDetails));

            foreach ($submittedDetails as $submittedDetail){
//            Log::info('libs/AttendanceProcess/leaderAssessment schedule_detail_id: '. $submittedDetail["schedule_detail_id"]);
                //new attendance checkin
                $newAttendanceCheckin = Attendance::create([
                    'employee_id'           => $employee->id,
                    'schedule_id'           => $schedule->id,
                    'schedule_detail_id'    => $submittedDetail["schedule_detail_id"],
                    'place_id'              => $place->id,
                    'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'               => 1,
                    'assessment_leader'     => 0,
                    'image_path'            => 'default.png',
                    'status_id'             => 6
                ]);


                $newAttendanceCheckout = Attendance::create([
                    'employee_id'           => $employee->id,
                    'schedule_id'           => $schedule->id,
                    'schedule_detail_id'    => $submittedDetail["schedule_detail_id"],
                    'place_id'              => $place->id,
                    'date'                  => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'is_done'               => 1,
                    'assessment_leader'     => 1,
                    'notes'                 => '',
                    'is_action_checked'     => 1,
                    'status_id'             => 7
                ]);

                $newAttendanceCheckout->assessment_score = $submittedDetail["assessment_score"];
                $newAttendanceCheckout->assessment_notes = $submittedDetail["assessment_notes"];
                $newAttendanceCheckout->save();
            }


            return $returnData;
        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/leaderAssessment error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
    //old function of leaderassessment
//    public static function leaderAssessment($employee, $request, $type){
//        try{
//            $returnData = [
//                'status_code'           => 200,
//                'desc'                  => "Berhasil Check out",
//            ];
//
//            $submittedDac = $request->input('schedule_detail');
//
//            $attendance = Attendance::where('employee_id', $employee->id)
//                ->where('status_id', 7)
//                ->where('schedule_detail_id', $submittedDac['schedule_detail_id'])
//                ->where('is_done', 0)
//                ->first();
//            if(empty($attendance)){
//                $returnData = [
//                    'status_code'           => 482,
//                    'desc'                  => "Tidak ditemukan checkout",
//                ];
//                return $returnData;
//            }
//            $place = Place::find($attendance->place_id);
//
//            if($type == 1){
//                if($place->qr_code != $request->input('qr_code')){
//                    $returnData = [
//                        'status_code'           => 483,
//                        'desc'                  => "Tempat yang discan tidak tepat",
//                    ];
//                    return $returnData;
//                }
//            }
//
//            if(!$request->filled('schedule_detail')){
//                $returnData = [
//                    'status_code'           => 400,
//                    'desc'                  => "Tidak ada data Dac yang diterima",
//                ];
//                return $returnData;
//            }
//
//            $attendance->is_done = 1;
//            $attendance->assessment_leader = 1;
//            $attendance->assessment_score = $submittedDac['assessment_score'];
//            $attendance->assessment_notes = $request->input('notes');
//            $attendance->save();
//
//
//            return $returnData;
//        }
//        catch (\Exception $ex){
//            Log::error('libs/AttendanceProcess/leaderAssessment error EX: '. $ex);
//
//            $returnData = [
//                'status_code'           => 500,
//                'desc'                  => "Maaf terjadi kesalahan",
//            ];
//            return $returnData;
//        }
//
//    }

    /**
     * Function to get attendance validation Data (counting per cso valid or not valid attendance.
     *
     * @param
     * @return string
     */
    public static function DownloadAttendanceValidationProcess($startDate, $endDate){
        try{

//            $data = "Employee Code\tEmployee Name\tEmployee Phone\tProject\tTotal Valid Absensi\tTotal Invalid Absensi\n";

            // checking attendance START
            $allEmployee = Employee::where('status_id', 1)->where('id', '>', 29)->get();
            $list = collect();
            $ct=0;

            //get all employee
            foreach ($allEmployee as $employee){
                if($ct%1500 == 0){
                    sleep(5);
                }
//                $data .= $employee->code."\t";
//                $data .= $employee->first_name." ".$employee->last_name."\t";

                $user = DB::table('users')
                    ->select('phone', 'status_id')
                    ->where('employee_id', $employee->id)
                    ->first();
//                $data .= "'".$user->phone."\t";

                $projectEmployeeCount = ProjectEmployee::where('employee_id', $employee->id)->count();

                //pengecekan apakah cso ada di 1 project atau banyak project
                if($projectEmployeeCount == 1){
                    $projectEmployee = DB::table('project_employees')
                        ->select('project_id')
                        ->where('employee_id', $employee->id)
                        ->first();
                }
                else{
                    $projectEmployee = DB::table('project_employees')
                        ->select('project_id')
                        ->where('employee_id', $employee->id)
                        ->where('status_id', 1)
                        ->first();
                }

                $projectName = "-";
                $projectCode = "-";
                $projectCity = "-";
                if(!empty($projectEmployee)){
                    $project = DB::table('projects')
                        ->select('name', 'code', 'city')
                        ->where('id', $projectEmployee->project_id)
                        ->first();
//                    $project = Project::where('id', $projectEmployee->project_id)
//                        ->first();
                    $projectName = $project->name;
                    $projectCode = $project->code;
                    $projectCity = $project->city ?? "-";
                }

                $countA = "0";
                $countB = "0";
//                $description = "Tidak ada absen";

//                if(DB::table('attendance_absents')
//                    ->where('employee_id', $employee->id)
//                    ->exists()){
//
//                    $countADB = DB::table('attendance_absents')
//                        ->select('id')
//                        ->where('employee_id', $employee->id)
//                        ->where('status_id', 6)
//                        ->where('is_done', 1)
//                        ->whereBetween('attendance_absents.date', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
//                        ->count();
//                    $countA = $countADB;
//                    $countBDB = DB::table('attendance_absents')
//                        ->select('id')
//                        ->where('employee_id', $employee->id)
//                        ->where('status_id', 6)
//                        ->where('is_done', 0)
//                        ->whereBetween('attendance_absents.date', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
//                        ->count();
//                    $countB = $countBDB;
//                }

                $countADB = DB::table('attendance_absents')
                    ->select('id')
                    ->where('employee_id', $employee->id)
                    ->where('status_id', 6)
                    ->where('is_done', 1)
                    ->whereBetween('attendance_absents.date', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
                    ->count();
                $countA = $countADB;
                $countBDB = DB::table('attendance_absents')
                    ->select('id')
                    ->where('employee_id', $employee->id)
                    ->where('status_id', 6)
                    ->where('is_done', 0)
                    ->whereBetween('attendance_absents.date', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
                    ->count();
                $countB = $countBDB;

                $singleData = ([
                    'Employee_Code'         => $employee->code,
                    'Employee_Name'         => $employee->first_name." ".$employee->last_name,
                    'Employee_Phone'        => $user->phone != "" ? $user->phone : $employee->phone,
                    'Employee_Status'       => $user->status_id == 0 ? "NON AKTIF" : "AKTIF",
                    'Project_Code'          => $projectCode,
                    'Project_Name'          => $projectName,
                    'Project_City'          => $projectCity,
                    'Total_Valid_Absensi'   => $countA,
                    'Total_Invalid_Absensi' => $countB,
                ]);
                $list->push($singleData);
                $ct++;
            }
            return $list;
        }
        catch (\Exception $ex){
//            dd($ex);
            Log::error('libs/AttendanceProcess/DownloadAttendanceValidationProcess  error EX: '. $ex);
            $list = collect();

            return $list;
        }

    }

    /**
     * Function to get attendance Data.
     *
     * @param $startDate, $endDate
     * @return  \Illuminate\Support\Collection
     */
    public static function DownloadAttendanceProcess($startDate, $endDate){
        try{

            $attendanceAbsents = DB::table('attendance_absents')
                ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                ->select('attendance_absents.id as attendance_absent_id',
                    'attendance_absents.shift_type as shift_type',
                    'attendance_absents.is_done as is_done',
                    'attendance_absents.date as date',
                    'attendance_absents.date_checkout as date_checkout',
                    'attendance_absents.created_at as created_at',
                    'attendance_absents.type as atttendance_type',
                    'attendance_absents.description as description',
                    'employees.id as employee_id',
                    'employees.code as employee_code',
                    'employees.first_name as employee_first_name',
                    'employees.last_name as employee_last_name',
                    'employees.phone as employee_phone',
                    'projects.name as project_name',
                    'projects.code as project_code')
                ->whereBetween('attendance_absents.created_at', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
                ->where('attendance_absents.status_id',6)
                ->orderBy('attendance_absents.employee_id')
                ->get();

            $now = Carbon::now('Asia/Jakarta');
            $list = collect();
            $ct = 0;
            foreach($attendanceAbsents as $attendanceAbsent){
                if($ct % 1000 == 0){
                    sleep(40);
                }
                $userPhone = DB::table('users')
                    ->select('users.phone as user_phone')
                    ->where('users.employee_id', $attendanceAbsent->employee_id)
                    ->first();

                $attStatus = "U";
                $dataCheckout = "-";
                if($attendanceAbsent->is_done == 0){
                    $attStatus = "A";
                }
                else{
                    if(!empty($attendanceAbsent->date_checkout)){
                        $attStatus = "H";
//                        $attendanceOut = $attendanceAbsent->date_checkout->format('Y-m-d H:i:s');
                        $dataCheckout = $attendanceAbsent->date_checkout;
                    }
                    else{
                        $attStatus = "A";
                    }
                }
                $createdAt = Carbon::parse($attendanceAbsent->created_at);
                $singleData = ([
                    'Project Code' => $attendanceAbsent->project_code,
                    'Project Name' => $attendanceAbsent->project_name,
                    'Employee Code' => $attendanceAbsent->employee_code,
                    'Employee Name' => $attendanceAbsent->employee_first_name." ".$attendanceAbsent->employee_last_name,
                    'Employee Phone' => $userPhone->user_phone ?? "",
                    'Transaction Date' => $createdAt,
                    'Shift' => $attendanceAbsent->shift_type,
                    'Attendance In' => $attendanceAbsent->date,
                    'Attendance Out' => $dataCheckout,
                    'Attendance Status' => $attStatus,
                    'Description' => $attendanceAbsent->description,
                ]);
                $list->push($singleData);
                $ct++;
            }
            return $list;
        }
        catch (\Exception $ex){
            Log::error('libs/AttendanceProcess/DownloadAttendanceProcess  error EX: '. $ex);

            $list = collect();

            return $list;
        }

    }

    /**
     * Function to get attendance Data Version 2, with sick leaves, leave permission or overtime.
     *
     * @param $startDate, $endDate
     * @return  \Illuminate\Support\Collection
     */
    public static function DownloadAttendanceProcessV2($project, $startDate, $startDateMonth, $endDate, $endDateMonth){
        try{
            $dataModel = collect();
            $projectEmployees = ProjectEmployee::where('project_id', $project->id)
                ->where('status_id', 1)
                ->where('employee_roles_id', '<', 5)
                ->orderBy('employee_roles_id')
                ->get();
            foreach ($projectEmployees as $projectEmployee){
                $employeeSchedule = EmployeeSchedule::where('employee_id', $projectEmployee->employee_id)->first();

                //check kalau sudah pernah dibuat jadwal dari backend
                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){
                        $days = explode(';', $employeeSchedule->day_status);
                        $ctBefore = 0;
                        $ctCurrent = 0;
                        foreach($days as $day){
                            if(!empty($day)){
                                $date = explode(':', $day);

                                $status = "A";
                                $attendanceIn = "";
                                $attendanceOut = "";
                                $createdAt = $startDateMonth.'-'.$date[0];
                                $description = "";

                                //kalau scehdulenya tipenya O = Off
                                if($date[1] == 'O'){
                                    $status = "O";
                                    $attendanceIn = $createdAt;

                                    $projectCSOModel = ([
                                        'employeeId'        => $employeeSchedule->employee_id,
                                        'employeeCode'      => $employeeSchedule->employee_code,
                                        'transDate'         => $createdAt,
                                        'shiftCode'         => 1,
                                        'attendanceIn'      => $attendanceIn,
                                        'attendanceOut'     => $attendanceOut,
                                        'attendanceStatus'  => $status,
                                        'description'       => $description,
                                    ]);
                                    $dataModel->push($projectCSOModel);
                                }
                                //kalau scehdulenya tipenya H = masuk
                                else{
//                                    dd($startDateMonth.'-'.$date[0].' 00:00:00', $endDateMonth.'-'.$date[0].' 23:59:00');

                                    //validate the day
                                    $selectedStartDate = $startDateMonth.'-'.$date[0];
                                    $selectedEndDate = $startDateMonth.'-'.$date[0];
                                    if($days[$ctBefore][0] > $days[$ctCurrent][0]){
                                        $createdAt = $endDateMonth.'-'.$date[0];
                                        $selectedStartDate = $endDateMonth.'-'.$date[0];
                                        $selectedEndDate = $endDateMonth.'-'.$date[0];
                                    }

                                    //get list attendance by
                                    $attendanceAbsents = DB::table('attendance_absents')
                                        ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                                        ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                                        ->select('attendance_absents.id as attendance_absent_id',
                                            'attendance_absents.shift_type as shift_type',
                                            'attendance_absents.is_done as is_done',
                                            'attendance_absents.date as date',
                                            'attendance_absents.date_checkout as date_checkout',
                                            'attendance_absents.created_at as created_at',
                                            'attendance_absents.type as attendance_type',
                                            'attendance_absents.description as description',
                                            'employees.id as employee_id',
                                            'employees.code as employee_code',
                                            'projects.name as project_name',
                                            'projects.code as project_code')
                                        ->whereBetween('attendance_absents.date',
                                            array($selectedStartDate.' 00:00:00', $selectedEndDate.' 23:59:00'))
                                        ->where('attendance_absents.project_id', $project->id)
                                        ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                                        ->where('attendance_absents.status_id',6)
                                        ->get();


                                    if($attendanceAbsents->count() < 1){
                                        $projectCSOModel = ([
                                            'employeeId'        => $projectEmployee->employee->id,
                                            'employeeCode'      => $projectEmployee->employee->code,
                                            'transDate'         => $createdAt,
                                            'shiftCode'         => 1,
                                            'attendanceIn'      => "",
                                            'attendanceOut'     => "",
                                            'attendanceStatus'  => "A",
                                            'description'       => "Scheduled but attendance not found",
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                        continue;
                                    }
                                    else{
                                        $ct = 0;
                                        foreach ($attendanceAbsents as $attendanceAbsent){
                                            if($attendanceAbsent->attendance_type == "NORMAL"){
                                                if($attendanceAbsent->is_done == 0){
                                                    $status = "A";
                                                    $description = "Belum Melakukan checkout";
                                                }
                                                else{
                                                    if(!empty($attendanceAbsent->date_checkout)){
                                                        $status = "H";
                                                        $attendanceOut = $attendanceAbsent->date_checkout;
                                                    }
                                                    else{
                                                        $status = "A";
                                                        $description = "Belum Melakukan checkout";
                                                    }
                                                }
                                            }
                                            else{
                                                $status = "A";
                                                $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                            }
                                            $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                            $attendanceIn = $attendanceAbsent->date;

                                            if($status != "A"){
                                                //validasi tipe H, minimal harus 8 jam (480 menit)
//                                              $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                                $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                              $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                                $trxDate = Carbon::parse($attendanceAbsent->date);
//                                              $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                                $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                                if($intervalMinute >= 480){
                                                    $projectCSOModel = ([
                                                        'employeeId'        => $projectEmployee->employee->id,
                                                        'employeeCode'      => $projectEmployee->employee->code,
                                                        'transDate'         => $createdAt->format('Y-m-d'),
                                                        'shiftCode'         => 1,
                                                        'attendanceIn'      => $attendanceIn,
                                                        'attendanceOut'     => $attendanceOut,
                                                        'attendanceStatus'  => $status,
                                                        'description'       => $description,
                                                    ]);
                                                    $dataModel->push($projectCSOModel);
                                                }
                                            }
                                            else{
                                                $projectCSOModel = ([
                                                    'employeeId'        => $projectEmployee->employee->id,
                                                    'employeeCode'      => $projectEmployee->employee->code,
                                                    'transDate'         => $createdAt->format('Y-m-d'),
                                                    'shiftCode'         => 1,
                                                    'attendanceIn'      => $attendanceIn,
                                                    'attendanceOut'     => $attendanceOut,
                                                    'attendanceStatus'  => $status,
                                                    'description'       => $description,
                                                ]);
                                                $dataModel->push($projectCSOModel);
                                            }
                                            $ct++;
                                        }
                                    }
                                }

                                $ctBefore = $ctCurrent;
                                $ctCurrent++;
                            }
                        }
                    }
                }
                //kalau belum pernah dibuat jadwal dari backend, maka pakai cara lama utk memproses data
                else{
                    $attendanceAbsents = DB::table('attendance_absents')
                        ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                        ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                        ->select('attendance_absents.id as attendance_absent_id',
                            'attendance_absents.shift_type as shift_type',
                            'attendance_absents.is_done as is_done',
                            'attendance_absents.date as date',
                            'attendance_absents.date_checkout as date_checkout',
                            'attendance_absents.created_at as created_at',
                            'attendance_absents.type as attendance_type',
                            'attendance_absents.description as description',
                            'employees.id as employee_id',
                            'employees.code as employee_code',
                            'projects.name as project_name',
                            'projects.code as project_code')
                        ->whereBetween('attendance_absents.date', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
                        ->where('attendance_absents.project_id', $project->id)
                        ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                        ->where('attendance_absents.status_id',6)
                        ->get();
//                    dd($projectEmployee->employee_id, $attendanceAbsents);

                    if($attendanceAbsents->count() < 1){
//                        $projectCSOModel = ([
//                            'employeeId'        => $projectEmployee->employee_id,
//                            'employeeCode'      => $projectEmployee->employee_code,
//                            'transDate'         => "",
//                            'shiftCode'         => 1,
//                            'attendanceIn'      => "",
//                            'attendanceOut'     => "",
//                            'attendanceStatus'   => "U",
//                            'description'       => "Data Not found",
//                        ]);
//                        $dataModel->push($projectCSOModel);
                        continue;
                    }
                    else{
                        foreach ($attendanceAbsents as $attendanceAbsent){
                            $status = "A";
                            $attendanceOut = "";
                            $description = "By Attendance data only, no schedule provided";
                            if($attendanceAbsent->is_done == 0){
                                $status = "A";
                                $description = "Belum Melakukan checkout";
                            }
                            else{
                                if(!empty($attendanceAbsent->date_checkout)){
                                    if($attendanceAbsent->attendance_type == 'NORMAL'){
                                        $status = "H";
                                        $attendanceOut = $attendanceAbsent->date_checkout;
                                    }
                                    else{
                                        $status = "A";
                                        $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                    }
                                }
                                else{
                                    $status = "A";
                                    $description = "Belum Melakukan checkout";
                                }
                            }

                            if($status != "A"){
                                //validasi tipe H, minimal harus 8 jam
//                                $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                $trxDate = Carbon::parse($attendanceAbsent->date);
//                                $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                if($intervalMinute >= 480){
                                    $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                    $projectCSOModel = ([
                                        'employeeId'        => $attendanceAbsent->employee_id,
                                        'employeeCode'      => $attendanceAbsent->employee_code,
                                        'transDate'         => $createdAt->format('Y-m-d'),
                                        'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                        'attendanceIn'      => $attendanceAbsent->date,
                                        'attendanceOut'     => $attendanceOut,
                                        'attendanceStatus'  => $status,
                                        'description'       => $description,
                                    ]);
                                    $dataModel->push($projectCSOModel);
                                }
                            }
                            else{
                                $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                $projectCSOModel = ([
                                    'employeeId'        => $attendanceAbsent->employee_id,
                                    'employeeCode'      => $attendanceAbsent->employee_code,
                                    'transDate'         => $createdAt->format('Y-m-d'),
                                    'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                    'attendanceIn'      => $attendanceAbsent->date,
                                    'attendanceOut'     => $attendanceOut,
                                    'attendanceStatus'  => $status,
                                    'description'       => $description,
                                ]);
                                $dataModel->push($projectCSOModel);
                            }
                        }
                    }

                }
            }
            return $dataModel;
        }
        catch (\Exception $ex){
//            Log::error('libs/AttendanceProcess/DownloadAttendanceProcessV2  error EX: '. $ex);
            Log::channel('in_sys')
                ->error('libs/AttendanceProcess/DownloadAttendanceProcessV2  error EX: '. $ex);

            $dataModel = collect();

            return $dataModel;
        }

    }


    /**
     * Function to get attendance Data Version 3, with sick leaves, leave permission or overtime, and change logic for employee schedule
     *
     * @param $startDate, $endDate
     * @return  \Illuminate\Support\Collection
     */
    public static function DownloadAttendanceProcessV3($project, $startDate, $startDateMonth, $endDate, $endDateMonth){
        try{
            $dataModel = collect();
//            $projectEmployees = ProjectEmployee::where('project_id', $project->id)
//                ->where('status_id', 1)
//                ->where('employee_roles_id', '<', 5)
//                ->orderBy('employee_roles_id')
//                ->get();
            $projectEmployees = DB::table('project_employees')
                ->join('employees', 'project_employees.employee_id', '=', 'employees.id')
                ->select('project_employees.id as project_employee_id',
                    'employees.id as employee_id',
                    'employees.code as employee_code')
                ->where('project_employees.project_id', $project->id)
                ->where('project_employees.status_id',1)
                ->where('project_employees.employee_roles_id', '<', 5)
                ->orderBy('project_employees.employee_roles_id')
                ->get();


            //ambil tanggal dari start_date ke end_date
            $period = CarbonPeriod::create($startDate, $endDate);
            $dayPeriods = [];
            $monthPeriods = [];
            foreach ($period as $date) {
                // formated j = The day of the month without leading zeros (1 to 31)
                array_push($dayPeriods, $date->format('j'));
                array_push($monthPeriods, $date->format('m'));
            }

            foreach ($projectEmployees as $projectEmployee){
//                $employeeSchedule = EmployeeSchedule::where('employee_id', $projectEmployee->employee_id)->first();
                $employeeSchedule = DB::table('employee_schedules')
                    ->select('day_status','employee_id', 'employee_code')
                    ->where('employee_id', $projectEmployee->employee_id)
                    ->first();

                //check kalau sudah pernah dibuat jadwal dari backend
                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){

                        //convert day status to collection
                        $days = explode(';', $employeeSchedule->day_status);
                        $dayCollections = collect();
                        foreach($days as $day){
                            if(empty($day)) continue;
                            $date = explode(':', $day);
                            $item = ([
                                'day'        => $date[0],
                                'status'      => $date[1]
                            ]);
                            $dayCollections->push($item);
                        }

                        $status = "A";
                        $attendanceIn = "";
                        $attendanceOut = "";
                        $description = "";
                        //ambil tanggal dan banding kan dengan database
                        $monthCt = 0;
                        foreach ($dayPeriods as $dayPeriod){
                            $dayCollection = $dayCollections->where('day', $dayPeriod)->first();
                            $dayObject = (object)$dayCollection;


                            $selectedMonth = $startDateMonth;
                            if(str_contains($endDateMonth, $monthPeriods[$monthCt])){
                                $selectedMonth = $endDateMonth;
                            }

                            if($dayObject->day < 10){
                                $createdAt = $selectedMonth.'-0'.$dayObject->day;
                            }
                            else{
                                $createdAt = $selectedMonth.'-'.$dayObject->day;
                            }

                            //kalau scehdulenya tipenya O = Off
                            if($dayObject->status == 'O'){
                                $attendanceAbsents = DB::table('attendance_absents')
                                    ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                                    ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                                    ->select('attendance_absents.id as attendance_absent_id',
                                        'attendance_absents.shift_type as shift_type',
                                        'attendance_absents.is_done as is_done',
                                        'attendance_absents.date as date',
                                        'attendance_absents.date_checkout as date_checkout',
                                        'attendance_absents.created_at as created_at',
                                        'attendance_absents.type as attendance_type',
                                        'attendance_absents.description as description',
                                        'employees.id as employee_id',
                                        'employees.code as employee_code',
                                        'projects.name as project_name',
                                        'projects.code as project_code')
                                    ->whereBetween('attendance_absents.date',
                                        array($createdAt.' 00:00:00', $createdAt.' 23:59:00'))
                                    ->where('attendance_absents.project_id', $project->id)
                                    ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                                    ->where('attendance_absents.status_id',6)
                                    ->get();
                                $status = "O";
                                $attendanceIn = $createdAt;

                                $projectCSOModel = ([
                                    'employeeId'        => $employeeSchedule->employee_id,
                                    'employeeCode'      => $employeeSchedule->employee_code,
                                    'transDate'         => $createdAt,
                                    'shiftCode'         => 1,
                                    'attendanceIn'      => $attendanceIn.' 00:00:00',
                                    'attendanceOut'     => "",
                                    'attendanceStatus'  => $status,
                                    'description'       => $description,
                                ]);
                                $dataModel->push($projectCSOModel);
                            }
                            //kalau scehdulenya tipenya H = masuk
                            else{
                                //get list attendance by
                                $attendanceAbsents = DB::table('attendance_absents')
                                    ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                                    ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                                    ->select('attendance_absents.id as attendance_absent_id',
                                        'attendance_absents.shift_type as shift_type',
                                        'attendance_absents.is_done as is_done',
                                        'attendance_absents.date as date',
                                        'attendance_absents.date_checkout as date_checkout',
                                        'attendance_absents.created_at as created_at',
                                        'attendance_absents.type as attendance_type',
                                        'attendance_absents.description as description',
                                        'employees.id as employee_id',
                                        'employees.code as employee_code',
                                        'projects.name as project_name',
                                        'projects.code as project_code')
                                    ->whereBetween('attendance_absents.date',
                                        array($createdAt.' 00:00:00', $createdAt.' 23:59:00'))
                                    ->where('attendance_absents.project_id', $project->id)
                                    ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                                    ->where('attendance_absents.status_id',6)
                                    ->get();


                                if($attendanceAbsents->count() < 1){
                                    $status = "A";
                                    $projectCSOModel = ([
                                        'employeeId'        => $projectEmployee->employee_id,
                                        'employeeCode'      => $projectEmployee->employee_code,
                                        'transDate'         => $createdAt,
                                        'shiftCode'         => 1,
                                        'attendanceIn'      => "",
                                        'attendanceOut'     => "",
                                        'attendanceStatus'  => $status,
                                        'description'       => "Scheduled but attendance not found",
                                    ]);
                                    $dataModel->push($projectCSOModel);
                                }
                                else{
                                    $ct = 0;
                                    foreach ($attendanceAbsents as $attendanceAbsent){
                                        if($attendanceAbsent->attendance_type == "NORMAL"){
                                            if($attendanceAbsent->is_done == 0){
                                                $status = "A";
                                                $description = "Belum Melakukan checkout";
                                            }
                                            else{
                                                if(!empty($attendanceAbsent->date_checkout)){
                                                    $status = "H";
                                                    $attendanceOut = $attendanceAbsent->date_checkout;
                                                }
                                                else{
                                                    $status = "A";
                                                    $description = "Belum Melakukan checkout";
                                                }
                                            }
                                        }
                                        else{
                                            $status = "A";
                                            $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                        }
                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                        $attendanceIn = $attendanceAbsent->date;

                                        if($status != "A"){
                                            //validasi tipe H, minimal harus 8 jam (480 menit)
//                                              $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                            $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                              $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                            $trxDate = Carbon::parse($attendanceAbsent->date);
//                                              $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                            $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                            if($intervalMinute >= 480){
                                                $projectCSOModel = ([
                                                    'employeeId'        => $projectEmployee->employee_id,
                                                    'employeeCode'      => $projectEmployee->employee_code,
                                                    'transDate'         => $createdAt->format('Y-m-d'),
                                                    'shiftCode'         => 1,
                                                    'attendanceIn'      => $attendanceIn,
                                                    'attendanceOut'     => $attendanceOut,
                                                    'attendanceStatus'  => $status,
                                                    'description'       => $description,
                                                ]);
                                                $dataModel->push($projectCSOModel);
                                            }
                                            else{
                                                $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                                $projectCSOModel = ([
                                                    'employeeId'        => $attendanceAbsent->employee_id,
                                                    'employeeCode'      => $attendanceAbsent->employee_code,
                                                    'transDate'         => $createdAt->format('Y-m-d'),
                                                    'shiftCode'         => 1,
                                                    'attendanceIn'      => $attendanceIn,
                                                    'attendanceOut'     => $attendanceOut,
                                                    'attendanceStatus'  => "A",
                                                    'description'       => "Absent not 8 hours",
                                                ]);
                                                $dataModel->push($projectCSOModel);
                                            }
                                        }
                                        else{
                                            $projectCSOModel = ([
                                                'employeeId'        => $projectEmployee->employee_id,
                                                'employeeCode'      => $projectEmployee->employee_code,
                                                'transDate'         => $createdAt->format('Y-m-d'),
                                                'shiftCode'         => 1,
                                                'attendanceIn'      => $attendanceIn,
                                                'attendanceOut'     => $attendanceOut,
                                                'attendanceStatus'  => $status,
                                                'description'       => $description,
                                            ]);
                                            $dataModel->push($projectCSOModel);
                                        }
                                        $ct++;
                                    }
                                }
                            }

                            $monthCt++;
                        }
                    }
                }
                //kalau belum pernah dibuat jadwal dari backend, maka pakai cara lama utk memproses data
                else{
                    $monthCt = 0;
                    foreach ($dayPeriods as $dayPeriod){
                        $selectedMonth = $startDateMonth;
                        if(str_contains($endDateMonth, $monthPeriods[$monthCt])){
                            $selectedMonth = $endDateMonth;
                        }

                        if((int)$dayPeriod < 10){
                            $createdAt = $selectedMonth.'-0'.$dayPeriod;
                        }
                        else{
                            $createdAt = $selectedMonth.'-'.$dayPeriod;
                        }

                        $attendanceAbsents = DB::table('attendance_absents')
                            ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                            ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                            ->select('attendance_absents.id as attendance_absent_id',
                                'attendance_absents.shift_type as shift_type',
                                'attendance_absents.is_done as is_done',
                                'attendance_absents.date as date',
                                'attendance_absents.date_checkout as date_checkout',
                                'attendance_absents.created_at as created_at',
                                'attendance_absents.type as attendance_type',
                                'attendance_absents.description as description',
                                'employees.id as employee_id',
                                'employees.code as employee_code',
                                'projects.name as project_name',
                                'projects.code as project_code')
                            ->whereBetween('attendance_absents.date',
                                array($createdAt.' 00:00:00', $createdAt.' 23:59:00'))
                            ->where('attendance_absents.project_id', $project->id)
                            ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                            ->where('attendance_absents.status_id',6)
                            ->get();

                        if($attendanceAbsents->count() < 1){
                            $projectCSOModel = ([
                                'employeeId'        => $projectEmployee->employee_id,
                                'employeeCode'      => $projectEmployee->employee_code,
                                'transDate'         => $createdAt,
                                'shiftCode'         => 1,
                                'attendanceIn'      => "",
                                'attendanceOut'     => "",
                                'attendanceStatus'   => "A",
                                'description'       => "Data Not found",
                            ]);
                            $dataModel->push($projectCSOModel);
                        }
                        else{
                            foreach ($attendanceAbsents as $attendanceAbsent){
                                $status = "A";
                                $attendanceOut = "";
                                $description = "By Attendance data only, no schedule provided";
                                if($attendanceAbsent->is_done == 0){
                                    $status = "A";
                                    $description = "Belum Melakukan checkout";
                                }
                                else{
                                    if(!empty($attendanceAbsent->date_checkout)){
                                        if($attendanceAbsent->attendance_type == 'NORMAL'){
                                            $status = "H";
                                            $attendanceOut = $attendanceAbsent->date_checkout;
                                        }
                                        else{
                                            $status = "A";
                                            $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                        }
                                    }
                                    else{
                                        $status = "A";
                                        $description = "Belum Melakukan checkout";
                                    }
                                }

                                if($status != "A"){
                                    //validasi tipe H, minimal harus 8 jam
//                                $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                    $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                    $trxDate = Carbon::parse($attendanceAbsent->date);
//                                $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                    $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                    if($intervalMinute >= 480){
                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                        $projectCSOModel = ([
                                            'employeeId'        => $attendanceAbsent->employee_id,
                                            'employeeCode'      => $attendanceAbsent->employee_code,
                                            'transDate'         => $createdAt->format('Y-m-d'),
                                            'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                            'attendanceIn'      => $attendanceAbsent->date,
                                            'attendanceOut'     => $attendanceOut,
                                            'attendanceStatus'  => $status,
                                            'description'       => $description,
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                    }
                                    else{
                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                        $projectCSOModel = ([
                                            'employeeId'        => $attendanceAbsent->employee_id,
                                            'employeeCode'      => $attendanceAbsent->employee_code,
                                            'transDate'         => $createdAt->format('Y-m-d'),
                                            'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                            'attendanceIn'      => $attendanceAbsent->date,
                                            'attendanceOut'     => $attendanceOut,
                                            'attendanceStatus'  => "A",
                                            'description'       => $description,
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                    }
                                }
                                else{
                                    $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                    $projectCSOModel = ([
                                        'employeeId'        => $attendanceAbsent->employee_id,
                                        'employeeCode'      => $attendanceAbsent->employee_code,
                                        'transDate'         => $createdAt->format('Y-m-d'),
                                        'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                        'attendanceIn'      => $attendanceAbsent->date,
                                        'attendanceOut'     => $attendanceOut,
                                        'attendanceStatus'  => $status,
                                        'description'       => $description,
                                    ]);
                                    $dataModel->push($projectCSOModel);
                                }
                            }
                        }

                        $monthCt++;
                    }

                }
            }
            return $dataModel;
        }
        catch (\Exception $ex){
            Log::channel('in_sys')
                ->error('libs/AttendanceProcess/DownloadAttendanceProcessV3  error EX: '. $ex);

            $dataModel = collect();

            return $dataModel;
        }

    }


    /**
     * Function to get attendance Data Version 4, with sick leaves, leave permission or overtime, and change logic for employee schedule
     *
     * @param $startDate, $endDate
     * @return  \Illuminate\Support\Collection
     */
    public static function DownloadAttendanceProcessV4($project, $startDate, $startDateMonth, $endDate, $endDateMonth){
        $currentEmployeeNUC = "";
        try{
            $dataModel = collect();
            $dataModelCopy = collect();
//            $projectEmployees = ProjectEmployee::where('project_id', $project->id)
//                ->where('status_id', 1)
//                ->where('employee_roles_id', '<', 5)
//                ->orderBy('employee_roles_id')
//                ->get();
            $projectEmployees = DB::table('project_employees')
                ->join('employees', 'project_employees.employee_id', '=', 'employees.id')
                ->select('project_employees.id as project_employee_id',
                    'project_employees.status_id as project_employee_status_id',
                    'employees.id as employee_id',
                    'employees.code as employee_code')
                ->where('project_employees.project_id', $project->id)
//                ->where('project_employees.status_id',1)
                ->where('project_employees.employee_roles_id', '<', 5)
                ->orderBy('project_employees.employee_roles_id')
                ->get();


            //ambil tanggal dari start_date ke end_date
            $period = CarbonPeriod::create($startDate, $endDate);
            $dayPeriods = [];
            $monthPeriods = [];
            foreach ($period as $date) {
                // formated j = The day of the month without leading zeros (1 to 31)
                array_push($dayPeriods, $date->format('j'));
                array_push($monthPeriods, $date->format('m'));
            }

            foreach ($projectEmployees as $projectEmployee){
                $currentEmployeeNUC = $projectEmployee->employee_id." | NUC = ".$projectEmployee->employee_code;
//                $employeeSchedule = EmployeeSchedule::where('employee_id', $projectEmployee->employee_id)->first();
                $employeeSchedule = DB::table('employee_schedules')
                    ->select('day_status','employee_id', 'employee_code')
                    ->where('employee_id', $projectEmployee->employee_id)
                    ->first();

                //check kalau sudah pernah dibuat jadwal dari backend
                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){

                        //convert day status to collection
                        $days = explode(';', $employeeSchedule->day_status);
                        $dayCollections = collect();
                        foreach($days as $day){
                            if(empty($day)) continue;
                            $date = explode(':', $day);
                            $item = ([
                                'day'        => $date[0],
                                'status'      => $date[1]
                            ]);
                            $dayCollections->push($item);
                        }

                        $status = "A";
                        $attendanceIn = "";
                        $attendanceOut = "";
                        $description = "";
                        //ambil tanggal dan banding kan dengan database
                        $monthCt = 0;
                        foreach ($dayPeriods as $dayPeriod){
                            $dayCollection = $dayCollections->where('day', $dayPeriod)->first();
                            if(!empty($dayCollection)){
                                $dayObject = (object)$dayCollection;

                                $selectedMonth = $startDateMonth;
                                if(str_contains($endDateMonth, $monthPeriods[$monthCt])){
                                    $selectedMonth = $endDateMonth;
                                }

                                if($dayObject->day < 10){
                                    $createdAt = $selectedMonth.'-0'.$dayObject->day;
                                }
                                else{
                                    $createdAt = $selectedMonth.'-'.$dayObject->day;
                                }

                                //get list attendance by
                                $attendanceAbsents = DB::table('attendance_absents')
                                    ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                                    ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                                    ->select('attendance_absents.id as attendance_absent_id',
                                        'attendance_absents.shift_type as shift_type',
                                        'attendance_absents.is_done as is_done',
                                        'attendance_absents.date as date',
                                        'attendance_absents.date_checkout as date_checkout',
                                        'attendance_absents.created_at as created_at',
                                        'attendance_absents.type as attendance_type',
                                        'attendance_absents.description as description',
                                        'employees.id as employee_id',
                                        'employees.code as employee_code',
                                        'projects.name as project_name',
                                        'projects.code as project_code')
                                    ->whereBetween('attendance_absents.date',
                                        array($createdAt.' 00:00:00', $createdAt.' 23:59:00'))
                                    ->where('attendance_absents.project_id', $project->id)
                                    ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                                    ->where('attendance_absents.status_id',6)
                                    ->get();

                                if($projectEmployee->project_employee_status_id == 0 && $attendanceAbsents->count() < 1){
                                    continue;
                                }

                                //kalau scehdulenya tipenya O = Off
                                if($dayObject->status == 'O'){

                                    if($attendanceAbsents->count() < 1){
                                        $projectCSOModel = ([
                                            'employeeId'        => $projectEmployee->employee_id,
                                            'employeeCode'      => $projectEmployee->employee_code,
                                            'transDate'         => $createdAt,
                                            'shiftCode'         => 1,
                                            'attendanceIn'      => "",
                                            'attendanceOut'     => "",
                                            'attendanceStatus'   => "O",
                                            'description'       => "Off Schedule",
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                    }
                                    else{
                                        foreach ($attendanceAbsents as $attendanceAbsent){
                                            $status = "H";
                                            if(!empty($attendanceAbsent->date_checkout)){
                                                $status = "H";
                                                $attendanceIn = $attendanceAbsent->date;
                                                $attendanceOut = $attendanceAbsent->date_checkout;
                                            }
                                        }

                                        $projectCSOModel = ([
                                            'employeeId'        => $employeeSchedule->employee_id,
                                            'employeeCode'      => $employeeSchedule->employee_code,
                                            'transDate'         => $createdAt,
                                            'shiftCode'         => 1,
                                            'attendanceIn'      => $attendanceIn,
                                            'attendanceOut'     => $attendanceOut,
                                            'attendanceStatus'  => $status,
                                            'description'       => "Schedule O, but found attendance",
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                    }
                                }
                                //kalau schedulenya tipenya H = masuk
                                else{
                                    if($attendanceAbsents->count() < 1){
                                        $status = "A";
                                        $projectCSOModel = ([
                                            'employeeId'        => $projectEmployee->employee_id,
                                            'employeeCode'      => $projectEmployee->employee_code,
                                            'transDate'         => $createdAt,
                                            'shiftCode'         => 1,
                                            'attendanceIn'      => "",
                                            'attendanceOut'     => "",
                                            'attendanceStatus'  => $status,
                                            'description'       => "Scheduled but attendance not found",
                                        ]);
                                        $dataModel->push($projectCSOModel);
                                    }
                                    else{
                                        $ct = 0;
                                        foreach ($attendanceAbsents as $attendanceAbsent){
                                            if($attendanceAbsent->attendance_type == "NORMAL"){
                                                if($attendanceAbsent->is_done == 0){
                                                    $status = "A";
                                                    $description = "Belum Melakukan checkout";
                                                }
                                                else{
                                                    if(!empty($attendanceAbsent->date_checkout)){
                                                        $status = "H";
                                                        $attendanceOut = $attendanceAbsent->date_checkout;
                                                    }
                                                    else{
                                                        $status = "A";
                                                        $description = "Belum Melakukan checkout";
                                                    }
                                                }
                                            }
                                            else{
                                                if($attendanceAbsent->attendance_type == "SR" || $attendanceAbsent->attendance_type == "IR"){
                                                    $status = "A";
                                                    $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                                }
                                                else{
                                                    continue;
                                                }
//                                            $status = "A";
//                                            $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                            }
                                            $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                            $attendanceIn = $attendanceAbsent->date;

                                            if($status != "A"){
                                                //validasi tipe H, minimal harus 8 jam (480 menit)
//                                              $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                                $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                              $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                                $trxDate = Carbon::parse($attendanceAbsent->date);
//                                              $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                                $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                                $intervalMinutValidation = 480;
                                                if($project->id == 803){
                                                    $intervalMinutValidation = 180;
                                                }

                                                if($intervalMinute >= $intervalMinutValidation){
                                                    $projectCSOModel = ([
                                                        'employeeId'        => $projectEmployee->employee_id,
                                                        'employeeCode'      => $projectEmployee->employee_code,
                                                        'transDate'         => $createdAt->format('Y-m-d'),
                                                        'shiftCode'         => 1,
                                                        'attendanceIn'      => $attendanceIn,
                                                        'attendanceOut'     => $attendanceOut,
                                                        'attendanceStatus'  => $status,
                                                        'description'       => $description,
                                                    ]);
                                                    $isNotDouble = self::checkAttendanceDouble($dataModel, $projectCSOModel, $createdAt);
                                                    if($isNotDouble){
                                                        $dataModel->push($projectCSOModel);
                                                    }
                                                }
                                                else{
                                                    if($ct < 1){
                                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                                        $projectCSOModel = ([
                                                            'employeeId'        => $attendanceAbsent->employee_id,
                                                            'employeeCode'      => $attendanceAbsent->employee_code,
                                                            'transDate'         => $createdAt->format('Y-m-d'),
                                                            'shiftCode'         => 1,
                                                            'attendanceIn'      => $attendanceIn,
                                                            'attendanceOut'     => $attendanceOut,
                                                            'attendanceStatus'  => "A",
                                                            'description'       => "Absent not full",
                                                        ]);
                                                        $dataModel->push($projectCSOModel);
                                                    }
                                                }
                                            }
                                            else{
                                                if($ct < 1){
                                                    $projectCSOModel = ([
                                                        'employeeId'        => $projectEmployee->employee_id,
                                                        'employeeCode'      => $projectEmployee->employee_code,
                                                        'transDate'         => $createdAt->format('Y-m-d'),
                                                        'shiftCode'         => 1,
                                                        'attendanceIn'      => $attendanceIn,
                                                        'attendanceOut'     => $attendanceOut,
                                                        'attendanceStatus'  => $status,
                                                        'description'       => $description,
                                                    ]);
                                                    $dataModel->push($projectCSOModel);
                                                }
                                                else{
                                                    if($attendanceAbsent->attendance_type != "NORMAL"){

                                                        $projectCSOModel = ([
                                                            'employeeId'        => $projectEmployee->employee_id,
                                                            'employeeCode'      => $projectEmployee->employee_code,
                                                            'transDate'         => $createdAt->format('Y-m-d'),
                                                            'shiftCode'         => 1,
                                                            'attendanceIn'      => $attendanceIn,
                                                            'attendanceOut'     => $attendanceOut,
                                                            'attendanceStatus'  => $status,
                                                            'description'       => $description,
                                                        ]);
                                                        $selected = [];
//                                                    foreach ($dataModel as $key => $item) {
//                                                        dd($item);
//                                                        if ($dataModel->selected == true) {
//                                                            $selected[] = $item;
//                                                            $dataModel->forget($key);
//                                                        }
//                                                    }

//                                                    $deleteKey = 0;
//                                                    $dataModelCopy = $dataModel;
//                                                    foreach($dataModel as $data){
//                                                        if($data["employeeCode"] == $projectEmployee->employee_code &&
//                                                            $data["transDate"] == $createdAt->format('Y-m-d')){
////                                                            dd($data,$projectCSOModel, $deleteKey);
//                                                            $dataModelCopy->forget($deleteKey);
//                                                            $dataModel = collect();
//                                                        }
//                                                        $deleteKey++;
//                                                    }
//                                                    foreach($dataModelCopy as $data){
//                                                        $projectCSOModel = ([
//                                                            'employeeId'        => $data["employeeId"],
//                                                            'employeeCode'      => $data["employeeCode"],
//                                                            'transDate'         => $data["transDate"],
//                                                            'shiftCode'         => $data["shiftCode"],
//                                                            'attendanceIn'      => $data["attendanceIn"],
//                                                            'attendanceOut'     => $data["attendanceOut"],
//                                                            'attendanceStatus'  => $data["attendanceStatus"],
//                                                            'description'       => $data["description"],
//                                                        ]);
//                                                        $dataModel->push($projectCSOModel);
//                                                    }

                                                        $isNotDouble = self::checkAttendanceDouble($dataModel, $projectCSOModel, $createdAt);
                                                        if($isNotDouble){
                                                            $dataModel->push($projectCSOModel);
                                                        }
                                                    }
                                                }
                                            }
                                            $ct++;
                                        }
                                    }
                                }

                                $monthCt++;
                            }
                        }
                    }
                }
                //kalau belum pernah dibuat jadwal dari backend, maka pakai cara lama utk memproses data
                else{
                    $monthCt = 0;
                    foreach ($dayPeriods as $dayPeriod){
                        $selectedMonth = $startDateMonth;
                        if(str_contains($endDateMonth, $monthPeriods[$monthCt])){
                            $selectedMonth = $endDateMonth;
                        }

                        if((int)$dayPeriod < 10){
                            $createdAt = $selectedMonth.'-0'.$dayPeriod;
                        }
                        else{
                            $createdAt = $selectedMonth.'-'.$dayPeriod;
                        }

                        $attendanceAbsents = DB::table('attendance_absents')
                            ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
                            ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
                            ->select('attendance_absents.id as attendance_absent_id',
                                'attendance_absents.shift_type as shift_type',
                                'attendance_absents.is_done as is_done',
                                'attendance_absents.date as date',
                                'attendance_absents.date_checkout as date_checkout',
                                'attendance_absents.created_at as created_at',
                                'attendance_absents.type as attendance_type',
                                'attendance_absents.description as description',
                                'employees.id as employee_id',
                                'employees.code as employee_code',
                                'projects.name as project_name',
                                'projects.code as project_code')
                            ->whereBetween('attendance_absents.date',
                                array($createdAt.' 00:00:00', $createdAt.' 23:59:00'))
                            ->where('attendance_absents.project_id', $project->id)
                            ->where('attendance_absents.employee_id', $projectEmployee->employee_id)
                            ->where('attendance_absents.status_id',6)
                            ->get();
                        if($projectEmployee->project_employee_status_id == 0 && $attendanceAbsents->count() < 1){
                            continue;
                        }

                        if($attendanceAbsents->count() < 1){
                            $projectCSOModel = ([
                                'employeeId'        => $projectEmployee->employee_id,
                                'employeeCode'      => $projectEmployee->employee_code,
                                'transDate'         => $createdAt,
                                'shiftCode'         => 1,
                                'attendanceIn'      => "",
                                'attendanceOut'     => "",
                                'attendanceStatus'   => "A",
                                'description'       => "Data Not found",
                            ]);
                            $dataModel->push($projectCSOModel);
                        }
                        else{
                            foreach ($attendanceAbsents as $attendanceAbsent){
                                $status = "A";
                                $attendanceOut = "";
                                $description = "By Attendance data only, no schedule provided";
                                if($attendanceAbsent->is_done == 0){
                                    $status = "A";
                                    $description = "Belum Melakukan checkout";
                                }
                                else{
                                    if(!empty($attendanceAbsent->date_checkout)){
                                        if($attendanceAbsent->attendance_type == 'NORMAL'){
                                            $status = "H";
                                            $attendanceOut = $attendanceAbsent->date_checkout;
                                        }
                                        else{
                                            $status = "A";
                                            $description = "Ijin Tidak masuk/Sakit, dgn status = ".$attendanceAbsent->attendance_type;
                                        }
                                    }
                                    else{
                                        $status = "A";
                                        $description = "Belum Melakukan checkout";
                                    }
                                }

                                if($status != "A"){
                                    //validasi tipe H, minimal harus 8 jam
//                                $trxDateOut = Carbon::parse(date_format($attendanceAbsent->date_checkout,'j-F-Y H:i:s'));
                                    $trxDateOut = Carbon::parse($attendanceAbsent->date_checkout);

//                                $trxDate = Carbon::parse(date_format($attendanceAbsent->date, 'j-F-Y H:i:s'));
                                    $trxDate = Carbon::parse($attendanceAbsent->date);
//                                $intervalMinute = $trxDateOut->diffInMinutes($trxDate);
                                    $intervalMinute = $trxDate->diffInMinutes($trxDateOut);

                                    $intervalMinutValidation = 480;
                                    if($project->id == 803){
                                        $intervalMinutValidation = 180;
                                    }

                                    if($intervalMinute >= $intervalMinutValidation){
//                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                        $projectCSOModel = ([
                                            'employeeId'        => $attendanceAbsent->employee_id,
                                            'employeeCode'      => $attendanceAbsent->employee_code,
                                            'transDate'         => $createdAt,
                                            'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                            'attendanceIn'      => $attendanceAbsent->date,
                                            'attendanceOut'     => $attendanceOut,
                                            'attendanceStatus'  => $status,
                                            'description'       => $description,
                                        ]);
                                        $isNotDouble = self::checkAttendanceDouble($dataModel, $projectCSOModel, $createdAt);
                                        if($isNotDouble){
                                            $dataModel->push($projectCSOModel);
                                        }
                                    }
//                                    else{
////                                        $createdAt = Carbon::parse($attendanceAbsent->created_at);
//                                        $projectCSOModel = ([
//                                            'employeeId'        => $attendanceAbsent->employee_id,
//                                            'employeeCode'      => $attendanceAbsent->employee_code,
//                                            'transDate'         => $createdAt,
//                                            'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
//                                            'attendanceIn'      => $attendanceAbsent->date,
//                                            'attendanceOut'     => $attendanceOut,
//                                            'attendanceStatus'  => "A",
//                                            'description'       => $description,
//                                        ]);
//                                        $dataModel->push($projectCSOModel);
//                                    }
                                }
                                else{
//                                    $createdAt = Carbon::parse($attendanceAbsent->created_at);
                                    $projectCSOModel = ([
                                        'employeeId'        => $attendanceAbsent->employee_id,
                                        'employeeCode'      => $attendanceAbsent->employee_code,
                                        'transDate'         => $createdAt,
                                        'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                                        'attendanceIn'      => $attendanceAbsent->date,
                                        'attendanceOut'     => $attendanceOut,
                                        'attendanceStatus'  => $status,
                                        'description'       => $description,
                                    ]);
                                    $isNotDouble = self::checkAttendanceDouble($dataModel, $projectCSOModel, $createdAt);
                                    if($isNotDouble){
                                        $dataModel->push($projectCSOModel);
                                    }
                                }
                            }
                        }

                        $monthCt++;
                    }

                }
            }
//            dd("Data Model = ".count($dataModel)." | Data Model Copy = ".count($dataModelCopy));
            return $dataModel;
        }
        catch (\Exception $ex){
            Log::channel('in_sys')
                ->error('libs/AttendanceProcess/DownloadAttendanceProcessV4  latest Employee : '. $currentEmployeeNUC);
            Log::channel('in_sys')
                ->error('libs/AttendanceProcess/DownloadAttendanceProcessV4  error EX: '. $ex);

            $dataModel = collect();

            return $dataModel;
        }

    }

    public static function checkAttendanceDouble($datas, $currentData, $createdAt){
        $isNotFound = true;
        foreach($datas as $data){
            if($data["employeeCode"] == $currentData["employeeCode"] &&
                $data["transDate"] == $createdAt){
                $isNotFound = false;
            }
        }
        return $isNotFound;
    }
}

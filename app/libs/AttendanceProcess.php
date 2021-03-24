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
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
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
                                //kalau scehdulenya tipenya M = masuk
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
            Log::error('libs/AttendanceProcess/DownloadAttendanceProcess  error EX: '. $ex);

            $dataModel = collect();

            return $dataModel;
        }

    }

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
                if(!empty($projectEmployee)){
                    $project = DB::table('projects')
                        ->select('name', 'code')
                        ->where('id', $projectEmployee->project_id)
                        ->first();
//                    $project = Project::where('id', $projectEmployee->project_id)
//                        ->first();
                    $projectName = $project->name;
                    $projectCode = $project->code;
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
                    'Employee_Code' => $employee->code,
                    'Employee_Name' => $employee->first_name." ".$employee->last_name,
                    'Employee_Phone' => $user->phone != "" ? $user->phone : $employee->phone,
                    'Employee_Status' => $user->status_id == 0 ? "NON AKTIF" : "AKTIF",
                    'Project_Code' => $projectCode,
                    'Project_Name' => $projectName,
                    'Total_Valid_Absensi' => $countA,
                    'Total_Invalid_Absensi' => $countB,
                ]);
                $list->push($singleData);
                $ct++;
            }
            return $list;
        }
        catch (\Exception $ex){
            dd($ex);
            Log::error('libs/AttendanceProcess/DownloadAttendanceValidationProcess  error EX: '. $ex);
            $list = collect();

            return $list;
        }

    }
}

<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\libs;

use App\Models\Action;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\AutoNumber;
use App\Models\Employee;
use App\Models\EmployeePlottingSchedule;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectEmployee;
use App\Models\ProjectShift;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
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


class EmployeeProcess
{
    public static function GetEmployeeSchedule($employee_id, $employee_name){
        try{
            //get employee schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee_id)->first();
//            Log::info('employee_id = '.$employee->id);

            // weekOfMonth will returns 1 for the 7 first days of the month, then 2 from the 8th to
            // the 14th, 3 from the 15th to the 21st, 4 from 22nd to 28th and 5 above
            $todayWeekOfMonth = $date->weekOfMonth;
            // dayOfWeekIso returns a number between 1 (monday) and 7 (sunday)
            $todayOfWeek = $date->dayOfWeekIso;

            $schedules = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('employee_id', $employee_id)
                ->where('weeks', 'like', '%'.$todayWeekOfMonth.'%')
                ->where('days', 'like', '%'.$todayOfWeek.'%')
//                ->whereTime('start', '<=', $time)
//                ->whereTime('finish', '>=', $time)
                ->get();

//            Log::info('jumlah schedule = '. $schedules->count());
//            Log::info('weeks = '. $todayWeekOfMonth);
//            Log::info('days = '. $todayOfWeek);

            if($schedules->count() == 0){
                return null;
            }

            $scheduleModels = collect();
//            Log::info('project_id = '. $projectEmployee->project_id.', project_employee_id = '. $projectEmployee->id);
            foreach ($schedules as $schedule){
                $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();

                $place = Place::find($schedule->project_activities_header->place_id);
                $scheduleDetailModels = collect();
//                $actionName = "";
                $detailId = "";
                foreach ($scheduleDetails as $scheduleDetail){
                    $actionName = collect();
//                    $projectObject = ProjectObject::find($scheduleDetail->project_object_id);
//                    $objectName = "";
//                    $unitName = $projectObject->unit_name != "-" ? $projectObject->unit_name." " : "";
//                    $sub1unitName = $projectObject->sub1_unit_name != "-" ? $projectObject->sub1_unit_name." " : "";
//                    $sub2unitName = $projectObject->sub2_unit_name != "-" ? $projectObject->sub2_unit_name." " : "";
//                    $objectName = $objectName.$unitName;
//                    $objectName = $objectName.$sub1unitName;
//                    $objectName = $objectName.$sub2unitName;

                    $actionList = explode('#', $scheduleDetail->action_id);
                    foreach ($actionList as $action){
                        if(!empty($action)){
                            $action = Action::find($action);
//                            $actionName .= $action->name. ", ";
                                $actionName->push($action->name);
                        }
                    }
                    $detailId = $scheduleDetail->id;
//                    $scheduleDetailModel = [
//                        'detail_id'        => $scheduleDetail->id,
//                        'place_name'        => $place->name,
//                        'object_name'       => $schedule->project_activities_header->plotting_name,
//                        'action_name'       => $actionName,
//                    ];
//                    $scheduleDetailModels->push($scheduleDetailModel);

                    $checkStatus = 1;
                    //checkout action done =
                    // true (kalau cso sudah melakukan checkout setelah melakukan pekerjaan)
                    // or false
                    $checkoutActionDone = false;
                    $attendanceCheckin = Attendance::where('schedule_id', $schedule->id)
                        ->where('schedule_detail_id', $detailId)
                        ->where('status_id', 6)
                        ->where('is_done', 0)
                        ->first();
                    if(!empty($attendanceCheckin)){
                        $checkStatus = 2;
                    }
                    $attendanceCheckout = Attendance::where('schedule_id', $schedule->id)
                        ->where('schedule_detail_id', $detailId)
                        ->where('status_id', 7)
                        ->where('is_done', 0)
                        ->where('assessment_leader', 0)
                        ->first();
                    if(!empty($attendanceCheckout)){
                        if($attendanceCheckout->is_action_checked == "1"){
                            $checkoutActionDone = true;
                        }
                        $checkStatus = 3;
                    }

                    $assessmentStatus = 0;
                    $assessmentScore = -1;
                    $attendanceAssessment = Attendance::where('schedule_id', $schedule->id)
                        ->where('schedule_detail_id', $detailId)
                        ->where('status_id', 7)
                        ->where('is_done', 1)
                        ->where('assessment_leader', '>', 0)
                        ->first();
                    if(!empty($attendanceAssessment)){
                        if($attendanceAssessment->is_action_checked == "1"){
                            $checkoutActionDone = true;
                        }
                        $checkStatus = 3;
                        $assessmentStatus = 1;
                        $assessmentScore = $attendanceAssessment->assessment_score;
                    }

                    $scheduleModel = [
                        'id'                => $detailId,
//                    'employee_name'     => $employee_name,
//                    'project_id'        => $schedule->project_id,
//                    'project_name'      => $schedule->project->name,
//                    'shift_type'        => $schedule->shift_type,
//                    'start'             => Carbon::parse($schedule->start)->toTimeString(),
//                    'finish'            => Carbon::parse($schedule->finish)->toTimeString(),
//                    'schedule_details'  => $scheduleDetailModels,

                        'start_time'        => Carbon::parse($scheduleDetail->start)->format('H:i:s'),
                        'finish_time'       => Carbon::parse($scheduleDetail->finish)->format('H:i:s'),
//                        'start_time'        => Carbon::parse($scheduleDetail->start)->format('d M Y H:i:s'),
//                        'finish_time'       => Carbon::parse($scheduleDetail->finish)->format('d M Y H:i:s'),
                        'place'             => $place->name,
                        'object'            => $schedule->project_activities_header->plotting_name,
                        'shift'             => $schedule->shift_type,
                        'status_check'      => $checkStatus,
                        'status_assessment' => $assessmentStatus,
                        'is_done'           => $assessmentStatus,
                        'checkout_action_done' => $checkoutActionDone,
                        'assessment_score'  => $assessmentScore,
                        'actions'           => $actionName,
                        'header_id'         => $schedule->id,
                    ];

                    $scheduleModels->push($scheduleModel);
                }
            }

            return $scheduleModels;
        }
        catch (\Exception $ex){
            Log::error('libs/EmployeeProcess - GetEmployeeSchedule error EX: '. $ex);
            return null;
        }

    }

    public static function GetEmployeePlotting($id){

        try{
            $projectActivities = ProjectActivitiesHeader::where('project_id', $id)->get();
            $projectActivityModels = collect();
            //check if cleaner null
            if($projectActivities->count() == 0){
                return Response::json($projectActivityModels, 482);
            }

            $projectActivityModels = collect();
            foreach ($projectActivities as $projectActivity){
                $dacDetailModel = collect();

                $shiftString = "";
                foreach ($projectActivity->project_activities_details as $projectDetail){
                    $actionName = collect();
//                    $actionName = "";
                    if(!empty($projectDetail->action_id)){
                        $actionList = explode('#', $projectDetail->action_id);
                        foreach ($actionList as $action){
                            if(!empty($action)){
                                $action = Action::find($action);
//                                $actionName .= $action->name. ", ";
                                $actionName->push($action->name);
                            }
                        }
                    }
                    $dacDetail = ([
                        //id in here for header_id
                        'id'       => $projectActivity->id,
                        'time'     => Carbon::parse($projectDetail->start)->format('H:i')." - ".Carbon::parse($projectDetail->finish)->format('H:i'),
                        'action'   => $actionName
                    ]);
                    $dacDetailModel->push($dacDetail);
                    $projectShifts = ProjectShift::Where('id', $projectDetail->shift_type)->first();
                    $shiftString = empty($projectShifts) ? "-" : $projectShifts->shift_type;
                }
                $place = Place::find($projectActivity->place_id);
                $project = Project::find($projectActivity->project_id);

                //section to get employee from employee_schedule_plotting
                $projectCSOModel = null;
                $employeeSchedulePlotting = EmployeePlottingSchedule::where('project_activity_id', $projectActivity->id)->first();
                if(!empty($employeeSchedulePlotting)){
                    $days = explode(';', $employeeSchedulePlotting->day_employee_id);
                    $todayDay = Carbon::now()->format('j');
                    $csoFromSchedulePlotting = "";
                    foreach($days as $day){
                        if(empty($day)) continue;
                        $date = explode(':', $day);
                        if($todayDay == $date[0]){
                            $csoFromSchedulePlotting = $date[1];
                        }
                    }
                }

                //section to get employee from schedule (plotting from leader)
                $assignedCso = Schedule::where('project_activity_id', $projectActivity->id)->first();
                if(!empty($assignedCso)){
                    $csoFromSchedulePlotting = $assignedCso->employee_id;
                }

                if($csoFromSchedulePlotting != ""){
                    $employee = Employee::find($csoFromSchedulePlotting);
                    $employeeImage = empty($employee->image_path) ? null : asset('storage/employees/'. $employee->image_path);
                    $projectCSOModel = [
                        'id'        => $employee->id,
                        'name'      => $employee->first_name." ".$employee->last_name,
                        'avatar'    => $employeeImage,
                        'role'      => "",
                    ];
                }

                $dacHeaderModel = ([
                    'id'        => $projectActivity->id,
                    'place'     => $place->name,
                    'object'    => $projectActivity->plotting_name,
                    'shift'     => $shiftString,
                    'project'   => $project->name,
                    'details'   => $dacDetailModel,
                    'employee'  => $projectCSOModel
                ]);


                $projectActivityModels->push($dacHeaderModel);
            }
            return $projectActivityModels;
        }
        catch (\Exception $ex){
            Log::error('libs/EmployeeProcess - GetEmployeeSchedulePlotting error EX: '. $ex);
            return null;
        }
    }

    public static function GetEmployeeScheduleV2($employee_id, $projectId, $employee_name, $startDate, $finishDate, $attendanceModels){
        try{
            $attendances = DB::table('attendance_absents')
                ->where('employee_id', $employee_id)
                ->whereBetween('created_at', [$startDate, $finishDate])
                ->where('status_id', 6)
                ->orderByDesc('date')
                ->get();

            if($attendances->count() == 0){
                return $attendanceModels;
            }
            else{
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

                    $checkInOutModels = collect();
                    $attInAsStartDate = Carbon::parse($attendance->date)->format('Y-m-d 00:00:00');
                    $attInAsFinishDate = Carbon::parse($attendance->date)->format('Y-m-d 23:59:59');

                    $checkinAttendances = Attendance::where('employee_id', $employee_id)
                        ->whereBetween('created_at', [$attInAsStartDate, $attInAsFinishDate])
                        ->where('status_id', 6)
                        ->get();

                    foreach($checkinAttendances as $checkinAttendance){
                        $checkIn = "";
                        $placeId = "";
                        $placeName = "";
                        $ObjectName = "";
                        $SubObjectName = "";

                        $checkIn = Carbon::parse($checkinAttendance->date)->format('d m Y H:i:s');
                        $placeId = $checkinAttendance->place_id;
                        $placeName = $checkinAttendance->place->name;

                        $checkOut = "";
                        if($checkinAttendance->is_done == 1){
                            $checkOut = Carbon::parse($checkinAttendance->date_checkout)->format('d m Y H:i:s');
                        }
//                        $checkoutAttendance = Attendance::where('employee_id', $employee_id)
//                            ->whereBetween('created_at', [$attInAsStartDate, $attInAsFinishDate])
//                            ->where('place_id', $placeId)
//                            ->where('status_id', 7)
//                            ->first();
//                        if(!empty($checkoutAttendance))
//                            $checkOut = Carbon::parse($checkoutAttendance->date)->format('d M Y H:i:s');

                        $checkInOutModel = collect([
                            'checkin_datetime'  => $checkIn,
                            'checkout_datetime' => $checkOut,
                            'place_id'          => $placeId,
                            'place_name'        => $placeName,
                            'object_name'       => $ObjectName,
                            'sub_object_name'   => $SubObjectName
                        ]);
                        $checkInOutModels->push($checkInOutModel);
                    }

                    $attendanceModel = collect([
                        'attendance_in_date'    => $attIn,
                        'attendance_out_date'   => $attOut,
                        'project_name'          => $projectName,
                        'employee_id'           => $employee_id,
                        'employee_name'         => $employee_name,
                        'checkins'              => $checkInOutModels
                    ]);
                    $attendanceModels->push($attendanceModel);
                }
//                Log::channel('in_sys')
//                    ->info('GetEmployeeScheduleV2 attendanceResult count = '.json_encode($attendanceModels));
                return $attendanceModels;
            }
        }
        catch (\Exception $ex){
            Log::error('libs/EmployeeProcess - GetEmployeeSchedule error EX: '. $ex);
            return $attendanceModels;
        }

    }
}

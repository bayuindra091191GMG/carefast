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
use App\Models\Place;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
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
                        $checkStatus = 3;
                    }

                    $assessmentStatus = 0;
                    $attendanceAssessment = Attendance::where('schedule_id', $schedule->id)
                        ->where('schedule_detail_id', $detailId)
                        ->where('status_id', 7)
                        ->where('is_done', 1)
                        ->where('assessment_leader', 1)
                        ->first();
                    if(!empty($attendanceAssessment)){
                        $assessmentStatus = 1;
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

                        'start_time'        => Carbon::parse($scheduleDetail->start)->format('H:i'),
                        'finish_time'       => Carbon::parse($scheduleDetail->finish)->format('H:i'),
                        'place'             => $place->name,
                        'object'            => $schedule->project_activities_header->plotting_name,
                        'shift'             => $schedule->shift_type,
                        'status_check'      => $checkStatus,
                        'status_assessment' => $assessmentStatus,
                        'is_done'           => $assessmentStatus,
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
}

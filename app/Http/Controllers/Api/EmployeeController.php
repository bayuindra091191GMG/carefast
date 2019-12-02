<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function getEmployees()
    {
        error_log("exception");
        try{

            $employees = Employee::all();

            return Response::json([
                'message' => "Success Getting Employee Data!",
                'model'     => json_encode($employees)
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getEmployees error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeDetail(Request $request)
    {
        $id = $request->input('id');
        error_log("exception");
        try{

            $employee = Employee::find($id)->get();

            return Response::json([
                'message' => "Success Getting Employee Detail!",
                'model'     => json_encode($employee)
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getEmployeeDetail error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeCSO(Request $request)
    {
        $id = $request->input('id');
        try{
            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();
            $projectCSOs = ProjectEmployee::where('project_id', $projectEmployee->project_id)
                ->where('employee_roles_id', 1)
                ->get();
            $projectCSOModels = collect();
            //check if cleaner null
            if($projectCSOs->count() == 0){
                return Response::json($projectCSOModels, 200);
            }

            foreach($projectCSOs as $projectCSO){
                $employeeImage = empty($projectCSO->employee->image_path) ? null : asset('storage/employees/'. $projectCSO->employee->image_path);
                $projectCSOModel = ([
                    'id'       => $projectCSO->employee_id,
                    'name'     => $projectCSO->employee->first_name." ".$projectCSO->employee->last_name,
                    'avatar'   => $employeeImage,
                ]);
                $projectCSOModels->push($projectCSOModel);
            }

            return Response::json($projectCSOModels, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getEmployeeCSO error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getPlottings(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $employee->id;
        try{
            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();
            $projectActivities = ProjectActivity::where('project_id', $projectEmployee->project_id)
                ->get();
            $projectActivityModels = collect();
            //check if cleaner null
            if($projectActivities->count() == 0){
                return Response::json($projectActivityModels, 200);
            }

            foreach($projectActivities as $projectActivity){
                $actionName = "";
                if(!empty($projectActivity->action_id)){
                    $actionList = explode('#', $projectActivity->action_id);
                    foreach ($actionList as $action){
                        if(!empty($action)){
                            $action = Action::find($action);
                            $actionName .= $action->name. ", ";
                        }
                    }
                }
                $projectCSOModel = ([
                    'id'       => $projectActivity->employee_id,
                    'plot_name'     => $projectActivity->place->name." | ".$projectActivity->plotting_name,
                    'activities'   => $actionName,
                ]);
                $projectActivityModels->push($projectCSOModel);
            }

            return Response::json($projectActivityModels, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getPlottings error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }


    public function employeeSchedule(){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            //get employee schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i');
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
//            Log::info('employee_id = '.$employee->id);

            // weekOfMonth will returns 1 for the 7 first days of the month, then 2 from the 8th to
            // the 14th, 3 from the 15th to the 21st, 4 from 22nd to 28th and 5 above
            $todayWeekOfMonth = $date->weekOfMonth;
            // dayOfWeekIso returns a number between 1 (monday) and 7 (sunday)
            $todayOfWeek = $date->dayOfWeekIso;

            $schedules = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
                ->where('weeks', 'like', '%'.$todayWeekOfMonth.'%')
                ->where('days', 'like', '%'.$todayOfWeek.'%')
//                ->whereTime('start', '<=', $time)
//                ->whereTime('finish', '>=', $time)
                ->get();


            if($schedules->count() == 0){
                return Response::json("Tidak ada Jadwal hari ini", 482);
            }

            $scheduleModels = collect();
//            Log::info('project_id = '. $projectEmployee->project_id.', project_employee_id = '. $projectEmployee->id);
            foreach ($schedules as $schedule){
                $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();

                $scheduleDetailModels = collect();
                foreach ($scheduleDetails as $scheduleDetail){
                    $projectObject = ProjectObject::find($scheduleDetail->project_object_id);
                    $objectName = "";
                    $unitName = $projectObject->unit_name != "-" ? $projectObject->unit_name." " : "";
                    $sub1unitName = $projectObject->sub1_unit_name != "-" ? $projectObject->sub1_unit_name." " : "";
                    $sub2unitName = $projectObject->sub2_unit_name != "-" ? $projectObject->sub2_unit_name." " : "";
                    $objectName = $objectName.$unitName;
                    $objectName = $objectName.$sub1unitName;
                    $objectName = $objectName.$sub2unitName;

                    $scheduleDetailModel = [
                        'detail_id'        => $scheduleDetail->id,
                        'place_name'        => $projectObject->place_name,
                        'object_name'       => $objectName,
                        'action_name'       => $scheduleDetail->action->name,
                    ];
                    $scheduleDetailModels->push($scheduleDetailModel);
                }

                $scheduleModel = [
                    'id'                => $schedule->id,
                    'employee_name'     => $employee->first_name ." ". $employee->last_name,
                    'project_id'        => $schedule->project_id,
                    'project_name'      => $schedule->project->name,
                    'shift_type'        => $schedule->shift_type,
                    'start'             => Carbon::parse($schedule->start)->toTimeString(),
                    'finish'            => Carbon::parse($schedule->finish)->toTimeString(),
                    'schedule_details'  => $scheduleDetailModels
                ];

                $scheduleModels->push($scheduleModel);
            }
            return Response::json($scheduleModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

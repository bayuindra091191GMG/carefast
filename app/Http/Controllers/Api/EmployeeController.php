<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\EmployeeProcess;
use App\Models\Action;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class EmployeeController extends Controller
{
    /**
     * Function to listing of the employee.
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
     * Function to get the employee information.
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
     * Function to listing of the employee CSO.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeCSO(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $employee->id;
        try{
            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $projectCSOs = ProjectEmployee::where('project_id', $projectEmployee->project_id)
                ->where('employee_roles_id', 1)
                ->get();
            $projectCSOModels = collect();
            //check if cleaner null
            if($projectCSOs->count() == 0){
                return Response::json($projectCSOModels, 482);
            }

            foreach($projectCSOs as $projectCSO){
                $employee = Employee::find($projectCSO->employee_id);
                $employeeImage = empty($employee->image_path) ? null : asset('storage/employees/'. $employee->image_path);
                $projectCSOModel = ([
                    'id'       => $projectCSO->employee_id,
                    'name'     => $employee->first_name." ".$employee->last_name,
                    'avatar'   => $employeeImage,
                    'role'   => $projectCSO->employee_role->name,
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
     * Function to listing of the plotting.
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
//            $projectActivities = ProjectActivity::where('project_id', $projectEmployee->project_id)
//                ->get();
            $projectActivities = ProjectActivitiesHeader::where('project_id', $projectEmployee->project_id)
                ->get();
            $projectActivityModels = collect();
            //check if cleaner null
            if($projectActivities->count() == 0){
                return Response::json($projectActivityModels, 482);
            }

            foreach($projectActivities as $projectActivity){
                foreach ($projectActivity->project_activities_details as $projectActivityDetail)
                $actionName = collect();
                if(!empty($projectActivityDetail->action_id)){
                    $actionList = explode('#', $projectActivityDetail->action_id);
                    foreach ($actionList as $action){
                        if(!empty($action)){
                            $action = Action::find($action);
                            $actionName->push($action->name);
                        }
                    }
                }
                $projectCSOModel = ([
                    'id'       => $projectActivity->employee_id,
                    'name'     => $projectActivityDetail->place->name." | ".$projectActivityDetail->plotting_name,
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

    /**
     * Function to listing of the plotting.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getDacs()
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $employee->id;
        try{
            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();
            $projectActivities = ProjectActivitiesHeader::where('project_id', $projectEmployee->project_id)
                ->get();
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
//                    $actionName = collect();
                    $actionName = "";
                    if(!empty($projectDetail->action_id)){
                        $actionList = explode('#', $projectDetail->action_id);
                        foreach ($actionList as $action){
                            if(!empty($action)){
                                $action = Action::find($action);
                                $actionName .= $action->name. ", ";
//                                $actionName->push($action->name);
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
                    $shiftString = $projectDetail->shift_type;
                }
                $place = Place::find($projectActivity->place_id);
                $project = Project::find($projectActivity->project_id);
                $dacHeaderModel = ([
                    'place'     => $place->name,
                    'object'    => $projectActivity->plotting_name,
                    'shift'     => $shiftString,
                    'project'   => $project->name,
                    'details'   => $dacDetailModel
                ]);
                $projectActivityModels->push($dacHeaderModel);
            }

            // get group for dac
//            $projectActivitiesGroups = DB::table('project_activities')
//                ->groupBy('plotting_name')
//                ->groupBy('place_id')
//                ->get();
//            $projectActivityModels = collect();
//            foreach ($projectActivitiesGroups as $projectActivitiesGroup){
//                $projectActivities = ProjectActivity::where('project_id', $projectEmployee->project_id)
//                    ->where("plotting_name", $projectActivitiesGroup->plotting_name)
//                    ->where("place_id", $projectActivitiesGroup->place_id)
//                    ->get();
//                $dacDetailModel = collect();
//                foreach ($projectActivities as $projectActivity){
//                    $actionName = collect();
//                    if(!empty($projectActivity->action_id)){
//                        $actionList = explode('#', $projectActivity->action_id);
//                        foreach ($actionList as $action){
//                            if(!empty($action)){
//                                $action = Action::find($action);
//                                $actionName .= $action->name. ", ";
////                                $actionName->push($action->name);
//                            }
//                        }
//                    }
//                    $dacDetail = ([
//                        'id'       => $projectActivity->id,
//                        'time'     => Carbon::parse($projectActivity->start)->format('H:i')." - ".Carbon::parse($projectActivity->finish)->format('H:i'),
//                        'action'   => $actionName
//                    ]);
//                    $dacDetailModel->push($dacDetail);
//                }
//                $place = Place::find($projectActivitiesGroup->place_id);
//                $project = Project::find($projectEmployee->project_id);
//                $dacHeaderModel = ([
//                    'place'     => $place->name,
//                    'object'   => $projectActivitiesGroup->plotting_name,
//                    'shift'     => $projectActivitiesGroup->shift_type,
//                    'project'     => $project->name,
//                    'details'   => $dacDetailModel
//                ]);
//                $projectActivityModels->push($dacHeaderModel);
//            }

//            foreach($projectActivities as $projectActivity){
//                $projectCSOModel = ([
//                    'id'       => $projectActivity->employee_id,
//                    'name'     => $projectActivity->place->name." | ".$projectActivity->plotting_name,
//                    'activities'   => $actionName,
//                    'details'   => null
//                ]);
//                $projectActivityModels->push($projectCSOModel);
//            }

            return Response::json($projectActivityModels, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getPlottings error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function submitPlottings(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employeeLeader = $user->employee;
            $projectEmployee = ProjectEmployee::where('employee_id', $employeeLeader->id)->first();

            $employeePlottingModels = json_decode($request->input('employee_plotting_models'));
            foreach ($employeePlottingModels as $employeePlottingModel){
                $id = $employeePlottingModel->employee_id;
                $employee = Employee::find($id);
                $projectEmployeeCso = ProjectEmployee::where('employee_id', $employee->id)->first();

                $plottings = $employeePlottingModel->dac_details;

                foreach ($plottings as $plotting){
                    $projectActivity = ProjectActivitiesHeader::find($plotting);

                    foreach ($projectActivity->project_activities_details as $projectDetail){
                        $schedule = Schedule::create([
                            'project_id'            => $projectActivity->project_id,
                            'employee_id'           => $employee->id,
                            'project_activity_id'   => $projectActivity->id,
                            'project_employee_id'   => $projectEmployeeCso->id,
                            'shift_type'            => $projectDetail->shift_type,
                            'place_id'              => $projectActivity->place_id,
                            'weeks'                 => $projectDetail->weeks,
                            'days'                  => $projectDetail->days,
                            'start'                 => $projectDetail->start,
                            'finish'                => $projectDetail->finish,
                            'status_id'             => 1,
                            'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'created_by'            => $user->id,
                            'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'updated_by'            => $user->id,
                        ]);

                        if(!empty($projectDetail->action_id)){
                            $scheduleDetail = ScheduleDetail::create([
                                'schedule_id'           => $schedule->id,
                                'action_id'             => $projectDetail->action_id,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => $user->id,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => $user->id,
                            ]);
                        }
                    }
                }
            }
            return Response::json("Sukses!", 200);

        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - submitPlottings error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }


    /**
     * Function to get the employee schedule.
     *
     * @param $id
     * @return JsonResponse
     */
    public function employeeSchedule(){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;

            $employeeSchedule = EmployeeProcess::GetEmployeeSchedule($employee->id, $employee->first_name ." ". $employee->last_name);
            return Response::json($employeeSchedule, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to get the employee schedule by leader.
     *
     * @param $id
     * @return JsonResponse
     */
    public function employeeScheduleByLeader(Request $request){
        try{
            $id = $request->input('cso_id');
            $employee = Employee::find($id);

            $employeeSchedule = EmployeeProcess::GetEmployeeSchedule($employee->id, $employee->first_name ." ". $employee->last_name);
            return Response::json($employeeSchedule, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

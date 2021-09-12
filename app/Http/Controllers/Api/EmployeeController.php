<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\EmployeeProcess;
use App\Models\Action;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesDetail;
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
                ->where('status_id', 1)
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
     * Function to listing of the employee CSO.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeCSOByProject(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $employee->id;        try{
            $projectId = $request->input('project_id');
//            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $projectCSOs = ProjectEmployee::where('project_id', $projectId)
                ->where('employee_roles_id', 1)
                ->where('status_id', 1)
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
            Log::error('Api/EmployeeController - getEmployeeCSOByProject error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to listing of the employee CSO for offline feature.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeCSOOffline()
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        try{

            $projectModels = collect();
            $projectLists = DB::table('project_employees')->where('employee_id', $employee->id)->where('status_id', 1)->get();

            foreach ($projectLists as $projectList){
                $project = DB::table('projects')->where('id', $projectList->project_id)->first();

                $projectCSOs = ProjectEmployee::where('project_id', $project->id)
                    ->where('employee_roles_id', 1)
                    ->get();
                $projectCSOModels = collect();
                //check if cleaner null
                if($projectCSOs->count() == 0){
                    return Response::json($projectCSOModels, 482);
                }

                foreach($projectCSOs as $projectCSO){
                    $employee = DB::table('employees')->where('id', $projectCSO->employee_id)->first();
                    $employeeImage = empty($employee->image_path) ? null : asset('storage/employees/'. $employee->image_path);
                    $projectCSOModel = ([
                        'id'       => $projectCSO->employee_id,
                        'name'     => $employee->first_name." ".$employee->last_name,
                        'avatar'   => $employeeImage,
                        'role'   => $projectCSO->employee_role->name,
                        'project_code' => $project->code,
                    ]);
                    $projectCSOModels->push($projectCSOModel);
                }
                $projectDetailModel = collect([
                    'id'            => $project->id,
                    'name'          => $project->name,
                    'address'          => $project->address,
                    'image'          => $project->image_path == null ? asset('storage/projects/default.jpg') : asset('storage/projects/'.$project->image_path),
                    'lat'          => $project->latitude,
                    'lng'          => $project->longitude,
                    'project_code' => $project->code,
                    'project_name' => $project->name,
                    'cso'          => $projectCSOModels,
                ]);
                $projectModels->push($projectDetailModel);
            }

            return Response::json($projectModels, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getEmployeeCSOOffline error EX: '. $ex);
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
                    'name'     => $projectActivity->place->name." | ".$projectActivity->plotting_name,
                    'actions'   => $actionName,
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

//            Log::error('Api/EmployeeController - getDacs ' .json_encode($projectActivityModels));
            return Response::json($projectActivityModels, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeController - getDacs error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    public function submitPlottings(Request $request){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employeeLeader = $user->employee;

            $employeePlottingModels = json_decode($request->input('employee_plotting_models'));
            foreach ($employeePlottingModels as $employeePlottingModel){
                $employeeId = $employeePlottingModel->employee_id;
                $projectEmployeeCso = ProjectEmployee::where('employee_id', $employeeId)->first();

                $plottings = $employeePlottingModel->dac_details;

                foreach ($plottings as $plotting){
                    $projectActivity = ProjectActivitiesHeader::find($plotting);

                    foreach ($projectActivity->project_activities_details as $projectDetail){
                        $existSchedule = Schedule::where('project_activity_id', $projectActivity->id)
                            ->where('project_id', $projectActivity->project_id)
                            ->where('place_id', $projectActivity->place_id)
                            ->first();
                        if(empty($existSchedule)){
                            $schedule = Schedule::create([
                                'project_id'            => $projectActivity->project_id,
                                'employee_id'           => $employeeId,
                                'project_activity_id'   => $projectActivity->id,
                                'project_employee_id'   => $projectEmployeeCso->id,
                                'shift_type'            => $projectDetail->shift_type,
                                'place_id'              => $projectActivity->place_id,
                                'weeks'                 => $projectDetail->weeks,
                                'days'                  => $projectDetail->days,
//                                'start'                 => $projectDetail->start,
//                                'finish'                => $projectDetail->finish,
                                'status_id'             => 1,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => $user->id,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => $user->id,
                            ]);
                            $projectActivityDetails = ProjectActivitiesDetail::where("activities_header_id", $plotting)->get();
                            foreach ($projectActivityDetails as $projectActivityDetail){
                                $scheduleDetail = ScheduleDetail::create([
                                    'schedule_id'           => $schedule->id,
                                    'action_id'             => $projectActivityDetail->action_id,
                                    'start'                 => $projectActivityDetail->start,
                                    'finish'                => $projectActivityDetail->finish,
                                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'created_by'            => $user->id,
                                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'updated_by'            => $user->id,
                                ]);
                            }
                        }
                        else{
                            $existSchedule->employee_id = $employeeId;
                            $existSchedule->project_employee_id = $projectEmployeeCso->id;
                            $existSchedule->save();
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
            if(empty($employeeSchedule)){
                return Response::json("Tidak ada Jadwal hari ini", 482);
            }
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

            $employeeSchedule = EmployeeProcess::GetEmployeeSchedule($id, $employee->first_name ." ". $employee->last_name);
            if(empty($employeeSchedule)){
                return Response::json("Tidak ada Jadwal hari ini", 482);
            }
            return Response::json($employeeSchedule, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to get the employee checkin/out Log.
     *
     * @param $id
     * @return JsonResponse
     */
    public function employeeCheckInOutLog(Request $request){
        try{
            $projectId = $request->input('project_id');

            $startDate = Carbon::parse($request->input('start_date'))->format('Y-m-d 00:00:00');
            $finishDate = Carbon::parse($request->input('finish_date'))->format('Y-m-d 23:59:59');

            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $employeeId = $employee->id;
            $employeeName = $employee->first_name." ".$employee->last_name;
            $id = $request->input('employee_id');

            //employee_id = 0 => leader, otherwise
            if($id > 0){
                $employeeDb = Employee::where('id', $id)->first();
                $employeeId = $employeeDb->id;
                $employeeName = $employeeDb->first_name." ".$employeeDb->last_name;

                $employeeSchedule = EmployeeProcess::GetEmployeeScheduleV2($employeeId, $projectId, $employeeName, $startDate, $finishDate);
                if(empty($employeeSchedule)){
                    return Response::json("Tidak ada Date", 482);
                }

                return Response::json($employeeSchedule, 200);
            }
            else{
                $projectEmployeeCsos = ProjectEmployee::where('project_id', $projectId)
                    ->where('employee_roles_id', 1)
                    ->where('status_id', 1)
                    ->get();
                $employeeSchedules = collect();
                foreach ($projectEmployeeCsos as $projectEmployeeCso){
                    $employeeDb = Employee::where('id', $projectEmployeeCso->employee_id)->first();
                    $employeeId = $employeeDb->employee_id;
                    $employeeName = $employeeDb->first_name." ".$employeeDb->last_name;
                    $employeeSchedule = EmployeeProcess::GetEmployeeScheduleV2($employeeId, $projectId, $employeeName, $startDate, $finishDate);
                    $employeeSchedules->push($employeeSchedule);
                }

            }

        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to get the employee assesment history
     *
     * @param $id
     * @return JsonResponse
     */
    public function employeeAssessments(Request $request){
        try{
            if($request->input('cso_id') != "0"){
                $employeeId = $request->input('cso_id');
            }
            else{
                $userLogin = auth('api')->user();
                $user = User::where('phone', $userLogin->phone)->first();
                $employee = $user->employee;
                $employeeId = $employee->id;
            }
            $attendanceAssessments = Attendance::where('employee_id', $employeeId)
                ->where('status_id', 7)
                ->where('is_done', 1)
                ->where('assessment_leader', 1)
                ->get();

            $assessmentModels = collect();
            $assessHeader = collect();
            foreach ($attendanceAssessments as $attendanceAssessment){
                $scheduleHeader = Schedule::find($attendanceAssessment->schedule_id);

            }



            return Response::json($assessmentModels, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/EmployeeController - employeeSchedule error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

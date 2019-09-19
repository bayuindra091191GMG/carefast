<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
            error_log($ex);
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
            error_log($ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }


    public function employeeSchedule(){
        try{
            $userLogin = auth('api')->user();
            $user = User::where('email', $userLogin->email)->first();
            $employee = $user->employee;

            //get employee schedule
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();
            $schedules = Schedule::where('project_id', $projectEmployee->project_id)->where('project_employee_id', $projectEmployee->id)->get();

            $datetime = Carbon::now('Asia/Jakarta');
            // weekOfMonth will returns 1 for the 7 first days of the month, then 2 from the 8th to
            // the 14th, 3 from the 15th to the 21st, 4 from 22nd to 28th and 5 above
            $todayWeekOfMonth = $datetime->weekOfMonth;
            // dayOfWeekIso returns a number between 1 (monday) and 7 (sunday)
            $todayOfWeek = $datetime->dayOfWeekIso;

            $scheduleModels = collect();
            foreach ($schedules as $schedule){
                //kalau hari dan minggu sama dengan yang ada di DB
                if(strpos($schedule->weeks, $todayWeekOfMonth) === true && strpos($schedule->days, $todayOfWeek) === true){
                    $scheduleDetails = $schedule->schedule_details;
                    $scheduleDetailModels = collect();
                    foreach ($scheduleDetails as $scheduleDetail){
                        $projectObject = ProjectObject::find($scheduleDetail->project_object_id);
                        $objectName = "";
                        $objectName = $objectName.$projectObject->unit_name != "-" ? $projectObject->unit_name : "";
                        $objectName = $objectName.$projectObject->sub1_unit_name != "-" ? $projectObject->sub1_unit_name : "";
                        $objectName = $objectName.$projectObject->sub2_unit_name != "-" ? $projectObject->sub2_unit_name : "";

                        $scheduleDetailModel = [
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
                        'start'             => $schedule->project_id,
                        'finish'            => $schedule->project_id,
                        'schedule_details'  => $scheduleDetailModels
                    ];

                    $scheduleModels->push($scheduleModel);
                }
            }
            return Response::json($scheduleModels, 200);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

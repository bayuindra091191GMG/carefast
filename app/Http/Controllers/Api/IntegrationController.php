<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\TempInsysEmploye;
use App\Models\TempInsysProject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class IntegrationController extends Controller
{
    /**
     * Function API to Receive all the Employee and Save them.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function employees(Request $request){
        try {
            $employees = $request->json()->all();
            Log::channel('in_sys')
                ->info('API/IntegrationController - employees DATA : '.json_encode($employees));

            $nonActiveEmp = DB::statement("update employees set status_id = 2 where id > 29 and status_id = 1 and employee_role_id < 4");
            sleep(60);

            $ct = 1;
            foreach ($employees as $employee) {
                if($ct %2000 == 0){
                    sleep(30);
                }
                $rules = array(
                    'code'          => 'required',
                    'first_name'    => 'required',
//                    'last_name'     => 'required',
//                    'phone'         => 'required',
                    'dob'           => 'required',
                    'nik'           => 'required',
//                    'address'       => 'required',
                    'role'          => 'required'
                );

                $validator = Validator::make($employee, $rules);

                if ($validator->fails()) {
                    return Response::json([
                        'errors'=> $validator->messages(),
                        'meta'  => [
                            'http_status' => 400
                        ]
                    ], 400);
                }

                try{
//                    $tempEmployee = TempInsysEmploye::create([
//                        'code' => $employee['code'],
//                        'first_name' => $employee['first_name'],
//                        'last_name' => $employee['last_name'],
//                        'phone' => $employee['phone'],
//                        'dob' => $employee['dob'],
//                        'nik' => $employee['nik'],
//                        'address' => $employee['address'],
//                        'role' => $employee['role'],
//                    ]);


                    $phone = "";
                    if(!empty($employee['phone'])){
                        if($employee['phone'] == "-" || $employee['phone'] == "--" ||
                            $employee['phone'] == " " || $employee['phone'] == "" || $employee['phone'] == " " ||
                            $employee['phone'] == "XXX" || $employee['phone'] == "12345"){
                            $phone = "";
                        }
                        else{
                            $phone = $employee['phone'];
                        }
                    }
                    $phone = str_replace(' ', '', $phone);
                    $phone = str_replace('-', '', $phone);
                    $phone = str_replace('.', '', $phone);
                    $employeeChecking = Employee::where('code', $employee['code'])->first();
//                    if (!DB::table('employees')->where('code', $employee['code'])->exists()) {
                    if (empty($employeeChecking)) {
                        $nEmployee = Employee::create([
                            'code' => $employee['code'],
                            'first_name' => $employee['first_name'],
                            'last_name' => $employee['last_name'] ?? "",
//                            'phone' => $phone,
                            'dob' => $employee['dob'],
                            'nik' => $employee['nik'],
                            'address' => $employee['address'],
                            'employee_role_id' => $employee['role'],
                            'status_id' => 1
                        ]);

                        User::create([
                            'employee_id' => $nEmployee->id,
                            'name' => $employee['first_name'] . ' ' . $employee['last_name'] ?? "",
//                            'phone' => $phone,
                            'status_id' => 1,
                            'password' => Hash::make('carefastid')
                        ]);
                    } else {
                        $employeeChecking = Employee::where('code', $employee['code'])->first();
                        $employeeChecking->first_name = $employee['first_name'];
                        $employeeChecking->last_name = $employee['last_name'] ?? "";
//                        $employeeChecking->phone = $phone;
                        $employeeChecking->dob = $employee['dob'];
                        $employeeChecking->nik = $employee['nik'];
                        $employeeChecking->employee_role_id = $employee['role'];
                        $employeeChecking->address = $employee['address'] ?? "";
                        $employeeChecking->status_id = 1;
                        $employeeChecking->save();

                        $oUser = User::where('employee_id', $employeeChecking->id)->first();
                        if(empty($oUser)){
                            User::create([
                                'employee_id' => $employeeChecking->id,
                                'name' => $employee['first_name'] . ' ' . $employee['last_name'] ?? "",
//                                'phone' => $phone,
                                'status_id' => 1,
                                'password' => Hash::make('carefastid')
                            ]);
                        }
                        else{
//                            $oUser->phone = $phone;
                            $oUser->name = $employee['first_name'] . ' ' . $employee['last_name'] ?? "";
                            $oUser->status_id = 1;
                            $oUser->save();
                        }
                    }
                }
                catch (\Exception $ex){
                    Log::channel('in_sys')
                        ->error('API/IntegrationController - inside employees error data: '.json_encode($employee));
                    Log::channel('in_sys')->error('API/IntegrationController - inside employees error EX: '. $ex);
                    return Response::json([
                        'message' => $ex
                    ], 500);
                }
                $ct++;
            }

            sleep(30);
            $nonActiveEmpPhone = DB::statement("update employees set phone = '' where status_id = 2");

            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Log::channel('in_sys')
                ->info('API/IntegrationController - employees PROCESS DONE at '.$now);
            return Response::json([
                'message' => 'Success Updating Employee Data!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - employees error EX: '. $ex);
//            Log::error('API/IntegrationController - employees error EX: '. $ex);
            return Response::json([
                'message' => $ex
            ], 500);
        }
    }

    /**
     * Function to receive all Projects.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function projects(Request $request){
        try {
            $projects = $request->json()->all();
            Log::channel('in_sys')
                ->info('API/IntegrationController - projects DATA : '.json_encode($projects));

            foreach ($projects as $project) {
                $rules = array(
                    'code'          => 'required',
                    'name'          => 'required',
//                    'description'   => 'required',
//                    'phone'         => 'required',
                    'start_date'    => 'required',
                    'finish_date'   => 'required',
//                    'address'       => 'required'
                );

                $validator = Validator::make($project, $rules);

                if ($validator->fails()) {
                    return Response::json([
                        'errors'=> $validator->messages(),
                        'meta'  => [
                            'http_status' => 400
                        ]
                    ], 400);
                }
                //add to temp table

//                TempInsysProject::create([
//                    'code' => $project['code'],
//                    'name' => $project['name'],
//                    'phone' => $project['phone'],
//                    'address' => $project['address'],
//                    'description' => $project['description'],
//                    'start_date' => $project['start_date'],
//                    'finish_date' => $project['finish_date'],
//                ]);

                if (!DB::table('projects')->where('code', $project['code'])->exists()) {
                    $nProject = Project::create([
                        'code' => $project['code'],
                        'name' => $project['name'],
                        'phone' => $project['phone'] ?? "12345",
                        'address' => $project['address'] ?? " ",
                        'description' => $project['description'] ?? " ",
                        'start_date' => $project['start_date'],
                        'finish_date' => $project['finish_date'],
                        'status_id' => 1,
                        'customer_id'       => '2#4#62#63#64#65#66#67#68#69#70#71#72#73',
                        'latitude'          => '-6.1560448',
                        'longitude'         => '106.79019979999998',
                        'total_manday'      => 10,
                        'total_mp_onduty'   => 10,
                        'total_mp_off'      => 10,
                        'total_manpower'    => 10,
                        'total_manpower_used'=> 0,
                    ]);
//                    if(strpos($project['description'], "PUTUS KONTRAK") !== false){
//                        $nProject->status_id = 2;
//                    }
                    $nProject->save();
                } else {
                    $oProject = Project::where('code', $project['code'])->first();
                    $oProject->name = $project['name'];
                    $oProject->phone = $project['phone'];
                    $oProject->address = $project['address'];
                    $oProject->description = $project['description'];
                    $oProject->start_date = $project['start_date'];
                    $oProject->finish_date = $project['finish_date'];
                    $oProject->status_id = 1;
//                    if(strpos($project['description'], "PUTUS KONTRAK") !== false){
//                        $oProject->status_id = 2;
//                    }
                    $oProject->save();
                }
            }

            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Log::channel('in_sys')
                ->info('API/IntegrationController - projects PROCESS DONE at '.$now);
            return Response::json([
                'message' => 'Success Updating Projects!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - projects error EX: '. $ex);
//            Log::error('API/IntegrationController - projects error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }

    /**
     * Function to update Job Assigment.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jobAssignments(Request $request){
        try{
            $projects = $request->json()->all();
            sleep(420);
            Log::channel('in_sys')
                ->info('API/IntegrationController - jobAssignments DATA : '.json_encode($projects));
//            Log::channel('in_sys')
//                ->info('API/IntegrationController - jobAssignments PROJECT COUNT : '.$projects->count());

            foreach ($projects as $project){
                $rules = array(
                    'project_code'  => 'required',
                    'employee_codes' => 'required|array|min:1'
                );

                $validator = Validator::make($project, $rules);

                if ($validator->fails()) {
                    return Response::json([
                        'errors'=> $validator->messages(),
                        'meta'  => [
                            'http_status' => 400
                        ]
                    ], 400);
                }

                if(DB::table('projects')->where('code', $project['project_code'])->exists()){
                    $nProject = Project::where('code', $project['project_code'])->first();

                    $projectEmployees = ProjectEmployee::where('project_id', $nProject->id)
                        ->where('status_id', 1)
                        ->where('employee_roles_id', '<', 4)
                        ->get();
                    foreach($projectEmployees as $projectEmployee){
                        $projectEmployee->status_id = 0;
                        $projectEmployee->save();
                    }
//                    Log::channel('in_sys')->info('API/IntegrationController - jobAssignments checkpoint 1 change status employee, project='.$nProject->code);

                    foreach ($project['employee_codes'] as $employee){
                        if(DB::table('employees')->where('code', $employee)->exists()){
                            $nEmployee = Employee::where('code', $employee)->first();

                            $projectEmployeeDB = ProjectEmployee::where('employee_id', $nEmployee->id)
                                ->where('project_id', $nProject->id)->first();
                            if(empty($projectEmployeeDB)){
                                ProjectEmployee::create([
                                    'project_id'        => $nProject->id,
                                    'employee_id'       => $nEmployee->id,
                                    'employee_roles_id' => $nEmployee->employee_role_id,
                                    'status_id'         => 1
                                ]);
                            }
                            else{
                                $projectEmployeeDB->employee_roles_id = $nEmployee->employee_role_id;
                                $projectEmployeeDB->status_id = 1;
                                $projectEmployeeDB->save();
                            }
                        }
                    }
//                    Log::channel('in_sys')->info('API/IntegrationController - jobAssignments checkpoint 2 create/edit employee, project='.$nProject->code);
                }

            }

            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Log::channel('in_sys')
                ->info('API/IntegrationController - jobAssignments PROCESS DONE at '.$now);
            return Response::json([
                'message' => 'Success Updating Job Assigment!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - jobAssignments error EX: '. $ex);
//            Log::error('API/IntegrationController - jobAssignments error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }

    /**
     * Function to get Attendances with Filters.
     * @param Request $request
     * @return
     */
    public function getAttendances(){
        try{
            $projectCode = Input::get('projectCode');
            $startDate = Input::get('beginDate');
            $endDate = Input::get('endDate');
            $project = Project::where('code', $projectCode)->first();

            if(!DB::table('projects')->where('code', $projectCode)->exists()){
                return Response::json([
                    'error' => 'Project code not found!'
                ], 400);
            }
            if(empty($startDate) || empty($endDate)){
                return Response::json([
                    'error' => 'Please provide Begin Date and End Date!'
                ], 400);
            }

            $attendanceAbsents = AttendanceAbsent::where('project_id', $project->id)
                ->where('status_id', 6)
                ->whereBetween('created_at', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
                ->get();

            $dataModel = collect();
//            Log::channel('in_sys')
//                ->info('API/IntegrationController - getAttendances data 0 '. count($attendanceAbsents));
            if(count($attendanceAbsents) < 1){
                return Response::json([
                    'error' => 'No Attendances found within allocated time range!'
                ], 400);
            }

//            timestamp: ...,
//            projectCode: XXX, //assume project codes are synced
//            beginDate: ..., //date YYYY-MM-DD
//            endDate: ..., //date YYYY-MM-DD
//            data: [
//              {
//                  employeeId: ...,
//                  employeeCode: ...,
//                  transDate: ..., //date YYYY-MM-DD
//                  shiftCode: ..., // 1|2|3 or A|B|C or whatever
//                  attendanceIn: ..., //timestamp YYYY-MM-DD HH:mm:ss
//                  attendanceOut: ..., //timestamp YYYY-MM-DD HH:mm:ss
//                  attendanceStatus: ..., // H=Hadir, A=Alpa, U=Unknown
//                  ]
//              }
            foreach ($attendanceAbsents as $attendanceAbsent){
                $status = "U";
                $attendanceOut = "";
                if($attendanceAbsent->is_done == 0){
                    $status = "A";
                }
                else{
                    if(!empty($attendanceAbsent->date_checkout)){
                        $status = "H";
                        $attendanceOut = $attendanceAbsent->date_checkout->format('Y-m-d H:i:s');
                    }
                    else{
                        $status = "A";
                    }
                }
                $projectCSOModel = ([
                    'employeeId'        => $attendanceAbsent->employee->id,
                    'employeeCode'      => $attendanceAbsent->employee->code,
                    'transDate'         => $attendanceAbsent->created_at->format('Y-m-d'),
                    'shiftCode'         => $attendanceAbsent->shift_type ?? 0,
                    'attendanceIn'      => $attendanceAbsent->date->format('Y-m-d H:i:s'),
                    'attendanceOut'     => $attendanceOut,
                    'attendanceStatus'   => $status,
                ]);
                $dataModel->push($projectCSOModel);
//                Log::channel('in_sys')
//                    ->info('API/IntegrationController - getAttendances data 2 '. json_encode($projectCSOModel));
            }
            $date = Carbon::now('Asia/Jakarta')->timestamp;
            $returnModel = collect([
                'timestamp'     => $date,
                'projectCode'   => $projectCode,
                'beginDate'     => $startDate,
                'endDate'       => $endDate,
                'data'          => $dataModel,
            ]);
            Log::channel('in_sys')
                ->info('API/IntegrationController - getAttendances data 3 '.json_encode($returnModel));
            return Response::json($returnModel, 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - getAttendances error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }
}

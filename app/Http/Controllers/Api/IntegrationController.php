<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Address;
use App\Models\AttendanceAbsent;
use App\Models\Configuration;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

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

            foreach ($employees as $employee) {
                if (!DB::table('employees')->where('code', $employee['code'])->exists()) {
                    $nEmployee = Employee::create([
                        'code' => $employee['code'],
                        'first_name' => $employee['first_name'],
                        'last_name' => $employee['last_name'],
                        'phone' => $employee['phone'],
                        'dob' => $employee['dob'],
                        'nik' => $employee['nik'],
                        'address' => $employee['address'],
                        'employee_role' => $employee['role'],
                        'status_id' => 1
                    ]);

                    User::create([
                        'employee_id' => $nEmployee->id,
                        'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                        'phone' => $employee['phone'],
                        'password' => Hash::make('carefastid')
                    ]);
                } else {
                    $oEmployee = Employee::where('code', $employee['code'])->first();
                    $oEmployee->first_name = $employee['first_name'];
                    $oEmployee->last_name = $employee['last_name'];
                    $oEmployee->phone = $employee['phone'];
                    $oEmployee->dob = $employee['dob'];
                    $oEmployee->nik = $employee['nik'];
                    $oEmployee->address = $employee['address'];
                    $oEmployee->save();

                    $oUser = User::where('employee_id', $oEmployee->id)->first();
                    $oUser->phone = $employee['phone'];
                    $oUser->name = $employee['first_name'] . ' ' . $employee['last_name'];
                    $oUser->save();
                }
            }

            return Response::json([
                'message' => 'Success Updating Employee Data!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::error('API/IntegrationController - employees error EX: '. $ex);
            return Response::json([
                'error' => $ex
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

            foreach ($projects as $project) {
                if (!DB::table('projects')->where('code', $project['code'])->exists()) {
                    Project::create([
                        'code' => $project['code'],
                        'name' => $project['name'],
                        'phone' => $project['phone'],
                        'address' => $project['address'],
                        'description' => $project['description'],
                        'start_date' => $project['start_date'],
                        'finish_date' => $project['finish_date'],
                        'status_id' => 1,
                        'customer_id'       => '2#4',
                        'latitude'          => '-6.1560448',
                        'longitude'         => '106.79019979999998',
                        'total_manday'      => 10,
                        'total_mp_onduty'   => 10,
                        'total_mp_off'      => 10,
                        'total_manpower'    => 10,
                        'total_manpower_used'=> 0,
                    ]);
                } else {
                    $oProject = Project::where('code', $project['code'])->first();
                    $oProject->name = $project['name'];
                    $oProject->phone = $project['phone'];
                    $oProject->address = $project['address'];
                    $oProject->description = $project['description'];
                    $oProject->start_date = $project['start_date'];
                    $oProject->finish_date = $project['finish_date'];
                    $oProject->save();
                }
            }

            return Response::json([
                'message' => 'Success Updating Projects!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::error('API/IntegrationController - projects error EX: '. $ex);
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

            foreach ($projects as $project){
                if(DB::table('projects')->where('code', $project['project_code'])->exists()){
                    $nProject = Project::where('code', $project['project_code'])->first();

                    foreach ($project['employee_codes'] as $employee){
                        if(DB::table('employees')->where('code', $employee)->exists()){
                            $nEmployee = Employee::where('code', $employee)->first();
                            ProjectEmployee::create([
                                'project_id'        => $nProject->id,
                                'employee_id'       => $nEmployee->id,
                                'employee_roles_id' => $nEmployee->employee_role_id,
                                'status_id'         => 1
                            ]);
                        }
                    }
                }

            }

            return Response::json([
                'message' => 'Success Updating Job Assigment!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::error('API/IntegrationController - jobAssignments error EX: '. $ex);
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
    public function getAttendances(Request $request){
        try{
            if(!DB::table('projects')->where('code', $request->project_code)->exists()){
                return Response::json([
                    'error' => 'Project code not found!'
                ], 400);
            }

            if($request->start_date != null && $request->finish_date != null && $request->status != null){
                $result = AttendanceAbsent::whereHas('project', function($query) use ($request){
                    $query->where('code', $request->project_code);
                })
                    ->whereBetween('created_at', array($request->start_date.' 00:00:00', $request->finish_date.' 23:59:00'))
                    ->where('status_id', $request->status)
                    ->get();
            }
            else if($request->start_date != null && $request->finish_date != null){
                $result = AttendanceAbsent::whereHas('project', function($query) use ($request){
                    $query->where('code', $request->project_code);
                })
                    ->whereBetween('created_at', array($request->start_date.' 00:00:00', $request->finish_date.' 23:59:00'))
                    ->get();
            }
            else {
                $result = AttendanceAbsent::whereHas('project', function ($query) use ($request) {
                    $query->where('code', $request->project_code);
                })->get();
            }

            return Response::json([
                'message' => 'Success Getting Attendance Data!',
                'result'  => $result
            ], 200);
        }
        catch (\Exception $ex){
            Log::error('API/IntegrationController - getAttendances error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }
}

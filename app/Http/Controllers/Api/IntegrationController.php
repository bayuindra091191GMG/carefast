<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Address;
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
                if (!DB::table('employees')->where('code', $employee['code'])->any()) {
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
                if (!DB::table('projects')->where('code', $project['code'])->any()) {
                    Project::create([
                        'code' => $project['code'],
                        'name' => $project['name'],
                        'phone' => $project['phone'],
                        'address' => $project['address'],
                        'description' => $project['description'],
                        'start_date' => $project['start_date'],
                        'finish_date' => $project['finish_date'],
                        'status_id' => 1
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
                if(DB::table('projects')->where('code', $project['project_code'])->any()){
                    $nProject = Project::where('code', $project['project_code'])->first();

                    foreach ($project['employee_codes'] as $employee){
                        if(DB::table('employees')->where('code', $employee)->any()){
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
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }
}

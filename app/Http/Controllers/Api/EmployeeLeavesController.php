<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\AttendanceOvertime;
use App\Models\AttendancePermission;
use App\Models\AttendanceSickLeafe;
use App\Models\Employee;
use App\Models\ProjectEmployee;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;

class EmployeeLeavesController extends Controller
{
    /**
     * Function to get single sick leaves.
     *
     * @return JsonResponse
     */
    public function sickLeaves()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();

            $employees = AttendanceSickLeafe::where('employee_id', $user->employee_id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }
            $processBy = "";
            Log::error('Api/EmployeeLeavesController - sickLeaves sickLeave->updated_by: '. $employees->updated_by);
            if(!empty($employees->updated_by)){
                $user = User::where('id', $employees->updated_by)->first();
                $employeeApprove = Employee::where('id', $user->employee_id)->first();
                $processBy = $employeeApprove->first_name. " ". $employeeApprove->last_name;
            }
            $attImage = empty($employees->image_path) ? null : asset('storage/attendance_sick_leaves/'. $employees->image_path);
            $model = ([
                'id'                => $employees->id,
                'approval_status'   => $employees->is_approve,
                'processed_by'        => $processBy,
                'project_name'      => $employees->project->name,
                'employee_name'     => $employees->employee->first_name. " ".$employees->employee->last_name,
                'employee_code'     => $employees->employee->code,
                'date'              => Carbon::parse($employees->date)->format('d M Y H:i:s'),
                'description'       => $employees->description,
                'image_path'        => $attImage,
            ]);

            return Response::json($model, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - sickLeaves error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to get list sick leaves.
     *
     * @return JsonResponse
     */
    public function getSickLeaves()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->get();
            $projectEmployeeArr = [];
            foreach ($projectEmployee as $prjtEmp){
                array_push($projectEmployeeArr, $prjtEmp->project_id);
            }
            if($employee->employee_role_id == 1){
                $sickLeaves = AttendanceSickLeafe::where('project_id', $projectEmployee->project_id)
                    ->where('employee_id', $id)
                    ->orderbyDesc('created_at')
                    ->get();
            }
            else{
                $sickLeaves = AttendanceSickLeafe::whereIn('project_id', $projectEmployeeArr)
                    ->orderbyDesc('created_at')
                    ->get();
            }
            $models = collect();
            if($sickLeaves->count() == 0){
                return Response::json($models, 482);
            }
            foreach($sickLeaves as $sickLeave){
                $processBy = "";
                Log::error('Api/EmployeeLeavesController - getSickLeaves sickLeave->updated_by: '. $sickLeave->updated_by);
                if(!empty($sickLeave->updated_by)){
                    $user = User::where('id', $sickLeave->updated_by)->first();
                    $employeeApprove = Employee::where('id', $user->employee_id)->first();
                    $processBy = $employeeApprove->first_name. " ". $employeeApprove->last_name;
                }
                $attImage = empty($sickLeave->image_path) ? null : asset('storage/attendance_sick_leaves/'. $sickLeave->image_path);
                $model = collect([
                    'id'                => $sickLeave->id,
                    'approval_status'   => $sickLeave->is_approve,
                    'processed_by'        => $processBy,
                    'project_name'      => $sickLeave->project->name,
                    'employee_name'     => $sickLeave->employee->first_name.' '.$sickLeave->employee->last_name,
                    'employee_code'     => $sickLeave->employee->code,
                    'employee_role_id'  => $sickLeave->employee->employee_role_id,
                    'date'              => Carbon::parse($sickLeave->date)->format('d M Y H:i:s'),
                    'description'       => $sickLeave->description,
                    'image_path'        => $attImage,
                ]);
                $models->push($model);
            }

            return Response::json($models, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - getSickLeaves error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to submit sick leaves.
     *
     * @param $id
     * @return JsonResponse
     */
    public function sickLeavesSubmit(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);

            $employee = Employee::where('id', $user->employee_id)->first();

            $data = json_decode($request->input('sick_leave_model'));
//            Log::error('Api/EmployeeLeavesController - sickLeavesSubmit data: '. $request->input('sick_leave_model'));

            if($data->sick_leave_id == 0){
                $newAttendanceSick = AttendanceSickLeafe::create([
                    'employee_id'  => $data->employee_id,
                    'project_id'   => $data->project_id,
                    'date'         => Carbon::parse($data->date)->format('Y-m-d H:i:s'),
                    'description'  => $data->description,
                    'is_approve'   => 0,
                    'created_at'   => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'   => $user->id,
                ]);

                //add to attendance record
                $newAttendance = AttendanceAbsent::create([
                    'employee_id'   => $newAttendanceSick->employee_id,
                    'project_id'    => $newAttendanceSick->project_id,
                    'shift_type'    => 1,
                    'is_done'       => 1,
                    'date'          => Carbon::parse($newAttendanceSick->date)->toDateTimeString(),
                    'date_checkout' => Carbon::parse($newAttendanceSick->date)->toDateTimeString(),
                    'status_id'     => 6,
                    'image_path'    => $newAttendanceSick->image_path,
                    'created_by'     => $newAttendanceSick->employee_id,
                    'type'          => "S",
                    'description'   => "Attendance sick ID=".$newAttendanceSick->id
                ]);
                $newAttendance = AttendanceAbsent::create([
                    'employee_id'   => $newAttendanceSick->employee_id,
                    'project_id'    => $newAttendanceSick->project_id,
                    'shift_type'    => 1,
                    'is_done'       => 1,
                    'date'          => Carbon::parse($newAttendanceSick->date)->toDateTimeString(),
                    'status_id'     => 7,
                    'image_path'    => $newAttendanceSick->image_path,
                    'created_by'    => $newAttendanceSick->employee_id,
                    'type'          => "S",
                    'description'   => "Attendance sick ID=".$newAttendanceSick->id
                ]);
            }
            else{
                $newAttendanceSick = AttendanceSickLeafe::where('id', $data->sick_leave_id)->first();
            }

            $employeeDB = Employee::where('id', $data->employee_id)->first();
            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendance_sick_leaves/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employeeDB->first_name . ' ' . $employeeDB->last_name . '_attendance_sick_leaves_'. $newAttendanceSick->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendanceSick->image_path = $todayStr.'/'.$filename;
                $newAttendanceSick->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee ". $employeeDB->first_name . ' ' . $employeeDB->last_name ." Mengajukan  Ijin Sakit";
            $data = array(
                "type_id" => 302,
                "sick_leave_id" => $newAttendanceSick->id,
                "sick_leave_model" => $newAttendanceSick,
            );
            $ProjectEmployees = ProjectEmployee::where('project_id', $newAttendanceSick->project_id)
                ->where('employee_roles_id', '>', 1)
                ->where('employee_roles_id', '<', 4)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    if($ProjectEmployee->employee_id != $employee->id){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                    }
                }
            }

            return Response::json([
                'message' => "Success Submit sick leaves!"
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - sickLeavesSubmit error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    /**
     * Function to Approve sick leaves by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function sickLeaveApprove(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = Employee::where('id', $user->employee_id)->first();
        $id = $request->input('id');

        try{
            //edit is_approve data
            $sickLeaves = AttendanceSickLeafe::find($id);
            $sickLeaves->is_approve = 1;
            $sickLeaves->updated_by = $user->id;
            $sickLeaves->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $sickLeaves->save();

            $attSicks = AttendanceAbsent::where('employee_id', $sickLeaves->employee_id)
                ->where('project_id', $sickLeaves->project_id)
                ->where('description', 'like', '%sick ID='.$id.'%')
                ->get();
            foreach($attSicks as $attSick){
                $attSick->date_checkout = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $attSick->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $attSick->updated_by = $user->id;
                $attSick->type = "SR";
                $attSick->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Sakit Disetujui";
            $data = array(
                "type_id" => 302,
                "sick_leave_model" => $sickLeaves,
            );
            if($sickLeaves->employee_id != $employee->id){
                $user = User::where('employee_id', $sickLeaves->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success approve", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - sickLeaveApprove error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to Reject sick leaves by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function sickLeaveReject(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
        $employee = Employee::where('id', $user->employee_id)->first();
        $id = $request->input('id');
        try{
            //edit is_approve data
            $sickLeaves = AttendanceSickLeafe::find($id);
            $sickLeaves->is_approve = 2;
            $sickLeaves->updated_by = $user->id;
            $sickLeaves->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $sickLeaves->save();

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Sakit Ditolak";
            $data = array(
                "type_id" => 302,
                "sick_leave_model" => $sickLeaves,
            );
            if($sickLeaves->employee_id != $employee->id){
                $user = User::where('employee_id', $sickLeaves->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success reject", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - sickLeaveReject error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /*================================================================================================================*/
    /**
     * Function to get single permission.
     *
     * @return JsonResponse
     */
    public function permissions()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $employees = AttendancePermission::where('employee_id', $id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }

            $processBy = "";
            Log::error('Api/EmployeeLeavesController - permissions permissions->updated_by: '. $employees->updated_by);
            if(!empty($employees->updated_by)){
                Log::error('Api/EmployeeLeavesController - permissions permissions->updated_by: '. $employees->updated_by);
                $user = User::where('id', $employees->updated_by)->first();
                $employeeApprove = Employee::where('id', $user->employee_id)->first();
                $processBy = $employeeApprove->first_name. " ". $employeeApprove->last_name;
            }
            $attImage = empty($employees->image_path) ? null : asset('storage/attendance_permission_leaves/'. $employees->image_path);
            $model = ([
                'id'                => $employees->id,
                'approval_status'   => $employees->is_approve,
                'processed_by'        => $processBy,
                'project_name'      => $employees->project->name,
                'employee_name'     => $employees->employee->first_name.' '.$employees->employee->last_name,
                'employee_code'     => $employees->employee->code,
                'description'       => $employees->description,
                'date_start'        => Carbon::parse($employees->date_start)->format('d M Y'),//H:i:s
                'date_end'          => Carbon::parse($employees->date_end)->format('d M Y'),
                'image_path'       => $attImage,
            ]);

            return Response::json($model, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - permissions error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to get list permission.
     *
     * @return JsonResponse
     */
    public function getPermissions()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            if($employee->employee_role_id == 1){
                $permissions = AttendancePermission::where('project_id', $projectEmployee->project_id)
                    ->where('employee_id', $id)
                    ->orderbyDesc('created_at')
                    ->get();
            }
            else{
                $permissions = AttendancePermission::where('project_id', $projectEmployee->project_id)
                    ->orderbyDesc('created_at')
                    ->get();
            }
            $models = collect();
            if($permissions->count() == 0){
                return Response::json($models, 482);
            }
            foreach($permissions as $permission){
                $processBy = "";
                Log::error('Api/EmployeeLeavesController - getPermissions permissions->updated_by: '. $permission->updated_by);
                if(!empty($permission->updated_by)){
                    $user = User::where('id', $permission->updated_by)->first();
                    $employeeApprove = Employee::where('id', $user->employee_id)->first();
                    $processBy = $employeeApprove->first_name. " ". $employeeApprove->last_name;
                }
                $attImage = empty($permission->image_path) ? null : asset('storage/attendance_permission_leaves/'. $permission->image_path);
                $model = collect([
                    'id'                => $permission->id,
                    'approval_status'   => $permission->is_approve,
                    'processed_by'        => $processBy,
                    'project_name'      => $permission->project->name,
                    'employee_name'     => $permission->employee->first_name.' '.$permission->employee->last_name,
                    'employee_code'     => $permission->employee->code,
                    'employee_role_id'  => $permission->employee->employee_role_id,
                    'description'       => $permission->description,
                    'date_start'        => Carbon::parse($permission->date_start)->format('d M Y'), //H:i:s
                    'date_end'          => Carbon::parse($permission->date_end)->format('d M Y'),//H:i:s
                    'image_path'       => $attImage,
                ]);
                $models->push($model);
            }
            return Response::json($models, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - getPermissions error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to submit permission.
     *
     * @param $id
     * @return JsonResponse
     */
    public function permissionSubmit(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $data = json_decode($request->input('permission_model'));

            if($data->permission_leave_id == 0){
                $newAttendancePermission = AttendancePermission::create([
                    'employee_id'  => $data->employee_id,
                    'project_id'   => $data->project_id,
                    'date_start'         => Carbon::parse($data->date_start)->format('Y-m-d H:i:s'),
                    'date_end'         => Carbon::parse($data->date_end)->format('Y-m-d H:i:s'),
                    'description'  => $data->description,
                    'is_approve'   => 0,
                    'created_at'   => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'   => $user->id,
                ]);

                for($i=0; $i<10000; $i++){
                    $currentDate = Carbon::parse($newAttendancePermission->date_start)->addDays($i);
                    if($currentDate > Carbon::parse($newAttendancePermission->date_end)){
                        break;
                    }
                    //add to attendance record
                    $newAttendance = AttendanceAbsent::create([
                        'employee_id'   => $newAttendancePermission->employee_id,
                        'project_id'    => $newAttendancePermission->project_id,
                        'shift_type'    => 1,
                        'is_done'       => 1,
                        'date'          => $currentDate->toDateTimeString(),
                        'date_checkout' => $currentDate->toDateTimeString(),
                        'status_id'     => 6,
                        'image_path'    => $newAttendancePermission->image_path,
                        'created_by'     => $newAttendancePermission->employee_id,
                        'type'          => "I",
                        'description'   => "Attendance permission ID=".$newAttendancePermission->id
                    ]);
                    $newAttendance = AttendanceAbsent::create([
                        'employee_id'   => $newAttendancePermission->employee_id,
                        'project_id'    => $newAttendancePermission->project_id,
                        'shift_type'    => 1,
                        'is_done'       => 1,
                        'date'          => $currentDate->toDateTimeString(),
                        'status_id'     => 7,
                        'image_path'    => $newAttendancePermission->image_path,
                        'created_by'    => $newAttendancePermission->employee_id,
                        'type'          => "I",
                        'description'   => "Attendance permission ID=".$newAttendancePermission->id
                    ]);
                }
            }
            else{
                $newAttendancePermission = AttendancePermission::where('id', $data->permission_leave_id)->first();
            }

            $employeeDB = Employee::where('id', $data->employee_id)->first();
            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendance_permission_leaves/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employeeDB->first_name . ' ' . $employeeDB->last_name . '_attendance_permission_leaves_'. $newAttendancePermission->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendancePermission->image_path = $todayStr.'/'.$filename;
                $newAttendancePermission->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee ". $employeeDB->first_name . ' ' . $employeeDB->last_name ." Mengajukan Ijin";
            $data = array(
                "type_id" => 302,
                "permission_id" => $newAttendancePermission->id,
                "permission_model" => $newAttendancePermission,
            );
            $ProjectEmployees = ProjectEmployee::where('project_id', $newAttendancePermission->project_id)
                ->where('employee_roles_id', '>', 1)
                ->where('employee_roles_id', '<', 4)
                ->get();
            if($ProjectEmployees->count() >= 0){
                foreach ($ProjectEmployees as $ProjectEmployee){
                    if($ProjectEmployee->employee_id != $employee->id){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                    }
                }
            }

            return Response::json([
                'message' => "Success submit permission!",
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - permissionSubmit error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }


    /**
     * Function to Approve permission by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function permissionsApprove(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
        $employee = Employee::where('id', $user->employee_id)->first();
        $id = $request->input('id');
        try{
            //edit is_approve data
            $permission = AttendancePermission::find($id);
            $permission->is_approve = 1;
            $permission->updated_by = $user->id;
            $permission->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $permission->save();

            $attPermissions = AttendanceAbsent::where('employee_id', $permission->employee_id)
                ->where('project_id', $permission->project_id)
                ->where('description', 'like', '%permission ID='.$id.'%')
                ->get();
            foreach($attPermissions as $attPermission){
                $attPermission->date_checkout = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $attPermission->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
                $attPermission->updated_by = $user->id;
                $attPermission->type = "IR";
                $attPermission->save();
            }

//            for($i=0; $i<10000; $i++){
//                $currentDate = Carbon::parse($permission->date_start)->addDays($i);
//                if($currentDate > Carbon::parse($permission->date_end)){
//                    break;
//                }
//                //add to attendance record
//                $newAttendance = AttendanceAbsent::create([
//                    'employee_id'   => $permission->employee_id,
//                    'project_id'    => $permission->project_id,
//                    'shift_type'    => 1,
//                    'is_done'       => 1,
//                    'date'          => $currentDate->toDateTimeString(),
//                    'status_id'     => 6,
//                    'image_path'    => $permission->image_path,
//                    'created_by'     => $permission->employee_id,
//                    'type'          => "I",
//                    'description'   => $permission->description
//                ]);
//                $newAttendance = AttendanceAbsent::create([
//                    'employee_id'   => $permission->employee_id,
//                    'project_id'    => $permission->project_id,
//                    'shift_type'    => 1,
//                    'is_done'       => 1,
//                    'date'          => $currentDate->toDateTimeString(),
//                    'date_checkout' => $currentDate->toDateTimeString(),
//                    'status_id'     => 7,
//                    'image_path'    => $permission->image_path,
//                    'created_by'    => $permission->employee_id,
//                    'type'          => "I",
//                    'description'   => $permission->description
//                ]);
//            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Disetujui";
            $data = array(
                "type_id" => 302,
                "permission_model" => $permission,
            );
            if($permission->employee_id != $employee->id){
                $user = User::where('employee_id', $permission->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success approve", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - permissionsApprove error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to Reject permission by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function permissionsReject(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
        $employee = Employee::where('id', $user->employee_id)->first();
        $id = $request->input('id');
        try{
            //edit is_approve data
            $permission = AttendancePermission::find($id);
            $permission->is_approve = 2;
            $permission->updated_by = $user->id;
            $permission->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $permission->save();

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Ditolak";
            $data = array(
                "type_id" => 302,
                "permission_model" => $permission,
            );
            if($permission->employee_id != $employee->id){
                $user = User::where('employee_id', $permission->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success approve", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - permissionsReject error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /*================================================================================================================*/
    /**
     * Function to get single overtime.
     *
     * @return JsonResponse
     */
    public function overtimes()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $employees = AttendanceOvertime::where('employee_id', $id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }

            $attImage = empty($employees->image_path) ? null : asset('storage/attendance_overtimes/'. $employees->image_path);

            $model = ([
                'id'                => $employees->id,
                'approval_status'   => $employees->is_approve,
                'project_name'      => $employees->project->name,
                'employee_id'     => 0,
                'employee_name'     => "",
                'replacement_employee_id'     => 0,
                'replacement_employee_name'     => "",
                'replaced_employee_id'     => 0,
                'replaced_employee_name'     => "",
                'type'     => $employees->type,
                'date'              => Carbon::parse($employees->date)->format('Y-m-d H:i:s'),
                'description'       => $employees->description,
                'time_start'        => "00:00:00",
                'time_end'          => "00:00:00",
                'image_path'       => $attImage,
            ]);
            if($employees->type == "tagih"){
                $model->time_start = Carbon::parse($employees->time_start)->format('H:i:s');
                $model->time_end = Carbon::parse($employees->time_end)->format('H:i:s');
                $model->employee_id = $employees->employee_id;
                $model->employee_name = $employees->employee->first_name.' '.$employees->employee->last_name;
            }
            else{
                $model->replacement_employee_id = $employees->replacement_employee_id;
                $model->replacement_employee_name = $employees->employeeReplacement->first_name.' '.$employees->employeeReplacement->last_name;
                $model->replaced_employee_id = $employees->replaced_employee_id;
                $model->replaced_employee_name = $employees->employeeReplaced->first_name.' '.$employees->employeeReplaced->last_name;
            }

            return Response::json($model, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - overtimes error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to get list overtime.
     *
     * @return JsonResponse
     */
    public function getOvertimes()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $overtimes = AttendanceOvertime::where('project_id', $projectEmployee->project_id)
                ->orderbyDesc('created_at')
                ->get();
            $models = collect();
            if($overtimes->count() == 0){
                return Response::json(json_encode($models), 482);
            }
            foreach($overtimes as $overtime){
                $attImage = empty($overtime->image_path) ? null : asset('storage/attendance_overtimes/'. $overtime->image_path);
                $employee_id = 0;
                $employee_name = "";
                $time_start = "00:00:00";
                $time_end = "00:00:00";
                $replacement_employee_id = 0;
                $replacement_employee_name = "";
                $replaced_employee_id = 0;
                $replaced_employee_name = "";

                if($overtime->type == "tagih"){
                    $time_start = Carbon::parse($overtime->time_start)->format('H:i:s');
                    $time_end = Carbon::parse($overtime->time_end)->format('H:i:s');

                    $employeeDB = Employee::find($overtime->employee_id);
                    $employee_id = $overtime->employee_id;
                    $employee_name = $employeeDB->first_name.' '.$employeeDB->last_name;
                }
                else{
                    $employeeReplacementDB = Employee::find($overtime->replacement_employee_id);
                    $replacement_employee_id = $overtime->replacement_employee_id;
                    $replacement_employee_name = $employeeReplacementDB->first_name.' '.$employeeReplacementDB->last_name;

                    $employeeReplacedDB = Employee::find($overtime->replaced_employee_id);
                    $replaced_employee_id = $overtime->replaced_employee_id;
                    $replaced_employee_name = $employeeReplacedDB->first_name.' '.$employeeReplacedDB->last_name;
                }

                $model = collect([
                    'id'                => $overtime->id,
                    'approval_status'   => $overtime->is_approve,
                    'project_id'      => $overtime->project_id,
                    'project_name'      => $overtime->project->name,
                    'employee_id'     => $employee_id,
                    'employee_name'     => $employee_name,
                    'replacement_employee_id'     => $replacement_employee_id,
                    'replacement_employee_name'     => $replacement_employee_name,
                    'replaced_employee_id'     => $replaced_employee_id,
                    'replaced_employee_name'     => $replaced_employee_name,
                    'type'     => $overtime->type,
                    'date'              => Carbon::parse($overtime->date)->format('Y-m-d H:i:s'),
                    'description'       => $overtime->description,
                    'time_start'        => $time_start,
                    'time_end'          => $time_end,
                    'image_path'       => $attImage,
                ]);

                $models->push($model);
            }
//            Log::info('Api/EmployeeLeavesController - getOvertimes check data check 3: '.json_encode($models));

            return Response::json($models, 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - getOvertimes error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }


    /**
     * Function to submit overtime.
     *
     * @param $id
     * @return JsonResponse
     */
    public function overtimeSubmit(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
//            Log::error('Api/EmployeeLeavesController - sickLeaves data: '. $user->employee_id);
            $employee = Employee::where('id', $user->employee_id)->first();
            $id = $employee->id;

            $data = json_decode($request->input('overtime_model'));

            if($data->overtime_id == 0){
                if($data->type == "tagih"){
                    $newAttendanceOvertime = AttendanceOvertime::create([
                        'employee_id'  => $data->employee_id,
                        'project_id'   => $data->project_id,
                        'date'         => Carbon::parse($data->date)->format('Y-m-d H:i:s'),
                        'type'  => $data->type,
                        'description'  => $data->description,
                        'time_start'  => $data->time_start,
                        'time_end'  => $data->time_end,
                        'is_approve'            => 1,
                        'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'            => $user->id,
                    ]);
                }
                else{
                    //validasi ganti yang ada ijinnya.
                    $startDateFilter = Carbon::parse($data->date)->format('Y-m-d 00:00:00');
                    $endDateFilter = Carbon::parse($data->date)->format('Y-m-d 23:59:59');
                    $ijinValid = AttendancePermission::where('employee_id', $data->replaced_employee_id)
                        ->where('is_approve', 1)
                        ->where(function ($q) use ($startDateFilter, $endDateFilter) {
                            $q->whereBetween('date_start', array($startDateFilter, $endDateFilter))
                                ->orWhereBetween('date_end', array($startDateFilter, $endDateFilter));
                        })
                        ->first();

                    $ijinValid2 = AttendanceSickLeafe::where('employee_id', $data->replaced_employee_id)
                        ->where('is_approve', 1)
                        ->whereBetween('date', array($startDateFilter, $endDateFilter))
                        ->first();

                    if(empty($ijinValid) && empty($ijinValid2)){
                        return Response::json("Tidak ada Ijin", 482);
                    }
                    $newAttendanceOvertime = AttendanceOvertime::create([
                        'employee_id'  => $data->replacement_employee_id,
                        'project_id'   => $data->project_id,
                        'date'         => Carbon::parse($data->date)->format('Y-m-d H:i:s'),
                        'type'  => $data->type,
                        'description'  => $data->description,
                        'replacement_employee_id'  => $data->replacement_employee_id,
                        'replaced_employee_id'  => $data->replaced_employee_id,
                        'is_approve'            => 1,
                        'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'            => $user->id,
                    ]);
                }
            }
            else{
                $newAttendanceOvertime = AttendanceOvertime::where('id', $data->overtime_id)->first();
            }
            if($data->type == "tagih"){
                $employeeDB = Employee::where('id', $data->employee_id)->first();
            }
            else{
                $employeeDB = Employee::where('id', $data->replacement_employee_id)->first();
            }

            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendance_overtimes/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employeeDB->first_name . ' ' . $employeeDB->last_name . '_attendance_overtimes_'. $newAttendanceOvertime->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendanceOvertime->image_path = $todayStr.'/'.$filename;
                $newAttendanceOvertime->save();
            }

            return Response::json([
                'message' => "Success Submit Overtime!",
            ], 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - overtimeSubmit error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
    /**
     * Function to Approve overtime by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
//    public function overtimeApprove(Request $request)
//    {
//        $userLogin = auth('api')->user();
//        $user = User::where('phone', $userLogin->phone)->first();
//        $employee = $user->employee;
//        $id = $request->input('id');
//        try{
//            //edit is_approve data
//            $overtime = AttendanceOvertime::find($id);
//            $overtime->is_approve = 1;
//            $overtime->update_by = $user->id;
//            $overtime->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
//            $overtime->save();
//
//            //Push Notification to employee App.
//            $title = "ICare";
//            $body = "Employee Ijin Lembur Diterima";
//            $data = array(
//                "type_id" => 302,
//                "overtime_model" => $overtime,
//            );
//            if($overtime->employee_id != $employee->id){
//                $user = User::where('employee_id', $overtime->employee_id)->first();
//                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
//            }
//
//            return Response::json("success approve", 200);
//        }
//        catch(\Exception $ex){
//            Log::error('Api/EmployeeLeavesController - overtimeApprove error EX: '. $ex);
//            return Response::json("Maaf terjadi kesalahan!", 500);
//        }
//    }

    /**
     * Function to Reject overtime by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
//    public function overtimeReject(Request $request)
//    {
//        $userLogin = auth('api')->user();
//        $user = User::where('phone', $userLogin->phone)->first();
//        $employee = $user->employee;
//        $id = $request->input('id');
//        try{
//            //edit is_approve data
//            $overtime = AttendanceOvertime::find($id);
//            $overtime->is_approve = 1;
//            $overtime->update_by = $user->id;
//            $overtime->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
//            $overtime->save();
//
//            //Push Notification to employee App.
//            $title = "ICare";
//            $body = "Employee Ijin Lembur Ditolak";
//            $data = array(
//                "type_id" => 302,
//                "overtime_model" => $overtime,
//            );
//            if($overtime->employee_id != $employee->id){
//                $user = User::where('employee_id', $overtime->employee_id)->first();
//                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
//            }
//
//            return Response::json("success approve", 200);
//        }
//        catch(\Exception $ex){
//            Log::error('Api/EmployeeLeavesController - overtimeReject error EX: '. $ex);
//            return Response::json("Maaf terjadi kesalahan!", 500);
//        }
//    }
    /*================================================================================================================*/
}

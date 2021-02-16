<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\EmployeeProcess;
use App\Models\Action;
use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\AttendanceOvertime;
use App\Models\AttendancePermission;
use App\Models\AttendanceSickLeafe;
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
            $employee = $user->employee;
            $id = $employee->id;

            $employees = AttendanceSickLeafe::where('employee_id', $id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }

            return Response::json([
                'message' => "Success Getting sick leaves Data!",
                'model'     => json_encode($employees)
            ], 200);
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
            $employee = $user->employee;
            $id = $employee->id;

            $employee = Employee::find($id)->get();
            $data = json_decode($request->input('sick_leave_model'));

            $newAttendanceSick = AttendanceSickLeafe::create([
                'employee_id'  => $data->employee_id,
                'project_id'   => $data->project_id,
                'date'         => Carbon::parse($data->date)->format('Y-m-d H:i:s'),
                'description'  => $data->description,
                'is_approve'   => 0,
                'created_at'   => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'   => $user->id,
            ]);

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
                $filename = $employee->first_name . ' ' . $employee->last_name . '_attendanceSick_'. $newAttendanceSick->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendanceSick->image_path = $todayStr.'/'.$filename;
                $newAttendanceSick->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee ". $employee->first_name . ' ' . $employee->last_name ." Mengajukan  Ijin Sakit";
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
     * Function to get list sick leaves.
     *
     * @return JsonResponse
     */
    public function getSickLeaves()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $sickLeaves = AttendanceSickLeafe::where('project_id', $projectEmployee->project_id)
                ->orderby('is_approve')
                ->get();

            return Response::json([
                'message' => "Success Getting Sick Leaves Data!",
                'model'     => json_encode($sickLeaves)
            ], 200);
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
     * Function to Approve sick leaves by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function sickLeaveApprove(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $sickLeaves = AttendanceOvertime::find('employee_id', $id);
            $sickLeaves->is_approve = 1;
            $sickLeaves->update_by = $user->id;
            $sickLeaves->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $sickLeaves->save();

            //add to attendance record
            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $sickLeaves->employee_id,
                'project_id'    => $sickLeaves->project_id,
                'shift_type'    => 1,
                'is_done'       => 1,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 6,
                'image_path'    => $sickLeaves->image_path,
                'created_by'     => $sickLeaves->employee_id,
            ]);
            $newAttendance = AttendanceAbsent::create([
                'employee_id'   => $sickLeaves->employee_id,
                'project_id'    => $sickLeaves->project_id,
                'shift_type'    => 1,
                'is_done'       => 1,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'status_id'     => 6,
                'image_path'    => $sickLeaves->image_path,
                'created_by'     => $sickLeaves->employee_id,
            ]);


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
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $sickLeaves = AttendanceOvertime::find('employee_id', $id);
            $sickLeaves->is_approve = 2;
            $sickLeaves->update_by = $user->id;
            $sickLeaves->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
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
            $employee = $user->employee;
            $id = $employee->id;

            $employees = AttendancePermission::where('employee_id', $id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }

            return Response::json([
                'message' => "Success Getting attendance Permission Data!",
                'model'     => json_encode($employees)
            ], 200);
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
            $employee = $user->employee;
            $id = $employee->id;

            $employee = Employee::find($id)->get();
            $data = json_decode($request->input('permission_model'));

            $newAttendancePermission = AttendancePermission::create([
                'employee_id'  => $data->employee_id,
                'project_id'   => $data->project_id,
                'date'         => Carbon::parse($data->date_start)->format('Y-m-d H:i:s'),
                'date_end'         => Carbon::parse($data->date_end)->format('Y-m-d H:i:s'),
                'description'  => $data->description,
                'is_approve'   => 0,
                'created_at'   => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'   => $user->id,
            ]);

            if($request->hasFile('image')){
                //Upload Image
                //Creating Path Everyday
                $today = Carbon::now('Asia/Jakarta');
                $todayStr = $today->format('y-m-d l');
                $publicPath = 'storage/attendance_permissions/'. $todayStr;
                if(!File::isDirectory($publicPath)){
                    File::makeDirectory(public_path($publicPath), 0777, true, true);
                }

                $image = $request->file('image');
                $avatar = Image::make($image);
                $extension = $image->extension();
                $filename = $employee->first_name . ' ' . $employee->last_name . '_attendancePermission_'. $newAttendancePermission->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendancePermission->image_path = $todayStr.'/'.$filename;
                $newAttendancePermission->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee ". $employee->first_name . ' ' . $employee->last_name ." Mengajukan Ijin";
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
     * Function to get list permission.
     *
     * @return JsonResponse
     */
    public function getPermissions()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $permissions = AttendancePermission::where('project_id', $projectEmployee->project_id)
                ->orderby('is_approve')
                ->get();

            return Response::json([
                'message' => "Success get attendance permission!",
                'model'     => json_encode($permissions)
            ], 200);
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
     * Function to Approve permission by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function permissionsApprove(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $permission = AttendancePermission::find($id);
            $permission->is_approve = 1;
            $permission->update_by = $user->id;
            $permission->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $permission->save();

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
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $permission = AttendancePermission::find($id);
            $permission->is_approve = 2;
            $permission->update_by = $user->id;
            $permission->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
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
            $employee = $user->employee;
            $id = $employee->id;

            $employees = AttendanceOvertime::where('employee_id', $id)->first();
            if(empty($employees)){
                return Response::json("data kosong", 482);
            }

            return Response::json([
                'message' => "Success Getting overtime Data!",
                'model'     => json_encode($employees)
            ], 200);
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
            $employee = $user->employee;
            $id = $employee->id;

            $employee = Employee::find($id)->get();
            $data = json_decode($request->input('permission_model'));

            $newAttendanceOvertime = AttendanceOvertime::create([
                'employee_id'  => $data->employee_id,
                'project_id'   => $data->project_id,
                'date'         => Carbon::parse($data->date)->format('Y-m-d H:i:s'),
                'attendance_sick_id'  => $data->attendance_sick_id,
                'type'  => $data->type,
                'time_start'  => $data->time_start,
                'time_end'  => $data->time_end,
                'description'  => $data->description,
                'is_approve'            => 0,
                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'            => $user->id,
            ]);

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
                $filename = $employee->first_name . ' ' . $employee->last_name . '_attendanceOvertime_'. $newAttendanceOvertime->id . '_' .
                    Carbon::now('Asia/Jakarta')->format('Ymdhms') . '.' . $extension;
                $filename = str_replace('?', '', $filename);
                $avatar->save(public_path($publicPath ."/". $filename));

                $newAttendanceOvertime->image_path = $todayStr.'/'.$filename;
                $newAttendanceOvertime->save();
            }

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee ". $employee->first_name . ' ' . $employee->last_name ." Mengajukan Lembur";
            $data = array(
                "type_id" => 302,
                "overtime_id" => $newAttendanceOvertime->id,
                "overtime_model" => $newAttendanceOvertime,
            );
            $ProjectEmployees = ProjectEmployee::where('project_id', $newAttendanceOvertime->project_id)
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
     * Function to get list overtime.
     *
     * @return JsonResponse
     */
    public function getOvertimes()
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();
            $employee = $user->employee;
            $id = $employee->id;

            $projectEmployee = ProjectEmployee::where('employee_id', $id)->where('status_id', 1)->first();

            $overtimes = AttendanceOvertime::where('project_id', $projectEmployee->project_id)
                ->orderby('is_approve')
                ->get();

            return Response::json([
                'message' => "Success Getting Overtime Data!",
                'model'     => json_encode($overtimes)
            ], 200);
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
     * Function to Approve overtime by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function overtimeApprove(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $overtime = AttendanceOvertime::find($id);
            $overtime->is_approve = 1;
            $overtime->update_by = $user->id;
            $overtime->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $overtime->save();

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Lembur Diterima";
            $data = array(
                "type_id" => 302,
                "overtime_model" => $overtime,
            );
            if($overtime->employee_id != $employee->id){
                $user = User::where('employee_id', $overtime->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success approve", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - overtimeApprove error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }

    /**
     * Function to Reject overtime by supervisor.
     *
     * @param $id
     * @return JsonResponse
     */
    public function overtimeReject(Request $request)
    {
        $userLogin = auth('api')->user();
        $user = User::where('phone', $userLogin->phone)->first();
        $employee = $user->employee;
        $id = $request->input('id');
        try{
            //edit is_approve data
            $overtime = AttendanceOvertime::find($id);
            $overtime->is_approve = 1;
            $overtime->update_by = $user->id;
            $overtime->update_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $overtime->save();

            //Push Notification to employee App.
            $title = "ICare";
            $body = "Employee Ijin Lembur Ditolak";
            $data = array(
                "type_id" => 302,
                "overtime_model" => $overtime,
            );
            if($overtime->employee_id != $employee->id){
                $user = User::where('employee_id', $overtime->employee_id)->first();
                FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
            }

            return Response::json("success approve", 200);
        }
        catch(\Exception $ex){
            Log::error('Api/EmployeeLeavesController - overtimeReject error EX: '. $ex);
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
    /*================================================================================================================*/
}

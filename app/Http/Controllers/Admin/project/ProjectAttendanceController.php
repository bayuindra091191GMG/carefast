<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Project;
use App\Transformer\AttendanceTransformer;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;

class ProjectAttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show($id){
        try{
            $project = Project::find($id);
            return view('admin.project.attendance.show', compact('project'));
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectAttendanceController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $id = $request->input('id');

        $customers = AttendanceAbsent::with(['employee'])->where('project_id', $id);
//        $customers = AttendanceAbsent::where('project_id', $id)->get();
        return DataTables::of($customers)
            ->setTransformer(new AttendanceTransformer())
            ->make(true);
    }

    public function downloadAttendance(Request $request)
    {
//        dd($request);
        $projectId = $request->input('project_id');
//        $shiftType = $request->input('shift_type');
        $startDateRequest = $request->input('start_date');
        $startDate = Carbon::parse($startDateRequest)->format('Y-m-d H:i:s');
        $endDateRequest = $request->input('end_date');
        $endDate = Carbon::parse($endDateRequest)->format('Y-m-d H:i:s');

        $attendanceAbsents = DB::table('attendance_absents')
            ->join('employees', 'attendance_absents.employee_id', '=', 'employees.id')
            ->join('projects', 'attendance_absents.project_id', '=', 'projects.id')
            ->select('attendance_absents.id as attendance_absent_id',
                'attendance_absents.shift_type as shift_type',
                'attendance_absents.is_done as is_done',
                'attendance_absents.date as date',
                'attendance_absents.date_checkout as date_checkout',
                'attendance_absents.created_at as created_at',
                'employees.id as employee_id',
                'employees.code as employee_code',
                'employees.first_name as employee_first_name',
                'employees.last_name as employee_last_name',
                'employees.phone as employee_phone',
                'projects.name as project_name',
                'projects.code as project_code')
            ->whereBetween('attendance_absents.created_at', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
            ->where('attendance_absents.project_id', $projectId)
            ->where('attendance_absents.status_id',6)
            ->get();

        $now = Carbon::now('Asia/Jakarta');

        $list = collect();
        foreach($attendanceAbsents as $attendanceAbsent){
            $attStatus = "U";
            $dataCheckout = "-";
            if($attendanceAbsent->is_done == 0){
                $attStatus = "A";
            }
            else{
                if(!empty($attendanceAbsent->date_checkout)){
                    $attStatus = "H";
//                        $attendanceOut = $attendanceAbsent->date_checkout->format('Y-m-d H:i:s');
                    $dataCheckout = $attendanceAbsent->date_checkout;
                }
                else{
                    $attStatus = "A";
                }
            }
            $createdAt = Carbon::parse($attendanceAbsent->created_at);
            $singleData = ([
                'Project Code' => $attendanceAbsent->project_code,
                'Employee Code' => $attendanceAbsent->employee_code,
                'Employee Name' => $attendanceAbsent->employee_first_name." ".$attendanceAbsent->employee_last_name,
                'Employee Phone' => $attendanceAbsent->employee_phone,
                'Transaction Date' => $createdAt,
                'Shift' => $attendanceAbsent->shift_type,
                'Attendance In' => $attendanceAbsent->date,
                'Attendance Out' => $dataCheckout,
                'Attendance Status' => $attStatus,
            ]);
            $list->push($singleData);
        }
//        dd($list);
        $destinationPath = public_path()."/download_attendance/";
        $file = 'attendance-report_'.$now->format('Y-m-d')."-".time().'.xlsx';
//        dd($destinationPath.$file);
        (new FastExcel($list))->export($destinationPath.$file);

        return response()->download($destinationPath.$file);

        //format txt = projectCode | employeeCode | transDate | shiftCode | attendanceIn | attendanceOut | attendanceStatus
//        $data .= "Project Code\tEmployee Code\tTransaction Date\tShift\tAttendance In\tAttendance Out\tAttendance Status\n";
//        foreach($attendanceAbsents as $attendanceAbsent){
//            $data .= $attendanceAbsent->project->code."\t"
//                .$attendanceAbsent->employee->code."\t"
//                .$attendanceAbsent->date->format('Y-m-d')."\t"
//                .$attendanceAbsent->shift_type."\t"
//                .$attendanceAbsent->date->format('Y-m-d H:i:s')."\t";
//            if(empty($attendanceAbsent->date_checkout)){
//                $dataCheckout = "-\t";
//            }
//            else{
//                $dataCheckout = $attendanceAbsent->date_checkout."\t";
//            }
//            $data .= $dataCheckout;
//            if($attendanceAbsent->is_done == 0){
//                $data .= "A\n";
//            }
//            else{
//                $data .= "H\n";
//            }
//        }
////        dd($attendanceAbsents, $data, $startDate, $endDate);
//        $file = "Attendance Download_".$now->format('Y-m-d')."-".time().'.txt';
//        $destinationPath=public_path()."/download_attendance/";
//        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
//        File::put($destinationPath.$file, $data);
//        return response()->download($destinationPath.$file);
    }
}

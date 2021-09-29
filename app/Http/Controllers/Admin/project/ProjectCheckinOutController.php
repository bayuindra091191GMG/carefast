<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\libs\AttendanceProcess;
use App\libs\EmployeeProcess;
use App\libs\Utilities;
use App\Models\AttendanceAbsent;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Transformer\AttendanceTransformer;
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

class ProjectCheckinOutController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    public function downloadForm($id){
        try{
            $projectId = $id;
            return view('admin.project.checkin-out.download_form', compact('projectId'));
        }
        catch (\Exception $ex){
            Log::error('Admin/ProjectCheckinOutController - downloadForm error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }
    public function downloadAll(Request $request)
    {
//        dd($request);
//        $shiftType = $request->input('shift_type');
        $projectId = $request->input('project_id');
        $currentProject = Project::find($projectId);

        $startDateRequest = $request->input('start_date');
        $startDate = Carbon::parse($startDateRequest)->format('Y-m-d');
        $endDateRequest = $request->input('end_date');
        $endDate = Carbon::parse($endDateRequest)->format('Y-m-d');


        $projectEmployeeCsos = ProjectEmployee::where('project_id', $projectId)
            ->where('employee_roles_id', 1)
            ->where('status_id', 1)
            ->orderBy('employee_id')
            ->get();
        $attendanceModels = collect();
        foreach ($projectEmployeeCsos as $projectEmployeeCso){
            $employeeDb = Employee::where('id', $projectEmployeeCso->employee_id)->first();
            $employeeId = $employeeDb->id;
            $employeeName = $employeeDb->first_name." ".$employeeDb->last_name;
            $newAttendanceModels = EmployeeProcess::GetEmployeeScheduleV2($employeeId, $projectId, $employeeName, $startDate, $endDate, $attendanceModels);
            $attendanceModels = $newAttendanceModels;
        }
        $list = collect();
        if(count($attendanceModels) > 0){
            foreach($attendanceModels as $attendanceModel){
                foreach($attendanceModel["checkins"] as $attendanceCheckinModel){
//                    $date = Carbon::parse($attendanceCheckinModel["checkin_datetime"])->format('d-m-Y');
                    $date = Carbon::createFromFormat('d m Y H:i:s', $attendanceCheckinModel["checkin_datetime"])->format('d-m-Y');
                    $checkout = $attendanceCheckinModel["checkout_datetime"] == "" ? "-" : Carbon::createFromFormat('d m Y H:i:s', $attendanceCheckinModel["checkout_datetime"])->format('H:i');

                    $singleData = ([
                        'Employee Name'     => $attendanceModel["employee_name"],
                        'Place'             => $attendanceCheckinModel["place_name"],
                        'Date'              => $date,
//                        'Check-in'          => Carbon::parse($attendanceCheckinModel["checkin_datetime"])->format('H:i'),
//                        'Checkout'          => Carbon::parse($attendanceCheckinModel["checkout_datetime"])->format('H:i'),
                        'Check-in'          => Carbon::createFromFormat('d m Y H:i:s', $attendanceCheckinModel["checkin_datetime"])->format('H:i'),
                        'Checkout'          => $checkout,
                    ]);
                    $list->push($singleData);
                }
            }
        }
        $destinationPath = public_path()."/download_checkin-out/";
        $now = Carbon::now();
        $file = "List Checkin-out_".$currentProject->code."_".$now->format('d F Y_G.i.s').'.xlsx';

        (new FastExcel($list))->export($destinationPath.$file);

        return response()->download($destinationPath.$file);
    }
}

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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class ProjectAttendanceController extends Controller
{
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

        $customers = AttendanceAbsent::where('project_id', $id)->orderby('created_at', 'desc')->get();
        return DataTables::of($customers)
            ->setTransformer(new AttendanceTransformer())
            ->make(true);
    }

    public function downloadAttendance(Request $request)
    {
//        dd($request);
        $projectId = $request->input('project_id');
        $shiftType = $request->input('shift_type');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $attendanceAbsents = AttendanceAbsent::where('project_id', $projectId)
            ->where('shift_type', $shiftType)
            ->where('status_id', 6)
            ->whereBetween('created_by', [$startDate, $endDate])
            ->get();
//        dd($attendanceAbsents, $projectId, $shiftType);
        $data = "";
        $now = Carbon::now('Asia/Jakarta');

//        timestamp: ...,
//        projectCode: XXX, //assume project codes are synced
//        beginDate: ..., //date YYYY-MM-DD
//        endDate: ..., //date YYYY-MM-DD
//        data: [
//          {
//              employeeId: ...,
//              employeeCode: ...,
//              transDate: ..., //date YYYY-MM-DD
//              shiftCode: ..., // 1|2|3 or A|B|C or whatever
//              attendanceIn: ..., //timestamp YYYY-MM-DD HH:mm:ss
//              attendanceOut: ..., //timestamp YYYY-MM-DD HH:mm:ss
//              attendanceStatus: ..., // H=Hadir, A=Alpa, U=Unknown
//              ]
//          }
        //format txt = projectCode | employeeCode | transDate | shiftCode | attendanceIn | attendanceOut | attendanceStatus
        foreach($attendanceAbsents as $attendanceAbsent){
            $data .= $attendanceAbsent->project->code."\t"
                .$attendanceAbsent->employee->code."\t"
                .$now->format('d M Y')."\t"
                .$attendanceAbsent->shift_type."\t"
                .$attendanceAbsent->date->toDateTimeString()."\t"
                .$attendanceAbsent->date_checkout->toDateTimeString()."\t";
            if($attendanceAbsent->is_done == 0){
                $data .= "A\t";
            }
            else{
                $data .= "H\t";
            }
        }

        $file = time() .rand(). '_file.txt';
        $destinationPath=public_path()."/download_attendance/";
        if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
        File::put($destinationPath.$file, $data);
        return response()->download($destinationPath.$file);
    }
}

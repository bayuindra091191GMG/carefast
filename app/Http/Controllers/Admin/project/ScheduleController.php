<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Imports\EmployeeScheduleImport;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectShift;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectScheduleEmployeeTransformer;
use App\Transformer\ProjectTransformer;
use Box\Spout\Writer\Style\Color;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;
use Box\Spout\Writer\Style\StyleBuilder;

class ScheduleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getScheduleEmployees(Request $request){
        $id = $request->input('id');
//        $project = Project::find($id);
//        $employeeSchedule = $project->project_employees->sortByDesc('employee_roles_id');
        $employeeSchedule = ProjectEmployee::where('project_id', $id)->orderby('employee_roles_id', 'desc')->get();

        return DataTables::of($employeeSchedule)
            ->setTransformer(new ProjectScheduleEmployeeTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }
        $employeeRoleIds = ProjectEmployee::select('employee_roles_id')->where('project_id', $id)->distinct('employee_roles_id')->get();
        $ids = array();
        foreach ($employeeRoleIds as $employeeRoleId){
            array_push($ids, $employeeRoleId->employee_roles_id);
        }

        $employeeRoles = EmployeeRole::whereIn('id', $ids)->get();

        $data = [
            'employeeRoles'   => $employeeRoles,
            'project'         => $project,
        ];
        return view('admin.project.schedule.show2')->with($data);
    }

    public function create(Request $request, int $id){
        try{

            $projectEmployee = ProjectEmployee::find($id);
            $employeeRole = EmployeeRole::find($id);
            $projectId = $request->project;
            $project = Project::find($projectId);

            if(empty($projectEmployee)){
                return redirect()->back();
            }
            $data = [
                'employeeRole'     => $employeeRole,
                'project'           => $project,
                'projectEmployee'   => $projectEmployee,
            ];

            return view('admin.project.schedule.create2')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function store(Request $request)
    {
        try{
//            dd($request);
            $weeks = $request->input('week');
            $days = $request->input('day');
            $start_times = $request->input('start_times');
            $finish_times = $request->input('finish_times');
            $places = $request->input('places');
            $shiftType = $request->input('shift_type');
            $periodType = $request->input('period');
            $start = Carbon::parse('00-00-00 '.$start_times[0])->format('Y-m-d H:i:s');
            $finish = Carbon::parse('00-00-00 '.$finish_times[0])->format('Y-m-d H:i:s');

//            dd($start, $finish);
//            dd($weeks, $days, $start_times, $finish_times);

            //validation for every input
            $j = 0;
            foreach($periodType as $periodValue){
                if($periodValue != "Daily"){
                    foreach($days[$j] as $dayValue){
                        if(empty($dayValue)){
                            return back()->withErrors("Terdapat HARI yang belum terpilih untuk periodic weekly dan monthly!")->withInput($request->all());
                        }
                    }
                }
                $j++;
            }

//            $j = 0;
//            if(empty($request->input('day'))){
//                return back()->withErrors("Terdapat HARI yang belum terpilih!")->withInput($request->all());
//            }
//            if(empty($request->input('week'))){
//                return back()->withErrors("Terdapat HARI yang belum terpilih!")->withInput($request->all());
//            }
//            foreach ($start_times as $start_time){
//                if(empty($weeks[$j])){
//                    return back()->withErrors("Terdapat MINGGU yang belum terpilih!")->withInput($request->all());
//                }
//                if(empty($days[$j])){
//                    return back()->withErrors("Terdapat MINGGU yang belum terpilih!")->withInput($request->all());
//                }
//                if($places[$j] == '-1'){
//                    return back()->withErrors("Terdapat PLACE yang belum terpilih!")->withInput($request->all());
//                }
//                $j++;
//            }
//            dd($request);

            $i = 0;
            $user = Auth::guard('admin')->user();
            //create schedule
            foreach ($start_times as $start_time){
                $daysString = "";
                $weekString = "";
//                foreach($weeks[$i] as $weekValue){
//                    $weekString .= $weekValue."#";
//                }
                $start = Carbon::parse('00-00-00 '.$start_times[$i])->format('Y-m-d H:i:s');
                $finish = Carbon::parse('00-00-00 '.$finish_times[$i])->format('Y-m-d H:i:s');

                $schedule = Schedule::create([
                    'project_id'            => $request->input('project_id'),
//                    'project_employee_id'   => $request->input('project_employee_id'),
                    'shift_type'            => $shiftType,
                    'place_id'              => $places,
                    'weeks'                 => $weekString,
//                    'days'                  => $daysString,
                    'start'                 => $start,
                    'finish'                => $finish,
                    'status_id'             => 1,
                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'            => $user->id,
                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'            => $user->id,
                ]);

                if($periodType[$i]  == "Daily"){
                    $day = "1#2#3#4#5#6#7#";
                }
                else{
                    foreach($days[$i] as $dayValue){
                        $daysString .= $dayValue."#";
                    }
                    $day = $daysString;
                }
                $schedule->days = $day;
                $schedule->save();

                $actionsString = "";
                $objectsString = "";
                $actions = $request->input('actions'.$i);
                foreach($actions as $actionValue){
                    $actionsString .= $actionValue."#";
                }

                $objects = $request->input('project_objects'.$i);
                foreach($objects as $objectValue){
                    $objectsString .= $objectValue."#";
                }
                $scheduleDetail = ScheduleDetail::create([
                    'schedule_id'           => $schedule->id,
                    'project_object_id'     => $objectsString,
                    'action_id'             => $actionsString,
                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'            => $user->id,
                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'            => $user->id,
                ]);


                $i++;
            }

            return redirect()->route('admin.project.schedule.show', ['id' => $request->input('project_id')]);
        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!" . $ex;
        }
    }
    public function scheduleEdit(Request $request, int $id){
        try{
            $currentProject = Project::find($id);

            if(empty($currentProject)){
                return redirect()->back();
            }
            $start_date = Carbon::now()->format("d M Y");
            $end_date = Carbon::now()->format("d M Y");
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $id)
                ->where('employee_roles_id','<', 4)
                ->where('status_id', 1)
                ->get();

            $isSelectDate = true;
            $dayArr = collect();
            $projectScheduleModel = collect();
            if(empty($request->start_date) || empty($request->finish_date)){
                $isSelectDate = false;
            }
            else{
                $start_date = Carbon::parse($request->start_date)->format("d M Y");
                $end_date = Carbon::parse($request->finish_date)->format("d M Y");

                $startDate = Carbon::parse($request->start_date)->day;
                $startDate2 = Carbon::parse($request->start_date);
                $lastofStartDate = $startDate2->daysInMonth;
                $endDate = Carbon::parse($request->finish_date)->day;

                for($i=$startDate;$i<=$lastofStartDate; $i++){
                    $dayArr->push($i);
                }
                for($i=1;$i<=$endDate; $i++){
                    $dayArr->push($i);
                }

                foreach($employeeProjects as $employeeProject){

                    $isEmpty = true;
                    $scheduleModel = collect();
                    $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeProject->employee_id)->first();

                    $schedule = collect([
                        'employee_id'   => $employeeProject->employee_id,
                        'employee_name' => $employeeProject->employee->first_name. ' '.$employeeProject->employee->last_name,
                        'employee_code' => $employeeProject->employee->code,
                        'days'          => '',
                    ]);
                    if(!empty($employeeSchedule)){
                        if(!empty($employeeSchedule->day_status)){
                            $isEmpty = false;
                            $days = explode(";", $employeeSchedule->day_status);
                            foreach ($days as $day){
                                if(!empty($day)){
                                    $dayStatus = explode(":", $day);
                                    $scheduleDetail = ([
                                        'day'   => $dayStatus[0],
                                        'status' => $dayStatus[1],
                                    ]);
                                    $scheduleModel->push($scheduleDetail);
                                }
                            }
                            $schedule['days'] = $scheduleModel;
                            $projectScheduleModel->push($schedule);
                        }
                    }
                    if($isEmpty){
                        foreach($dayArr as $day){
                            $scheduleDetail = ([
                                'day'   => $day,
                                'status' => 1,
                            ]);
                            $scheduleModel->push($scheduleDetail);
                        }
                        $schedule['days'] = $scheduleModel;
                        $projectScheduleModel->push($schedule);
                    }
                }
            }
            $data = [
                'project'               => $currentProject,
                'projectScheduleModel'  => $projectScheduleModel,
                'days'                  => $dayArr,
                'isSelectDate'          => $isSelectDate,
                'start_date'            => $start_date,
                'end_date'              => $end_date,
            ];
//        dd($data);
            return view('admin.project.schedule.edit-schedule')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleEdit error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
    public function scheduleEditv2(Request $request, int $id){
        try{
            $currentProject = Project::find($id);

            if(empty($currentProject)){
                return redirect()->back();
            }
            $start_date = Carbon::now()->format("d M Y");
            $end_date = Carbon::now()->format("d M Y");
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $id)
                ->where('employee_roles_id','<', 5)
                ->where('status_id', 1)
                ->get();

            $isSelectDate = true;
            $dayArr = collect();
            $projectScheduleModel = collect();

            foreach($employeeProjects as $employeeProject){

                $isEmpty = true;
                $scheduleModel = collect();
                $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeProject->employee_id)->first();

                $schedule = collect([
                    'employee_id'   => $employeeProject->employee_id,
                    'employee_name' => $employeeProject->employee->first_name. ' '.$employeeProject->employee->last_name,
                    'employee_code' => $employeeProject->employee->code,
                    'days'          => '',
                ]);
                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){
                        $isEmpty = false;
                        $days = explode(";", $employeeSchedule->day_status);
                        $dayArr = collect();
                        foreach ($days as $day){
                            if(!empty($day)){
                                $dayStatus = explode(":", $day);
                                $scheduleDetail = ([
                                    'day'   => $dayStatus[0],
                                    'status' => $dayStatus[1],
                                ]);
                                $scheduleModel->push($scheduleDetail);
                                $dayArr->push($dayStatus[0]);
                            }
                        }
                        $schedule['days'] = $scheduleModel;
                        $projectScheduleModel->push($schedule);
                    }
                }
                if($isEmpty){
                    foreach($dayArr as $day){
                        $scheduleDetail = ([
                            'day'   => $day,
                            'status' => "",
                        ]);
                        $scheduleModel->push($scheduleDetail);
                    }
                    $schedule['days'] = $scheduleModel;
                    $projectScheduleModel->push($schedule);
                }
            }

            //get project shift
            $projectShifts = ProjectShift::Where('project_code', $currentProject->code)->get();
            $data = [
                'project'               => $currentProject,
                'projectShifts'         => $projectShifts,
                'projectScheduleModel'  => $projectScheduleModel,
                'days'                  => $dayArr,
                'isSelectDate'          => $isSelectDate,
                'start_date'            => $start_date,
                'end_date'              => $end_date,
            ];
//        dd($data);
            return view('admin.project.schedule.edit-schedule-v2')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleEdit error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
    public function scheduleEditEmployee(Request $request, int $id){
        try{
            $projectId = $request->projectId;
            $currentProject = Project::find($projectId);

            if(empty($currentProject)){
                return redirect()->back();
            }
            $start_date = Carbon::now()->format("d M Y");
            $end_date = Carbon::now()->format("d M Y");
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $projectId)
//                ->where('employee_id', $id)
                ->where('employee_roles_id','<', 4)
                ->where('status_id', 1)
                ->get();
            $projectSchedule = ProjectShift::where('project_id', $projectId)->get();

            $isSelectDate = true;
            $dayArr = collect();
            $projectScheduleModel = collect();

            foreach($employeeProjects as $employeeProject){
                $isEmpty = true;
                $scheduleModel = collect();
                $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeProject->employee_id)->first();

                $schedule = collect([
                    'employee_id'   => $employeeProject->employee_id,
                    'employee_name' => $employeeProject->employee->first_name. ' '.$employeeProject->employee->last_name,
                    'employee_code' => $employeeProject->employee->code,
                    'days'          => '',
                ]);
                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){
                        $isEmpty = false;
                        $days = explode(";", $employeeSchedule->day_status);
                        $dayArr = collect();
                        foreach ($days as $day){
                            if(!empty($day)){
                                $dayStatus = explode(":", $day);
                                $scheduleDetail = ([
                                    'day'   => $dayStatus[0],
                                    'status' => $dayStatus[1],
                                ]);
                                $scheduleModel->push($scheduleDetail);
                                $dayArr->push($dayStatus[0]);
                            }
                        }
                        $schedule['days'] = $scheduleModel;
                        $projectScheduleModel->push($schedule);
                    }
                }
                if($isEmpty){
                    foreach($dayArr as $day){
                        $scheduleDetail = ([
                            'day'   => $day,
                            'status' => "",
                        ]);
                        $scheduleModel->push($scheduleDetail);
                    }
                    $schedule['days'] = $scheduleModel;
                    $projectScheduleModel->push($schedule);
                }
            }
//            dd($scheduleModel, $projectSchedule);

            $data = [
                'selectedEmployee'      => $id,
                'project'               => $currentProject,
                'projectScheduleModel'  => $projectScheduleModel,
                'projectSchedules'       => $projectSchedule,
                'days'                  => $dayArr,
                'isSelectDate'          => $isSelectDate,
                'start_date'            => $start_date,
                'end_date'              => $end_date,
            ];
//        dd($data);
            return view('admin.project.schedule.edit-schedule-employee')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleEditEmployee error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
    public function scheduleUpdateEmployee(Request $request, int $id){
        try{
            $projectId = $request->input('projectId');
            $currentProject = Project::find($projectId);

            if(empty($currentProject)){
                return redirect()->back();
            }

            $employeeDB = DB::table('employees')
                ->select('id', 'code')
                ->where('id', $request->input('employeeId'))
                ->first();
            if(!empty($employeeDB)){
                $ct =0 ;
                $tempSchedule = "";
                //create day_status
                // ex : 16:M;17:M;18:M;19:M;20:M;21:M;22:O;23:M;24:M;25:M;26:M;27:M;28:M;29:O;30:O;31:O;1:M;2:M;3:M;4:M;5:M;6:M;7:O;8:M;9:M;10:M;11:M;12:M;13:M;14:O;15:M;
                $days = $request->input('days');
                $statuses = $request->input('statuses');
                foreach($days as $day){
                    $tempSchedule .= $days[$ct].":".$statuses[$ct].";";
                    $ct++;
                }
                $employeeScheduleDB = EmployeeSchedule::where('employee_code', $employeeDB->code)->first();
//                dd($tempSchedule, $employeeScheduleDB);
                if(empty($employeeScheduleDB)){
                    $employeeSchedule = EmployeeSchedule::create([
                        'employee_id'       => $employeeDB->id,
                        'employee_code'     => $employeeDB->code,
                        'day_status'        => $tempSchedule,
//                        'status_id'         => 1,
                        'created_by'        => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')
                    ]);
                }
                else{
                    $employeeScheduleDB->day_status = $tempSchedule;
                    $employeeScheduleDB->updated_by = 1;
                    $employeeScheduleDB->updated_at = Carbon::now('Asia/Jakarta');
                    $employeeScheduleDB->save();
                }

            }
//        dd($data);
            return redirect()->route('admin.project.set-schedule',['id' => $projectId]);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleEditEmployee error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }

    public function scheduleDownloadExcel(Request $request, int $id){
        try{
            $projectDB = DB::table('projects')
                ->select('name', 'code')
                ->where('id', $id)
                ->first();
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $id)
                ->where('employee_roles_id','<', 4)
                ->where('status_id', 1)
                ->get();

            $dayArr = collect();
            $projectScheduleModel = collect();
            foreach($employeeProjects as $employeeProject){

                $isEmpty = true;
                $scheduleModel = collect();
                $schedule = collect();
                $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeProject->employee_id)->first();

                if(!empty($employeeSchedule)){
                    if(!empty($employeeSchedule->day_status)){
                        $isEmpty = false;
                        $days = explode(";", $employeeSchedule->day_status);
                        $dayArr = collect();
                        foreach ($days as $day){
                            if(!empty($day)){
                                $dayStatus = explode(":", $day);
                                $scheduleDetail = ([
                                    'day'   => $dayStatus[0],
                                    'status' => $dayStatus[1],
                                ]);
                                $scheduleModel->push($scheduleDetail);
                                $dayArr->push($dayStatus[0]);
                            }
                        }
                    }
                }
                if($isEmpty){
                    foreach($dayArr as $day){
                        $scheduleDetail = ([
                            'day'   => $day,
                            'status' => "-",
                        ]);
                        $scheduleModel->push($scheduleDetail);
                    }
                }
                $schedule = collect([
                    'project_code'                  => $projectDB->code,
                    'project_name'                  => $projectDB->name,
                    'employee_id'                   => $employeeProject->employee_id,
                    'employee_name'                 => $employeeProject->employee->first_name. ' '.$employeeProject->employee->last_name,
                    'employee_code'                 => $employeeProject->employee->code,
                ]);
                foreach($scheduleModel as $scheduleCso){
                    $schedule->put('tanggal '.$scheduleCso['day'], $scheduleCso['status']);
                }
                $projectScheduleModel->push($schedule);
            }

            $now = Carbon::now('Asia/Jakarta');
            $file = "Jadwal-CSO-".$projectDB->code."(".$projectDB->name.")_".$now->format('d F Y_G.i.s').'.xlsx';
            // checking attendance END

            $destinationPath = public_path()."/download_attendance/";

            (new FastExcel($projectScheduleModel))
                ->export($destinationPath.$file);
            return response()->download($destinationPath.$file);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleDownloadExcel error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput($request->all());
        }
    }

    public function scheduleDownloadExcelTemplate(int $id){
        try{
            $destinationPath = public_path()."/storage/carefast - contoh upload jadwal.xlsx";

            $newCollection = collect();

            $users = (new FastExcel)->withoutHeaders()->import($destinationPath, function ($line) {
                // Skip if empty or first line (i.e. header), ...
                // ... return the object otherwise (without the first columns)
                return $line;
            });
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $id)
                ->where('employee_roles_id','<', 5)
                ->where('status_id', 1)
                ->get();

            $emptyRow = [];
            for($ct = 0; $ct < 33; $ct++){
                array_push($emptyRow, '');
            }

            $emptyRow2 = [];
            for($ct = 0; $ct < 1; $ct++){
                array_push($emptyRow2, $emptyRow);
            }
            $users->splice(2, 0, $emptyRow2);

            $projectName = '';
            $projectCode = '';
            foreach($employeeProjects as $employeeProject){
                $newItem = [];
                $employeeName = $employeeProject->employee->first_name. ' '.$employeeProject->employee->last_name;
                array_push($newItem, $employeeName);
                $employeeNuc = $employeeProject->employee->code;
                array_push($newItem, $employeeNuc);
                for($ct = 2; $ct < 33; $ct++){
                    array_push($newItem, 'H');
                }
                $users->push($newItem);
                $projectName = $employeeProject->project->name;
                $projectCode = $employeeProject->project->code;
            }
            $data = $users->jsonserialize();
            $data[0][1] = $projectName;
            $data[1][1] = $projectCode;

            $file = "contoh upload jadwal - ".$projectName."(". $projectCode .")".'.xlsx';

            $destinationPath = public_path()."/download_attendance/";

            (new FastExcel($data))
                ->withoutHeaders()
                ->export($destinationPath.$file);

            return response()->download($destinationPath.$file);
        }
        catch(\Exception $ex){
            dd($ex);
            Log::error('Admin/ScheduleController - scheduleDownloadExcelTemplate error EX: '. $ex);
            return null;
        }
    }

    public function scheduleUploadExcel(Request $request, int $id){
        try{
//            dd($request);
            $excel = request()->file('excel');
            $exportResult = Excel::import(new EmployeeScheduleImport(), $excel);

            Session::flash('success', 'Sukses mengubah jadwal karyawan!');
            return redirect()->route('admin.project.set-schedule',['id' => $id]);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - scheduleUploadExcel error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput($request->all());
        }
    }

    public function scheduleStore(Request $request, int $id){
        try{
//            $employee = Employee::find($id);
//        dd($request, $id);
//
//            if(empty($employee)){
//                return redirect()->back();
//            }

            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $id)
                ->where('employee_roles_id','<', 4)
                ->where('status_id', 1)
                ->get();

            $employeeIds = $request->input('employeeId');
            $days = $request->input('days');
            $statuses = $request->input('statuses');

            foreach($employeeProjects as $employeeProject){
                $dayStatuses = "";
                $i = 0;
                foreach ($employeeIds as $employee){
                    if($employeeProject->employee_id == $employee){
                        $dayStatuses .= $days[$i].":".$statuses[$i].";";
                    }
                    $i++;
                }

                $adminUser = Auth::guard('admin')->user();
                $now = Carbon::now('Asia/Jakarta');

                $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeProject->employee_id)->first();
                if(!empty($employeeSchedule)){
                    $employeeSchedule->day_status = $dayStatuses;
                    $employeeSchedule->updated_by = $adminUser->id;
                    $employeeSchedule->updated_at = $now->toDateTimeString();
                    $employeeSchedule->save();
                }
                else{
                    $projectActivityHeader = EmployeeSchedule::create([
                        'employee_id'   => $employeeProject->employee_id,
                        'employee_code' => $employeeProject->employee->code,
                        'day_status'    => $dayStatuses,
                        'created_by'    => $adminUser->id,
                        'created_at'    => $now->toDateTimeString(),
                    ]);
                }
            }

            Session::flash('success', 'Sukses mengubah jadwal karyawan!');
            return redirect()->route('admin.project.set-schedule',['id' => $id]);
        }
        catch(\Exception $ex){
            dd($ex);
            Log::error('Admin/ScheduleController - scheduleStore error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput($request->all());
        }
    }

    public function editProjectShift(int $id){
        try{
            $currentProject = Project::find($id);
            $projectShifts = ProjectShift::Where('project_id', $id)->get();
            $projectCount = count($projectShifts);
            $data = [
                'project'               => $currentProject,
                'projectShifts'         => $projectShifts,
                'projectCount'         => $projectCount,
            ];

            return view('admin.project.schedule.edit-shift')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - editProjectShift error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput();
        }
    }
    public function updateProjectShift(Request $request, int $id){
        try{

            $shifts = $request->input('shift_types');
            $start_time = $request->input('start_time');
            $finish_time = $request->input('finish_time');
//            dd($shifts, $start_time, $finish_time);

            //validation
            if(in_array(null, $start_time, true) || in_array(null, $finish_time, true)){
                return redirect()->back()->withErrors("Start or Finish time not set")->withInput($request->all());
            }
            $isDouble = false;
            for ($i=0; $i < count($shifts); $i++){
                for ($j=0; $j < count($shifts); $j++){
                    if($i == $j) continue;
                    if($shifts[$i] == $shifts[$j]) $isDouble = true;
                }
            }
            if($isDouble){
                return redirect()->back()->withErrors("More Than 1 shift type")->withInput($request->all());
            }

            $currentProject = Project::find($id);
            $projectShifts = ProjectShift::Where('project_id', $id)->get();
            if(count($projectShifts) > 0){
                foreach ($projectShifts as $projectShift){
                    $projectShift->delete();
                }
            }

            $ct=0;
            foreach($shifts as $shift){
                if($shift == null){
                    $ct++;
                    continue;
                }
                $newProjectShift = ProjectShift::create([
                    'project_id'            => $currentProject->id,
                    'project_code'          => $currentProject->code,
                    'shift_type'            => $shift,
                    'start_time'            => $start_time[$ct] ?? '00:00',
                    'finish_time'           => $finish_time[$ct] ?? '00:00',
                ]);
                $ct++;
            }


            Session::flash('success', 'Sukses mengubah Shift Project!');
            return redirect()->route('admin.project.set-schedule',['id' => $id]);
        }
        catch(\Exception $ex){
            Log::error('Admin/ScheduleController - updateProjectShift error EX: '. $ex);
            return redirect()->back()->withErrors($ex)->withInput($request->all());
        }
    }
}

<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectScheduleEmployeeTransformer;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

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
}

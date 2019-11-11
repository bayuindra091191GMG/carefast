<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
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
}

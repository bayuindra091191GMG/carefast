<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectActivityTransformer;
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

class ActivityController extends Controller
{

    public function getIndexActivities(Request $request){
        $id = $request->input('id');
//        $project = Project::find($id);
//        $employeeSchedule = $project->project_employees->sortByDesc('employee_roles_id');
        $projectActivity = ProjectActivity::where('project_id', $id)->orderby('created_at', 'desc')->get();

        return DataTables::of($projectActivity)
            ->setTransformer(new ProjectActivityTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }

        $activities = ProjectActivity::where('project_id', $id)->get();
        $data = [
            'activities'   => $activities,
            'project'         => $project,
        ];
//        dd($data, $activities->count());
        return view('admin.project.activity.show')->with($data);
    }

    public function createStepOne(Request $request, int $id){
        try{

            $project = Project::find($id);

            $data = [
                'project'           => $project,
            ];

            return view('admin.project.activity.create-one')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/activity/ActivityController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function submitCreateOne(Request $request){
        try{
            $start_times = $request->input('start_times');
            $finish_times = $request->input('finish_times');
            $shiftType = $request->input('shift_type');
            $projectId = $request->input('project_id');

            $validStart = true;
            if(!empty($start_times)){
                foreach ($start_times as $start_time){
                    if(empty($start_time)) $validStart = false;
                }
            }
            if(!$validStart){
                return back()->withErrors("Terdapat JAM MULAI yang belum terisi!")->withInput($request->all());
            }

            $validFinish = true;
            if(!empty($finish_times)){
                foreach ($finish_times as $finish_time){
                    if(empty($finish_time)) $validFinish = false;
                }
            }
            if(!$validFinish){
                return back()->withErrors("Terdapat JAM BERAKHIR yang belum terisi!")->withInput($request->all());
            }
            $timeModel = collect();
            $ct = 0;
            foreach ($start_times as $start_time){
                $dayModel = collect();
                for($i=0;$i<365;$i++){
                    $day = collect([
                        "day"       => $i,
				        "action"    => '',
				        "type"    => '',
                        "color"      => ''
                    ]);
                    $dayModel->push($day);
                }
                $time = collect([
                    'time_value'     => $start_times[$ct]."#".$finish_times[$ct],
                    'time_string'    => $start_times[$ct]." - ".$finish_times[$ct],
                    "action_daily"   => "",
                    'weekly_datas'   => [],
                    'monthly_datas'   => [],
                    "days"           => $dayModel,
                ]);
                $timeModel->push($time);
                $ct++;
            }
            $data = [
                'project'           => Project::find($projectId),
                'place'             => Place::find($request->input('places')),
                'shift'             => $shiftType,
                'times'             => $timeModel,
            ];
//dd(json_encode($timeModel));
            return view('admin.project.activity.create-two')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/activity/ActivityController - submitCreateOne error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }
//
//    public function createStepTwo(Request $request){
//        try{
//
//            $project = Project::find($id);
//
//            $data = [
//                'project'           => $project,
//            ];
//
//            return view('admin.project.activity.create-two')->with($data);
//        }
//        catch (\Exception $ex){
//            Log::error('Admin/activity/ActivityController - create error EX: '. $ex);
//            return "Something went wrong! Please contact administrator!";
//        }
//    }

    public function store(Request $request)
    {
        try{
            Log::info('Admin/activity/ActivityController - store request data : '. json_encode($request->input('times')));
            $items = $request->input('times');
            $user = Auth::guard('admin')->user();

            foreach ($items as $item){
                //save to database for daily plot
                if(!empty($item["action_daily"])){
                    $objectString = "";
                    foreach ($item["object_daily"] as $object){
                        $objectString = $objectString."".$object.",";
                    }
                    $actionArr = explode("-",$item["action_daily"]);
                    $action = $actionArr[0]."#";

                    $timeArr = explode("#",$item["time_value"]);
                    $start = Carbon::parse('00-00-00 '.$timeArr[0])->format('Y-m-d H:i:s');
                    $finish = Carbon::parse('00-00-00 '.$timeArr[1])->format('Y-m-d H:i:s');

                    //save to database
                    $projectActivity = ProjectActivity::create([
                        'project_id'            => $request->input('project_id'),
                        'plotting_name'         => $objectString,
                        'action_id'             => $action,
                        'shift_type'            => $request->input('shift_type'),
                        'place_id'              => $request->input('place_id'),
//                    'weeks'                 => $weekString,
                        'days'                  => "1#2#3#4#5#6#",
                        'start'                 => $start,
                        'finish'                => $finish,
                        'period_type'           => "Daily",
                        'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'            => $user->id,
                        'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'            => $user->id,
                    ]);
                }

                //save to database for weekly plot
                if(!empty($item["weekly_datas"])){
                    $i = 1;
                    foreach ($item["weekly_datas"] as $weeklyData){

                        $objectStringWeekly = "";
                        foreach ($weeklyData["Object"] as $objectWeekly){
                            $objectStringWeekly = $objectStringWeekly."".$objectWeekly.",";
                        }
                        $actionArr = explode("-",$weeklyData["Action"]);
                        $actionWeekly = $actionArr[0]."#";

                        $timeWeekArr = explode("#",$weeklyData["time_value"]);
                        $startWeek = Carbon::parse('00-00-00 '.$timeWeekArr[0])->format('Y-m-d H:i:s');
                        $finishWeek = Carbon::parse('00-00-00 '.$timeWeekArr[1])->format('Y-m-d H:i:s');
                        //save to database
                        $projectActivity = ProjectActivity::create([
                            'project_id'            => $request->input('project_id'),
                            'plotting_name'         => $objectStringWeekly,
                            'action_id'             => $actionWeekly,
                            'shift_type'            => $request->input('shift_type'),
                            'place_id'              => $request->input('place_id'),
//                                'weeks'                 => $weekString,
                            'days'                  => "1#2#3#4#5#6#",
                            'start'                 => $startWeek,
                            'finish'                => $finishWeek,
                            'period_type'           => "Weekly",
                            'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'created_by'            => $user->id,
                            'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'updated_by'            => $user->id,
                        ]);
                    }
                }

                //save to database for monthly plot
                if(!empty($item["monthly_datas"])){
                    $i = 1;
                    foreach ($item["monthly_datas"] as $monthlyData){
                        $objectStringMonthly = "";
                        foreach ($monthlyData["Object"] as $objectWeekly){
                            $objectStringMonthly = $objectStringMonthly."".$objectWeekly.",";
                        }
                        $actionArr = explode("-", $monthlyData["Action"]);
                        $actionMonthly = $actionArr[0]."#";

                        $timeMonthArr = explode("#",$monthlyData["time_value"]);
                        $startMonth = Carbon::parse('00-00-00 '.$timeMonthArr[0])->format('Y-m-d H:i:s');
                        $finishMonth = Carbon::parse('00-00-00 '.$timeMonthArr[1])->format('Y-m-d H:i:s');
                        //save to database
                        $projectActivity = ProjectActivity::create([
                            'project_id'            => $request->input('project_id'),
                            'plotting_name'         => $objectStringMonthly,
                            'action_id'             => $actionMonthly,
                            'shift_type'            => $request->input('shift_type'),
                            'place_id'              => $request->input('place_id'),
                            'weeks'                 => $monthlyData['Week'],
                            'days'                  => $monthlyData['Day'],
                            'start'                 => $startMonth,
                            'finish'                => $finishMonth,
                            'period_type'           => "Monthly",
                            'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'created_by'            => $user->id,
                            'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'updated_by'            => $user->id,
                        ]);
                    }
                }
            }
        }
        catch (\Exception $ex){
//            dd($ex);
            Log::error('Admin/activity/ActivityController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!" . $ex;
        }
    }
//    public function store(Request $request)
//    {
//        try{
//            Log::info('Admin/activity/ActivityController - store request data : '. json_encode($request->input('times')));
//
//            $weeks = $request->input('week');
//            $days = $request->input('day');
//            $start_times = $request->input('start_times');
//            $finish_times = $request->input('finish_times');
//            $places = $request->input('places');
//            $shiftType = $request->input('shift_type');
//            $periodType = $request->input('period');
//
//            $validStart = true;
//            if(!empty($start_times)){
//                foreach ($start_times as $start_time){
//                    if(empty($start_time)) $validStart = false;
//                }
//            }
//            if(!$validStart){
//                return back()->withErrors("Terdapat JAM MULAI yang belum terisi!")->withInput($request->all());
//            }
//
//            $validFinish = true;
//            if(!empty($finish_times)){
//                foreach ($finish_times as $finish_time){
//                    if(empty($finish_time)) $validFinish = false;
//                }
//            }
//            if(!$validFinish){
//                return back()->withErrors("Terdapat JAM BERAKHIR yang belum terisi!")->withInput($request->all());
//            }
//
//            //validation for every input
//            $j = 0;
//            foreach($periodType as $periodValue){
//                if($periodValue != "Daily"){
//                    foreach($days[$j] as $dayValue){
//                        if(empty($dayValue)){
//                            return back()->withErrors("Terdapat HARI yang belum terpilih untuk periodic weekly dan monthly!")->withInput($request->all());
//                        }
//                    }
//                }
//                $j++;
//            }
//
//            $i = 0;
//            $user = Auth::guard('admin')->user();
//            //create schedule
//            foreach ($start_times as $start_time){
//                $daysString = "";
//                $start = Carbon::parse('00-00-00 '.$start_times[$i])->format('Y-m-d H:i:s');
//                $finish = Carbon::parse('00-00-00 '.$finish_times[$i])->format('Y-m-d H:i:s');
//
//                if($periodType[$i]  == "Daily"){
//                    $day = "1#2#3#4#5#6#7#";
//                }
//                else{
//                    foreach($days[$i] as $dayValue){
//                        $daysString .= $dayValue."#";
//                    }
//                    $day = $daysString;
//                }
//
//                $actionsString = "";
//                $objectsString = "";
//                $actions = $request->input('actions'.$i);
//                foreach($actions as $actionValue){
//                    $actionsString .= $actionValue."#";
//                }
//
//                $objects = $request->input('project_objects'.$i);
//                foreach($objects as $objectValue){
////                    $objectsString .= $objectValue."#";
////                    dd($objectsString, $actionsString);
//
//                    $projectActivity = ProjectActivity::create([
//                        'project_id'            => $request->input('project_id'),
//                        'plotting_name'         => $objectValue,
//                        'action_id'             => $actionsString,
//                        'shift_type'            => $shiftType,
//                        'place_id'              => $places,
////                    'weeks'                 => $weekString,
//                        'days'                  => $day,
//                        'start'                 => $start,
//                        'finish'                => $finish,
//                        'period_type'           => $periodType[$i],
//                        'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
//                        'created_by'            => $user->id,
//                        'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
//                        'updated_by'            => $user->id,
//                    ]);
//                }
//
//                $i++;
//            }
//
//            return redirect()->route('admin.project.activity.show', ['id' => $request->input('project_id')]);
//        }
//        catch (\Exception $ex){
////            dd($ex);
//            Log::error('Admin/activity/ActivityController - store error EX: '. $ex);
//            return "Something went wrong! Please contact administrator!" . $ex;
//        }
//    }

    public function edit(int $id)
    {
        $projectActivity = ProjectActivity::find($id);
        $project = Project::find($projectActivity->project_id);
        if(empty($projectActivity)){
            return redirect()->back();
        }

        $data = [
            'projectActivity'           => $projectActivity,
            'project'           => $project,
        ];
//        dd($data);
        return view('admin.project.activity.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{

            Session::flash('success', 'Sukses mengubah data plotting!');
            return redirect()->route('admin.project.activity.show',['id' => $id]);

        }
        catch (\Exception $ex){
            Log::error('Admin/activity/ActivityController - update error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }
}

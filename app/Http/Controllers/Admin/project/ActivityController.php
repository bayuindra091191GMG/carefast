<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\EmployeePlottingSchedule;
use App\Models\EmployeeRole;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesDetail;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\ProjectShift;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectActivityTransformer;
use App\Transformer\ProjectScheduleEmployeeTransformer;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class ActivityController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndexActivities(Request $request){
        $id = $request->input('id');
        $place_id = $request->input('place_id');
//        $project = Project::find($id);
//        $employeeSchedule = $project->project_employees->sortByDesc('employee_roles_id');
        if($place_id > 0){
            $projectActivities = ProjectActivitiesHeader::where('project_id', $id)
                ->where('place_id', $place_id)
                ->orderby('place_id', 'desc')
                ->get();
        }
        else{
            $projectActivities = ProjectActivitiesHeader::where('project_id', $id)->orderby('place_id', 'desc')->get();
        }
        $idArr = collect();
        foreach ($projectActivities as $projectActivity){
            $idArr->push($projectActivity->id);
        }
        $projectDetails = ProjectActivitiesDetail::whereIn('activities_header_id', $idArr)->get();


        return DataTables::of($projectDetails)
            ->setTransformer(new ProjectActivityTransformer())
            ->make(true);
    }

    public function show(Request $request, int $id)
    {
        $placeId = 0;
        if(!empty($request->place)){
            $placeId = $request->place;
        }
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }

        $activities = ProjectActivitiesHeader::where('project_id', $id)->get();
        $placeIds = ProjectObject::select('place_id')->where('project_id', $id)->get();


        $places = Place::whereIn('id', $placeIds)->get();

        if($placeId > 0){
            $activities = ProjectActivitiesHeader::where('project_id', $id)
                ->where('place_id', $placeId)
                ->get();
        }
        $data = [
            'activities'   => $activities,
            'project'         => $project,
            'places'         => $places,
            'placeId'         => $placeId,
        ];
//        dd($data);
        return view('admin.project.activity.show')->with($data);
    }

    public function copy(Request $request, int $id)
    {
        $placeId = 0;
        if(!empty($request->place)){
            $placeId = $request->place;
        }
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }

        $activities = ProjectActivitiesHeader::where('project_id', $id)->get();
        $placeIds = ProjectObject::select('place_id')->where('project_id', $id)->get();


        $places = Place::whereIn('id', $placeIds)->get();

        if($placeId > 0){
            $activities = ProjectActivitiesHeader::where('project_id', $id)
                ->where('place_id', $placeId)
                ->get();
        }
        $data = [
            'activities'   => $activities,
            'project'         => $project,
            'places'         => $places,
            'placeId'         => $placeId,
        ];
//        dd($data);
        return view('admin.project.activity.show')->with($data);
    }

    public function createStepOne(Request $request, int $id){
        try{

            $project = Project::find($id);
            $projectShifts = ProjectShift::Where('project_code', $project->code)->get();
            $data = [
                'project'       => $project,
                'projectShifts' => $projectShifts,
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
            $projectObjects = $request->input('project_objects0');

            $objectString = "";
//            for($i=0;$i<count($projectObjects);$i++){
//                if($i == count($projectObjects)-1){
//                    $objectString = $objectString."".$projectObjects[$i];
//                }
//                else{
//                    $objectString = $objectString."".$projectObjects[$i].",";
//                }
//            }

//            foreach ($projectObjects as $projectObject){
//                $objectString = $objectString."".$projectObject.",";
//            }

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
            $projectDB = Project::find($projectId);
            $realDate = collect();

            $diffDate = $projectDB->start_date->diffInDays($projectDB->finish_date);
            for($i=0;$i<($diffDate + 1);$i++){
                $newDate = $projectDB->start_date->addDays($i);
                $newDateCarbon = Carbon::parse($newDate)->format("d M Y");
                $realDate->push($newDateCarbon);
            }

//            dd($diffDate);
            $ct = 0;
            foreach ($start_times as $start_time){
                $dayModel = collect();
                for($i=0;$i<($diffDate + 1);$i++){
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
                    "daily_datas"   => [],
                    'weekly_datas'   => [],
                    'monthly_datas'   => [],
                    "days"           => $dayModel,
                ]);
                $timeModel->push($time);
                $ct++;
            }
            $data = [
                'project'           => $projectDB,
                'place'             => Place::find($request->input('places')),
                'object'            => $objectString,
                'shift'             => $shiftType,
                'times'             => $timeModel,
                'realDate'          => $realDate,
            ];
//            dd($data);
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

//            return response()->json([
//                'request' => $request,
//                'url' => route('admin.project.activity.show', ['id' => (int)$request->input('project_id')])
//            ]);

            $items = $request->input('times');
            $user = Auth::guard('admin')->user();

            //save to database
            $projectActivityHeader = ProjectActivitiesHeader::create([
                'project_id'            => $request->input('project_id'),
                'plotting_name'         => $request->input('object'),
                'place_id'              => $request->input('place_id'),
                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'            => $user->id,
                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'            => $user->id,
            ]);

            foreach ($items as $item){
                //save to database for daily plot
                if(!empty($item["daily_datas"])){
                    foreach ($item["daily_datas"] as $dailyData){
                        $objectStringDaily = "";
                        foreach ($dailyData["Object"] as $objectDaily){
                            $objectStringDaily = $objectStringDaily."".$objectDaily.",";
                        }
                        $actionArr = explode("-",$dailyData["Action"]);
                        $action = $actionArr[0]."#";

//                        foreach ($dailyData["TimeValue"] as $singleDailyData) {
                            $timeArr = explode("#", $item["time_value"]);
                            $start = Carbon::parse('00-00-00 ' . $timeArr[0])->format('Y-m-d H:i:s');
                            $finish = Carbon::parse('00-00-00 ' . $timeArr[1])->format('Y-m-d H:i:s');

                            //save to database
                            $projectActivityDetail = ProjectActivitiesDetail::create([
                                'activities_header_id' => $projectActivityHeader->id,
                                'action_id' => $action,
                                'shift_type' => $request->input('shift_type'),
                                'weeks' => "1#2#3#4#5#",
                                'days' => "1#2#3#4#5#6#7#",
                                'start' => $start,
                                'finish' => $finish,
                                'object_name' => $objectStringDaily,
                                'period_type' => "Daily",
                                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by' => $user->id,
                                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by' => $user->id,
                            ]);
//                        }
                    }
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

//                        foreach ($weeklyData["TimeValue"] as $singleWeeklyData) {
                            $timeWeekArr = explode("#", $item["time_value"]);
                            $startWeek = Carbon::parse('00-00-00 ' . $timeWeekArr[0])->format('Y-m-d H:i:s');
                            $finishWeek = Carbon::parse('00-00-00 ' . $timeWeekArr[1])->format('Y-m-d H:i:s');

                            //save to database
                            $projectActivityDetail = ProjectActivitiesDetail::create([
                                'activities_header_id' => $projectActivityHeader->id,
                                'action_id' => $actionWeekly,
                                'shift_type' => $request->input('shift_type'),
                                'weeks' => "1#2#3#4#5#",
                                'days' => $weeklyData['Day'],
                                'start' => $startWeek,
                                'finish' => $finishWeek,
                                'object_name' => $objectStringWeekly,
                                'period_type' => "Weekly",
                                'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by' => $user->id,
                                'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by' => $user->id,
                            ]);
//                        }
                    }
                }

                //save to database for monthly plot
                if(!empty($item["monthly_datas"])){
                    $i = 1;
                    foreach ($item["monthly_datas"] as $monthlyData){
                        $objectStringMonthly = "";
                        foreach ($monthlyData["Object"] as $objectMonthly){
                            $objectStringMonthly = $objectStringMonthly."".$objectMonthly.",";
                        }
                        $actionArr = explode("-", $monthlyData["Action"]);
                        $actionMonthly = $actionArr[0]."#";

//                        foreach ($monthlyData["TimeValue"] as $singleMonthlyData){
                            $timeMonthArr = explode("#",$item["time_value"]);
                            $startMonth = Carbon::parse('00-00-00 '.$timeMonthArr[0])->format('Y-m-d H:i:s');
                            $finishMonth = Carbon::parse('00-00-00 '.$timeMonthArr[1])->format('Y-m-d H:i:s');
                            //save to database
                            $projectActivityDetail = ProjectActivitiesDetail::create([
                                'activities_header_id'  => $projectActivityHeader->id,
                                'action_id'             => $actionMonthly,
                                'shift_type'            => $request->input('shift_type'),
                                'weeks'                 => $monthlyData['Week'],
                                'days'                  => $monthlyData['Day'],
                                'start'                 => $startMonth,
                                'finish'                => $finishMonth,
                                'object_name'           => $objectStringMonthly,
                                'period_type'           => "Monthly",
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => $user->id,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => $user->id,
                            ]);
//                        }
                    }
                }
            }
            return response()->json([
                'request' => $request,
                'url' => route('admin.project.activity.show', ['id' => (int)$request->input('project_id')])
            ]);
        }
        catch (\Exception $ex){
//            dd($ex);
            Log::error('Admin/activity/ActivityController - store error EX: '. $ex);
            return response()->json(['errors' => "Something went wrong!"]);
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
        $projectActivity = ProjectActivitiesDetail::find($id);
        $projectActivityHeader = ProjectActivitiesHeader::find($projectActivity->activities_header_id);
        $project = Project::find($projectActivityHeader->project_id);
        if(empty($projectActivity)){
            return redirect()->back();
        }
        $actionName = "";
        if(!empty($projectActivity->action_id)){
            $actionList = explode('#', $projectActivity->action_id);
            foreach ($actionList as $action){
                if(!empty($action)){
                    $action = Action::find($action);
                    $actionName .= $action->name. " ";
                }
            }
        }
        $projectShifts = ProjectShift::Where('project_code', $project->code)->get();

        $data = [
            'projectActivity'           => $projectActivity,
            'projectActivityHeader'           => $projectActivityHeader,
            'actionName'           => $actionName,
            'project'           => $project,
            'projectShifts' => $projectShifts,
        ];
//        dd($data);
        return view('admin.project.activity.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{

//            dd($request);
            $activityDetailId = $request->input('project_activity_detail');
            $actions0 = $request->input('actions0');
            $actionNew = $request->input('actionNew');
            $startTime = $request->input('start_times');
            $finishTime = $request->input('finish_times');
            $shiftType = $request->input('shift_type');
            $projectObjects = $request->input('project_objects0');

            $objectString = "";
            $projectDetailDB = ProjectActivitiesDetail::find($activityDetailId);
            if(!empty($projectObjects)){
                for($i=0;$i<count($projectObjects);$i++){
                    if($i == count($projectObjects)-1){
                        $objectString = $objectString.$projectObjects[$i];
                    }
                    else{
                        $objectString = $objectString.$projectObjects[$i].", ";
                    }
                }

                $projectDetailDB->object_name = $objectString;
            }
            $startTimeParse = Carbon::parse('00-00-00 '.$startTime)->format('Y-m-d H:i:s');
            $finishTimeParse = Carbon::parse('00-00-00 '.$finishTime)->format('Y-m-d H:i:s');
            $projectDetailDB->start = $startTimeParse;
            $projectDetailDB->finish = $finishTimeParse;
            $projectDetailDB->shift_type = $shiftType;

            if(!empty($actions0) && empty($actionNew)){
                $actionArr = explode("-", $actions0);
                $action = $actionArr[0]."#";
                $projectDetailDB->action_id = $action;
            }
            if(!empty($actionNew)){
                $user = Auth::guard('admin')->user();
                $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

                $actionNewDb = Action::create([
                    'name'          => strtoupper($actionNew),
                    'description'   => strtoupper($actionNew),
                    'status_id'     => 1,
                    'created_at'    => $dateNow,
                    'created_by'    => $user->id,
                    'updated_at'    => $dateNow,
                    'updated_by'    => $user->id
                ]);
                $action = $actionNewDb."#";
                $projectDetailDB->action_id = $action;
            }
            $projectDetailDB->save();

            $scheduleDetail = ScheduleDetail::where('project_activity_detail_id', $activityDetailId)->first();
            $scheduleDetail->project_activity_detail_id = $action;
            $scheduleDetail->save();


            Session::flash('success', 'Sukses mengubah data plotting!');
            return redirect()->route('admin.project.activity.show',['id' => $id]);

        }
        catch (\Exception $ex){
            Log::error('Admin/activity/ActivityController - update error EX: '. $ex);
            return "Something went wrong! Please contact administrator! ". $ex;
        }
    }
    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $sub1unit = ProjectActivitiesDetail::find($deletedId);
            $sub1unit->delete();

            $scheduleDetail = ScheduleDetail::where('project_activity_detail_id', $deletedId);
            $scheduleDetail->delete();


            Session::flash('success', 'Sukses menghapus data!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/ActivityController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }

    //JADWAL PLOTTING
    public function schedulePlottingShow(Request $request, int $id)
    {
        try {
            $currentProject = Project::find($id);

            if (empty($currentProject)) {
                return redirect()->back();
            }
            $start_date = Carbon::now()->startOfMonth()->toDateString();
            $end_date = Carbon::now()->endOfMonth()->toDateString();

            $isSelectDate = true;
            $dayArr = collect();
            $period = CarbonPeriod::create($start_date, $end_date);
            foreach ($period as $date) {
                // formated j = The day of the month without leading zeros (1 to 31)
                $dayArr->push($date->format('j'));
            }

            $projectPlottingScheduleModel = collect();
            $projectActivities = ProjectActivitiesHeader::where('project_id', $id)->get();
            if (count($projectActivities) > 0) {
                foreach ($projectActivities as $projectActivity) {
                    $isEmpty = true;
                    $scheduleModel = collect();
                    $dacDetailDescription = "";

                    $employeePlottingSchedule = EmployeePlottingSchedule::where('project_activity_id', $projectActivity->id)->first();
                    $place = Place::find($projectActivity->place_id);
                    $projectActivityDetail = ProjectActivitiesDetail::where('activities_header_id', $projectActivity->id)->first();
                    $projectShifts = ProjectShift::Where('id', $projectActivityDetail->shift_type)->first();
                    $shiftString = $projectShifts == null ? "-" : $projectShifts->shift_type;

                    foreach ($projectActivity->project_activities_details as $projectDetail){
                        $actionName = collect();
//                    $actionName = "";
                        if(!empty($projectDetail->action_id)){
                            $actionList = explode('#', $projectDetail->action_id);
                            foreach ($actionList as $action){
                                if(!empty($action)){
                                    $action = Action::find($action);
//                                $actionName .= $action->name. ", ";
                                    $actionName->push($action->name);
                                }
                            }
                        }
                        $dacDetailDescription .= Carbon::parse($projectDetail->start)->format('H:i')." - ".Carbon::parse($projectDetail->finish)->format('H:i'). " ".$actionName."\n";
                    }
                    $plottingModel = collect([
                        'project_activities_header_id' => $projectActivity->id,
                        'project_activities_detail_description'     => $dacDetailDescription,
                        'place' => $place->name,
                        'shift' => $shiftString,
                        'days' => '',
                    ]);

                    if (!empty($employeePlottingSchedule)) {
                        if (!empty($employeePlottingSchedule->day_employee_id)) {
                            $isEmpty = false;
                            $days = explode(";", $employeePlottingSchedule->day_employee_id);
                            $dayArr = collect();
                            foreach ($days as $day) {
                                if (!empty($day)) {
                                    $dayStatus = explode(":", $day);
                                    $employeeDB = Employee::find($dayStatus[1]);

                                    $scheduleDetail = ([
                                        'day' => $dayStatus[0],
                                        'employee_id' => $dayStatus[1],
                                        'employee_name' => $employeeDB->first_name." ".$employeeDB->last_name,
                                    ]);
                                    $scheduleModel->push($scheduleDetail);
                                    $dayArr->push($dayStatus[0]);
                                }
                            }
                            $plottingModel['days'] = $scheduleModel;
                            $projectPlottingScheduleModel->push($plottingModel);
                        }
                    }
                    if ($isEmpty) {
                        foreach ($dayArr as $day) {
                            $scheduleDetail = ([
                                'day' => $day,
                                'employee_id' => "",
                                'employee_name' => "",
                            ]);
                            $scheduleModel->push($scheduleDetail);
                        }
                        $plottingModel['days'] = $scheduleModel;
                        $projectPlottingScheduleModel->push($plottingModel);
                    }
                }
            }

            //get project shift
            $projectShifts = ProjectShift::Where('project_code', $currentProject->code)->get();
            $data = [
                'project' => $currentProject,
                'projectShifts' => $projectShifts,
                'projectPlottingScheduleModel' => $projectPlottingScheduleModel,
                'days' => $dayArr,
                'isSelectDate' => $isSelectDate,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];
//        dd($data);
            return view('admin.project.activity.show-plotting')->with($data);
        } catch (\Exception $ex) {
            Log::error('Admin/ActivityController - schedulePlottingShow error EX: ' . $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
    public function schedulePlottingEdit(Request $request, int $id){
        try{
            $projectId = $request->projectId;
            $currentProject = Project::find($projectId);

            if (empty($currentProject)) {
                return redirect()->back();
            }
            $start_date = Carbon::now()->startOfMonth()->toDateString();
            $end_date = Carbon::now()->endOfMonth()->toDateString();

            $isSelectDate = true;
            $dayArr = collect();
            $period = CarbonPeriod::create($start_date, $end_date);
            foreach ($period as $date) {
                // formated j = The day of the month without leading zeros (1 to 31)
                $dayArr->push($date->format('j'));
            }

            $projectPlottingScheduleModel = collect();
            $projectActivity = ProjectActivitiesHeader::where('id', $id)->first();
            if (!empty($projectActivity)) {
                $isEmpty = true;
                $dacDetailDescription = "";
                $scheduleModel = collect();

                $employeePlottingSchedule = EmployeePlottingSchedule::where('project_activity_id', $projectActivity->id)->first();
                $place = Place::find($projectActivity->place_id);
                $projectActivityDetail = ProjectActivitiesDetail::where('activities_header_id', $projectActivity->id)->first();
                $projectShifts = ProjectShift::Where('id', $projectActivityDetail->shift_type)->first();
                $shiftString = $projectShifts == null ? "-" : $projectShifts->shift_type;

                foreach ($projectActivity->project_activities_details as $projectDetail){
                    $actionName = collect();
//                    $actionName = "";
                    if(!empty($projectDetail->action_id)){
                        $actionList = explode('#', $projectDetail->action_id);
                        foreach ($actionList as $action){
                            if(!empty($action)){
                                $action = Action::find($action);
//                                $actionName .= $action->name. ", ";
                                $actionName->push($action->name);
                            }
                        }
                    }
                    $dacDetailDescription .= Carbon::parse($projectDetail->start)->format('H:i')." - ".Carbon::parse($projectDetail->finish)->format('H:i'). " ".$actionName."\n";
                }
                $plottingModel = collect([
                    'project_activities_header_id' => $projectActivity->id,
                    'project_activities_detail_description'     => $dacDetailDescription,
                    'place' => $place->name,
                    'shift' => $shiftString,
                    'days' => '',
                ]);

                if (!empty($employeePlottingSchedule)) {
                    if (!empty($employeePlottingSchedule->day_employee_id)) {
                        $isEmpty = false;
                        $days = explode(";", $employeePlottingSchedule->day_employee_id);
                        $dayArr = collect();
                        foreach ($days as $day) {
                            if (!empty($day)) {
                                $dayStatus = explode(":", $day);
                                $employeeDB = Employee::find($dayStatus[1]);

                                $scheduleDetail = ([
                                    'day' => $dayStatus[0],
                                    'employee_id' => $dayStatus[1],
                                    'employee_name' => $employeeDB->first_name." ".$employeeDB->last_name,
                                ]);
                                $scheduleModel->push($scheduleDetail);
                                $dayArr->push($dayStatus[0]);
                            }
                        }
                        $plottingModel['days'] = $scheduleModel;
                        $projectPlottingScheduleModel->push($plottingModel);
                    }
                }
                if ($isEmpty) {
                    foreach ($dayArr as $day) {
                        $scheduleDetail = ([
                            'day' => $day,
                            'employee_id' => "",
                            'employee_name' => "",
                        ]);
                        $scheduleModel->push($scheduleDetail);
                    }
                    $plottingModel['days'] = $scheduleModel;
                    $projectPlottingScheduleModel->push($plottingModel);
                }
            }

            //get project shift
            $employeeProjects = ProjectEmployee::with('employee')
                ->where('project_id', $projectId)
                ->where('employee_roles_id', 1)
                ->where('status_id', 1)
                ->get();
            $data = [
                'project' => $currentProject,
                'employeeProjects' => $employeeProjects,
                'projectPlottingScheduleModel' => $projectPlottingScheduleModel,
                'days' => $dayArr,
                'isSelectDate' => $isSelectDate,
                'start_date' => $start_date,
                'end_date' => $end_date,
            ];
//            dd($data);
            return view('admin.project.activity.edit-plotting')->with($data);
        }
        catch(\Exception $ex){
            Log::error('Admin/ActivityController - schedulePlottingEdit error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
    public function schedulePlottingUpdate(Request $request){
        try{
            $projectId = $request->input('projectId');
            $currentProject = Project::find($projectId);

            if(empty($currentProject)){
                return redirect()->back();
            }

            $ct =0 ;
            $tempPlottingSchedule = "";
            $projectActivitesHeaderId = 1;
            //create day_status
            // ex : 16:M;17:M;18:M;19:M;20:M;21:M;22:O;23:M;24:M;25:M;26:M;27:M;28:M;29:O;30:O;31:O;1:M;2:M;3:M;4:M;5:M;6:M;7:O;8:M;9:M;10:M;11:M;12:M;13:M;14:O;15:M;
            $days = $request->input('days');
            $employees = $request->input('employeeIds');
            $projectActivitesHeaderIds = $request->input('projectActivitesHeaderIds');
            foreach($days as $day){
                $tempPlottingSchedule .= $days[$ct].":".$employees[$ct].";";
                $projectActivitesHeaderId = $projectActivitesHeaderIds[$ct];
                $ct++;
            }
            $employeeScheduleDB = EmployeePlottingSchedule::where('project_activity_id', $projectActivitesHeaderId)
                ->where('project_id', $projectId)
                ->first();
//            dd($tempPlottingSchedule, $employeeScheduleDB);
            if(empty($employeeScheduleDB)){
                $employeeSchedule = EmployeePlottingSchedule::create([
                    'project_id'            => $projectId,
                    'project_activity_id'   => $projectActivitesHeaderId,
                    'day_employee_id'       => $tempPlottingSchedule,
//                        'status_id'           => 1,
                    'created_by'            => 1,
                    'created_at'            => Carbon::now('Asia/Jakarta')
                ]);
            }
            else{
                $employeeScheduleDB->day_employee_id = $tempPlottingSchedule;
                $employeeScheduleDB->updated_by = 1;
                $employeeScheduleDB->updated_at = Carbon::now('Asia/Jakarta');
                $employeeScheduleDB->save();
            }

//        dd($data);
            return redirect()->route('admin.project.activity.show-schedule-plotting',['id' => $projectId]);
        }
        catch(\Exception $ex){
            dd($ex);
            Log::error('Admin/ScheduleController - scheduleEditEmployee error EX: '. $ex);
            return redirect()->back()->withErrors($ex);
        }
    }
}

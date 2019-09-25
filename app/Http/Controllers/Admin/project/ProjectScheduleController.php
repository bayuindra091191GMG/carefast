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

class ProjectScheduleController extends Controller
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
        $projectSchedules = $project->schedules;
        $projectEmployees = $project->project_employees->sortByDesc('employee_roles_id');

        $isCreate = false;
        if($projectEmployees->count() === 0){
            $isCreate = true;
        }
        $data = [
            'project'               => $project,
            'projectEmployees'     => $projectEmployees,
            'projectSchedules'     => $projectSchedules,
            'isCreate'          => $isCreate,
        ];
        return view('admin.project.schedule.show')->with($data);
    }

    public function create(int $employee_id){
        try{
            $projectEmployee = ProjectEmployee::find($employee_id);
            $project = $projectEmployee->project;

            if(empty($projectEmployee)){
                return redirect()->back();
            }
            $data = [
                'project'           => $project,
                'projectEmployee'     => $projectEmployee,
            ];

            return view('admin.project.schedule.create')->with($data);
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
            $finish_times= $request->input('finish_times');
            $places= $request->input('places');
            $start = Carbon::parse('00-00-00 '.$start_times[0])->format('Y-m-d H:i:s');
            $finish = Carbon::parse('00-00-00 '.$finish_times[0])->format('Y-m-d H:i:s');
//            dd($start, $finish);
//            dd($weeks, $days, $start_times, $finish_times);

            //validation for every input
            $j = 0;
            if(empty($request->input('day'))){
                return back()->withErrors("Terdapat MINGGU yang belum terpilih!")->withInput($request->all());
            }
            if(empty($request->input('week'))){
                return back()->withErrors("Terdapat HARI yang belum terpilih!")->withInput($request->all());
            }
            foreach ($start_times as $start_time){
                if(empty($weeks[$j])){
                    return back()->withErrors("Terdapat MINGGU yang belum terpilih!")->withInput($request->all());
                }
                if(empty($days[$j])){
                    return back()->withErrors("Terdapat MINGGU yang belum terpilih!")->withInput($request->all());
                }
                if($places[$j] == '-1'){
                    return back()->withErrors("Terdapat PLACE yang belum terpilih!")->withInput($request->all());
                }
                $j++;
            }
//            dd($request);

            $i = 0;
            $user = Auth::guard('admin')->user();
            //create schedule
            foreach ($start_times as $start_time){
                $daysString = "";
                $weekString = "";
                foreach($weeks[$i] as $weekValue){
                    $weekString .= $weekValue."#";
                }
                foreach($days[$i] as $dayValue){
                    $daysString .= $dayValue."#";
                }
                $start = Carbon::parse('00-00-00 '.$start_times[0])->format('Y-m-d H:i:s');
                $finish = Carbon::parse('00-00-00 '.$finish_times[0])->format('Y-m-d H:i:s');

                $schedule = Schedule::create([
                    'project_id'            => $request->input('project_id'),
                    'project_employee_id'   => $request->input('project_employee_id'),
                    'shift_type'            => 1,
                    'place_id'              => $places[$i],
                    'weeks'                 => $weekString,
                    'days'                  => $daysString,
                    'start'                 => $start,
                    'finish'                => $finish,
                    'status_id'             => 1,
                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'            => $user->id,
                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'            => $user->id,
                ]);
                $i++;
            }

            return redirect()->route('admin.project.schedule.create-detail', ['id' => $request->input('project_employee_id')]);
        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!" . $ex;
        }
    }

    public function createDetail(int $employee_id){
        try{
            $projectSchedule = Schedule::where('project_employee_id', $employee_id)->get();
            $projectEmployee = ProjectEmployee::find($projectSchedule[0]->project_employee_id);
            $project = Project::find($projectSchedule[0]->project_id);

            if(empty($projectSchedule)){
                return redirect()->back();
            }

            $data = [
                'project'           => $project,
                'projectEmployee'     => $projectEmployee,
                'projectSchedules'     => $projectSchedule,
            ];

            return view('admin.project.schedule.create-detail')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - createDetail error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }
    public function storeDetail(Request $request)
    {
//        dd($request);
        try{

            $scheduleIds = $request->input('schedule_id');
            $projectObjects = $request->input('project_objects');
            $actions = $request->input('actions');
            $actionNews = $request->input('action_new');
            $descriptions = $request->input('description');

            $user = Auth::guard('admin')->user();
            $i = 0;
            foreach($scheduleIds as $scheduleId){
                $selectedAction = "";
                //check if any new action, then create new place, else use selected place
                if(empty($actionNews[$i])){
                    $selectedAction = $actions[$i];
                }
                else{
                    $placeDBNew = Action::create([
                        'name'              => strtoupper($actionNews[$i]),
                        'description'       => strtoupper($actionNews[$i]),
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => $user->id,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => $user->id,
                    ]);
                    $selectedAction = $placeDBNew->id;
                }

                if($selectedAction != '-1' && $projectObjects[$i] != '-1' ){
                    $scheduleDetail = ScheduleDetail::create([
                        'schedule_id'           => $scheduleIds[$i],
                        'project_object_id'     => $projectObjects[$i],
                        'action_id'             => $selectedAction,
                        'description'           => $descriptions[$i],
                        'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'            => $user->id,
                        'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'            => $user->id,
                    ]);
                }
                $i++;
            }
            Session::flash('success', 'Sukses membuat schedule detail baru!');
            return redirect()->route('admin.project.schedule.show', ['id' => $request->input('project_id')]);
        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function edit(int $id)
    {
        $project = Project::find($id);
        if(empty($project)){
            return redirect()->back();
        }

        $data = [
            'project'          => $project,
        ];
        return view('admin.project.schedule.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'name'          => 'required',
                'address'       => 'required',
                'phone'         => 'required',
                'customer'           => 'required',
                'latitude'      => 'required',
                'longitude'     => 'required',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $project = Project::find($id);
            if(empty($project)){
                return redirect()->back();
            }
            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $project->name = strtoupper($request->input('name'));
            $project->phone = $request->input('phone') ?? '';
            $project->customer_id = $request->input('customer');
            $project->latitude = $request->input('latitude');
            $project->longitude =$request->input('longitude');
            $project->address = $request->input('address');
            $project->description = $request->input('description');
            $project->status_id = $request->input('status');
            $project->updated_by = $adminUser->id;
            $project->updated_at = $now->toDateTimeString();
            $project->save();

            Session::flash('success', 'Sukses mengubah data schedule!');
            return redirect()->route('admin.project.schedule.show',['id' => $project->id]);

        }
        catch (\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - update error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function destroy(Request $request){
        try{
//            $deletedId = $request->input('id');
//            $customer = Customer::find($deletedId);
//            $customer->status_id = 2;
//            $customer->save();

            Session::flash('success', 'Sukses mengganti status customer!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/schedule/ProjectScheduleController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

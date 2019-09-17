<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Project;
use App\Models\ProjectEmployee;
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
            $validator = Validator::make($request->all(), [
                'name'          => 'required',
                'address'       => 'required',
                'phone'         => 'required',
                'customer'           => 'required',
                'latitude'      => 'required',
                'longitude'     => 'required',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            //Create Project

            $user = Auth::guard('admin')->user();
            $project = Project::create([
                'name'              => $request->input('name'),
                'phone'             => $request->input('phone'),
                'customer_id'            => $request->input('customer'),
                'latitude'          => $request->input('latitude'),
                'longitude'         => $request->input('longitude'),
                'address'           => $request->input('address'),
                'description'           => $request->input('description'),
                'status_id'         => $request->input('status'),
                'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'created_by'        => $user->id,
                'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_by'        => $user->id,
            ]);

            Session::flash('success', 'Sukses membuat schedule baru!');
            return redirect()->route('admin.project.schedule.index');
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

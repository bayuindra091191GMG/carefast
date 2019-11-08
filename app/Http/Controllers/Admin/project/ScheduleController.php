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
        $employeeRoles = EmployeeRole::where('id', '<', 5)->get();

        $data = [
            'employeeRoles'   => $employeeRoles,
            'project'         => $project,
        ];
        return view('admin.project.schedule.show2')->with($data);
    }

    public function create(int $id){
        try{
            $projectEmployee = ProjectEmployee::find($id);
            $project = $projectEmployee->project;

            $employeeRole = EmployeeRole::find($id);

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
}

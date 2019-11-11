<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProjectEmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show(int $project_id)
    {
        $project = Project::find($project_id);
        if(empty($project)){
            return redirect()->back();
        }

        $manpowerLeft = $project->total_manpower - $project->total_manpower_used;

        $employeeRoleAssigned = collect();

        $employeeRoles = EmployeeRole::where('id', '<', 9)->get();
        foreach($employeeRoles as $employeeRole){
            $assignedEmployees = ProjectEmployee::where('project_id', $project_id)
                ->where('employee_roles_id', $employeeRole->id)
                ->count();
            $employeeRoleAssigned->push($assignedEmployees);
        }

        $upperEmployees = ProjectEmployee::with(['employee','employee_role'])
            ->where('project_id', $project_id)
            ->whereIn('employee_roles_id', [2,3,4])
            ->get();

        $cleanerEmployees = ProjectEmployee::with('employee')
            ->where('project_id', $project_id)
            ->where('employee_roles_id', 1)
            ->get();

        $isCreate = false;
        if($upperEmployees->count() === 0 && $cleanerEmployees->count() === 0){
            $isCreate = true;
        }

        $data = [
            'project'               => $project,
            'employeeRoleAssigned'  => $employeeRoleAssigned,
            'employeeRoles'         => $employeeRoles,
            'manpowerLeft'          => $manpowerLeft,
            'isCreate'              => $isCreate,
            'upperEmployees'        => $upperEmployees,
            'cleanerEmployees'      => $cleanerEmployees
        ];

        return view('admin.project.employee.show2')->with($data);
    }

    public function create(int $project_id){
        try{
            $project = Project::find($project_id);
            if(empty($project)){
                return redirect()->back();
            }

            $employeeRoles = EmployeeRole::where('id', '<', 9)->get();

            $manpower = $project->total_manpower - 2;

            $data = [
                'project'       => $project,
                'employeeRoles'       => $employeeRoles,
                'manpower'      => $manpower
            ];

            return view('admin.project.employee.create2')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectEmployeeController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }
    public function store(Request $request, int $project_id)
    {
        try{
            // Validate input
            $employeeTotalList = $request->input('employee_total');
            $employeeRoleIds = $request->input('employee_role_id');

            $valid = false;
            if(!empty($employeeTotalList)){
                foreach ($employeeTotalList as $employeeTotal){
                    if(empty($employeeTotal)) $valid = true;
                }
            }
            if(!$valid){
                return back()
                    ->withErrors("Jumlah Role harus terisi semua!")
                    ->withInput($request->all());
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $manpowerUsed = 0;
            $project = Project::find($project_id);
            if(!empty($employeeTotalList)){
                $idx = 0;
                foreach ($employeeTotalList as $employeeTotal){
                    $employeeRole = EmployeeRole::find((int)$employeeRoleIds[$idx]);
                    for($i=1; $i<=(int)$employeeTotal; $i++){
                        ProjectEmployee::create([
                            'project_id'        => $project_id,
                            'employee_roles_id' => $employeeRoleIds[$idx],
                            'project_employee_code' => $project->code.'-'.$employeeRole->description.'-'.$i,
                            'status_id'         => 1,
                            'created_by'        => $adminUser->id,
                            'created_at'        => $now->toDateTimeString(),
                            'updated_by'        => $adminUser->id,
                            'updated_at'        => $now->toDateTimeString(),
                        ]);
                        $manpowerUsed++;
                    }
                    $idx++;
                }
            }

            $project->total_manpower_used = $manpowerUsed;
            $project->save();

            Session::flash('success', 'Sukses menugaskan employee ke project!');
            return redirect()->route('admin.project.employee.show', ['project_id' => $project_id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/project/ProjectEmployeeController - store error EX: '. $ex);
            return back()
                ->withErrors("Something went wrong! Please contact administrator!")
                ->withInput($request->all());
        }
    }

    public function edit(int $project_id)
    {
        try{
            $project = Project::find($project_id);
            if(empty($project)){
                return redirect()->back();
            }

            $manpowerLeft = $project->total_manpower - $project->total_manpower_used;

            $employeeRoleAssigned = collect();

            $employeeRoles = EmployeeRole::where('id', '<', 9)->get();
            foreach($employeeRoles as $employeeRole){
                $assignedEmployees = ProjectEmployee::where('project_id', $project_id)
                    ->where('employee_roles_id', $employeeRole->id)
                    ->count();
                $employeeRoleAssigned->push($assignedEmployees);
            }

            //dd($includeIds);

            $data = [
                'project'                   => $project,
                'employeeRoleAssigned'  => $employeeRoleAssigned,
                'employeeRoles'         => $employeeRoles,
                'manpowerLeft'              => $manpowerLeft,
            ];

            return view('admin.project.employee.edit2')->with($data);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::error('Admin/information/ProjectEmployeeController - edit error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    /*
     * =================================================================================================================
     * old function
     * =================================================================================================================
     * */

//    public function show(int $project_id)
//    {
//        $project = Project::find($project_id);
//        if(empty($project)){
//            return redirect()->back();
//        }
//
//        $manpowerLeft = $project->total_manpower - $project->total_manpower_used;
//
//        $upperEmployees = ProjectEmployee::with(['employee','employee_role'])
//            ->where('project_id', $project_id)
//            ->whereIn('employee_roles_id', [2,3,4])
//            ->get();
//
//        $cleanerEmployees = ProjectEmployee::with('employee')
//            ->where('project_id', $project_id)
//            ->where('employee_roles_id', 1)
//            ->get();
//
//        $isCreate = false;
//        if($upperEmployees->count() === 0 && $cleanerEmployees->count() === 0){
//            $isCreate = true;
//        }
//
//        $data = [
//            'project'               => $project,
//            'manpowerLeft'          => $manpowerLeft,
//            'isCreate'              => $isCreate,
//            'upperEmployees'        => $upperEmployees,
//            'cleanerEmployees'      => $cleanerEmployees
//        ];
//
//        return view('admin.project.employee.show')->with($data);
//    }

//    public function create(int $project_id){
//        try{
//            $project = Project::find($project_id);
//            if(empty($project)){
//                return redirect()->back();
//            }
//
//            $manpower = $project->total_manpower - 2;
//
//            $data = [
//                'project'       => $project,
//                'manpower'      => $manpower
//            ];
//
//            return view('admin.project.employee.create')->with($data);
//        }
//        catch (\Exception $ex){
//            Log::error('Admin/information/ProjectEmployeeController - create error EX: '. $ex);
//            return "Something went wrong! Please contact administrator!";
//        }
//    }

//    public function store(Request $request, int $project_id)
//    {
//        try{
//            // Validate input
//            $upperEmployeeIds = $request->input('upper_employee_ids');
//            $cleanerEmployeeIds = $request->input('cleaner_employee_ids');
//
//            if(empty($upperEmployeeIds) && empty($cleanerEmployeeIds)){
//                return back()->withErrors("INVALID INPUT!")->withInput($request->all());
//            }
//
//            $valid = true;
//            if(!empty($upperEmployeeIds)){
//                foreach ($upperEmployeeIds as $upperEmployeeId){
//                    if(empty($upperEmployeeId)) $valid = false;
//                }
//            }
//
//            if(!empty($cleanerEmployeeIds)){
//                foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
//                    if(empty($cleanerEmployeeId)) $valid = false;
//                }
//            }
//
//            if(!$valid){
//                return back()->withErrors("INVALID INPUT!")->withInput($request->all());
//            }
//
//            $adminUser = Auth::guard('admin')->user();
//            $now = Carbon::now('Asia/Jakarta');
//
//            $manpowerUsed = 0;
//            if(!empty($upperEmployeeIds)){
//                foreach ($upperEmployeeIds as $upperEmployeeId){
//                    $emp = Employee::find($upperEmployeeId);
//                    if(!empty($emp)){
//                        $valueArr = explode('#', $upperEmployeeId);
//                        ProjectEmployee::create([
//                            'project_id'        => $project_id,
//                            'employee_id'       => $valueArr[0],
//                            'employee_roles_id' => $emp->employee_role_id,
//                            'status_id'         => 1,
//                            'created_by'        => $adminUser->id,
//                            'created_at'        => $now->toDateTimeString(),
//                            'updated_by'        => $adminUser->id,
//                            'updated_at'        => $now->toDateTimeString(),
//                        ]);
//                    }
//                    $manpowerUsed++;
//                }
//            }
//
//            if(!empty($cleanerEmployeeIds)){
//                foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
//                    $emp = Employee::find($cleanerEmployeeId);
//                    if(!empty($emp)){
//                        ProjectEmployee::create([
//                            'project_id'        => $project_id,
//                            'employee_id'       => $cleanerEmployeeId,
//                            'employee_roles_id' => $emp->employee_role_id,
//                            'status_id'         => 1,
//                            'created_by'        => $adminUser->id,
//                            'created_at'        => $now->toDateTimeString(),
//                            'updated_by'        => $adminUser->id,
//                            'updated_at'        => $now->toDateTimeString(),
//                        ]);
//                    }
//                    $manpowerUsed++;
//                }
//            }
//
//            $project = Project::find($project_id);
//            $project->total_manpower_used = $manpowerUsed;
//            $project->save();
//
//            Session::flash('success', 'Sukses menugaskan employee ke project!');
//            return redirect()->route('admin.project.employee.show', ['project_id' => $project_id]);
//        }
//        catch (\Exception $ex){
//            Log::error('Admin/project/ProjectEmployeeController - store error EX: '. $ex);
//            return "Something went wrong! Please contact administrator!";
//        }
//    }
//
//    public function edit(int $project_id)
//    {
//        try{
//            $project = Project::find($project_id);
//            if(empty($project)){
//                return redirect()->back();
//            }
//
//            $manpowerLeft = $project->total_manpower - $project->total_manpower_used;
//
//            $upperEmployees = ProjectEmployee::with(['employee','employee_role'])
//                ->where('project_id', $project_id)
//                ->whereIn('employee_roles_id', [2,3,4])
//                ->get();
//
//            if($upperEmployees->count() === 0){
//                $manpowerLeft--;
//            }
//
//            $includeIds = [];
//            $collectUpperEmployees = collect();
//            foreach ($upperEmployees as $upperEmployee){
//                array_push($includeIds, $upperEmployee->employee_id);
//                // Schedule check here
//
//                $collectUpperEmployee = collect([
//                    'id'                    => $upperEmployee->id,
//                    'employee_id'           => $upperEmployee->employee_id,
//                    'employee_code'         => $upperEmployee->employee->code,
//                    'employee_name'         => $upperEmployee->employee->first_name. ' '. $upperEmployee->employee->last_name,
//                    'employee_role_id'      => $upperEmployee->employee_roles_id,
//                    'employee_role_name'    => $upperEmployee->employee_role->name,
//                    'is_created_schedule'   => false
//                ]);
//
//                $collectUpperEmployees->push($collectUpperEmployee);
//            }
//
//            $cleanerEmployees = ProjectEmployee::with('employee')
//                ->where('project_id', $project_id)
//                ->where('employee_roles_id', 1)
//                ->get();
//
//            if($cleanerEmployees->count() === 0){
//                $manpowerLeft--;
//            }
//
//            $collectCleanerEmployees = collect();
//            foreach ($cleanerEmployees as $cleanerEmployee){
//                array_push($includeIds, $cleanerEmployee->employee_id);
//                // Schedule check here
//
//                $collectCleanerEmployee = collect([
//                    'id'                    => $cleanerEmployee->id,
//                    'employee_id'           => $cleanerEmployee->employee_id,
//                    'employee_code'         => $cleanerEmployee->employee->code,
//                    'employee_name'         => $cleanerEmployee->employee->first_name. ' '. $cleanerEmployee->employee->last_name,
//                    'employee_role_id'      => $cleanerEmployee->employee_roles_id,
//                    'employee_role_name'    => $cleanerEmployee->employee_role->name,
//                    'is_created_schedule'   => false
//                ]);
//
//                $collectCleanerEmployees->push($collectCleanerEmployee);
//            }
//
//            //dd($includeIds);
//
//            $data = [
//                'project'                   => $project,
//                'manpowerLeft'              => $manpowerLeft,
//                'upperEmployees'            => $upperEmployees,
//                'collectUpperEmployees'     => $collectUpperEmployees,
//                'cleanerEmployees'          => $cleanerEmployees,
//                'collectCleanerEmployees'   => $collectCleanerEmployees,
//                'includeIds'                => json_encode($includeIds)
//            ];
//
//            return view('admin.project.employee.edit')->with($data);
//        }
//        catch (\Exception $ex){
//            Log::error('Admin/information/ProjectEmployeeController - edit error EX: '. $ex);
//            return "Something went wrong! Please contact administrator!";
//        }
//    }

    public function update(Request $request, int $project_id){
        try{
            // Validate input
            $upperEmployeeIds = $request->input('upper_employee_ids');
            $cleanerEmployeeIds = $request->input('cleaner_employee_ids');

            if(empty($upperEmployeeIds) && empty($cleanerEmployeeIds)){
                return back()->withErrors("INVALID INPUT!")->withInput($request->all());
            }

            $valid = true;
            if(!empty($upperEmployeeIds)){
                foreach ($upperEmployeeIds as $upperEmployeeId){
                    if(empty($upperEmployeeId)) $valid = false;
                }
            }

            if(!empty($cleanerEmployeeIds)){
                foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
                    if(empty($cleanerEmployeeId)) $valid = false;
                }
            }

            if(!$valid){
                return back()->withErrors("INVALID INPUT!")->withInput($request->all());
            }

            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $projectEmployees = ProjectEmployee::where('project_id', $project_id)
                ->get();

            // Unassign employee from project
            foreach ($projectEmployees as $projectEmployee){
                $isFound = false;

                if(!empty($upperEmployeeIds)){
                    foreach ($upperEmployeeIds as $upperEmployeeId){
                        $valueArr = explode('#', $upperEmployeeId);
                        $empId = intval($valueArr[0]);

                        if($projectEmployee->employee_id === $empId){
                            $isFound = true;
                        }
                    }
                }

                if(!empty($cleanerEmployeeIds)){
                    foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
                        $empId = intval($cleanerEmployeeId);

                        if($projectEmployee->employee_id === $empId){
                            $isFound = true;
                        }
                    }
                }

                if(!$isFound){
                    $projectEmployee->delete();
                }
            }

            $manpowerUsed = 0;
            if(!empty($upperEmployeeIds)){
                foreach ($upperEmployeeIds as $upperEmployeeId){
                    $emp = Employee::find($upperEmployeeId);
                    if(!empty($emp)){
                        if(!DB::table('project_employees')
                            ->where('project_id', $project_id)
                            ->where('employee_id', $emp->id)
                            ->exists()){
                            $valueArr = explode('#', $upperEmployeeId);
                            ProjectEmployee::create([
                                'project_id'        => $project_id,
                                'employee_id'       => $valueArr[0],
                                'employee_roles_id' => $emp->employee_role_id,
                                'status_id'         => 1,
                                'created_by'        => $adminUser->id,
                                'created_at'        => $now->toDateTimeString(),
                                'updated_by'        => $adminUser->id,
                                'updated_at'        => $now->toDateTimeString(),
                            ]);
                        }
                    }
                    $manpowerUsed++;
                }
            }

            if(!empty($cleanerEmployeeIds)){
                foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
                    $emp = Employee::find($cleanerEmployeeId);
                    if(!empty($emp)){
                        if(!DB::table('project_employees')
                            ->where('project_id', $project_id)
                            ->where('employee_id', $emp->id)
                            ->exists()){
                            ProjectEmployee::create([
                                'project_id'        => $project_id,
                                'employee_id'       => $cleanerEmployeeId,
                                'employee_roles_id' => $emp->employee_role_id,
                                'status_id'         => 1,
                                'created_by'        => $adminUser->id,
                                'created_at'        => $now->toDateTimeString(),
                                'updated_by'        => $adminUser->id,
                                'updated_at'        => $now->toDateTimeString(),
                            ]);
                        }
                    }
                    $manpowerUsed++;
                }
            }

            $project = Project::find($project_id);
            $project->total_manpower_used = $manpowerUsed;
            $project->save();

            Session::flash('success', 'Sukses mengubah penugasan employee ke project!');
            return redirect()->route('admin.project.employee.show', ['project_id' => $project_id]);

        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectObjectController - update error EX: '. $ex);
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
            Log::error('Admin/CustomerController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

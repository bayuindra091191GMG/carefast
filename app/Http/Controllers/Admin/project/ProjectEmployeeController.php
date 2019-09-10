<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProjectEmployeeController extends Controller
{
    public function show(int $id)
    {
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }

        $upperEmployees = ProjectEmployee::with(['employee','employee_role'])
            ->where('project_id', $id)
            ->whereIn('employee_roles_id', [2,3,4])
            ->get();

        $cleanerEmployees = ProjectEmployee::with('employee')
            ->where('project_id', $id)
            ->where('employee_roles_id', 1)
            ->get();

        $data = [
            'project'               => $project,
            'upperEmployees'        => $upperEmployees,
            'cleanerEmployees'      => $cleanerEmployees
        ];

        return view('admin.project.employee.show')->with($data);
    }

    public function create(int $id){
        try{
            $project = Project::find($id);
            $manpower = $project->total_manpower - 2;

            $data = [
                'project'       => $project,
                'manpower'      => $manpower
            ];

            return view('admin.project.employee.create')->with($data);
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

            if(!empty($upperEmployeeIds)){
                foreach ($upperEmployeeIds as $upperEmployeeId){
                    $emp = Employee::find($upperEmployeeId);
                    if(!empty($emp)){
                        ProjectEmployee::create([
                            'project_id'        => $project_id,
                            'employee_id'       => $upperEmployeeId,
                            'employee_role_id'  => $emp->employee_role_id,
                            'status_id'         => 1,
                            'created_by'        => $adminUser->id,
                            'created_at'        => $now->toDateTimeString(),
                            'updated_by'        => $adminUser->id,
                            'updated_at'        => $now->toDateTimeString(),
                        ]);
                    }
                }
            }

            if(!empty($cleanerEmployeeIds)){
                foreach ($cleanerEmployeeIds as $cleanerEmployeeId){
                    $emp = Employee::find($cleanerEmployeeId);
                    if(!empty($emp)){
                        ProjectEmployee::create([
                            'project_id'        => $project_id,
                            'employee_id'       => $cleanerEmployeeId,
                            'employee_role_id'  => $emp->employee_role_id,
                            'status_id'         => 1,
                            'created_by'        => $adminUser->id,
                            'created_at'        => $now->toDateTimeString(),
                            'updated_by'        => $adminUser->id,
                            'updated_at'        => $now->toDateTimeString(),
                        ]);
                    }
                }
            }

            Session::flash('success', 'Sukses menugaskan employee ke project!');
            return redirect()->route('admin.project.employee.show');
        }
        catch (\Exception $ex){
            Log::error('Admin/project/ProjectEmployeeController - store error EX: '. $ex);
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
            'information'          => $project,
        ];
        return view('admin.information.edit')->with($data);
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

            Session::flash('success', 'Sukses mengubah data information!');
            return redirect()->route('admin.information.show',['id' => $project->id]);

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

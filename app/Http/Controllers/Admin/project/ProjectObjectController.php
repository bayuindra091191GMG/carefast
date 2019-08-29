<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Project;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
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

class ProjectObjectController extends Controller
{
    public function show(int $id)
    {
        $project = Project::find($id);

        if(empty($project)){
            return redirect()->back();
        }
        $projectObject = $project->project_objects;

        $data = [
            'project'           => $project,
            'projectObject'     => $projectObject,
        ];
        return view('admin.project.object.show')->with($data);
    }

    public function create(){
        try{
            return view('admin.project.object.create');
        }
        catch (\Exception $ex){
            Log::error('Admin/object/ProjectObjectController - create error EX: '. $ex);
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

            Session::flash('success', 'Sukses membuat project object baru!');
            return redirect()->route('admin.project.object.index');
        }
        catch (\Exception $ex){
            Log::error('Admin/object/ProjectObjectController - store error EX: '. $ex);
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
        return view('admin.project.object.edit')->with($data);
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

            Session::flash('success', 'Sukses mengubah data project object!');
            return redirect()->route('admin.project.object.show',['id' => $project->id]);

        }
        catch (\Exception $ex){
            Log::error('Admin/object/ProjectObjectController - update error EX: '. $ex);
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

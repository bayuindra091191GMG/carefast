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

class ProjectController extends Controller
{
    public function index(){
        try{
            return view('admin.project.information.index');
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $customers = Project::all();
        return DataTables::of($customers)
            ->setTransformer(new ProjectTransformer())
            ->make(true);
    }

    public function show(int $id)
    {
        $project = Project::find($id);
        $start_date = !empty($project->start_date) ? Carbon::parse($project->start_date)->format("d M Y") : "-";
        $finish_date = !empty($project->finish_date) ? Carbon::parse($project->finish_date)->format("d M Y") : "-";
        $customerIds = explode('#', $project->customer_id);
        $customerList = Customer::whereIn('id', $customerIds)->get();

        if(empty($project)){
            return redirect()->back();
        }

        $data = [
            'project'          => $project,
            'start_date'          => $start_date,
            'finish_date'          => $finish_date,
            'customerList'          => $customerList,
        ];
        return view('admin.project.information.show')->with($data);
    }

    public function create(){
        try{
            return view('admin.project.information.create');
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectController - create error EX: '. $ex);
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
                'code'         => 'required',
                'start_date'         => 'required',
                'finish_date'         => 'required',
                'customer'          => 'required',
                'latitude'          => 'required',
                'longitude'         => 'required',
                'total_manday'      => 'required',
                'total_mp_onduty'   => 'required',
                'total_mp_off'      => 'required',
                'total_manpower'    => 'required',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            //Create Project
            $start_date = Carbon::createFromFormat('d M Y', $request->input('start_date'), 'Asia/Jakarta');
            $finish_date = Carbon::createFromFormat('d M Y', $request->input('finish_date'), 'Asia/Jakarta');

            $customersString = "";
            foreach ($request->input('customer') as $customer){
                $customersString .= $customer."#";
            }

            $now = Carbon::now('Asia/Jakarta');
            $user = Auth::guard('admin')->user();
            $project = Project::create([
                'name'              => strtoupper($request->input('name')),
                'code'             => strtoupper($request->input('code')),
                'phone'             => $request->input('phone'),
                'customer_id'       => $customersString,
                'latitude'          => $request->input('latitude'),
                'longitude'         => $request->input('longitude'),
                'address'           => $request->input('address'),
                'start_date'        => $start_date,
                'finish_date'       => $finish_date,
                'description'       => $request->input('description'),
                'total_manday'      => $request->input('total_manday'),
                'total_mp_onduty'   => $request->input('total_mp_onduty'),
                'total_mp_off'      => $request->input('total_mp_off'),
                'total_manpower'    => $request->input('total_manpower'),
                'status_id'         => $request->input('status'),
                'created_at'        => $now->toDateTimeString(),
                'created_by'        => $user->id,
                'updated_at'        => $now->toDateTimeString(),
                'updated_by'        => $user->id,
            ]);

            if($request->hasFile('photo')){
                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $project->id.'_project_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/projects/'. $filename), 75);
                $project->image_path = $filename;
                $project->save();
            }
            Session::flash('success', 'Sukses membuat information baru!');
            return redirect()->route('admin.project.information.show', ['id'=>$project->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectController - store error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function edit(int $id)
    {
        $project = Project::find($id);
        $start_date = !empty($project->start_date) ? Carbon::parse($project->start_date)->format("d M Y") : "-";
        $finish_date = !empty($project->finish_date) ? Carbon::parse($project->finish_date)->format("d M Y") : "-";
        $customerIds = explode('#', $project->customer_id);
        $customerList = Customer::whereIn('id', $customerIds)->get();

        if(empty($project)){
            return redirect()->back();
        }

        $data = [
            'project'          => $project,
            'customerList'          => $customerList,
            'start_date'          => $start_date,
            'finish_date'          => $finish_date,
        ];
//        dd($data);
        return view('admin.project.information.edit')->with($data);
    }

    public function update(Request $request, int $id){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'address'           => 'required',
                'phone'             => 'required',
                'code'             => 'required',
                'customer'          => 'required',
                'latitude'          => 'required',
                'longitude'         => 'required',
                'total_manday'      => 'required',
                'total_mp_onduty'   => 'required',
                'total_mp_off'      => 'required',
                'total_manpower'    => 'required',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $project = Project::find($id);
            if(empty($project)){
                return redirect()->back();
            }
            $adminUser = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta');

            $start_date = Carbon::createFromFormat('d M Y', $request->input('start_date'), 'Asia/Jakarta');
            $finish_date = Carbon::createFromFormat('d M Y', $request->input('finish_date'), 'Asia/Jakarta');

            $customersString = "";
            foreach ($request->input('customer') as $customer){
                $customersString .= $customer."#";
            }

            $project->name = strtoupper($request->input('name'));
            $project->code = strtoupper($request->input('code'));
            $project->phone = $request->input('phone') ?? '';
            $project->customer_id = $customersString;
            $project->latitude = $request->input('latitude');
            $project->longitude =$request->input('longitude');
            $project->address = $request->input('address');
            $project->start_date = $start_date;
            $project->finish_date = $finish_date;
            $project->description = $request->input('description');
            $project->total_manday = $request->input('total_manday');
            $project->total_mp_onduty = $request->input('total_mp_onduty');
            $project->total_mp_off = $request->input('total_mp_off');
            $project->total_manpower = $request->input('total_manpower');
            $project->status_id = $request->input('status');
            $project->updated_by = $adminUser->id;
            $project->updated_at = $now->toDateTimeString();
            $project->save();

            if($request->hasFile('photo')){
                // Delete old image
                if(!empty($project->image_path)){
                    $deletedPath = public_path('storage/projects/'. $project->image_path);
                    if(file_exists($deletedPath)) unlink($deletedPath);
                }

                $img = Image::make($request->file('photo'));
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);

                $filename = $project->id.'_photo_'. $now->format('Ymdhms'). '.'. $ext[1];

                $img->save(public_path('storage/projects/'. $filename), 75);
                $project->image_path = $filename;
            }

            Session::flash('success', 'Sukses mengubah data information!');
            return redirect()->route('admin.project.information.show',['id' => $project->id]);
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectController - update error EX: '. $ex);
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

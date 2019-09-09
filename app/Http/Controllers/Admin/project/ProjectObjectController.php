<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectObject;
use App\Models\Sub1Unit;
use App\Models\Sub2Unit;
use App\Models\Unit;
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
            'projectObjects'     => $projectObject,
        ];
//        dd($data);
        return view('admin.project.object.show')->with($data);
    }

    public function create(int $id){
        try{
            $project = Project::find($id);

            if(empty($project)){
                return redirect()->back();
            }

            $data = [
                'project'           => $project,
            ];
            return view('admin.project.object.create')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/object/ProjectObjectController - create error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function store(Request $request)
    {
        try{
            $places = $request->input('places');
            $placeNews = $request->input('place_new');
            $units = $request->input('units');
            $unit_news = $request->input('unit_new');
            $sub_1_units = $request->input('sub_1_units');
            $sub_1_unit_news = $request->input('sub_1_unit_new');
            $sub_2_units = $request->input('sub_2_units');
            $sub_2_unit_news = $request->input('sub_2_unit_new');
//            $adf = in_array("-1", $places);
//            $asdf2 = in_array("-1", $sub_1_units);
//            dd($adf, $asdf2);
//            dd($places, $placeNews, $units, $unit_news, $sub_1_units, $sub_1_unit_news, $sub_2_units, $sub_2_unit_news);

            $user = Auth::guard('admin')->user();
            $project = Project::find($request->input('project_id'));

            $i = 0;
            foreach($places as $place){
                $selectedPlace = "";
                $selectedPlaceName = "";
                $selectedUnit = "";
                $selectedUnitName = "";
                $selectedSubUnit1 = "";
                $selectedSubUnit1Name = "";
                $selectedSubUnit2 = "";
                $selectedSubUnit2Name = "";

                //check if any new place, then create new place, else use selected place
                if(empty($placeNews[$i])){
                    $selectedPlace = $place;
                    $placeDB = Place::find($place);
                    $selectedPlaceName = $placeDB->name;
                }
                else{
                    $placeDBNew = Place::create([
                        'name'              => strtoupper($placeNews[$i]),
                        'description'       => strtoupper($placeNews[$i]),
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => $user->id,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => $user->id,
                    ]);
                    $selectedPlace = $placeDBNew->id;
                    $selectedPlaceName = $placeDBNew->name;
                }

                //check if any new unit, then create new unit, else use selected unit
                if(empty($unit_news[$i])){
                    $selectedUnit = $units[$i];
                    $placeDB = Unit::find($units[$i]);
                    $selectedUnitName = $placeDB->name;
                }
                else{
                    $unitDBNew = Unit::create([
                        'name'              => strtoupper($unit_news[$i]),
                        'description'       => strtoupper($unit_news[$i]),
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => $user->id,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => $user->id,
                    ]);
                    $selectedUnit = $unitDBNew->id;
                    $selectedUnitName = $unitDBNew->name;
                }

                //check if any new sub 1 unit, then create new sub 1 unit, else use selected sub 1 unit
                if(empty($sub_1_unit_news[$i])){
                    $selectedSubUnit1 = $sub_1_units[$i];
                    $placeDB = Sub1Unit::find($sub_1_units[$i]);
                    $selectedSubUnit1Name = $placeDB->name;
                }
                else{
                    $sub1unitDBNew = Unit::create([
                        'name'              => strtoupper($sub_1_unit_news[$i]),
                        'description'       => strtoupper($sub_1_unit_news[$i]),
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => $user->id,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => $user->id,
                    ]);
                    $selectedSubUnit1 = $sub1unitDBNew->id;
                    $selectedSubUnit1Name = $sub1unitDBNew->name;
                }

                //check if any new sub 2 unit, then create new sub 2 unit, else use selected sub 2 unit
                if(empty($placeNews[$i])){
                    $selectedSubUnit2 = $sub_2_units[$i];
                    $placeDB = Sub2Unit::find($sub_2_units[$i]);
                    $selectedSubUnit2Name = $placeDB->name;
                }
                else{
                    $sub2unitDBNew = Unit::create([
                        'name'              => strtoupper($sub_2_unit_news[$i]),
                        'description'       => strtoupper($sub_2_unit_news[$i]),
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => $user->id,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => $user->id,
                    ]);
                    $selectedSubUnit2 = $sub2unitDBNew->id;
                    $selectedSubUnit2Name = $sub2unitDBNew->name;
                }

                //Create Project Object
                $projectObject = ProjectObject::create([
                    'project_id'        => $project->id,
                    'place_id'          => $selectedPlace,
                    'unit_id'           => $selectedUnit,
                    'sub1_unit_id'      => $selectedSubUnit1,
                    'sub2_unit_id'      => $selectedSubUnit2,
                    'place_name'        => $request->input('description'),
                    'unit_name'         => $selectedUnitName,
                    'sub1_unit_name'    => $selectedSubUnit1Name,
                    'sub2_unit_name'    => $selectedSubUnit2Name,
                    'status_id'         => 1,
                    'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'created_by'        => $user->id,
                    'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                    'updated_by'        => $user->id,
                ]);
                $i++;
            }

            Session::flash('success', 'Sukses membuat project object baru!');
            return redirect()->route('admin.project.object.show',['id' => $project->id]);
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
        $projectObject = $project->project_objects;
        $data = [
            'project'           => $project,
            'projectObjects'     => $projectObject,
            'projectObjectCount'     => $projectObject->count(),
        ];
//        dd($data);
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

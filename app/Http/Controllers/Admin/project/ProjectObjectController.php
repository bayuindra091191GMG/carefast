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
        $projectObject = $project->project_objects->sortBy('place_id');

        $isCreate = false;
        if($projectObject->count() === 0){
            $isCreate = true;
        }

        $data = [
            'project'           => $project,
            'projectObjects'    => $projectObject,
            'isCreate'          => $isCreate,
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
//            dd($request);
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
                    $sub1unitDBNew = Sub1Unit::create([
                        'unit_id'           => $selectedUnit,
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
                    $sub2unitDBNew = Sub2Unit::create([
                        'sub_1_unit_id'           => $selectedSubUnit1,
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
//                dd($selectedPlace,$selectedUnit, $selectedSubUnit1, $selectedSubUnit2, $selectedPlaceName, $selectedUnitName, $selectedSubUnit1Name, $selectedSubUnit2Name);
                //Create Project Object
                $projectObject = ProjectObject::create([
                    'project_id'        => $project->id,
                    'place_id'          => $selectedPlace,
                    'unit_id'           => $selectedUnit,
                    'sub1_unit_id'      => $selectedSubUnit1,
                    'sub2_unit_id'      => $selectedSubUnit2,
                    'place_name'        => $selectedPlaceName,
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
        $projectObject = $project->project_objects->sortBy('place_id');
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
            $projectObjectIds = $request->input('id');
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
            $project = Project::find($request->input('project-id'));

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
                    $sub1unitDBNew = Sub1Unit::create([
                        'unit_id'           => $selectedUnit,
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
                    $sub2unitDBNew = Sub2Unit::create([
                        'sub_1_unit_id'           => $selectedSubUnit1,
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

                foreach ($project->project_objects as $projectObject){
                    $isFound = false;
                    if (in_array($projectObject->id, $projectObjectIds)) {
                        $isFound = true;
                    }
                    if(!$isFound){
                        $projectObject->delete();
                    }
                }

//                dd($selectedPlace, $selectedUnit, $selectedSubUnit1, $selectedSubUnit2,$selectedPlaceName,$selectedUnitName,$selectedSubUnit1Name,$selectedSubUnit2Name);
                if($projectObjectIds[$i] != '-1'){
                    $projectObjectDB = ProjectObject::find($projectObjectIds[$i]);
                    // kalau salah satu object ada yang berbeda update dengan data terbaru
                    if(
                    !($projectObjectDB->place_id == $selectedPlace &&
                        $projectObjectDB->unit_id == $selectedUnit &&
                        $projectObjectDB->sub1_unit_id == $selectedSubUnit1 &&
                        $projectObjectDB->sub2_unit_id == $selectedSubUnit2)
                    ){
                        $projectObjectDB->place_id = $selectedPlace;
                        $projectObjectDB->unit_id = $selectedUnit;
                        $projectObjectDB->sub1_unit_id = $selectedSubUnit1;
                        $projectObjectDB->sub2_unit_id = $selectedSubUnit2;
                        $projectObjectDB->place_name = $selectedPlaceName;
                        $projectObjectDB->unit_name = $selectedUnitName;
                        $projectObjectDB->sub1_unit_name = $selectedSubUnit1Name;
                        $projectObjectDB->sub2_unit_name = $selectedSubUnit2Name;
                        $projectObjectDB->save();
                    }
                }
                else{
                    //Create Project Object
                    if($selectedPlace != '-1'){
                        $projectObject = ProjectObject::create([
                            'project_id'        => $project->id,
                            'place_id'          => $selectedPlace,
                            'unit_id'           => $selectedUnit,
                            'sub1_unit_id'      => $selectedSubUnit1,
                            'sub2_unit_id'      => $selectedSubUnit2,
                            'place_name'        => $selectedPlaceName,
                            'unit_name'         => $selectedUnitName,
                            'sub1_unit_name'    => $selectedSubUnit1Name,
                            'sub2_unit_name'    => $selectedSubUnit2Name,
                            'status_id'         => 1,
                            'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'created_by'        => $user->id,
                            'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'updated_by'        => $user->id,
                        ]);
                    }
                }
                $i++;
            }

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
    public function getProjectObjects(Request $request){
        $term = trim($request->q);
        $project_id = $request->project_id;
        $term = strtoupper($term);
//        Log::info('project_id = '.$project_id.", term = ".$term);

        $projectObjects = ProjectObject::where(function ($q) use ($term) {
            $q->where('place_name', 'LIKE', '%' . $term . '%')
                ->orWhere('unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub1_unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub2_unit_name', 'LIKE', '%' . $term . '%');
        })
            ->get();
        $projectObjectSelected = $projectObjects->where('project_id', $project_id);

        $formatted_tags = [];

        foreach ($projectObjectSelected as $projectObject) {
            $objectName = "";
            $placeName = $projectObject->place_name != "-" ? $projectObject->place_name." " : "";
            $unitName = $projectObject->unit_name != "-" ? $projectObject->unit_name." " : "";
            $sub1unitName = $projectObject->sub1_unit_name != "-" ? $projectObject->sub1_unit_name." " : "";
            $sub2unitName = $projectObject->sub2_unit_name != "-" ? $projectObject->sub2_unit_name." " : "";
            $objectName = $objectName.$placeName;
            $objectName = $objectName.$unitName;
            $objectName = $objectName.$sub1unitName;
            $objectName = $objectName.$sub2unitName;

            $formatted_tags[] = ['id' => $projectObject->id, 'text' => $objectName];
        }

        return \Response::json($formatted_tags);
    }
    public function getProjectObjects2(Request $request){
        $term = trim($request->q);
        $project_id = $request->project_id;
        $place_id = $request->place_id;
        $term = strtoupper($term);
//        Log::info('project_id = '.$project_id.", term = ".$term);

        $projectObjects = ProjectObject::where(function ($q) use ($term) {
            $q->where('place_name', 'LIKE', '%' . $term . '%')
                ->orWhere('unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub1_unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub2_unit_name', 'LIKE', '%' . $term . '%');
        })
            ->get();
        $projectObjectSelected = $projectObjects->where('project_id', $project_id)->where('place_id', $place_id);

        $formatted_tags = [];

        foreach ($projectObjectSelected as $projectObject) {
            $objectName = "";
            $unitName = $projectObject->unit_name != "-" ? $projectObject->unit_name." " : "";
            $sub1unitName = $projectObject->sub1_unit_name != "-" ? $projectObject->sub1_unit_name." " : "";
            $sub2unitName = $projectObject->sub2_unit_name != "-" ? $projectObject->sub2_unit_name." " : "";
            $objectName = $unitName;
            $objectName = $objectName." ".$sub1unitName;
            $objectName = $objectName." ".$sub2unitName;

            $formatted_tags[] = ['id' => $projectObject->id, 'text' => $objectName];
        }

        return \Response::json($formatted_tags);
    }
    public function getProjectObjectActivities(Request $request){
        $term = trim($request->q);
        $project_id = $request->project_id;
        $place_id = $request->place_id;
        $term = strtoupper($term);
//        Log::info('project_id = '.$project_id.", term = ".$term);

        $projectObjects = ProjectObject::where(function ($q) use ($term) {
            $q->where('place_name', 'LIKE', '%' . $term . '%')
                ->orWhere('unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub1_unit_name', 'LIKE', '%' . $term . '%')
                ->orWhere('sub2_unit_name', 'LIKE', '%' . $term . '%');
        })
            ->get();
        $projectObjectSelected = $projectObjects->where('project_id', $project_id)->where('place_id', $place_id);

        $formatted_tags = [];

        foreach ($projectObjectSelected as $projectObject) {
            $objectName = "";
            if($projectObject->unit_name != "-"){
                $unitName = $projectObject->unit_name;
                $objectName = $unitName;
                $formatted_tags[] = ['id' => $objectName, 'text' => $objectName];
            }
            if($projectObject->sub1_unit_name != "-"){
                $sub1unitName = $projectObject->sub1_unit_name;
                $objectName = $objectName." - ".$sub1unitName;
                $formatted_tags[] = ['id' => $objectName, 'text' => $objectName];
            }
            if($projectObject->sub2_unit_name != "-"){
                $sub2unitName = $projectObject->sub2_unit_name;
                $objectName = $objectName." - ".$sub2unitName;
                $formatted_tags[] = ['id' => $objectName, 'text' => $objectName];
            }

        }

        return \Response::json($formatted_tags);
    }
}

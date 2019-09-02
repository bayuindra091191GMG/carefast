<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Sub2Unit;
use App\Transformer\Sub2UnitTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class Sub2UnitController extends Controller
{
    public function getObjects(Request $request){
        $term = trim($request->q);
        $sub2_units = Sub2Unit::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%')
                ->where('email', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($sub2_units as $sub2unit) {
            $formatted_tags[] = ['id' => $sub2unit->id, 'text' => $sub2unit->name . ' - ' . $sub2unit->email];
        }

        return \Response::json($formatted_tags);
    }

    public function getSub2UnitDropdowns(Request $request){
        $term = trim($request->q);
        $sub2_units = Sub2Unit::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%')
                ->where('email', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($sub2_units as $sub2unit) {
            $formatted_tags[] = ['id' => $sub2unit->id, 'text' => $sub2unit->name . ' - ' . $sub2unit->email];
        }

        return \Response::json($formatted_tags);
    }

    public function getSub2Units(Request $request){
        $term = trim($request->q);
        $sub2_units = Sub2Unit::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%')
                ->where('description', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($sub2_units as $sub2unit) {
            $formatted_tags[] = ['id' => $sub2unit->id, 'text' => $sub2unit->name . ' - ' . $sub2unit->description];
        }

        return \Response::json($formatted_tags);
    }

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.Sub2Unit.index');
    }

    public function getIndex(){
        $sub2_units = Sub2Unit::all();
        return DataTables::of($sub2_units)
            ->setTransformer(new Sub2UnitTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.Sub2Unit.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:sub2_units',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            Sub2Unit::create([
                'name'          => $name,
                'description'   => $request->input('description') ?? null,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);

            Session::flash('success', 'Sukses membuat Sub2Unit baru!');
            return redirect()->route('admin.Sub2Unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/Sub2UnitController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id){
        $sub2unit = sub2unit::find($id);
        if(empty($sub2unit)){
            return redirect()->back();
        }

        return view('admin.sub2unit.edit', compact('sub2unit'));
    }

    public function update(Request $request, int $id){
        try{
            $sub2Unit = sub2Unit::find($id);
            //dd($Sub2Unit);
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama Sub2Unit wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $sub2unit->name = $name;
            $sub2unit->description = $request->input('description') ?? null;
            $sub2unit->status_id = $request->input('status');
            $sub2unit->updated_at = $dateNow;
            $sub2unit->updated_by = $user->id;
            $sub2unit->save();

            Session::flash('success', 'Sukses mengubah Sub Unit 2 baru!');
            return redirect()->route('admin.sub2unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/Sub2UnitController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $Sub2Unit = Sub2Unit::find($deletedId);
            $Sub2Unit->delete();

            Session::flash('success', 'Sukses menghapus Sub Unit 2!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/Sub2UnitController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

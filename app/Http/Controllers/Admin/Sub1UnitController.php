<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Sub1Unit;
use App\Models\Unit;
use App\Transformer\Sub1UnitTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class Sub1UnitController extends Controller
{
    public function getSub1UnitDropdowns(Request $request){
        $id = $request->input('id');
        $sub1_units = Sub1Unit::where('unit_id', $id)->get();

        $formatted_tags = [];

        foreach ($sub1_units as $sub1unit) {
            $formatted_tags[] = ['id' => $sub1unit->id, 'text' => $sub1unit->name];
        }

        return \Response::json($formatted_tags);
    }

    public function getSub1Units(Request $request){
        $term = trim($request->q);
        $sub1_units = Sub1Unit::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($sub1_units as $sub1unit) {
            $formatted_tags[] = ['id' => $sub1unit->id, 'text' => $sub1unit->name];
        }

        return \Response::json($formatted_tags);
    }

    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.sub1unit.index');
    }

    public function getIndex(){
        $sub1_units = Sub1Unit::all();
        return DataTables::of($sub1_units)
            ->setTransformer(new Sub1UnitTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.sub1unit.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
//                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            Sub1Unit::create([
                'name'          => $name,
                'unit_id'          => $request->input('unit_id'),
                'description'   => $request->input('description') ?? null,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);

            Session::flash('success', 'Sukses membuat sub1unit baru!');
            return redirect()->route('admin.sub1unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/sub1unitController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id){
        $sub1unit = sub1unit::find($id);
        if(empty($sub1unit)){
            return redirect()->back();
        }
        $unit = Unit::find($sub1unit->unit_id);

        return view('admin.sub1unit.edit', compact('sub1unit', 'unit'));
    }

    public function update(Request $request, int $id){
        try{
            $sub1unit = sub1unit::find($id);
            //dd($sub1unit);
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama Sub Unit 1 wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $sub1unit->name = $name;
            $sub1unit->unit_id = $request->input('unit_id');
            $sub1unit->description = $request->input('description') ?? null;
            $sub1unit->status_id = $request->input('status');
            $sub1unit->updated_at = $dateNow;
            $sub1unit->updated_by = $user->id;
            $sub1unit->save();

            Session::flash('success', 'Sukses mengubah Sub Unit 1 baru!');
            return redirect()->route('admin.sub1unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/Sub1UnitController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $sub1unit = sub1unit::find($deletedId);
            $sub1unit->delete();

            Session::flash('success', 'Sukses menghapus Sub Unit 1!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/sub1unitController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

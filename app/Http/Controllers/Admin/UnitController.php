<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Transformer\UnitTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UnitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.unit.index');
    }

    public function getIndex(){
        $units = Unit::all();
        return DataTables::of($units)
            ->setTransformer(new UnitTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.unit.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:units',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            Unit::create([
                'name'          => $name,
                'description'   => $request->input('description') ?? null,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);

            Session::flash('success', 'Sukses membuat unit baru!');
            return redirect()->route('admin.unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/UnitController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id){
        $unit = Unit::find($id);
        if(empty($unit)){
            return redirect()->back();
        }

        return view('admin.unit.edit', compact('unit'));
    }

    public function update(Request $request, int $id){
        try{
            $unit = Unit::find($id);
            //dd($unit);
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama unit wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $unit->name = $name;
            $unit->description = $request->input('description') ?? null;
            $unit->status_id = $request->input('status');
            $unit->updated_at = $dateNow;
            $unit->updated_by = $user->id;
            $unit->save();

            Session::flash('success', 'Sukses mengubah unit baru!');
            return redirect()->route('admin.unit.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/UnitController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $unit = Unit::find($deletedId);
            $unit->delete();

            Session::flash('success', 'Sukses menghapus unit!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/UnitController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

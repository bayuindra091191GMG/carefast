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
        $Sub2Unit = Sub2Unit::find($id);
        if(empty($Sub2Unit)){
            return redirect()->back();
        }

        return view('admin.Sub2Unit.edit', compact('Sub2Unit'));
    }

    public function update(Request $request, int $id){
        try{
            $Sub2Unit = Sub2Unit::find($id);
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

            $Sub2Unit->name = $name;
            $Sub2Unit->description = $request->input('description') ?? null;
            $Sub2Unit->status_id = $request->input('status');
            $Sub2Unit->updated_at = $dateNow;
            $Sub2Unit->updated_by = $user->id;
            $Sub2Unit->save();

            Session::flash('success', 'Sukses mengubah Sub Unit 2 baru!');
            return redirect()->route('admin.Sub2Unit.index');
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

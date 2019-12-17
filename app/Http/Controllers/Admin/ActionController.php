<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Transformer\ActionTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ActionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.action.index');
    }

    public function getIndex(){
        $actions = Action::all();
        return DataTables::of($actions)
            ->setTransformer(new ActionTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.action.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:actions',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            Action::create([
                'name'          => $name,
                'description'   => $request->input('description') ?? null,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);

            Session::flash('success', 'Sukses membuat action baru!');
            return redirect()->route('admin.action.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/ActionController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id){
        $action = Action::find($id);
        if(empty($action)){
            return redirect()->back();
        }

        return view('admin.action.edit', compact('action'));
    }

    public function update(Request $request, int $id){
        try{
            $action = Action::find($id);
            //dd($action);
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama action wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $action->name = $name;
            $action->description = $request->input('description') ?? null;
            $action->status_id = $request->input('status');
            $action->updated_at = $dateNow;
            $action->updated_by = $user->id;
            $action->save();

            Session::flash('success', 'Sukses mengubah action baru!');
            return redirect()->route('admin.action.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/ActionController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $action = Action::find($deletedId);
            $action->delete();

            Session::flash('success', 'Sukses menghapus action!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/ActionController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
    public function getActions(Request $request){
        $term = trim($request->q);

        $actions = Action::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%')
                ->orWhere('description', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($actions as $action) {
            $formatted_tags[] = ['id' => $action->id."-".$action->name, 'text' => $action->name];
        }

        return \Response::json($formatted_tags);
    }
}

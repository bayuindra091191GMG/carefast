<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Place;
use App\Transformer\PlaceTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class PlaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.place.index');
    }

    public function getIndex(){
        $places = Place::all();
        return DataTables::of($places)
            ->setTransformer(new PlaceTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.place.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:places',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            Place::create([
                'name'          => $name,
                'description'   => $request->input('description') ?? null,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);

            Session::flash('success', 'Sukses membuat place baru!');
            return redirect()->route('admin.place.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/PlaceController - store error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function edit(int $id){
        $place = Place::find($id);
        if(empty($place)){
            return redirect()->back();
        }

        return view('admin.place.edit', compact('place'));
    }

    public function update(Request $request, int $id){
        try{
            $place = Place::find($id);
            //dd($place);
            $validator = Validator::make($request->all(), [
                'name'              => 'required',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama place wajib diisi!',
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = strtoupper($request->input('name'));

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $place->name = $name;
            $place->description = $request->input('description') ?? null;
            $place->status_id = $request->input('status');
            $place->updated_at = $dateNow;
            $place->updated_by = $user->id;
            $place->save();

            Session::flash('success', 'Sukses mengubah place baru!');
            return redirect()->route('admin.place.index');
        }
        catch(\Exception $ex){
            Log::error('Admin/PlaceController - update error EX: '. $ex);
            return redirect()->back()->withErrors('Internal Server Error')->withInput();
        }
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $place = Place::find($deletedId);
            $place->delete();

            Session::flash('success', 'Sukses menghapus place!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/PlaceController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
    public function getPlaces(Request $request){
        $term = trim($request->q);
        $places = Place::where(function ($q) use ($term) {
            $q->where('name', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($places as $place) {
            $formatted_tags[] = ['id' => $place->id, 'text' => $place->name];
        }

        return \Response::json($formatted_tags);
    }
}

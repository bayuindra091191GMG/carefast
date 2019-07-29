<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCategory;
use App\Transformer\UserCategoryTransformer;
use App\Transformer\UserTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UserCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function getIndex(Request $request){
        $users = UserCategory::all();
        return DataTables::of($users)
            ->setTransformer(new UserCategoryTransformer())
            ->make(true);
    }

    public function getCategories(Request $request){
        $term = trim($request->q);
        $roles = UserCategory::where('id', '!=', $request->id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', '%' . $term . '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($roles as $role) {
            $formatted_tags[] = ['id' => $role->id, 'text' => $role->name];
        }

        return \Response::json($formatted_tags);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('admin.user_category.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('admin.user_category.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:100|unique:user_categories'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $category = UserCategory::create([
                'name'              => $request->input('name'),
                'slug'              => '',
                'meta_description'  => $request->input('meta_description'),
                'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString()
            ]);

            Session::flash('success', 'Sukses Membuat Kategori Master Dealer!');
        }
        catch(\Exception $ex){
            Log::error('Admin/UserCategoryController - store error EX: '. $ex);
            Session::flash('error', 'Gagal Mengubah Kategori Master Dealer!');
        }
        return redirect()->route('admin.user_categories.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $category = UserCategory::find($id);
        return view('admin.user_category.edit', compact('category'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $id)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'  => 'required|max:100|unique:user_categories,name,'. $id
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $userCategory = UserCategory::find($id);
            if(empty($userCategory)){
                return redirect()->back()->withErrors('Invalid User Category!', 'default')->withInput($request->all());
            }

            $userCategory->name = $request->input('name');
            $userCategory->meta_description = $request->input('meta_description');
            $userCategory->updated_at = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $userCategory->save();

            Session::flash('success', 'Sukses Mengubah Kategori Master Dealer!');
        }
        catch(\Exception $ex){
            Log::error('Admin/UserCategoryController - update error EX: '. $ex);
            Session::flash('error', 'Gagal Mengubah Kategori Master Dealer!');
        }

        return redirect()->route('admin.user_categories.edit', ['id' => $userCategory->id]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request)
    {
        //
        try {
            //Belum melakukan pengecekan hubungan antar Table
            $userId = $request->input('id');
            $user = User::find($userId);
//            $user->delete();

            Session::flash('success', 'Sukses menghapus MD category ' . $user->email . ' - ' . $user->name);
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/UserCategoryController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

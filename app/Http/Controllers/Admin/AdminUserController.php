<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AdminUserRole;
use App\Models\Employee;
use App\Models\Project;
use App\Transformer\AdminUserTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class AdminUserController extends Controller
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

    public function getAdminUsers(Request $request){
        $term = trim($request->q);
        $adminUsers = AdminUser::where(function ($q) use ($term) {
            $q->where('first_name', 'LIKE', '%' . $term . '%')
            ->where('last_name', 'LIKE', '%' . $term . '%');
        })
            ->get();

        $formatted_tags = [];

        foreach ($adminUsers as $adminUser) {
            $formatted_tags[] = ['id' => $adminUser->id, 'text' => $adminUser->first_name . ' ' . $adminUser->last_name];
        }

        return \Response::json($formatted_tags);
    }

    public function getIndex(Request $request){
        $users = AdminUser::query();
        return DataTables::of($users)
            ->setTransformer(new AdminUserTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.adminuser.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = AdminUserRole::orderBy('name')->get();

        $data = [
            'roles'         => $roles
        ];

        return view('admin.adminuser.create')->with($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'        => 'required|max:100',
            'last_name'         => 'required|max:100',
            'email'             => 'required|regex:/^\S*$/u|unique:admin_users|max:50',
            'role'              => 'required',
            'password'          => 'required'
        ],[
            'email.unique'      => 'ID Login Akses telah terdaftar!',
            'email.regex'       => 'ID Login Akses harus tanpa spasi!'
        ]);

        $validator->sometimes('password', 'min:6|confirmed', function ($input) {
            return $input->password;
        });

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        //Create Admin
        $user = Auth::guard('admin')->user();
//        $assignedWasteBankId = null;
        if($request->filled('is_super_admin')){
            $superAdmin = 1;
        }
        else{
            $superAdmin = 0;

//            if($request->input('waste_bank') != '-1'){
//                $assignedWasteBankId = $request->input('waste_bank');
//            }
        }


        $fmString = "0#";
        if($request->input('role') == 4){
            $fmString = $request->input('fm_id')."#";
        }
        else if($request->input('role') == 5){
            $fmString = "";
            $fmString = $request->input('om_id')."#";
//            $fmIds = $request->input('multi_fm_id');
//            foreach ($fmIds as $fmId){
//                $fmString .= $fmId."#";
//            }
        }

        $adminUser = AdminUser::create([
            'first_name'        => $request->input('first_name'),
            'last_name'         => $request->input('last_name'),
            'email'             => $request->input('email'),
            'role_id'           => $request->input('role'),
            'password'          => Hash::make($request->input('password')),
            'status_id'         => $request->input('status'),
            'project_id'        => $request->input('project_id'),
            'fm_id'             => $fmString,
            'is_super_admin'    => 0,
            'created_by'        => $user->id,
            'created_at'        => Carbon::now('Asia/Jakarta')
        ]);

        Session::flash('success', 'Sukses membuat Admin User baru');
        return redirect()->route('admin.admin-users.index');
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
        $adminUser = AdminUser::find($id);

        $roles = AdminUserRole::orderBy('name')->get();
        if($adminUser->project_id != 0){
            $project = Project::find($adminUser->project_id);
            $projectId = $project->id;
            $projectName = $project->code.' - '. $project->name;
        }
        else{
            $projectId = 0;
            $projectName = "Semua";
        }
        $fmId = 0;
        $fmName = "";
        $fmList = null;
        if($adminUser->role_id == 4){
            $fmArr = explode('#', $adminUser->fm_id);
            $fmData = Employee::where('id', $fmArr[0])->first();
            if(!empty($fmData)){
                $fmId = $fmData->id;
                $fmName = $fmData->code.' - '.$fmData->first_name. " ". $fmData->last_name;
            }
        }

        else if($adminUser->role_id == 5){
            $omArr = explode('#', $adminUser->fm_id);
            $omList = Employee::whereIn('id', $omArr)->get();
            $omData = Employee::where('id', $omArr[0])->first();
            if(!empty($omData)){
                $omId = $omData->id;
                $omName = $omData->code.' - '.$omData->first_name. " ". $omData->last_name;
            }
        }

        $data = [
            'adminUser'     => $adminUser,
            'roles'         => $roles,
            'projectId'     => $projectId,
            'projectName'   => $projectName,
            'fmId'          => $fmId,
            'fmName'        => $fmName,
            'omId'          => $omId,
            'omName'        => $omName,
            'fmList'        => $fmList,
        ];

        return view('admin.adminuser.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name'        => 'required|max:100',
            'last_name'         => 'required|max:100'
        ],[
            'first_name.required'      => 'Nama Depan wajib diisi!',
            'last_name.required'       => 'Nama Belakang wajib diisi!'
        ]);

        if(!ctype_space($request->input('password'))){
            $validator->sometimes('password', 'min:6|confirmed', function ($input) {
                return $input->password;
            });
        }

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $adminUser = AdminUser::find($request->input('id'));

        if($request->filled('password')){
            $adminUser->password = Hash::make($request->input('password'));
        }
        $fmString = "0#";
        if($request->input('role') == 4){
            $fmString = $request->input('fm_id')."#";
        }
        else if($request->input('role') == 5){
            $fmString = "";
            $fmString = $request->input('om_id')."#";
//            $fmIds = $request->input('multi_fm_id');
//            foreach ($fmIds as $fmId){
//                $fmString .= $fmId."#";
//            }
        }

        $adminUser->first_name = $request->input('first_name');
        $adminUser->last_name = $request->input('last_name');
        $adminUser->role_id = $request->input('role');
        $adminUser->status_id = $request->input('status');
        $adminUser->fm_id =$fmString;
        $adminUser->project_id = $request->input('project_id');
        $adminUser->updated_at = Carbon::now('Asia/Jakarta');
        $adminUser->save();

        Session::flash('success', 'Sukses menyimpan data Admin User!');
        return redirect()->route('admin.admin-users.index');
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
            $adminUserId = $request->input('id');
            $adminUser = AdminUser::find($adminUserId);
//            $adminUser->delete();

            Session::flash('success', 'Sukses menghapus Admin User ' . $adminUser->email . ' - ' . $adminUser->first_name . ' ' . $adminUser->last_name);
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

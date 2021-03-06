<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminUserRole;
use App\Models\Product;
use App\Models\ProductUserCategory;
use App\Models\User;
use App\Models\UserCategory;
use App\Transformer\UserTransformer;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class UserController extends Controller
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
        $users = User::query();
        return DataTables::of($users)
            ->setTransformer(new UserTransformer)
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
        //
        return view('admin.user.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $categories = UserCategory::orderBy('name')->get();
        return view('admin.user.create', compact('categories'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:100',
            'email'             => 'required|regex:/^\S*$/u|unique:users|max:50',
            'category'          => 'required',
            'password'          => 'required',
            'address'           => 'required|max:255',
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
        $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
        $user = User::create([
            'name'          => $request->input('name'),
            'email'         => $request->input('email'),
            'category_id'   => $request->input('category'),
            'phone'         => $request->input('phone'),
            'password'      => Hash::make($request->input('password')),
            'status_id'     => 1,
            'created_at'    => $now
        ]);

        $adminUser = Auth::guard('admin')->user();

        // Create new address
        Address::create([
            'user_id'       => $user->id,
            'description'   => $request->input('address'),
            'primary'       => 1,
            'postal_code'   => $request->filled('postal_code') ? $request->input('postal_code') : "",
            'created_by'    => $adminUser->id,
            'created_at'    => $now,
            'updated_by'    => $adminUser->id,
            'updated_at'    => $now
        ]);

        Session::flash('success', 'Sukses membuat MD Baru');
        return redirect()->route('admin.users.index');
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
        $user = User::find($id);
        $categories = UserCategory::orderBy('name')->get();
        $address = $user->addresses->first();

        $data = [
            'user'          => $user,
            'categories'    => $categories,
            'address'       => $address
        ];

        return view('admin.user.edit')->with($data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'name'        => 'required|max:100',
            'email'             => 'required|regex:/^\S*$/u|unique:users|max:50',
            'phone'             => 'required'
        ],[
            'email.unique'      => 'ID Login Akses telah terdaftar!',
            'email.regex'       => 'ID Login Akses harus tanpa spasi!'
        ]);

        if(!ctype_space($request->input('password'))){
            $validator->sometimes('password', 'min:6|confirmed', function ($input) {
                return $input->password;
            });
        }

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $adminUser = Auth::guard('admin')->user();
        $now = Carbon::now('Asia/Jakarta')->toDateTimeString();

        $user = User::find($request->input('id'));

        if($request->filled('password')){
            $user->password = Hash::make($request->input('password'));
        }

        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->category_id = $request->input('category');
        $user->save();

        $address = $user->addresses->first();
        $address->description = $request->input('address');
        $address->postal_code = $request->filled('postal_code') ? $request->input('postal_code') : "";
        $address->updated_at = $now;
        $address->updated_by = $adminUser->id;
        $address->save();

        Session::flash('success', 'Sukses mengubah data Master Dealer!');
        return redirect()->route('admin.users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        //
        try {
            //Belum melakukan pengecekan hubungan antar Table
            $userId = $request->input('id');
            $user = User::find($userId);
            $user->delete();

            Session::flash('success', 'Sukses menghapus data MD ' . $user->email . ' - ' . $user->name);
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

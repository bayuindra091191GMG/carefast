<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\PermissionMenu;
use App\Models\AdminUserRole;
use App\Models\PermissionMenuHeader;
use App\Transformer\PermissionMenuTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class PermissionMenuController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return view('admin.permission_menus.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $roles = AdminUserRole::all();
        $menus = Menu::all();
        return view('admin.permission_menus.create', compact('menus', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'admin_role' => 'required'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors());

        //Checking
        if($request->input('role') === '-1'){
            return redirect()->back()->withErrors('Pilih Role!', 'default')->withInput($request->all());
        }

        $menus = Input::get('ids');
        $role_id = $request->get('admin_role');
        $exist = true;
        $valid = true;

        foreach ($menus as $menu){
            if(empty($menu)) $exist = false;
            $permission = PermissionMenu::where('admin_role_id', $role_id)->where('menu_id', $menu)->first();
            if($permission != null){
                $valid = false;
            }
        }

        if(!$exist){
            return redirect()->back()->withErrors('Belum ada menu yang dipilih!', 'default')->withInput($request->all());
        }

        if(!$valid){
            return redirect()->back()->withErrors('Otorisasi Menu Sudah Dibuat!', 'default')->withInput($request->all());
        }

        $dateTimeNow = Carbon::now('Asia/Jakarta');
        $user = Auth::user();

        foreach ($menus as $menu){
            PermissionMenu::create([
                'admin_role_id' => $role_id,
                'menu_id'       => $menu,
                'created_by'    => $user->id,
                'created_at'    => $dateTimeNow->toDateTimeString(),
                'updated_by'    => $user->id,
                'updated_at'    => $dateTimeNow->toDateTimeString()
            ]);
        }

        Session::flash('message', 'Berhasil membuat data otorisasi menu baru!');
        return redirect(route('admin.permission_menus'));
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
     * @param $id
     * @return \Illuminate\Http\Response
     * @internal param PermissionMenu $permissionMenu
     */
    public function edit($id)
    {
        $menus = Menu::orderBy('name')->get();
        $role = AdminUserRole::find($id);
        $permissionMenus = PermissionMenu::where('admin_role_id', $role->id)->get();

        return view('admin.permission_menus.edit', compact('menus', 'admin_role_id', 'permissionMenus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @internal param int $id
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'admin_role_id' => 'required'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors());

        //Add Permission Menus
        $dateTimeNow = Carbon::now('Asia/Jakarta');
        $user = Auth::user();

        $menus = Input::get('ids');
        $role_id = $request->get('admin_role_id');
        $exist = true;

        if($menus == null){
            $exist = false;
        }

        if($exist) {
            foreach ($menus as $menu) {
                $permission = PermissionMenu::where('admin_role_id', $role_id)->where('menu_id', $menu)->first();
                if ($permission == null) {
                    $menuResult = PermissionMenu::create([
                        'role_id' => $role_id,
                        'menu_id' => $menu,
                        'created_by' => $user->id,
                        'created_at' => $dateTimeNow->toDateTimeString(),
                        'updated_by' => $user->id,
                        'updated_at' => $dateTimeNow->toDateTimeString()
                    ]);

                    //Create menu Header
                    //Check first if Exist
                    $menuHeader = PermissionMenuHeader::where('admin_role_id', $role_id)->where('menu_header_id', $menuResult->menu->menu_header->id)->first();
                    if ($menuHeader == null) {
                        PermissionMenuHeader::create([
                            'role_id'           => $role_id,
                            'menu_header_id'    => $menuResult->menu->menu_header->id,
                            'created_by'        => $user->id,
                            'created_at'        => $dateTimeNow->toDateTimeString(),
                            'updated_by'        => $user->id,
                            'updated_at'        => $dateTimeNow->toDateTimeString()
                        ]);
                    }
                }
            }
        }
        //End add permission menu

        //Delete Permission Menus
        $menusDelete = Input::get('idsDelete');
        if($menusDelete != null){


            foreach($menusDelete as $menu){
                $data = PermissionMenu::where('admin_role_id', $role_id)->where('menu_id', $menu)->first();
                if($data != null){
                    $menuHeader = PermissionMenuHeader::where('admin_role_id', $role_id)->where('menu_header_id', $data->menu->menu_header->id)->first();
                    $data->delete();

                    //Checking Header and Delete Them if no more data in PermissionMenu that Contains Menu header with same ID
                    $menuHeaderId = $menuHeader->menu_header_id;
                    $valid = true;

                    if(PermissionMenu::where('admin_role_id', $role_id)->whereHas('menu', function($query) use ($menuHeaderId){
                        $menuHeaderId2 = $menuHeaderId;
                        $query->whereHas('menu_header', function ($query1) use ($menuHeaderId2){
                            $query1->where('id', $menuHeaderId2);
                        });
                    })->exists()){
                        $valid = false;
                    }

                    if($valid){
                        $menuHeader->delete();
                    }
                }
            }
        }
        //End Delete Permission Menu

        Session::flash('message', 'Berhasil mengubah otorisasi menu!');
        return redirect(route('admin.permission_menus'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = PermissionMenu::find($id);
        $permission->delete();

        Session::flash('message', 'Sukses menghapus data Otorisasi Menu!');
        return view('admin.permission_menus.index');
    }

    public function getIndex()
    {
        $permissionMenus = Role::orderBy('name')->get();
        return DataTables::of($permissionMenus)
            ->setTransformer(new PermissionMenuTransformer)
            ->make(true);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuHeader;
use App\Transformer\MenuHeaderTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Response;
use Yajra\DataTables\DataTables;

class MenuHeaderController extends Controller
{
    public function getIndex()
    {
        $menuheaders = MenuHeader::all();
        return DataTables::of($menuheaders)
            ->setTransformer(new MenuHeaderTransformer)
            ->addIndexColumn()
            ->make(true);
    }

    /**
     * Function to show Menu Index Page.
    */
    public function index()
    {
        return view('admin.menu_header.index');
    }

    /**
     * Function to show create Menu page.
    */
    public function create()
    {
        return view('admin.menu_header.create');
    }

    /**
     * Function to store the new added Menu to the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|max:50'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        MenuHeader::create([
            'name'              => $request->get('name')
        ]);

        Session::flash('message', 'Berhasil membuat data menu header baru!');

        return redirect()->route('admin.menu_headers');
    }

    /**
     * Function to show Edit Menu Page.
     *
     * @param MenuHeader $menuHeader
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(MenuHeader $menuHeader)
    {
        return view('admin.menu_header.edit', compact('menuHeader'));
    }

    /**
     * Function to save the updated Menu.
     *
     * @param Request $request
     * @param MenuHeader $menuHeader
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, MenuHeader $menuHeader)
    {
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:50'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors());

        $menuHeader->name = $request->get('name');
        $menuHeader->save();

        Session::flash('message', 'Berhasil mengubah data menu header!');

        return redirect()->route('admin.menu_headers.edit', ['menu_header' => $menuHeader]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @internal param int $id
     */
    public function destroy(Request $request)
    {
        try{
            $menuHeader = MenuHeader::find($request->input('id'));

            //Deleting all the Sub Menus
            Menu::where('menu_header_id', $menuHeader->id)->delete();
            $menuHeader->delete();

            Session::flash('message', 'Berhasil menghapus data menu header'. $menuHeader->name);
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}

<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use App\Transformer\ProductCategoryTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\DataTables;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.product_category.index');
    }

    public function getIndex(){
        $productCategories = ProductCategory::all();
        return DataTables::of($productCategories)
            ->setTransformer(new ProductCategoryTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.product_category.create');
    }

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:100|unique:product_categories',
            'description'       => 'max:255'
        ],[
            'name.required'     => 'Nama kategori wajib diisi!',
            'name.unique'       => 'Nama kategori sudah terdaftar!'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $name = strtoupper($request->input('name'));

        $user = Auth::guard('admin')->user();
        $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

        ProductCategory::create([
            'name'          => $name,
            'description'   => $request->input('description') ?? null,
            'status_id'     => $request->input('status'),
            'created_at'    => $dateNow,
            'created_by'    => $user->id,
            'updated_at'    => $dateNow,
            'updated_by'    => $user->id
        ]);

        Session::flash('success', 'Sukses membuat kategori produk baru!');
        return redirect()->route('admin.product.category.index');
    }

    public function edit(int $id){
        $productCategory = ProductCategory::find($id);
        if(empty($productCategory)){
            return redirect()->back();
        }

        return view('admin.product_category.edit', compact('productCategory'));
    }

    public function update(Request $request, int $id){
        $productCategory = ProductCategory::find($id);
        //dd($productCategory);
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:100|unique:product_categories,name,'. $productCategory->id,
            'description'       => 'max:255'
        ],[
            'name.required'     => 'Nama kategori wajib diisi!',
            'name.unique'       => 'Nama kategori sudah terdaftar!'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $name = strtoupper($request->input('name'));

        $user = Auth::guard('admin')->user();
        $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

        $productCategory->name = $name;
        $productCategory->description = $request->input('description') ?? null;
        $productCategory->status_id = $request->input('status');
        $productCategory->updated_at = $dateNow;
        $productCategory->updated_by = $user->id;
        $productCategory->save();

        Session::flash('success', 'Sukses mengubah kategori produk baru!');
        return redirect()->route('admin.product.category.index');
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $productCategory = ProductCategory::find($deletedId);
            $productCategory->delete();

            Session::flash('success', 'Sukses menghapus kategori produk!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}
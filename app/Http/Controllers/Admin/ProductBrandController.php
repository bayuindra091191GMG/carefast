<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\ProductBrand;
use App\Transformer\ProductBrandTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class ProductBrandController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.product_brand.index');
    }

    public function getIndex(){
        $productBrands = ProductBrand::all();
        return DataTables::of($productBrands)
            ->setTransformer(new ProductBrandTransformer)
            ->make(true);

    }

    public function create(){
        return view('admin.product_brand.create');
    }

    public function store(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:product_brands',
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama merk wajib diisi!',
                'name.unique'       => 'Nama merk sudah terdaftar!'
            ]);

            $brandImages = $request->file('brand_image');
    
            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
    
            $name = strtoupper($request->input('name'));
    
            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
    
            $productBrand = ProductBrand::create([
                'name'          => $name,
                // 'description'   => $request->input('description') ?? null,
                // 'status_id'     => $request->input('status'),
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);
            
            //get file request
            $brandImages = $request->file('brand_image');
            // dd($brandImages);
    
            //checking if null 
            if(!empty($brandImages)){
                // create brand image file
                $img = Image::make($brandImages);
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);
        
                $filename = $name.'_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];
        
                // save brand image file
                $img->save(public_path('storage/product_brands/'. $filename), 75);
    
                //update database
                $productBrand->img_path = $filename;
                $productBrand->save();
            }
    
            Session::flash('success', 'Sukses membuat merk produk baru!');
        }
        catch(\Exception $ex){
            Session::flash('error', 'Gagal membuat merk produk baru!');
        }
        return redirect()->route('admin.product.brand.index');
    }

    public function edit(int $id){
        $productBrand = ProductBrand::find($id);
        if(empty($productBrand)){
            return redirect()->back();
        }

        return view('admin.product_brand.edit', compact('productBrand'));
    }

    public function update(Request $request, int $id){
        $productBrand = ProductBrand::find($id);
        //dd($productBrand);
        $validator = Validator::make($request->all(), [
            'name'              => 'required|max:100|unique:product_brands,name,'. $productBrand->id,
            'description'       => 'max:255'
        ],[
            'name.required'     => 'Nama kategori wajib diisi!',
            'name.unique'       => 'Nama kategori sudah terdaftar!'
        ]);

        if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

        $name = strtoupper($request->input('name'));

        $user = Auth::guard('admin')->user();
        $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

        $productBrand->name = $name;
        // $productBrand->description = $request->input('description') ?? null;
        // $productBrand->status_id = $request->input('status');
        $productBrand->updated_at = $dateNow;
        $productBrand->updated_by = $user->id;
        $productBrand->save();

        if(!empty($brandImages)){
            $brandImage = ProductBrand::where('product_id', $product->id)->where('img_path', 1)->first();

            $img = Image::make($brandImage);
            $img->save(public_path('storage/product_brands/'. $mainImage->path), 75);

        }

        Session::flash('success', 'Sukses mengubah merk produk baru!');
        return redirect()->route('admin.product.brand.index');
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $productBrand = ProductBrand::find($deletedId);
            $productBrand->delete();

            Session::flash('success', 'Sukses menghapus merk produk!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}
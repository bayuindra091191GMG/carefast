<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Transformer\BannerTransformer;
use App\Transformer\ProductBrandTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class BannerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){
        return view('admin.banner.index');
    }

    public function getIndex(){
        $banners = Banner::all();
        return DataTables::of($banners)
            ->setTransformer(new BannerTransformer())
            ->make(true);

    }

    public function create(){
        return view('admin.banner.create');
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

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());
    
            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $name = $request->input('name');
    
            $productBrand = Banner::create([
                'name'          => $name,
                'alt_text'      => $request->input('description'),
                'url'           => $request->input('url') ?? null,
                'product_id'    => $request->input('product_id') ?? null,
                'brand_id'      => $request->input('brand_id') ?? null,
                'status_id'     => 1,
                'created_at'    => $dateNow,
                'created_by'    => $user->id,
                'updated_at'    => $dateNow,
                'updated_by'    => $user->id
            ]);
            
            //get file request
            $bannerImage = $request->file('banner_image');
            // dd($bannerImage);
    
            //checking if null 
            if(!empty($bannerImage)){
                // create brand image file
                $img = Image::make($bannerImage);
                $extStr = $img->mime();
                $ext = explode('/', $extStr, 2);
        
                $filename = $name.'_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];
        
                // save brand image file
                $img->save(public_path('storage/banner/'. $filename), 75);
    
                //update database
                $productBrand->image_path = $filename;
                $productBrand->save();
            }
    
            Session::flash('success', 'Sukses membuat banner baru!');
        }
        catch(\Exception $ex){
            Log::error('Admin/BannerController - store error EX: '. $ex);
            Session::flash('error', 'Gagal membuat banner baru!');
        }
        return redirect()->route('admin.banner.index');
    }

    public function edit(int $id){
        $banner = Banner::find($id);
        if(empty($banner)){
            return redirect()->back();
        }

        if($banner->product_id){
            $product = Product::find($banner->product_id)->first();
        }
        if($banner->brand_id){
            $brand = ProductBrand::find($banner->brand_id)->first();
        }

        return view('admin.banner.edit', compact('banner', 'product', 'brand'));
    }

    public function update(Request $request, int $id){
        try{
            $banner = Banner::find($id);
            //dd($banner);
            $validator = Validator::make($request->all(), [
                'name'              => 'required|max:100|unique:product_brands,name,'. $banner->id,
                'description'       => 'max:255'
            ],[
                'name.required'     => 'Nama kategori wajib diisi!',
                'name.unique'       => 'Nama kategori sudah terdaftar!'
            ]);

            if ($validator->fails()) return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $name = $request->input('name');

            $user = Auth::guard('admin')->user();
            $dateNow = Carbon::now('Asia/Jakarta')->toDateTimeString();

            $banner->name = $name;
            $banner->alt_text = $request->input('description') ?? null;
            $banner->url = $request->input('url') ?? null;
            $banner->product_id = $request->input('product_id') ?? null;
            $banner->brand_id = $request->input('brand_id') ?? null;
            $banner->status_id = $request->input('status');
            $banner->updated_at = $dateNow;
            $banner->updated_by = $user->id;
            $banner->save();

            $bannerImage = $request->file('banner_image');

            if(!empty($bannerImage)){
                $img = Image::make($bannerImage);
                $img->save(public_path('storage/banner/'. $banner->image_path), 75);
            }

            Session::flash('success', 'Sukses mengubah banner baru!');
        }
        catch(\Exception $ex){
            Log::error('Admin/BannerController - update error EX: '. $ex);
            Session::flash('success', 'Sukses mengubah banner baru!');
        }
        return redirect()->route('admin.banner.index');
    }

    public function destroy(Request $request){
        try{
            $deletedId = $request->input('id');
            $productBrand = Banner::find($deletedId);
            $productBrand->delete();

            Session::flash('success', 'Sukses menghapus banner!');
            return Response::json(array('success' => 'VALID'));
        }
        catch(\Exception $ex){
            Log::error('Admin/BannerController - destroy error EX: '. $ex);
            return Response::json(array('errors' => 'INVALID'));
        }
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: YANSEN
 * Date: 12/10/2018
 * Time: 10:06
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Category;
use App\Models\CategoryProduct;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductPosition;
use App\Models\ProductUserCategory;
use App\Models\UserCategory;
use App\Transformer\ProductCustomizeTransformer;
use App\Transformer\ProductTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class ProductController extends Controller
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

    public function index()
    {
        return view('admin.product.index');
    }

    public function indexCustomize()
    {
        return view('admin.product.index-customize');
    }

    public function getIndex(Request $request){
        $products = Product::query();
        return DataTables::of($products)
            ->setTransformer(new ProductTransformer())
            ->make(true);
    }

    public function getIndexCustomize(){
        $productUserCategories = ProductUserCategory::query();
        return DataTables::of($productUserCategories)
            ->setTransformer(new ProductCustomizeTransformer)
            ->make(true);
    }

    public function show(int $id)
    {
        $product = Product::find($id);

        if(empty($product)){
            return redirect()->back();
        }

        $mainImage = ProductImage::where('product_id', $product->id)->where('is_main_image', 1)->first();
        $secondaryImages = ProductImage::where('product_id', $product->id)->where('is_main_image', 0)->get();

        //dd($secondaryImages);

        $data = [
            'product'               => $product,
            'mainImage'             => $mainImage,
            'secondaryImages'       => $secondaryImages,
        ];
        return view('admin.product.show')->with($data);
    }

    public function create()
    {
        $categories = ProductCategory::orderBy('name')->get();
        $brands = ProductBrand::orderBy('name')->get();

        $data = [
            'categories'    => $categories,
            'brands'        => $brands
        ];

        return view('admin.product.create')->with($data);
    }

    public function store(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'name'          => 'required|max:100',
                'sku'           => 'required|max:50',
                'price'         => 'required'
            ]);

            if ($request->input('category') == "-1") {
                return back()->withErrors("Kategori wajib dipilih!")->withInput($request->all());
            }

            $mainImages = $request->file('image_main');

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $slug = Utilities::CreateProductSlug($request->input('name'));

            // String to float conversion
            $floatPrice = Utilities::toFloat($request->input('price'));
            $floatWeight = 0;
            if($request->filled('weight') && $request->input('weight') != '0'){
                $floatWeight = Utilities::toFloat($request->input('weight'));
            }

            // save product
            $newProduct = Product::create([
                'name'          => $request->input('name'),
                'category_id'   => $request->input('category'),
                'brand_id'      => $request->input('brand'),
                'slug'          => $slug,
                'sku'           => strtoupper($request->input('sku')),
                'description'   => $request->input('description'),
                'price'         => $floatPrice,
                'weight'        => $floatWeight,
                'status_id'     => $request->input('status'),
                'created_at'    => $dateTimeNow,
                'updated_at'    => $dateTimeNow
            ]);

            // save product main image, and image detail
            $img = Image::make($mainImages);
            $extStr = $img->mime();
            $ext = explode('/', $extStr, 2);

            $filename = $newProduct->id.'_main_'.$slug. '.'. $ext[1];

            $img->save(public_path('storage/products/'. $filename), 75);
            $newProductImage = ProductImage::create([
                'product_id'    => $newProduct->id,
                'path'          => $filename,
                'is_main_image' => 1
            ]);

            if($request->hasFile('image_secondary')){
                $idx = 1;
                foreach($request->file('image_secondary') as $detailImage){
                    $img = Image::make($detailImage);
                    $extStr = $img->mime();
                    $ext = explode('/', $extStr, 2);

                    $filename = $newProduct->id.'_secondary_'. $idx. '_'. $slug.'_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];

                    $img->save(public_path('storage/products/'. $filename), 75);

                    $newProductImage = ProductImage::create([
                        'product_id'    => $newProduct->id,
                        'path'          => $filename,
                        'is_main_image' => 0
                    ]);

                    $idx++;
                }
            }

            // Save to basic user category
            $user = Auth::guard('admin')->user();
            ProductUserCategory::create([
                'product_id'        => $newProduct->id,
                'user_category_id'  => 0,
                'price'             => $floatPrice,
                'created_at'        => $dateTimeNow,
                'created_by'        => $user->id,
                'updated_at'        => $dateTimeNow,
                'updated_by'        => $user->id
            ]);

            if($request->input('is_start_customize') == '1'){
                return redirect()->route('admin.product.customize.create',['product_id' => $newProduct->id]);
            }

            Session::flash('success', 'Sukses membuat produk baru!');
            return redirect()->route('admin.product.show',['id' => $newProduct->id]);
        }catch(\Exception $ex){
            error_log($ex);
            Log::error('Admin/ProductController - store error EX: '. $ex);
            return back()->withErrors("Something Went Wrong")->withInput();
        }
    }

    public function createCustomize(int $product_id)
    {
        $product = Product::find($product_id);
        if(empty($product)){
            dd('PRODUK INVALID!!');
        }

        $userCategories = UserCategory::where('id', '!=', 0)
            ->orderBy('name')
            ->get();

        $data = [
            'product'           => $product,
            'userCategories'    => $userCategories
        ];

        return view('admin.product.create-customize')->with($data);
    }

    public function storeCustomize(Request $request)
    {
        try{

            $product = Product::find($request->input('product_id'));
            if(empty($product)){
                return redirect()->back()->withErrors('PRODUK INVALID!!', 'default')->withInput($request->all());
            }

            $valid = true;
            $categories = $request->input('categories');
            $prices = $request->input('prices');

            if(empty($categories || empty($prices || empty($weights)))){
                return redirect()->back()->withErrors('Detil kategori dan harga wajib diisi!', 'default')->withInput($request->all());
            }

            $idx = 0;
            foreach ($categories as $category){
                if(empty($category) || $category == "-1") $valid = false;
                if(empty($prices[$idx]) || $prices[$idx] === '0') $valid = false;
                $idx++;
            }

            if(!$valid){
                return redirect()->back()->withErrors('Detil kategori MD, berat dan harga wajib diisi!', 'default')->withInput($request->all());
            }

            // Check duplicate MD categories
            $validUnique = Utilities::arrayIsUnique($categories);
            if(!$validUnique){
                return redirect()->back()->withErrors('Detil kategori MD tidak boleh kembar!', 'default')->withInput($request->all());
            }

            $user = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $idx = 0;
            foreach ($categories as $category){
                $userCategoryId = intval($category);
                $price = Utilities::toFloat($prices[$idx]);

                ProductUserCategory::create([
                    'product_id'        => $product->id,
                    'user_category_id'  => $userCategoryId,
                    'price'             => $price,
                    'created_at'        => $now,
                    'created_by'        => $user->id,
                    'updated_at'        => $now,
                    'updated_by'        => $user->id
                ]);
            }

            Session::flash('success', 'Sukses membuat kustomisasi harga produk!');

            return redirect()->route('admin.product.show',['id' => $product->id]);

        }
        catch(\Exception $ex){
            Log::error('Admin/ProductController - storeCustomize error EX: '. $ex);
            return back()->withErrors("Something Went Wrong")->withInput();
        }
    }

    public function editCustomize(Request $request)
    {
        $redirect = $request->redirect;

        $productMdPrices = null;
        $product = null;
        if($request->product_id != null){
            $productId = $request->product_id;
            $product = Product::find($productId);
            if(empty($product)){
                $product = null;
            }
            else{
                $productMdPrices = collect();

                $userCategories = UserCategory::orderBy('name')->get();
                foreach ($userCategories as $userCategory){
                    $productUserCategory = $userCategory->product_user_categories->where('product_id', $productId)->first();

                    $productMdPrice = collect([
                        'id'                => !empty($productUserCategory) ? $productUserCategory->id : -1,
                        'product_id'        => $productId,
                        'md_category_id'    => $userCategory->id,
                        'md_category_name'  => $userCategory->name,
                        'price'             => !empty($productUserCategory) ? $productUserCategory->price : 0
                    ]);

                    $productMdPrices->push($productMdPrice);
                }
            }
        }

        $data = [
            'product'           => $product,
            'productMdPrices'   => $productMdPrices,
            'redirect'          => $redirect
        ];

        return view('admin.product.edit-customize')->with($data);
    }

    public function updateCustomize(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'product'           => 'required|max:100',
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $product = Product::find($request->input('product'));
            if(empty($product)){
                return redirect()->back()->withErrors('PRODUK INVALID!!', 'default')->withInput($request->all());
            }

            $valid = true;
            $productUserCategoryIds = $request->input('product_user_category_ids');
            $userCategoryIds = $request->input('md_category_ids');
            $prices = $request->input('prices');

            $user = Auth::guard('admin')->user();
            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $idx = 0;
            foreach ($productUserCategoryIds as $productUserCategoryId){
                $productUserCategoryIdInt = intval($productUserCategoryId);
                $userCategoryIdInt = intval($userCategoryIds[$idx]);
                $price = Utilities::toFloat($prices[$idx]);

                if($productUserCategoryIdInt === -1){
                    ProductUserCategory::create([
                        'product_id'        => $product->id,
                        'user_category_id'  => $userCategoryIdInt,
                        'price'             => $price,
                        'created_at'        => $now,
                        'created_by'        => $user->id,
                        'updated_at'        => $now,
                        'updated_by'        => $user->id
                    ]);
                }
                else{
                    $productUserCategory = ProductUserCategory::find($productUserCategoryIdInt);
                    if(!empty($productUserCategory)){
                        $productUserCategory->price = $price;
                        $productUserCategory->updated_at = $now;
                        $productUserCategory->updated_by = $user->id;
                        $productUserCategory->save();
                    }
                }
            }

            Session::flash('success', 'Sukses mengubah kustomisasi harga produk!');

            $redirect = $request->input('redirect');
            if($redirect === 'product'){
                return redirect()->route('admin.product.show',['id' => $product->id]);
            }
            else{
                return redirect()->route('admin.product.customize.index');
            }
        }
        catch(\Exception $ex){
            Log::error('Admin/ProductController - storeCustomize error EX: '. $ex);
            return back()->withErrors("Something Went Wrong")->withInput();
        }
    }

    public function edit(int $id)
    {
        $product = Product::find($id);
        if(empty($product)){
            return redirect()->back();
        }

        $categories = ProductCategory::orderBy('name')->get();
        $brands = ProductBrand::orderBy('name')->get();
        $mainImage = ProductImage::where('product_id', $product->id)->where('is_main_image', 1)->first();
        $secondaryImages = ProductImage::where('product_id', $product->id)->where('is_main_image', 0)->get();

        $data = [
            'product'           => $product,
            'categories'        => $categories,
            'brands'            => $brands,
            'mainImage'         => $mainImage,
            'secondaryImages'   => $secondaryImages,
        ];
        return view('admin.product.edit')->with($data);
    }

    public function update(Request $request, int $id){

        try{
            $validator = Validator::make($request->all(), [
                'name'          => 'required|max:100',
                'sku'           => 'required|max:50',
                'price'         => 'required'
            ]);

            if ($validator->fails())
                return redirect()->back()->withErrors($validator->errors())->withInput($request->all());

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $slug = Utilities::CreateProductSlug($request->input('name'));

            $floatPrice = Utilities::toFloat($request->input('price'));
            $floatWeight = Utilities::toFloat($request->input('weight'));

            // update product
            $product = Product::find($id);
            $product->name = $request->input('name');
            $product->category_id = $request->input('category');
            $product->brand_id = $request->input('brand');
            $product->slug = $slug;
            $product->sku = strtoupper($request->input('sku'));
            $product->description = $request->input('description');
            $product->price = $floatPrice;
            $product->weight = $floatWeight;
            $product->tag = $request->input('tags');
            $product->updated_at = $dateTimeNow;

            $product->save();

            // Change main image
            if($request->hasFile('image_main')){
                $mainImage = ProductImage::where('product_id', $product->id)->where('is_main_image', 1)->first();

                $img = Image::make($request->file('image_main'));
                $img->save(public_path('storage/products/'. $mainImage->path), 75);

            }

            // Check deleted image
            if($request->filled('deleted_image_ids')){
                $deletedImageIds = $request->input('deleted_image_ids');
                if(strpos($deletedImageIds, ',')){
                    $deletedImageIdArr = explode(',', $deletedImageIds);

                    foreach ($deletedImageIdArr as $deletedImageId){
                        $detailImage = ProductImage::find($deletedImageId);
                        if(!empty($detailImage)){
                            // Delete image file
                            $deletedPath = public_path('storage/products/'. $detailImage->path);
                            if(file_exists($deletedPath)) unlink($deletedPath);

                            $detailImage->delete();
                        }
                    }
                }
                else{
                    $detailImage = ProductImage::find($deletedImageIds);
                    if(!empty($detailImage)){
                        // Delete image file
                        $deletedPath = public_path('storage/products/'. $detailImage->path);
                        if(file_exists($deletedPath)) unlink($deletedPath);

                        $detailImage->delete();
                    }
                }
            }

            if($request->hasFile('image_secondary')){
                $idx = 1;
                foreach($request->file('image_secondary') as $imageSecondary){
                    $img = Image::make($imageSecondary);
                    $extStr = $img->mime();
                    $ext = explode('/', $extStr, 2);

                    $filename = $product->id.'_secondary_' .$idx. '_' .$slug.'_'.Carbon::now('Asia/Jakarta')->format('Ymdhms'). '.'. $ext[1];

                    $img->save(public_path('storage/products/'. $filename), 75);

                    $newProductImage = ProductImage::create([
                        'product_id'    => $product->id,
                        'path'          => $filename,
                        'is_main_image' => 0
                    ]);

                    $idx++;
                }
            }

            Session::flash('success', 'Sukses membuat produk baru!');

            return redirect()->route('admin.product.show',['item' => $product->id]);

        }catch(\Exception $ex){
            Log::error('Admin/ProductController - store error EX: '. $ex);
            return back()->withErrors("Something Went Wrong")->withInput();
        }
    }

    public function getProducts(Request $request){
        $term = trim($request->q);
        $products = Product::where('id', '!=', $request->id)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', '%' . $term . '%');
            })
            ->get();

        $formatted_tags = [];

        foreach ($products as $product) {
            $formatted_tags[] = ['id' => $product->id, 'text' => $product->name. ' - '. $product->sku];
        }

        return Response::json($formatted_tags);
    }
}
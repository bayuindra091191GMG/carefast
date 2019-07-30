<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductUserCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function getAllProduct(Request $request){
        try{
            $brand = intval($request->input('brand_id'));
            $startPrice = intval($request->input('price_start'));
            $endPrice = intval($request->input('price_end'));
            $orderingField = $request->input('ordering_field');
            $orderingType = $request->input('ordering_type');
            $skip = intval($request->input('skip'));
            $keyword = $request->input('keyword');

            $userId = intval($request->input('user_id'));

            $productModels = collect();
            if($userId === -1){
                $products = Product::with('product_images')->where('status_id', 1);

                if($brand !== 0){
                    $products = $products->where('brand_id', $brand);
                }

                if($startPrice > $endPrice){

                    $products = $products->whereBetween('price', array($startPrice, $endPrice));
                }

                if(!empty($keyword)){
                    $products = $products->where('name', 'ilike', '%'. $keyword. '%');
                }

                if($products->count() === 0){
                    return Response::json([
                        'message'       => 'EMPTY',
                        'model'         => ''
                    ], 482);
                }

                $products = $products->orderBy($orderingField, $orderingType)
                    ->skip($skip)
                    ->limit(15)
                    ->get();


                foreach ($products as $product){
                    $productImage = $product->product_images->where('is_main_image', 1)->first();
                    $productModel = collect([
                        'id'                => $product->id,
                        'name'              => $product->name,
                        'sku'               => $product->sku,
                        'category_id'       => $product->category_id,
                        'category_name'     => $product->product_category->name,
                        'brand_id'          => $product->brand_id,
                        'brand_name'        => $product->product_brand->name,
                        'image_path'        => asset('storage/products/'. $productImage->path),
                        'external_link'     => $product->external_link ?? '',
                        'price'             => $product->price,
                        'description'       => $product->meta_description ?? '',
                    ]);

                    $productModels->push($productModel);
                }
            }
            else{
                $user = User::find($userId);

                $productUserCategories = ProductUserCategory::with('product')->where('user_category_id', $user->category_id)
                    ->whereHas('product', function($query){
                        $query->where('status_id', 1);
                    });

                if($brand !== 0){
                    $productUserCategories = $productUserCategories->whereHas('product', function($query) use($brand){
                        $query->where('brand_id', $brand);
                    });
                }

                if($startPrice > $endPrice){
                    $productUserCategories = $productUserCategories->whereBetween('price', array($startPrice, $endPrice));
                }

                if(!empty($keyword)){
                    $productUserCategories = $productUserCategories->whereHas('product', function($query) use($keyword){
                        $query->where('name', 'ilike', '%'. $keyword. '%');
                    });
                }

                if($productUserCategories->count() === 0){
                    return Response::json([
                        'message'       => 'EMPTY',
                        'model'         => ''
                    ], 482);
                }

                if($orderingField === 'name'){
                    $productUserCategories = $productUserCategories->whereHas('product', function($query) use($orderingField, $orderingType){
                        $query->orderBy($orderingField, $orderingType);
                    })
                        ->skip($skip)
                        ->limit(15)
                        ->get();
                }
                else{
                    $productUserCategories = $productUserCategories->orderBy($orderingField, $orderingType)
                        ->skip($skip)
                        ->limit(15)
                        ->get();
                }

                foreach ($productUserCategories as $productUserCategory){
                    $productImage = $productUserCategory->product->product_images->where('is_main_image', 1)->first();
                    $productModel = collect([
                        'id'                => $productUserCategory->product_id,
                        'name'              => $productUserCategory->product->name,
                        'sku'               => $productUserCategory->product->sku,
                        'category_id'       => $productUserCategory->product->category_id,
                        'category_name'     => $productUserCategory->product->product_category->name,
                        'brand_id'          => $productUserCategory->product->brand_id,
                        'brand_name'        => $productUserCategory->product->product_brand->name,
                        'image_path'        => asset('storage/products/'. $productImage->path),
                        'external_link'     => $productUserCategory->product->external_link ?? '',
                        'price'             => $productUserCategory->price,
                        'description'       => $productUserCategory->product->meta_description ?? ''
                    ]);

                    $productModels->push($productModel);
                }
            }

            return Response::json([
                'message'       => 'SUCCESS',
                'model'         => json_encode($productModels)
            ]);
        }
        catch (\Exception $ex){
            Log::error('Api/ProductController - getAllProducts error EX: '. $ex);
            return Response::json([
                'message'       => 'ERROR',
                'model'         => ''
            ], 500);
        }
    }

    public function show(Request $request){
        try{
            $productId = intval($request->input('product_id'));

            $product = Product::find($productId);
            if(empty($product)){
                return Response::json([
                    'message'       => 'BAD REQUEST',
                    'model'         => ''
                ], 400);
            }

            $productImages = $product->product_images;
            $mainImage = $productImages->where('is_main_image', 1)->first();
            $otherImages = $productImages->where('is_main_image', 0)->get();

            $imageArr = array();
            foreach($otherImages as $image){
                array_push($imageArr, asset('storage/products/'. $image->path));
            }

            $productModel = collect([
                'id'                => $productId,
                'name'              => $product->name,
                'sku'               => $product->sku,
                'category_id'       => $product->category_id,
                'category_name'     => $product->product_category->name,
                'brand_id'          => $product->brand_id,
                'brand_name'        => $product->product_brand->name,
                'image_path'        => asset('storage/products/'. $mainImage->path),
                'image_path_others' => $imageArr,
                'external_link'     => $product->external_link ?? '',
                'price'             => $product->price,
                'description'       => $product->meta_description ?? '',
                'status_id'         => $product->status_id
            ]);

            return Response::json([
                'message'       => 'SUCCESS',
                'model'         => json_encode($productModel)
            ]);
        }
        catch (\Exception $ex){
            Log::error('Api/ProductController - show error EX: '. $ex);
            return Response::json([
                'message'       => 'ERROR',
                'model'         => ''
            ], 500);
        }
    }

//    public function search(Request $request){
//        try{
//            $keyword = $request->input('keyword');
//        }
//        catch (\Exception $ex){
//            Log::error('Api/ProductController - search error EX: '. $ex);
//            return Response::json([
//                'message'       => 'ERROR',
//                'model'         => ''
//            ], 500);
//        }
//    }
}

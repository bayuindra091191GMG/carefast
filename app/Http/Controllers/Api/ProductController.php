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
            $brand = intval($request->input('brand'));
            $startPrice = intval($request->input('price_start'));
            $endPrice = intval($request->input('price_end'));
            $orderingField = $request->input('ordering_field');
            $orderingType = $request->input('ordering_type');
            $skip = intval($request->input('skip'));

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
                        'status_id'         => $product->status_id
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

                foreach ($productUserCategories as $productUserCategorie){
                    $productModel = collect([
                        'name'              => $productUserCategorie->product->name,
                        'sku'               => $productUserCategorie->product->sku,
                        'category_id'       => $productUserCategorie->product->category_id,
                        'category_name'     => $productUserCategorie->product->product_category->name,
                        'brand_id'          => $productUserCategorie->product->brand_id,
                        'brand_name'        => $productUserCategorie->product->product_brand->name,
                        'external_link'     => $productUserCategorie->product->external_link ?? '',
                        'price'             => $productUserCategorie->price,
                        'description'       => $productUserCategorie->product->meta_description ?? '',
                        'status_id'         => $productUserCategorie->product->status_id
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

            $productModel = collect([
                'name'              => $product->name,
                'sku'               => $product->sku,
                'category_id'       => $product->category_id,
                'category_name'     => $product->product_category->name,
                'brand_id'          => $product->brand_id,
                'brand_name'        => $product->product_brand->name,
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
}
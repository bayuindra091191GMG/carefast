<?php


namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function getAllProduct(Request $request){
        $brand = intval($request->input('brand'));
        $startPrice = intval($request->input('price_start'));
        $endPrice = intval($request->input('price_end'));
        $orderingField = $request->input('ordering_field');
        $orderingType = $request->input('ordering_type');
        $skip = intval($request->input('skip'));

        $products = Product::where('status_id', 1);

        if($brand !== 0){
            $products = $products->where('brand_id', $brand);
        }

        if($startPrice > $endPrice){
            $products = $products->whereBetween('price', array($startPrice, $endPrice));
        }

        $products = $products->orderBy($orderingField, $orderingType)
            ->skip($skip)
            ->limit(15)
            ->get();

        return Response::json([
            'message'       => 'Success',
            'model'         => json_encode($products)
        ]);
    }
}
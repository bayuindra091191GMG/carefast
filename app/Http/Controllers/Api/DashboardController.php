<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\City;
use App\Models\DwsWasteCategoryData;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\User;
use App\Models\WasteBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function getData(){
        try {
            //get product
            //sementara masih ambil semua data product
            $products = Product::all();

            //get banner
            $banners = Banner::all();

            //get brand
            $brands = ProductBrand::all();

            $returnObj = [
                'products'  => $products,
                'banners'   => $banners,
                'brands'    => $brands,
            ];
            return Response::json([
                'message'   => 'success',
                'model'     => json_encode($returnObj)
            ], 200);
        }
        catch (\Exception $ex){
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
}

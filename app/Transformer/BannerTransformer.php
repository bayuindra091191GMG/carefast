<?php


namespace App\Transformer;

use App\Models\Banner;
use App\Models\Product;
use App\Models\ProductBrand;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Log;

class BannerTransformer extends TransformerAbstract
{
    public function transform(Banner $banner){

        try{
            $createdDate = Carbon::parse($banner->created_at)->toIso8601String();

            $bannerEditUrl = route('admin.banner.edit', ['id' => $banner->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$bannerEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $banner->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            $productName = "-";
            if(!empty($banner->product_id)){
                $product = Product::find($banner->product_id);
                $productName = $product->name;
            }
            $brandName = "-";
            if(!empty($banner->brand_id)){
                $brand = ProductBrand::find($banner->brand_id);
                $brandName = $brand->name;
            }

            $img_path = asset('storage/banner/'.$banner->image_path);
            return[
                'name'              => $banner->name,
                'url'              => $banner->url,
                'product'              => $productName,
                'brand'              => $brandName,
                'img_path'          => "<img src='".$img_path."' height=60>",
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ProductBrandTransformer.php > transform ".$exception);
        }
    }
}
<?php


namespace App\Transformer;

use App\Models\ProductBrand;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Facades\Log;

class ProductBrandTransformer extends TransformerAbstract
{
    public function transform(ProductBrand $productBrand){

        try{
            $createdDate = Carbon::parse($productBrand->created_at)->toIso8601String();

            $productBrandEditUrl = route('admin.product.brand.edit', ['id' => $productBrand->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$productBrandEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $productBrand->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            $img_path = asset('storage/product_brands/'.$productBrand->img_path);
            return[
                'name'              => $productBrand->name,
                'img_path'          => "<img src='".$img_path."' height=100>",
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
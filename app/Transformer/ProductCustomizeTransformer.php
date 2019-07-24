<?php


namespace App\Transformer;


use App\Models\ProductUserCategory;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ProductCustomizeTransformer extends TransformerAbstract
{
    public function transform(ProductUserCategory $productUserCategory){

        try{
            $createdDate = Carbon::parse($productUserCategory->created_at)->toIso8601String();
            $updatedDate = Carbon::parse($productUserCategory->updated_at)->toIso8601String();

            $productCategoryEditUrl = route('admin.product.customize.edit', ['product_id' => $productUserCategory->product_id]);

            $action = "<a class='btn btn-xs btn-info' href='".$productCategoryEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";

            return[
                'name'              => $productUserCategory->product->name,
                'sku'               => $productUserCategory->product->sku,
                'md_category'       => $productUserCategory->user_category->name,
                'price'             => $productUserCategory->price,
                'created_at'        => $createdDate,
                'updated_at'        => $updatedDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}
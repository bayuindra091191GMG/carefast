<?php


namespace App\Transformer;

use App\Models\ProductCategory;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ProductCategoryTransformer extends TransformerAbstract
{
    public function transform(ProductCategory $productCategory){

        try{
            $createdDate = Carbon::parse($productCategory->created_at)->toIso8601String();

            $productCategoryEditUrl = route('admin.product.category.edit', ['id' => $productCategory->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$productCategoryEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $productCategory->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $productCategory->name,
                'description'       => $productCategory->description,
                'status'            => $productCategory->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}
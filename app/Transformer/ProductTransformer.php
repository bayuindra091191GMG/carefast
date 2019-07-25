<?php
/**
 * Created by PhpStorm.
 * User: YANSEN
 * Date: 12/10/2018
 * Time: 10:03
 */

namespace App\Transformer;


use App\Models\Product;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract
{

    public function transform(Product $product){

        try{
            $createdDate = Carbon::parse($product->created_at)->toIso8601String();
            $updatedDate = Carbon::parse($product->updated_at)->toIso8601String();

            $itemShowUrl = route('admin.product.show', ['id' => $product->id]);
            $itemEditUrl = route('admin.product.edit', ['id' => $product->id]);

            $action = "<a class='btn btn-xs btn-primary' href='".$itemShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a> ";
            $action .= "<a class='btn btn-xs btn-info' href='".$itemEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";


            return[
                'name'              => $product->name,
                'sku'               => $product->sku,
                'price'             => $product->price,
                'weight'            => $product->weight,
                'status'            => $product->status->description,
                'created_at'        => $createdDate,
                'update_at'         => $updatedDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}
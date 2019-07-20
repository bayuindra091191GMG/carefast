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

            $itemShowUrl = route('admin.product.show', ['item' => $product->id]);
            $itemEditUrl = route('admin.product.edit', ['item' => $product->id]);

            $action = "<a class='btn btn-xs btn-primary' href='".$itemShowUrl."' data-toggle='tooltip' data-placement='top'><i class='icon-details'></i></a> ";
            $action .= "<a class='btn btn-xs btn-info' href='".$itemEditUrl."' data-toggle='tooltip' data-placement='top'><i class='icon-pencil'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $product->id ."' ><i class='icon-remove'></i></a>";


            return[
                'name'              => $product->name,
                'sku'               => $product->sku,
                'price'             => $product->price,
                'price'             => $product->weight,
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
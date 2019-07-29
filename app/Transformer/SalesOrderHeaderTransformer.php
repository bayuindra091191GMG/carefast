<?php


namespace App\Transformer;


use App\Models\SalesOrderHeader;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class SalesOrderHeaderTransformer extends TransformerAbstract
{
    public function transform(SalesOrderHeader $header){

        try{
            $date = Carbon::parse($header->date)->toIso8601String();
            $createdDate = Carbon::parse($header->created_at)->toIso8601String();

            $showUrl = route('admin.sales_order.show', ['id' => $header->id]);
            $showLink = "<a name='". $header->code. "' href='".$showUrl."'>". $header->code. "</a>";

            $orderShowUrl = route('admin.orders.detail', ['item' => $header->order_id]);
            $orderShowLink = "<a name='". $header->order->order_number. "' href='".$orderShowUrl."'>". $header->order->order_number. "</a>";

            $salesOrderEditUrl = route('admin.sales_order.edit', ['id' => $header->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$salesOrderEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";
//            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $productCategory->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'code'                  => $showLink,
                'date'                  => $date,
                'order_code'            => $orderShowLink,
                'user'                  => $header->user->name,
                'total_price'           => $header->total_price,
                'total_discount'        => $header->total_discount,
                'discount_percentage'   => $header->discount_percentage,
                'grand_total'           => $header->grand_total,
                'status'                => $header->status->description,
                'created_by'            => $header->createdBy->first_name. '_'. $header->createdBy->last_name,
                'created_at'            => $createdDate,
                'action'                => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}
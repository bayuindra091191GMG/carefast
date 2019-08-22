<?php


namespace App\Transformer;


use App\Models\Customer;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class CustomerTransformer extends TransformerAbstract
{
    public function transform(Customer $customer){

        try{
            $createdDate = Carbon::parse($customer->created_at)->toIso8601String();

            $routeShowUrl = route('admin.customer.show', ['id' => $customer->id]);
            $routeEditUrl = route('admin.customer.edit', ['id' => $customer->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";


            return[
                'name'              => $customer->name,
                'email'             => $customer->email,
                'phones'            => $customer->phone,
                'status'            => $customer->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("CustomerTransformer.php > transform ".$exception);
        }
    }
}

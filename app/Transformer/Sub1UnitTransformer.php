<?php


namespace App\Transformer;

use App\Models\Place;
use App\Models\Sub1Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class Sub1UnitTransformer extends TransformerAbstract
{
    public function transform(Sub1Unit $sub1unit){

        try{
            $createdDate = Carbon::parse($sub1unit->created_at)->toIso8601String();

            $sub1unitEditUrl = route('admin.sub1unit.edit', ['id' => $sub1unit->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$sub1unitEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
//            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $sub1unit->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";
            $action .= "<a class='btn btn-xs btn-danger' data-id='". $sub1unit->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $sub1unit->name,
                'unit'              => $sub1unit->unit->name,
                'description'       => $sub1unit->description,
                'status'            => $sub1unit->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("Sub1UnitTransformer.php > transform ".$exception);
        }
    }
}

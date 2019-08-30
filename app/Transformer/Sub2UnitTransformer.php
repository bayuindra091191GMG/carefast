<?php


namespace App\Transformer;

use App\Models\Place;
use App\Models\Sub2Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class Sub2UnitTransformer extends TransformerAbstract
{
    public function transform(Sub2Unit $sub2unit){

        try{
            $createdDate = Carbon::parse($sub2unit->created_at)->toIso8601String();

            $sub2unitEditUrl = route('admin.sub2unit.edit', ['id' => $sub2unit->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$sub2unitEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
//            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $sub2unit->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";
            $action .= "<a class='btn btn-xs btn-danger' data-id='". $sub2unit->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $sub2unit->name,
                'description'       => $sub2unit->description,
                'status'            => $sub2unit->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("Sub2UnitTransformer.php > transform ".$exception);
        }
    }
}

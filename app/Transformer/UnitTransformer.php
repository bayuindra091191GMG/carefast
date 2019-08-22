<?php


namespace App\Transformer;

use App\Models\Place;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class UnitTransformer extends TransformerAbstract
{
    public function transform(Unit $unit){

        try{
            $createdDate = Carbon::parse($unit->created_at)->toIso8601String();

            $unitEditUrl = route('admin.place.edit', ['id' => $unit->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$unitEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $unit->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $unit->name,
                'description'       => $unit->description,
                'status'            => $unit->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("UnitTransformer.php > transform ".$exception);
        }
    }
}

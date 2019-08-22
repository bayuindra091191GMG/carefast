<?php


namespace App\Transformer;

use App\Models\Place;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class PlaceTransformer extends TransformerAbstract
{
    public function transform(Place $place){

        try{
            $createdDate = Carbon::parse($place->created_at)->toIso8601String();

            $placeEditUrl = route('admin.place.edit', ['id' => $place->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$placeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
//            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $place->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";
            $action .= "<a class='btn btn-xs btn-danger' data-id='". $place->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";


            return[
                'name'              => $place->name,
                'description'       => $place->description,
                'status'            => $place->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}

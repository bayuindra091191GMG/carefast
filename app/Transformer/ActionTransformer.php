<?php


namespace App\Transformer;

use App\Models\Action;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ActionTransformer extends TransformerAbstract
{
    public function transform(Action $action){

        try{
            $createdDate = Carbon::parse($action->created_at)->toIso8601String();

            $actionEditUrl = route('admin.action.edit', ['id' => $action->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$actionEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $action->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $action->name,
                'description'       => $action->description,
                'status'            => $action->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
        }
    }
}

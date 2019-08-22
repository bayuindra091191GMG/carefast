<?php


namespace App\Transformer;

use App\Models\Action;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ActionTransformer extends TransformerAbstract
{
    public function transform(Action $actionDB){

        try{
            $createdDate = Carbon::parse($actionDB->created_at)->toIso8601String();

            $actionEditUrl = route('admin.action.edit', ['id' => $actionDB->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$actionEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $actionDB->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'              => $actionDB->name,
                'description'       => $actionDB->description,
                'status'            => $actionDB->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ActionTransformer.php > transform ".$exception);
        }
    }
}

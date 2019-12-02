<?php


namespace App\Transformer;


use App\Models\Action;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ProjectActivityTransformer extends TransformerAbstract
{
    public function transform(ProjectActivity $project){

        try{
            $routeEditUrl = route('admin.project.activity.edit', ['id' => $project->id]);
            $actionName = "";
            if(!empty($project->action_id)){
                $actionList = explode('#', $project->action_id);
                foreach ($actionList as $action){
                    if(!empty($action)){
                        $action = Action::find($action);
                        $actionName .= $action->name. " - ";
                    }
                }
            }
            $action = "<a href='".$routeEditUrl."' class='btn btn-primary'>UBAH</a>";

            return[
                'place_name'     => $project->place->name,
                'plotting_name'     => $project->plotting_name,
                'action_name'       => $actionName,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ProjectActivityTransformer.php > transform ".$exception);
        }
    }
}

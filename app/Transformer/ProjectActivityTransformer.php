<?php


namespace App\Transformer;


use App\Models\Action;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectActivitiesDetail;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\ProjectShift;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ProjectActivityTransformer extends TransformerAbstract
{
    public function transform(ProjectActivitiesDetail $project){

        try{
            $routeEditUrl = route('admin.project.activity.edit', ['id' => $project->id]);
            $actionName = "";
            if(!empty($project->action_id)){
                $actionList = explode('#', $project->action_id);
                foreach ($actionList as $action){
                    if(!empty($action)){
                        $action = Action::find($action);
                        $actionName .= $action->name. " ";
                    }
                }
            }
            $action = "<a href='".$routeEditUrl."' class='btn btn-primary'>UBAH</a>";
            $action .= "&nbsp;<a class='delete-modal btn btn-danger' data-id='". $project->id ."' >HAPUS</a>";
//            $action .= "&nbsp;<a href='".$routeEditUrl."' class='btn btn-danger'>HAPUS</a>";
//            $action = "<a href='#' class='btn btn-primary'>UBAH</a>";
            $activityHeader = ProjectActivitiesHeader::find($project->activities_header_id);
            $assignedCso = Schedule::where('project_activity_id', $project->activities_header_id)->first();
            $employeeName = "";
            if(!empty($assignedCso)){
                $employeeName = $assignedCso->employee->first_name. " " .$assignedCso->employee->last_name;
            }
            $projectShifts = ProjectShift::Where('id', $project->shift_type)->first();
            $shiftString = $projectShifts == null ? "-" : $projectShifts->shift_type;

            return[
                'time'              => Carbon::parse($project->start)->format('H:i')." - ".Carbon::parse($project->finish)->format('H:i'),
                'shift'             => $shiftString,
                'period_type'       => $project->period_type,
                'place'             => $activityHeader->place->name,
                'object_name'        => $project->object_name,
                'action_name'       => $actionName,
                'assigned_cso'       => $employeeName,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ProjectActivityTransformer.php > transform ".$exception);
        }
    }
}

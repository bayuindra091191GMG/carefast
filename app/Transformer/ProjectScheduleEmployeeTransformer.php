<?php


namespace App\Transformer;


use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ProjectScheduleEmployeeTransformer extends TransformerAbstract
{
    public function transform(ProjectEmployee $project){

        try{
            $routeCreateUrl = route('admin.project.schedule.create', ['employee_id' => $project->id]);
            $routeEditUrl = route('admin.project.schedule.edit', ['employee_id' => $project->id]);

            $projectScheduleEmployee = Schedule::where('project_id', $project->project_id)
                ->where('project_employee_id', $project->employee_id)
                ->first();

            if(empty($projectScheduleEmployee)){
                $action = "<a href='".$routeCreateUrl."' class='btn btn-success'>TAMBAH SCHEDULE</a>";
            }
            else{
                $action = "<a href='".$routeEditUrl."' class='btn btn-primary'>UBAH</a>";
            }

            $imageURL = asset('storage/employees/'.$project->employee->image_path);

            return[
                'picture'           => "<img src='".$imageURL."' width='50'>",
                'name'              => $project->employee->first_name." ".$project->employee->last_name,
                'employee_role'     => $project->employee_role->name,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ProjectScheduleTransformer.php > transform ".$exception);
        }
    }
}

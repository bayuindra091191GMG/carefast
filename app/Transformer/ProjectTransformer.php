<?php


namespace App\Transformer;


use App\Models\Employee;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ProjectTransformer extends TransformerAbstract
{
    public function transform(Project $project){

        try{
            $createdDate = Carbon::parse($project->created_at)->toIso8601String();

            $routeShowUrl = route('admin.project.show', ['id' => $project->id]);
//            $routeEditUrl = route('admin.project.edit', ['id' => $project->id]);
            $routeEditUrl = route('admin.project.index');

            $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";


            return[
                'name'        => $project->name,
                'customer_name'        => $project->customer->name,
                'phones'            => $project->phone,
                'status'            => $project->status->description,
                'address'            => $project->address,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ProjectTransformer.php > transform ".$exception);
        }
    }
}

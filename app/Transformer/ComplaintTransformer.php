<?php


namespace App\Transformer;


use App\Models\Complaint;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class ComplaintTransformer extends TransformerAbstract
{
    public function transform(Complaint $complaint){
        $createdDate = Carbon::parse($complaint->created_at)->toIso8601String();

        $routeShowUrl = route('admin.customer_complaint.show', ['id' => $complaint->id]);
        $code = "<a name='". $complaint->code. "' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'>". $complaint->code. "</a>";

        $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";

        // Check complaint type
        if(!empty($complaint->customer_id) && empty($complaint->employee_id)){
            $type = 'CUSTOMER';
            $complainer = $complaint->customer_name;
        }
        else{
            $type = 'INTERNAL';
            $complainer = $complaint->employee->first_name. ' '. $complaint->employee->last_name;
        }

        return [
            'code'          => $code,
            'date'          => $createdDate,
            'project'       => $complaint->project->name,
            'type'          => $type,
            'complainer'    => $complainer,
            'handled_by'    => $complaint->employee_role->name,
            'status'        => $complaint->status->description,
            'action'        => $action
        ];
    }
}

<?php


namespace App\Transformer;


use App\Models\Complaint;
use App\Models\ComplaintDetail;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ComplaintTransformer extends TransformerAbstract
{
    public function transform(Complaint $complaint){
        try{
            $createdDate = Carbon::parse($complaint->created_at)->toIso8601String();

            $routeShowUrl = route('admin.complaint.show', ['id' => $complaint->id]);
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
            $complaintDetailSorted = DB::table('complaint_details')
                ->where('complaint_id', $complaint->id)
                ->where('employee_id', '!=', null)
                ->orderByDesc('id')
                ->first();
            $handleBy = "-";
            if(!empty($complaintDetailSorted)){
                $employeeDB = Employee::find($complaintDetailSorted->employee_id);
                $handleBy = $employeeDB->employee_role->name;
            }

            return [
                'code'          => $code,
                'date'          => $createdDate,
                'project'       => $complaint->project->name,
                'subject'       => $complaint->subject,
                'type'          => $type,
                'complainer'    => $complainer,
                'handled_by'    => $handleBy,
                'status'        => $complaint->status->description,
                'action'        => $action
            ];
        }
        catch (\Exception $ex){
            Log::error('ComplaintTransformer - transform error EX: '. $ex);
            return 'Internal Server Error';
        }
    }
}

<?php


namespace App\Transformer;


use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    public function transform(Employee $employee){

        try{
            $createdDate = Carbon::parse($employee->created_at)->toIso8601String();

            $routeShowUrl = route('admin.employee.show', ['id' => $employee->id]);
            $routeEditUrl = route('admin.employee.edit', ['id' => $employee->id]);
            $routeDetailUrl = route('admin.employee.detail-attendance', ['id' => $employee->id]);


            $code = "<a href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'>". $employee->code. "</a>";

            $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-success' href='".$routeDetailUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-child'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";

            $phones = '-';
            if(!empty($employee->phone)){
                $phones = $employee->phone;
            }

            return[
                'code'              => $code,
                'first_name'        => $employee->first_name,
                'last_name'         => $employee->last_name,
                'role'              => empty($employee->employee_role_id) ? "" : $employee->employee_role->name,
                'nik'               => $employee->nik,
                'phones'            => $phones,
                'status'            => $employee->status->description,
                'created_at'        => $createdDate,
                'action'            => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("EmployeeTransformer.php > transform ".$exception);
        }
    }
}

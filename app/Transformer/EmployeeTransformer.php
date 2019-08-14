<?php


namespace App\Transformer;


use App\Models\Employee;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class EmployeeTransformer extends TransformerAbstract
{
    public function transform(Employee $employee){
        $createdDate = Carbon::parse($employee->created_at)->toIso8601String();

        $routeEditUrl = route('admin.employee.edit', ['id' => $employee->id]);

        $action = "<a class='btn btn-xs btn-info' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";
        $action .= "<a class='delete-modal btn btn-xs btn-danger' data-id='". $employee->id ."' ><i class='fas fa-trash-alt'></i></a>";

        $phones = '-';
        if(!empty($employee->telephone)){
            $phones = $employee->telephone;

            if(!empty($employee->phone)){
                $phones .= '/'. $employee->phone;
            }
        }
        else{
            if(!empty($employee->phone)){
                $phones = $employee->phone;
            }
        }

        return[
            'code'              => $employee->code,
            'first_name'        => $employee->first_name,
            'last_name'         => $employee->last_name,
            'nik'               => $employee->nik,
            'phone'             => $phones,
            'status'            => $employee->status->description,
            'created_at'        => $createdDate,
            'action'            => $action
        ];
    }
}

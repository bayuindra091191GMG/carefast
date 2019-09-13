<?php


namespace App\Transformer;


use App\Models\EmployeeRole;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class EmployeeRoleTransformer extends TransformerAbstract
{
    public function transform(EmployeeRole $employeeRole){

        try{
            $employeeRoleEditUrl = route('admin.employee_role.edit', ['id' => $employeeRole->id]);

            $action = "<a class='btn btn-xs btn-info' href='".$employeeRoleEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a> ";
            $action .= "<a class='btn btn-xs btn-danger' data-id='". $employeeRole->id ."' ><i class='fas fa-trash-alt text-white'></i></a>";

            return[
                'name'        => $employeeRole->name,
                'description'         => $employeeRole->description,
                'action'         => $action,
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("EmployeeRoleTransformer.php > transform ".$exception);
        }
    }
}

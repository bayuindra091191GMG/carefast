<?php

namespace App\Imports;

use App\Models\Action;
use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesDetail;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Sub1Unit;
use App\Models\Sub2Unit;
use App\Models\Unit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class DacImport  implements ToCollection, WithStartRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $count = 1;
        $stringError = "";
        try{
            $count = 1;
            $employeeChangeCt = 1;
            foreach($rows as $row){
                $projectCode = $row[0];
                $userEmail = $row[3];
                $userPassword = $row[4];

                $projectDb = Project::where('code', $projectCode)->first();

                if(!empty($projectDb)){
                    $adminUser = AdminUser::create([
                        'first_name'        => "Admin Project",
                        'last_name'         => $projectDb->code,
                        'email'             => $userEmail,
                        'role_id'           => 3,
                        'password'          => Hash::make($userPassword),
                        'status_id'         => 1,
                        'project_id'        => $projectDb->id,
                        'is_super_admin'    => 0,
                        'created_by'        => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')
                    ]);
                }
                $count++;
                $employeeChangeCt++;
            }
            return $employeeChangeCt;

//            $count = 1;
//            $employeeChangeCt = 1;
//            foreach($rows as $row){
//                $employeeCode = $row[0] ??  "-";
//                $employeePhone = $row[1] ?? "-";
//
//                if(!empty($employeeCode) && !empty($employeeCode)){
//                    $employeeDB = Employee::where('code', $employeeCode)
//                        ->where('status_id', 1)
//                        ->where('phone', '')
//                        ->first();
//                    if(!empty($employeeDB)){
//                        $employeeDB->phone = $employeePhone;
//                        $employeeDB->save();
//
//                        $userDB = User::where('employee_id', $employeeDB->id)->first();
//                        if(!empty($userDB)){
//                            $userDB->phone = $employeePhone;
//                            $employeeDB->save();
//                        }
//                    }
//                    $employeeChangeCt++;
//                }
//
//                $count++;
//            }
//            return $employeeChangeCt;
        }
        catch (\Exception $ex){
            dd("count = ".$count, "error = ".$ex);
            dd($ex);
            return 'failed';
        }
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2;
    }

}

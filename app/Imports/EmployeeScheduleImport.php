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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class EmployeeScheduleImport  implements ToCollection, WithStartRow
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
                $employeeCode = $row[1];

                $employeeDB = DB::table('employees')
                    ->select('id')
                    ->where('code', $employeeCode)
                    ->first();
                if(!empty($employeeDB)){

                }
                $count++;
                $employeeChangeCt++;
            }
            return $employeeChangeCt;
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
        return 5;
    }

}

<?php

namespace App\Imports;

use App\Models\Action;
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

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $count = 1;
            $employeeCode = "";
            $employeePhone = "";
            $employeeChangeCt = 1;
            foreach($rows as $row){
//                if($count == 6) break;
                $employeeCode = $row[0] ??  "-";
                $employeePhone = $row[1] ?? "-";

                if(!empty($employeeCode) && !empty($employeeCode)){
                    $employeeDB = Employee::where('code', $employeeCode)
                        ->where('status_id', 1)
                        ->where('phone', '')
                        ->first();
                    if(!empty($employeeDB)){
                        $employeeDB->phone = $employeePhone;
                        $employeeDB->save();

                        $userDB = User::where('employee_id', $employeeDB->id)->first();
                        if(!empty($userDB)){
                            $userDB->phone = $employeePhone;
                            $employeeDB->save();
                        }
                    }
                    $employeeChangeCt++;
                }

                $count++;
            }
            return $employeeChangeCt;
//            dd($customerPhone, $customerName, $projectName);
        }
        catch (\Exception $ex){
//            dd("count = ".$count, "string = ".$stringError, "error = ".$ex);
            dd($ex);
            return 'failed';
        }
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 3;
    }

}

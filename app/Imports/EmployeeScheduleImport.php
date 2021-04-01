<?php

namespace App\Imports;

use App\Models\Action;
use App\Models\AdminUser;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
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
            $dateCt = 2;

            $dateArr = [];
            //get all date
            if($count == 1){
                for($i=$dateCt; $i < count($rows); $i++){
                    $dates = explode("/", $rows[0][$i]);
                    array_push($dateArr, $dates[0]);
                }
                $count++;
            }

            foreach($rows as $row){
                $projectCode = $row[0];
                $employeeCode = $row[1];

                $employeeDB = DB::table('employees')
                    ->select('id')
                    ->where('code', $employeeCode)
                    ->first();
                if(!empty($employeeDB)){
                    $ct =0 ;
                    $tempSchedule = "";
                    //create day_status
                    // ex : 16:M;17:M;18:M;19:M;20:M;21:M;22:O;23:M;24:M;25:M;26:M;27:M;28:M;29:O;30:O;31:O;1:M;2:M;3:M;4:M;5:M;6:M;7:O;8:M;9:M;10:M;11:M;12:M;13:M;14:O;15:M;
                    for($i=$dateCt; $i < count($rows); $i++){
                        $tempSchedule .= $dateArr[$ct].":".$row[$i].";";
                        $ct++;
                    }
                    $employeeScheduleDB = EmployeeSchedule::where('employee_code', $employeeCode)->first();
                    if(empty($employeeScheduleDB)){
                        $employeeSchedule = EmployeeSchedule::create([
                            'employee_id'       => $employeeDB->id,
                            'employee_code'     => $employeeCode,
                            'day_status'        => $tempSchedule,
//                        'status_id'         => 1,
                            'created_by'        => 1,
                            'created_at'        => Carbon::now('Asia/Jakarta')
                        ]);
                    }
                    else{
                        $employeeScheduleDB->day_status = $tempSchedule;
                        $employeeScheduleDB->updated_by = 1;
                        $employeeScheduleDB->updated_at = Carbon::now('Asia/Jakarta');
                        $employeeScheduleDB->save();
                    }

                }
                $count++;
                $employeeChangeCt++;
            }
            return $employeeChangeCt;
        }
        catch (\Exception $ex){
            dd("count = ".$count, "error = ".$ex);
            return 'failed';
        }
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 4;
    }

}

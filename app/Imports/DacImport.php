<?php

namespace App\Imports;

use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\Employee;
use App\Models\Project;
use App\Models\ProjectEmployee;
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
        try{

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $count = 1;
            $customerPhone = "";
            $customerName = "";
            $projectNameTemp = "";

            foreach($rows as $row){
//                if($count == 6) break;
                $projectNameRow = $row[0] ?? null;
                $projectCodeRow = $row[1] ?? null;
                $shiftRow = $row[2] ?? null;
                $startTimeRow = $row[3] ?? null;
                $finishTimeRow = $row[4] ?? null;
                $placeRow = $row[5] ?? null;
                $objectRow = $row[6] ?? null;
                $subObjectRow = $row[7] ?? null;
                $subObjectOneRow = $row[8] ?? null;
                $subObjectTwoRow = $row[9] ?? null;
                $actionRow = $row[10] ?? null;
                $periodRow = $row[11] ?? null;
                $descriptionRow = $row[12] ?? null;

                if(!empty($projectNameRow)){
                    $projectNameTemp = $projectNameRow;
                }
                $projectDB = Project::where('name', 'like', '%'.$projectNameTemp.'%')->first();

                if(empty($projectDB)){

                }

            }
//            dd($customerPhone, $customerName, $projectName);
        }
        catch (\Exception $ex){
            dd($ex);
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

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

class ProjectEmployeeImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        try{

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $count = 1;
            foreach($rows as $row){
//                if($count == 6) break;
                //create project
                $jabatan = $row[2];
                $picName = $row[3];
                $projectName = $row[4];
//                dd($jabatan, $picName, $projectName);
                if(empty($picName) && empty($projectName)) continue;

                //create new project / use existing project
                $projectDB = Project::where('name', 'like', '%'.$projectName.'%')->first();
                if(empty($projectDB)){
                    $project = Project::create([
                        'name'              => $projectName,
                        'phone'             => '01234567891',
                        'customer_id'       => '2#4',
                        'latitude'          => '-6.1560448',
                        'longitude'         => '106.79019979999998',
                        'address'           => '',
                        'start_date'        => $dateTimeNow,
                        'finish_date'       => Carbon::now('Asia/Jakarta')->addYears(2)->toDateTimeString(),
                        'description'       => $projectName,
                        'total_manday'      => 10,
                        'total_mp_onduty'   => 10,
                        'total_mp_off'      => 10,
                        'total_manpower'    => 10,
                        'total_manpower_used'=> 0,
                        'status_id'         => 1,
                        'created_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by'        => 1,
                        'updated_at'        => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by'        => 1,
                    ]);
                    $project->code = $project->id;
                    $project->save();
                    $projectID = $project->id;
                }
                else{
                    $projectID = $projectDB->id;
                }
//                dd($projectID);

                $employeeRoleID = 5;
                if($jabatan == 'OPERATION MANAGER'){
                    $employeeRoleID = 6;
                }
                if($jabatan == 'PRESIDENT DIRECTOR'){
                    $employeeRoleID = 9;
                }
                if($jabatan == 'DIRECTOR OPERATION'){
                    $employeeRoleID = 8;
                }
                if($jabatan == 'GENERAL MANAGER'){
                    $employeeRoleID = 7;
                }
                if($jabatan == 'ADMIN'){
                    $employeeRoleID = 11;
                }

                $employeeDB = Employee::where('phone', $picName)->first();

                //create project employee (for complaint purpose only)
                $ProjectEmployee = ProjectEmployee::where('project_id', $projectID)->where('employee_id', $employeeDB->id)->first();
//                dd($ProjectEmployee, $projectID, $employeeDB->id, $employeeRoleID);
                if(empty($ProjectEmployee)){
                    ProjectEmployee::create([
                        'project_id'        => $projectID,
                        'employee_id'       => $employeeDB->id,
                        'employee_roles_id' => $employeeRoleID,
                        'status_id'         => 1,
                        'created_by'        => 1,
                        'created_at'        => $dateTimeNow,
                        'updated_by'        => 1,
                        'updated_at'        => $dateTimeNow,
                    ]);
                }
                $count++;
            }
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

    /**
     * @return int
     */
//    public function startRow(): int
//    {
//        return 6;
//    }
}

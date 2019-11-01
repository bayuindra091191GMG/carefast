<?php

namespace App\Imports;

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

class InitialDataImport implements ToCollection, WithStartRow
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
                $projectCode = $row[2];
                $projectName = $row[3];
                if(empty($projectCode) && empty($projectName)) break;
//            dd($employeeCode, $employeenName, $employeeIDNum, $employeeDOB, $employeePhone, $projectCode, $projectName);

                //create new project / use existing project
                $projectDB = Project::where('code', $projectCode)->first();
                if(empty($projectDB)){
                    $project = Project::create([
                        'name'              => $projectName,
                        'code'              => $projectCode,
                        'phone'             => '01234567891',
                        'customer_id'       => '2#4',
                        'latitude'          => '-6.1560448',
                        'longitude'         => '106.79019979999998',
                        'address'           => '',
                        'start_date'        => $dateTimeNow,
                        'finish_date'       => Carbon::now('Asia/Jakarta')->addYears(2)->toDateTimeString(),
                        'description'       => $projectName.' '.$projectCode,
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
                    $projectID = $project->id;
                }
                else{
                    $projectID = $projectDB->id;
                }

                // Create employee / user existing employee
                $employeeCode = $row[9];
                $employeeAddress = $row[11];
                $employeeAddressEdit = $employeeAddress;
                if($employeeAddress == "null"){
                    $employeeAddressEdit = "";
                }
                $employeeIDNum = trim($row[22]);
                $employeeIDNumEdit = str_replace(' ', '', $employeeIDNum);
                if($employeeIDNum == "null"){
                    $employeeIDNumEdit = "";
                }
                $employeeDOB = $row[24];
                $employeeDOBEdit = $employeeDOB;
                if($employeeDOB == "null"){
                    $employeeDOBEdit = null;
                }

                $employeePhone = trim($row[26]);
                $employeePhoneEdit = str_replace('-', '', $employeePhone);
                $employeePhoneEdit = str_replace('.', '', $employeePhoneEdit);
                if($employeePhone == "null"){
                    $employeePhoneEdit = $employeeCode;
                }

                $employeeName = trim($row[10]);
                $employeeNameSplit = explode(' ', $employeeName);
                if(count($employeeNameSplit) == 1){
                    $employeeLastName = "";
                    $employeeFirstName = $employeeName;
                }
                else{
                    $employeeLastName = $employeeNameSplit[(count($employeeNameSplit)-1)];
                    $employeeFirstName = str_replace(' '.$employeeLastName, '', $employeeName);
                }

                $employeeJob = trim($row[6]);
                $employeeRoleID = 1;
                if(strpos($employeeJob, 'CHIEF SUPERVISOR') !== false){
                    $employeeRoleID = 4;
                }
                if(strpos($employeeJob, 'SUPERVISOR') !== false){
                    $employeeRoleID = 3;
                }
                if(strpos($employeeJob, 'LEADER') !== false){
                    $employeeRoleID = 2;
                }


                $employeeDB = Employee::where('code', $employeeCode)->first();
                if(empty($employeeDB)){
                    $employee = Employee::create([
                        'employee_role_id'  => $employeeRoleID,
                        'code'              => $employeeCode,
                        'first_name'        => $employeeFirstName,
                        'last_name'         => $employeeLastName,
                        'address'           => $employeeAddressEdit,
                        'telephone'         => '',
                        'phone'             => $employeePhoneEdit,
                        'dob'               => $employeeDOBEdit,
                        'nik'               => $employeeIDNumEdit,
                        'notes'             => '',
                        'status_id'         => 1,
                        'created_by'        => 1,
                        'created_at'        => $dateTimeNow,
                        'updated_by'        => 1,
                        'updated_at'        => $dateTimeNow,
                    ]);

                    User::create([
                        'employee_id'       => $employee->id,
                        'name'              => $employeeName,
                        'password'          => Hash::make('carefastid'),
                        'phone'             => $employeePhoneEdit,
                        'status_id'         => 1
                    ]);
                    $employeeID = $employee->id;
                    $employeeRoleID = $employee->employee_role_id;

                }
                else{
                    $employeeID = $employeeDB->id;
                    $employeeRoleID = $employeeDB->employee_role_id;
                }

                //create project employee (for complaint purpose only)
                ProjectEmployee::create([
                    'project_id'        => $projectID,
                    'employee_id'       => $employeeID,
                    'employee_roles_id' => $employeeRoleID,
                    'status_id'         => 1,
                    'created_by'        => 1,
                    'created_at'        => $dateTimeNow,
                    'updated_by'        => 1,
                    'updated_at'        => $dateTimeNow,
                ]);
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
    public function startRow(): int
    {
        return 2;
    }
}

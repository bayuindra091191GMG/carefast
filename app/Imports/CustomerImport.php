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

class   CustomerImport implements ToCollection
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
            $projectName = "";

            foreach($rows as $row){
//                if($count == 6) break;
                //create project
                $customerPhone = $row[6] ?? null;
                $customerType = $row[5] ?? null;
                $customerName = $row[4] ?? null;
                $projectName = $row[3] ?? null;
                $categoryId = 2;

                if(!empty($row[0])){
                    if(!empty($customerPhone) && !empty($customerName)){
//                        dd($customerPhone, $customerType, $customerName, $projectName);

                        $project = Project::where('name', $projectName)->first();
                        if(!empty($project)){
                            $customerPhone = str_replace("'", '', $customerPhone);

                            if(!empty($customerType)){
                                $customerTypeDB = CustomerType::where('name', $customerType)->first();
                                if(!empty($customerTypeDB)){
                                    $categoryId = $customerTypeDB->id;
                                }
                                else{
                                    $customerTypeDB = CustomerType::create([
                                        'name'              => $customerType,
                                    ]);
                                    $categoryId = $customerTypeDB->id;
                                }
                            }

                            $customerDB = Customer::where('name', $customerName)->first();
                            $customerID = 2;
                            if(empty($customerDB)){
                                $customer = Customer::create([
                                    'name'              => $customerName,
                                    'category_id'       => $categoryId,
                                    'email'             => $customerPhone."@carefast.com",
                                    'image_path'         => '1_photo_20191007021015.png',
                                    'phone'             => $customerPhone,
                                    'status_id'         => 1,
                                    'password'          => Hash::make('carefast2019'),
                                    'created_at'        => $dateTimeNow,
                                    'updated_at'        => $dateTimeNow,
                                ]);
                                $customerID = $customer->id;
                            }
                            else{
                                $customerID = $customerDB->id;
                            }

                            $project->customer_id = $customerID;
                            $project->save();
                        }
                    }
                }

            }
//            dd($customerPhone, $customerName, $projectName);
        }
        catch (\Exception $ex){
            dd($ex);
        }
    }

}

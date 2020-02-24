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
        try{

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $count = 1;
            $customerPhone = "";
            $customerName = "";
            $projectNameTemp = "";

            $headerId = 1;
            foreach($rows as $row){
//                if($count == 6) break;
                $projectNameRow = $row[0] ?? null;
                $projectCodeRow = $row[1] ?? null;
                $shiftRow = $row[2] ?? null;
                $startTimeRow = $row[3] ?? null;
                $finishTimeRow = $row[4] ?? null;
                $placeRow = $row[5] ?? null;
                $objectRow = $row[6] ?? null;
                $subObjectOneRow = $row[7] ?? null;
                $subObjectTwoRow = $row[8] ?? null;
                $actionRow = $row[9] ?? null;
                $periodRow = $row[10] ?? null;
                $descriptionRow = $row[11] ?? null;

                if(!empty($projectNameRow)){
                    $projectNameTemp = $projectNameRow;
                }
                $projectDB = Project::where('name', 'like', '%'.$projectNameTemp.'%')->first();

                if(!empty($projectDB)){
                    if(empty($projectNameRow) && empty($projectCodeRow) && empty($shiftRow) && empty($startTimeRow) && empty($finishTimeRow)){
                        $placeId = 1;
                        $placeDb = Place::where('name', $placeRow)->first();
                        if(empty($placeDb)){
                            //save to database
                            $newPlace = Place::create([
                                'name'            => $placeRow,
                                'description'         => $placeRow,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => 1,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => 1,
                            ]);
                            $placeId = $newPlace->id;
                        }
                        else{
                            $placeId = $placeDb->id;
                        }

                        $projectActivityHeader = ProjectActivitiesHeader::create([
                            'project_id'            => $projectDB->id,
                            'plotting_name'         => $placeRow,
                            'place_id'              => $placeId,
                            'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'created_by'            => 1,
                            'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                            'updated_by'            => 1,
                        ]);
                        $headerId = $projectActivityHeader->id;
                    }

                    //action checking
                    $actionId = 1;
                    $unitName = "";
                    if($actionRow == "ISTIRAHAT"){
                        $actionId = 17;
                        $unitName = "SEMUA AREA";
                    }
                    else{
                        $actionDb = Action::where('name', $actionRow)->first();
                        if(empty($actionDb)){
                            //save to database
                            $newAction = Action::create([
                                'name'            => $actionRow,
                                'description'         => $actionRow,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => 1,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => 1,
                            ]);
                            $actionId = $newAction->id;
                        }
                        else{
                            $actionId = $actionDb->id;
                        }

                        //object checking
                        if(!empty($objectRow)){
                            $unitDb = Unit::where('name', $objectRow)->first();
                            if(empty($unitDb)){
                                //save to database
                                $newUnit = Unit::create([
                                    'name'            => $objectRow,
                                    'description'         => $objectRow,
                                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'created_by'            => 1,
                                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'updated_by'            => 1,
                                ]);
                            }
                            $unitName = $objectRow;
                        }
                        else{
                            $unitName = $placeRow;
                        }

                        //object 1 checking
                        if(!empty($subObjectOneRow)){
                            $unit1Db = Sub1Unit::where('name', $subObjectOneRow)->first();
                            if(empty($unit1Db)){
                                //save to database
                                $newUnit1 = Sub1Unit::create([
                                    'name'            => $subObjectOneRow,
                                    'description'         => $subObjectOneRow,
                                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'created_by'            => 1,
                                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'updated_by'            => 1,
                                ]);
                            }
                            $unitName = $unitName."--".$objectRow;
                        }

                        //object 2 checking
                        if(!empty($subObjectTwoRow)){
                            $unit2Db = Sub2Unit::where('name', $subObjectTwoRow)->first();
                            if(empty($unit2Db)){
                                //save to database
                                $newUnit2 = Sub2Unit::create([
                                    'name'            => $subObjectTwoRow,
                                    'description'         => $subObjectTwoRow,
                                    'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'created_by'            => 1,
                                    'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                    'updated_by'            => 1,
                                ]);
                            }
                            $unitName = $unitName."--".$objectRow;
                        }
                    }

                    //setting time
                    $start = Carbon::parse('00-00-00 ' . $startTimeRow)->format('Y-m-d H:i:s');
                    $finish = Carbon::parse('00-00-00 ' . $finishTimeRow)->format('Y-m-d H:i:s');

                    //checking period
                    if($periodRow == 'DAILY'){
                        $periodType = "Daily";
                        $weeks = "1#2#3#4#5#";
                        $days = "1#2#3#4#5#6#7#";
                    }
                    else if($periodRow == 'PERIODIK'){
                        $periodType = "Weekly";
                        $weeks = "1#2#3#4#5#";
                        $days = "1#2#3#4#5#6#7#";
                    }
                    else{
                        $periodType = "Daily";
                        $weeks = "1#2#3#4#5#";
                        $days = "1#2#3#4#5#6#7#";
                    }


                    //save to database
                    $projectActivityDetail = ProjectActivitiesDetail::create([
                        'activities_header_id' => $headerId,
                        'action_id' => $actionId."#",
                        'shift_type' => $shiftRow,
                        'weeks' => $weeks,
                        'days' => $days,
                        'start' => $start,
                        'finish' => $finish,
                        'period_type' => $periodType,
                        'object_name' => $unitName,
                        'description' => $descriptionRow,
                        'created_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'created_by' => 1,
                        'updated_at' => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                        'updated_by' => 1,
                    ]);
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

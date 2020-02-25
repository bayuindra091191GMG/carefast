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
            $customerPhone = "";
            $customerName = "";
            $projectNameTemp = "";

            $headerId = 1;
            $placeId = 1;
            $unitId =1;
            $unitName =1;
            $subUnit1Id = -1;
            $subUnit1Name = "-";
            $subUnit2Id = -1;
            $subUnit2name = "-";

            foreach($rows as $row){
//                if($count == 6) break;
                $projectNameRow = strtoupper($row[0]) ?? null;
                $projectCodeRow = $row[1] ?? null;
                $shiftRow = $row[2] ?? null;
                $startTimeRow = $row[3] ?? null;
                $finishTimeRow = $row[4] ?? null;
                $placeRow = strtoupper($row[5]) ?? null;
                $objectRow = strtoupper($row[6]) ?? null;
                $subObjectOneRow = strtoupper($row[7]) ?? null;
                $subObjectTwoRow = strtoupper($row[8]) ?? null;
                $actionRow = strtoupper($row[9]) ?? null;
                $periodRow = $row[10] ?? null;
                $descriptionRow = strtoupper($row[11]) ?? null;

                if(!empty($projectNameRow)){
                    $projectNameTemp = $projectNameRow;
                }
                $projectDB = Project::where('name', 'like', '%'.$projectNameTemp.'%')->first();

                if(!empty($projectDB)){
                    if(empty($projectNameRow) && empty($projectCodeRow) && empty($shiftRow) && empty($startTimeRow) && empty($finishTimeRow)){
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
//                        $headerId = 119;
                    }
                    else{
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
                                    $unitId = $newUnit->id;
                                    $unitName = $objectRow;
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
                                    $subUnit1Id = $newUnit1->id;
                                    $subUnit1Name = $subObjectOneRow;
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
                                    $subUnit2Id = $newUnit2->id;
                                    $subUnit2name = $subObjectTwoRow;
                                }
                                $unitName = $unitName."--".$objectRow;

                            }
                        }
                        $stringError = $unitName;

                        //setting time
//                        dd($startTimeRow, $finishTimeRow);
                        if(strpos($startTimeRow, '\'') !== false){
                            $startTimeRow = str_replace('\'', '', $startTimeRow);
                        }
                        else if(is_float($startTimeRow)){
                            $start = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($startTimeRow))->toDateTimeString();
//                        dd($start, gettype($start));
//                            $start = $startTimeRow->toDateTimeString();
//                            $start = Carbon::parse($startTimeRow)->format('Y-m-d H:i:s');

                        }
                        else{
//                            $timArr = explode(':', $startTimeRow);
//                            if(count($timArr) > 1){
//                                $startTimeRow = str_replace(":00", '', $startTimeRow);
//                            }
//                            $start = Carbon::parse('00-00-00 ' . $startTimeRow)->toDateTimeString();
//                            dd($start);

                            $start = Carbon::parse('00-00-00 ' . $startTimeRow)->format('Y-m-d H:i:s');
                        }

                        if(strpos($finishTimeRow, '\'') !== false){
                            $finishTimeRow = str_replace('\'', '', $finishTimeRow);
                        }
                        else if(is_float($finishTimeRow)){
                            $finish = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($finishTimeRow))->toDateTimeString();
                        }
                        else{
//                            $timArr = explode(':', $finishTimeRow);
//                            if(count($timArr) > 1){
//                                $finishTimeRow = str_replace(":00", '', $finishTimeRow);
//
//                            }
//                            $finish = Carbon::parse('00-00-00 ' . $finishTimeRow)->toDateTimeString();

                            $finish = Carbon::parse('00-00-00 ' . $finishTimeRow)->format('Y-m-d H:i:s');
                        }
//                        dd($start, $finish);

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


//                    dd($headerId, $actionId, (int)$shiftRow, $weeks, $days, $start, $finish, $periodType, $unitName, $descriptionRow, $placeRow);
                        //save to database
                        $projectActivityDetail = ProjectActivitiesDetail::create([
                            'activities_header_id' => $headerId,
                            'action_id' => $actionId."#",
                            'shift_type' => (int)$shiftRow,
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

                    //set project_object
                    $existProjectObject = ProjectObject::where('project_id', $projectDB->id)
                        ->where('place_id', $placeId)
                        ->where('unit_id', $unitId)
                        ->where('sub1_unit_id', $subUnit1Id)
                        ->first();
                    if(empty($existProjectObject)){
                        $newProjectObject= ProjectObject::create([
                            'project_id'        => $projectDB->id,
                            'place_id'          => $placeId,
                            'unit_id'           => $unitId,
                            'sub1_unit_id'      => $subUnit1Id,
                            'sub2_unit_id'      => $subUnit2Id,
                            'place_name'        => $placeRow,
                            'unit_name'         => $unitName,
                            'sub1_unit_name'    => $subUnit1Name,
                            'sub2_unit_name'    => $subUnit2name,
                        ]);
                    }
                }
                $count++;
            }
            return 'success';
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

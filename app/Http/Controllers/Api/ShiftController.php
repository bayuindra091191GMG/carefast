<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\libs\Utilities;
use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\AttendanceDetail;
use App\Models\AttendanceOvertime;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\ProjectShift;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class ShiftController extends Controller
{
    /**
     * Function to get Shift.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSchedule(Request $request)
    {
        try{
            $userLogin = auth('api')->user();
            $user = User::where('phone', $userLogin->phone)->first();

//            $data = json_decode($request->input('shift_model'));
            $employeeId =  $request->input('employee_id');
            $id = $request->input('project_id');
            $projects =  Project::find($id);
//            Log::error('Api/ShiftController - get | employee_id : '.$employeeId.' | project_id : '. $id);

            $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeId)->first();

            $projectShifts = ProjectShift::Where('project_id', $id)->get();
            if(count($projectShifts) == 0){
                return Response::json("Project Shift Tidak ditemukan!", 483);
            }

            //process project shift
            $shiftCollections = collect();
            foreach ($projectShifts as $projectShift){
                $item = ([
                    'type'        => $projectShift->shift_type,
                    'start'      => $projectShift->start_time,
                    'end'      => $projectShift->finish_time
                ]);
                $shiftCollections->push($item);
            }
            $item = ([
                'type'        => "O",
                'start'      => "-",
                'end'      => "-"
            ]);
            $shiftCollections->push($item);

            if(empty($employeeSchedule)){
                $shiftModel = collect([
                    'shifts'    => $shiftCollections,
                    'schedules'   => collect(),
                ]);
                return Response::json($shiftModel, 200);
            }

            //process employee schedule
            $days = explode(';', $employeeSchedule->day_status);
            $dayCollections = collect();
            foreach($days as $day){
                if(empty($day)) continue;
                $date = explode(':', $day);
                $item = ([
                    'date'        => $date[0],
                    'shift_type'      => $date[1]
                ]);
                $dayCollections->push($item);
            }

            //process lembur
            $datenow = Carbon::now();
            $datenow2 = Carbon::now();
//            $datenowMonth = Carbon::parse($datenow)->format('m');
//            $datenowYear = Carbon::parse($datenow)->format('Y');
            $firstDate = $datenow->firstOfMonth();
            $lastDate = $datenow2->lastOfMonth();

//            $firstDate = $datenowYear.'-'.$datenowMonth.'-'.$firstDate;
//            $lastDate = $datenowYear.'-'.$datenowMonth.'-'.$lastDate;

            $overtimes = AttendanceOvertime::where('employee_id', $employeeId)
                ->where('project_id', $id)
                ->where('is_approve', 1)
                ->whereBetween('date',
                    array($firstDate.' 00:00:00', $lastDate.' 23:59:00'))
                ->get();

            foreach($overtimes as $overtime){
                $dayFormat = Carbon::parse($overtime->date)->format('j');

                if($overtime->type == "ganti"){
                    $employeeSchedule = EmployeeSchedule::where('employee_id', $overtime->replaced_employee_id)->first();
                    if(!empty($employeeSchedule)){
                        $days = explode(';', $employeeSchedule->day_status);
                        $dayCollectionDBs = collect();
                        foreach($days as $day){
                            if(empty($day)) continue;
                            $date = explode(':', $day);
                            $item = ([
                                'day'        => $date[0],
                                'status'      => $date[1]
                            ]);
                            $dayCollectionDBs->push($item);
                        }
                        $daySelected = $dayCollectionDBs->where('day', $dayFormat)->first();
                        $dayObject = (object)$daySelected;
                        $projectShiftDb = ProjectShift::where('shift_type', $dayObject->status)->first();

                        $item = ([
                            'day'           => $dayFormat,
                            'type'          => "HL",
                            'start'         => $projectShiftDb->start_time,
                            'end'           => $projectShiftDb->finish_time
                        ]);
                        $shiftCollections->push($item);
                    }
                }
                else{
                    $item = ([
                        'day'           => $dayFormat,
                        'type'          => "HL",
                        'start'         => $overtime->time_start,
                        'end'           => $overtime->time_end
                    ]);
                    $shiftCollections->push($item);
                }
                $item = ([
                    'date'            => Carbon::parse($overtime->date)->format('j'),
                    'shift_type'      => "HL"
                ]);
                $dayCollections->push($item);
            }

            $shiftModel = collect([
                'shifts'    => $shiftCollections,
                'schedules'   => $dayCollections,
            ]);
            Log::error('Api/ShiftController - get shiftModel : '. json_encode($shiftModel));

            return Response::json($shiftModel, 200);
        }
        catch (\Exception $ex){
            Log::error('Api/ShiftController - get error EX: '. $ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

}

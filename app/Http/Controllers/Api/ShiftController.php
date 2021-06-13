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

            $employeeSchedule = EmployeeSchedule::where('employee_id', $employeeId)->first();
            if(empty($employeeSchedule)){
                return Response::json("Employee Schedule Tidak ditemukan!", 482);
            }

            $projectShifts = ProjectShift::Where('project_id', $id)->get();
            if(count($projectShifts) > 0){
                return Response::json("Project Shift Tidak ditemukan!", 483);
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
            $datenowMonth = Carbon::parse($datenow)->format('m');
            $datenowYear = Carbon::parse($datenow)->format('Y');
            $firstDate = $datenow->firstOfMonth();
            $lastDate = $datenow->lastOfMonth();

            $firstDate = $datenowYear.'-'.$datenowMonth.'-'.$firstDate;
            $lastDate = $datenowYear.'-'.$datenowMonth.'-'.$lastDate;

            $overtimes = AttendanceOvertime::where('replaced_employee_id', $employeeId)
                ->where('project_id', $id)
                ->where('type', 'ganti')
                ->where('is_approve', 1)
                ->whereBetween('date',
                    array($firstDate.' 00:00:00', $lastDate.' 23:59:00'))
                ->get();
            foreach($overtimes as $overtime){
                $item = ([
                    'date'            => Carbon::parse($overtime->date)->format('j'),
                    'shift_type'      => "HL"
                ]);
                $dayCollections->push($item);
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

            $shiftModel = collect([
                'shifts'    => $shiftCollections,
                'schedules'   => $dayCollections,
            ]);

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

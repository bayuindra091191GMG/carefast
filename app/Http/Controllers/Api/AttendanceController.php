<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\Employee;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Function to Submit Attendance.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request)
    {
        try{
            $rules = array(
                'type' => 'required'
            );

            $data = $request->json()->all();
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return response()->json($validator->messages(), 400);
            }

            $user = auth('api')->user();
            $employee = Employee::where('user_id', $user->id)->first();

            //Check Schedule
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i:s');
            $schedule = Schedule::where('employee_id', $employee->id)->where('start' >= $time)->where('finish' <= $time)->first();

            if($schedule == null){
                return Response::json("Jadwal Tidak ditemukan!", 500);
            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            $message = "";
            if($request->input('type') == 1){
                Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => $schedule->id,
                    'date'          => Carbon::now('Asia/Jakarta'),
                    'status_id'     => 6
                ]);
                $message = "Berhasil Check in";
            }
            else if($request->input('type') == 2){
                if($request->input('dac') == null){
                    return Response::json("Tidak ada data Dac yang diterima!", 500);
                }

                $newAttendance = Attendance::create([
                    'employee_id'   => $employee->id,
                    'schedule_id'   => $schedule->id,
                    'date'          => Carbon::now('Asia/Jakarta'),
                    'status_id'     => 7
                ]);

                //Create Attendance Detail
                $submittedDac = $request->input('dac');
                $i=0;

                //Done = 8
                //Not Done =9
                foreach ($schedule->schedule_details as $dac){
                    AttendanceDetail::create([
                        'attendance_id' => $newAttendance->id,
                        'unit'          => $dac->unit->name,
                        'action'        => $dac->action->description,
                        'status_id'     => $submittedDac[$i]->status
                    ]);
                    $i++;
                }

                //Add to the DAC work
                $message = "Berhasil Check out";
            }

            return Response::json([
                'message'   => $message,
                'model'     => ''
            ]);
        }
        catch (\Exception $ex){
            return Response::json("Maaf terjadi kesalahan!", 500);
        }
    }
}

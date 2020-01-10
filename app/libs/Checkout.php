<?php
/**
 * Created by PhpStorm.
 * User: yanse
 * Date: 14-Sep-17
 * Time: 2:38 PM
 */

namespace App\libs;

use App\Models\Attendance;
use App\Models\AttendanceDetail;
use App\Models\AutoNumber;
use App\Models\Place;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class Checkout
{
    public static function checkoutProcess($employee, $request){
        try{
            $returnData = [
                'status_code'           => 200,
                'desc'                  => "Berhasil Check out",
            ];

            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('status_id', 6)
                ->where('is_done', 0)
                ->first();
            if(empty($attendance)){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tidak ditemukan Jadwal Sesuai",
                ];
                return $returnData;
            }
            $schedule = Schedule::find($attendance->schedule_id);
            $place = Place::find($attendance->place_id);

//            $isPlace = Utilities::checkingQrCode($request->input('qr_code'));
//            if(!$isPlace){
            if($place->qr_code != $request->input('qr_code')){
                $returnData = [
                    'status_code'           => 400,
                    'desc'                  => "Tempat yang discan tidak tepat",
                ];
                return $returnData;
            }

//            if($schedule == null){
//                $returnData = [
//                    'status_code'           => 400,
//                    'desc'                  => "Jadwal Tidak ditemukan",
//                ];
//                return $returnData;
//            }

            //Check if Check in or Check out
            //Check in  = 1
            //Check out = 2
            if(!$request->filled('schedule_details')){
                $returnData = [
                    'status_code'           => 500,
                    'desc'                  => "Tidak ada data Dac yang diterima",
                ];
                return $returnData;
            }

            $newAttendance = Attendance::create([
                'employee_id'   => $employee->id,
                'schedule_id'   => $schedule->id,
                'place_id'      => $place->id,
                'date'          => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                'is_done'       => 1,
                'notes'         => $request->input('notes'),
                'status_id'     => 7
            ]);
            $attendance->is_done = 1;
            $attendance->save();

            //Create Attendance Detail
            $submittedDac = $request->input('schedule_details');
            $i=0;

            //Done = 8
            //Not Done =9
//            $scheduleDetails = ScheduleDetail::where('schedule_id', $schedule->id)->get();
            foreach ($submittedDac as $dac){

                AttendanceDetail::create([
                    'attendance_id' => $newAttendance->id,
                    'unit'          => $dac['object_name'],
                    'action'        => $dac['action_name'],
                    'status_id'     => $dac['status'],
                    'created_at'    => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                ]);
                $i++;
            }

            return $returnData;
        }
        catch (\Exception $ex){
            Log::error('libs/Checkout/checkoutProcess - checkoutProcess error EX: '. $ex);

            $returnData = [
                'status_code'           => 500,
                'desc'                  => "Maaf terjadi kesalahan",
            ];
            return $returnData;
        }

    }
}

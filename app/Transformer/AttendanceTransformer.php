<?php


namespace App\Transformer;


use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class AttendanceTransformer extends TransformerAbstract
{
    public function transform(AttendanceAbsent $attendance){

        try{
            $createdDate = Carbon::parse($attendance->created_at)->toIso8601String();
            $date = Carbon::parse($attendance->date)->toIso8601String();


            if(!empty($attendance->image_path)){
                $imageURL = asset('storage/attendances/'.$attendance->image_path);
                $imgPath = "<img src='".$imageURL."' width='40' loading='lazy'>";
            }
            else{
                $imgPath = "-";
            }

            $status = Status::find($attendance->status_id);
            $description = "";
            if($attendance->type == "SR"){
                $description = "Ijin Sakit";
            }
            elseif ($attendance->type == "S"){
                $description = "Ijin Sakit Belum Approve";
            }
            elseif ($attendance->type == "IR"){
                $description = "Ijin";
            }
            elseif ($attendance->type == "I"){
                $description = "Ijin Belum Approve";
            }

            return[
                'first_name'        => $attendance->employee->first_name,
//                'last_name'        => $attendance->employee->last_name,
                'employee_code'        => $attendance->employee->code,
                'date'               => $date,
                'status'             => $status->id==6 ? 'ABSEN MASUK' : 'ABSEN KELUAR',
                'image_path'         => $imgPath,
                'created_at'         => $createdDate,
                'description'         => $description,
//                'action'             => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("CheckinTransformer > transform ".$exception);
        }
    }
}

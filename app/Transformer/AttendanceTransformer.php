<?php


namespace App\Transformer;


use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class AttendanceTransformer extends TransformerAbstract
{
    public function transform(Attendance $attendance){

        try{
            $createdDate = Carbon::parse($attendance->created_at)->toIso8601String();
            $date = Carbon::parse($attendance->date)->toIso8601String();

            $routeShowUrl = route('admin.employee.show', ['id' => $attendance->id]);
            $routeEditUrl = route('admin.employee.edit', ['id' => $attendance->id]);
            $routeDetailUrl = route('admin.employee.detail-attendance', ['id' => $attendance->id]);


            $code = "<a href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'>". $attendance->code. "</a>";
            
            if(!empty($attendance->image_path)){
                $imageURL = asset('storage/checkins/'.$attendance->image_path);
                $imgPath = "<img src='".$imageURL."' width='50'>";
            }
            else{
                $imgPath = "-";
            }
            $imageURL = asset('storage/checkins/'.$attendance->image_path);
            $action = "<a class='btn btn-xs btn-info' href='".$routeShowUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-info'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-success' href='".$routeDetailUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-child'></i></a>";
            $action .= "&nbsp;<a class='btn btn-xs btn-primary' href='".$routeEditUrl."' data-toggle='tooltip' data-placement='top'><i class='fas fa-pencil-alt'></i></a>";

            // $phones = '-';
            // if(!empty($employee->telephone)){
            //     $phones = $employee->telephone;

            //     if(!empty($employee->phone)){
            //         $phones .= '/'. $employee->phone;
            //     }
            // }
            // else{
            //     if(!empty($employee->phone)){
            //         $phones = $employee->phone;
            //     }
            // }

            return[
                'employee'        => $attendance->employee->first_name." ".$attendance->employee->last_name,
                'schedule_id'        => $attendance->schedule_id,
                'place_name'           => $attendance->place->name,
                'date'               => $date,
                'status'             => $attendance->status->description,
                'image_path'         => $imgPath,
                'created_at'         => $createdDate,
                'action'             => $action
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("AttendanceTransformer.php > transform ".$exception);
        }
    }
}

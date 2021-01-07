<?php


namespace App\Transformer;


use App\Models\Attendance;
use App\Models\AttendanceAbsent;
use App\Models\ImeiHistory;
use App\Models\Status;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;

class ImeiTransformer extends TransformerAbstract
{
    public function transform(ImeiHistory $imeiHistory){

        try{
            $createdDate = Carbon::parse($imeiHistory->created_at)->toIso8601String();
            $date = Carbon::parse($imeiHistory->date)->toIso8601String();

            return[
                'nuc'               => $imeiHistory->nuc,
                'first_name'        => $imeiHistory->employee->first_name,
                'date'              => $date,
                'phone_type_old'    => $imeiHistory->phone_type_old,
                'phone_type_new'    => $imeiHistory->phone_type_new,
                'created_at'        => $createdDate,
            ];
        }
        catch (\Exception $exception){
            error_log($exception);
            Log::error("ImeiTransformer > transform ".$exception);
        }
    }
}

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
use App\Models\Complaint;
use App\Models\ComplaintFinish;
use App\Models\ComplaintFinishImage;
use App\Models\ComplaintReject;
use App\Models\ComplaintRejectImage;
use App\Models\Employee;
use App\Models\EmployeeRole;
use App\Models\EmployeeSchedule;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;


class ComplaintDetailFunc
{
    public static function getComplaintDetailFunc($complaint_id){
        try{
            $complaint =  Complaint::where('id', $complaint_id)->first();

            //get complaint header, reject, finish
            $complaintImageDBs = $complaint->complaint_header_images;
            $complaintImages = collect();
            foreach ($complaintImageDBs as $complaintImageDB){
                $complaintImage = asset('storage/complaints/'. $complaintImageDB->image);
                $complaintImages->push($complaintImage);
            }

            //get complaint reject
            $complaintResponses = collect();
            $complaintRejects = ComplaintReject::where('complaint_id', $complaint_id)
                ->orderBy('created_at')
                ->get();
            if(count($complaintRejects) > 0){
                foreach($complaintRejects as $complaintReject){
                    $messageImage = collect();
                    $rejectDetails = ComplaintRejectImage::where('complaint_id', $complaint_id)
                        ->where('complaint_reject_id', $complaintReject->id)
                        ->get();
                    foreach ($rejectDetails as $rejectDetail){
                        $detailImage = empty($rejectDetail->image) ? null : asset('storage/complaints/'. $rejectDetail->image);
                        $messageImage->push($detailImage);
                    }
//                    $messageImage = empty($complaintRejects->image) ? null : asset('storage/complaints/'. $complaintRejects->image);
                    $complaintRejectModel = ([
                        'status'            => 9,
                        'message'           => $complaintReject->message,
                        'images'             => $messageImage,
                        'date'              => Carbon::parse($complaintReject->created_at)->format('d M Y H:i:s')
                    ]);
                    $complaintResponses->push($complaintRejectModel);
                }
            }

            $complaintfinishs = ComplaintFinish::where('complaint_id', $complaint_id)
                ->orderBy('created_at')
                ->get();
            if(count($complaintfinishs) > 0){
                foreach($complaintfinishs as $complaintFinish){
                    $messageImage = collect();
                    $finishDetails = ComplaintFinishImage::where('complaint_id', $complaint_id)
                        ->where('complaint_finish_id', $complaintFinish->id)
                        ->get();
                    foreach ($finishDetails as $finishDetail){
                        $detailImage = empty($finishDetail->image) ? null : asset('storage/complaints/'. $finishDetail->image);
                        $messageImage->push($detailImage);
                    }
//                    $messageImage = empty($complaintFinish->image) ? null : asset('storage/complaints/'. $complaintFinish->image);
                    $complaintRejectModel = ([
                        'status'            => 8,
                        'message'           => $complaintFinish->message,
                        'images'             => $messageImage,
                        'date'              => Carbon::parse($complaintFinish->created_at)->format('d M Y H:i:s')
                    ]);
                    $complaintResponses->push($complaintRejectModel);
                }
            }
            $complaintResponsesSorted = $complaintResponses;
            if($complaintResponses->count() > 0){
                $complaintResponsesSorted = collect();
                $complaintSorted = $complaintResponses->sortBy('date', SORT_NATURAL);
                foreach($complaintSorted as $complaintSort){
                    $complaintResponsesSorted->push($complaintSort);
                }
            }
            //get last employee reply
//            $lastComplaintDetail = ComplaintDetail::where('complaint_id', $complaint->id)
//                ->where('employee_id', "!=", null)
//                ->orderBy('created_at')
//                ->first();
            $lastComplaintDetailRole = "";
            if(!empty($complaint->employee_handler_id)){
                $lastComplaintDetail = Employee::where('id', $complaint->employee_handler_id)->first();
                $lastComplaintDetailRole = $lastComplaintDetail->employee_role->name;
            }
            $currentHandlerRole = "";
            if(!empty($complaint->employee_handler_role_id)){
                $currentRole = EmployeeRole::where('id', $complaint->employee_handler_role_id)->first();
                $currentHandlerRole = $currentRole->description;
            }

            //get project's location (from project_objects tables)
            $locationModels = collect();
            $locationDB = ProjectObject::where('project_id', $complaint->project_id)
                ->where('status_id', 1)
                ->get();
            if(count($locationDB) > 0){
                foreach ($locationDB as $location){
                    $locationModel = $location->place_name. " - ".$location->unit_name;
                    $locationModels->push($locationModel);
                }
            }
            $customerId = 0;
            $customerAvatar = "";
            $customerName = "";
            $employeeId = 0;
            $employeeAvatar = "";
            $employeeName = "";
            if(!empty($complaint->customer_id)){
                $customerId = $complaint->customer_id;
                $customerAvatar = asset('storage/customers/'. $complaint->customer->image_path);
                $customerName = $complaint->customer->name;
            }
            else{
                $employeeId = $complaint->employee_id;
                $employeeAvatar = asset('storage/employees/'. $complaint->employee->image_path);
                $employeeName = $complaint->employee->first_name." ".$complaint->employee->last_name;
            }

            $complaintModel = collect([
                'id'                    => $complaint->id,
                'project_id'            => $complaint->project_id,
                'project_name'          => $complaint->project->name,
                'code'                  => $complaint->code,

                'customer_id'           => $customerId,
                'customer_name'         => $customerName,
                'customer_avatar'       => $customerAvatar,
                'employee_id'           => $employeeId,
                'employee_name'         => $employeeName,
                'employee_avatar'       => $employeeAvatar,

                'employee_handler_id'       => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : 0 ,
                'employee_handler_name'     => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                'employee_handler_role'     => $lastComplaintDetailRole,
                'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",

                'employee_handler_current_role_name'    => $currentHandlerRole,
                'employee_handler_current_role_id'      => $complaint->employee_handler_role_id,

                'subject'               => $complaint->subject,
                'description'           => !empty($complaint->description) ? $complaint->description : "",
                'date'                  => Carbon::parse($complaint->date, 'Asia/Jakarta')->format('d m Y H:i:s'),
                'status_id'             => $complaint->status_id,
                'images'                => $complaintImages,
                'response_models'        => $complaintResponsesSorted,
                'locations'             => $locationModels,
                'location'              => $complaint->location,
                'is_rated'              => !empty($complaint->score) ? 1 : 0,
                'rating'                => $complaint->score,
                'rating_message'        => $complaint->score_message,
                'priority'              => $complaint->priority
            ]);
            return $complaintModel;
        }
        catch (\Exception $ex){
            Log::error('Libs/ComplaintDetailFunc - getComplaintDetailFunc error EX: '. $ex);
            return null;
        }
    }
}

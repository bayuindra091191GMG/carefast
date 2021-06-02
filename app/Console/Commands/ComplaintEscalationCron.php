<?php

namespace App\Console\Commands;

use App\libs\ComplaintDetailFunc;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\ProjectEmployee;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ComplaintEscalationCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'complaint_escalation:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try{
            $complaintDBs = Complaint::whereIn('status_id', [10, 11, 9])->get();
//            $complaintDBs = Complaint::where('status_id', 10)->get();

            foreach($complaintDBs as $complaintDB){
                $temp = Carbon::now('Asia/Jakarta');
                $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

                //if pending status do escalation
                if($complaintDB->status_id == 10){
                    $trxDate = Carbon::parse(date_format($complaintDB->response_limit_date, 'j-F-Y H:i:s'));
                    // mencari perbedaan menit
                    //$intervalMinute = $trxDate->diffInMinutes($now);

//                    if($complaintDB->project_id == 1){
//                        $trueOrFalse = "false";
//                        $isValid1 =  $trxDate->gt($now);
//                        if($isValid1) $trueOrFalse= "true";
//                        Log::channel('cronjob')
//                            ->info("Test complaint 1 now = ".
//                                $now." | trxDate = ".$trxDate." | check validation 1 (formated) = ".$trueOrFalse);
//                    }
//                Log::channel('cronjob')
//                    ->info("complaint Number = ".$complaintDB->code." | now = ".$now." | trxDate = ".$trxDate." | intervalMinute = ".$intervalMinute);

                    if($now->gt($trxDate)){
                        Log::channel('cronjob')
                            ->info("complaint escalation | complaint id= ".$complaintDB->id. " | now = ".
                                $now." | trxDate = ".$trxDate);
                        $employeeDB = ProjectEmployee::where('project_id', $complaintDB->project_id)
                            ->where('employee_roles_id', '>', $complaintDB->employee_handler_role_id)
                            ->orderBy('employee_roles_id', 'asc')
                            ->first();
                        if(!empty($employeeDB)){
                            //mencari waktu eskalasi
//                            $minute = 30;
//                            if($employeeDB->employee_roles_id == 5){
//                                $minute = 60;
//                            }
//                            if($employeeDB->employee_roles_id == 6){
//                                $minute = 1260;
//                            }
//                            if($employeeDB->employee_roles_id == 7){
//                                $minute = 30;
//                            }
                            $minute = 1;
                            if($employeeDB->employee_roles_id == 5){
                                $minute = 1;
                            }
                            if($employeeDB->employee_roles_id == 6){
                                $minute = 1;
                            }
                            if($employeeDB->employee_roles_id == 7){
                                $minute = 1;
                            }

                            // update setelah pindah role
                            if(!empty($employeeDB)){
                                $complaintDB->employee_handler_role_id = $employeeDB->employee_roles_id;
                            }
                            $addedTime = $temp->addMinutes($minute)->toDateTimeString();
                            $complaintDB->response_limit_date = $addedTime;
                            Log::channel('cronjob')
                                ->info("complaint Number = ".$complaintDB->code." | temp = ".$temp.
                                    " | response_limit_date = ".$addedTime);
                            $complaintDB->save();

                            //send notif to employee escalation
                            $lastComplaintDetailRole = "";
                            if(!empty($complaintDB->employee_handler_id)){
                                $lastComplaintDetail = Employee::where('id', $complaintDB->employee_handler_id)->first();
                                $lastComplaintDetailRole = $lastComplaintDetail->employee_role->name;
                            }
                            $messageImage = empty($complaintDB->image) ? null : asset('storage/complaints/'. $complaintDB->image);
                            if(!empty($complaintDB->customer_id)){
                                $customerComplaintDetailModel = ([
                                    'project_id'        => $complaintDB->project_id,
                                    'project_name'      => $complaintDB->project->name,
                                    'customer_id'       => $complaintDB->customer_id,
                                    'customer_name'     => $complaintDB->customer->name,
                                    'customer_avatar'   => asset('storage/customers/'. $complaintDB->customer->image_path),
                                    'employee_id'       => null,
                                    'employee_name'     => "",
                                    'employee_avatar'   => "",

                                    'employee_handler_id'   => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : 0 ,
                                    'employee_handler_name' => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                                    'employee_handler_role' => $lastComplaintDetailRole,
                                    'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",

                                    'subject'           => $complaintDB->subject,
                                    'message'           => $complaintDB->message,
                                    'date'              => Carbon::parse($complaintDB->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                                    'image'             => $messageImage,
                                ]);
                            }
                            else{
                                $customerComplaintDetailModel = ([
                                    'project_id'        => $complaintDB->project_id,
                                    'project_name'      => $complaintDB->project->name,
                                    'customer_id'       => null,
                                    'customer_name'     => "",
                                    'customer_avatar'   => "",
                                    'employee_id'       => $complaintDB->employee_id,
                                    'employee_name'     => $complaintDB->employee->first_name." ".$complaintDB->employee->last_name,
                                    'employee_avatar'   => asset('storage/employees/'. $complaintDB->employee->image_path),

                                    'employee_handler_id'   => !empty($lastComplaintDetail) ? $lastComplaintDetail->id : 0 ,
                                    'employee_handler_name' => !empty($lastComplaintDetail) ? $lastComplaintDetail->first_name." ".$lastComplaintDetail->last_name : "" ,
                                    'employee_handler_role' => $lastComplaintDetailRole,
                                    'employee_handler_avatar'   => !empty($lastComplaintDetail) ? asset('storage/employees/'. $lastComplaintDetail->image_path) : "",

                                    'subject'           => $complaintDB->subject,
                                    'message'           => $complaintDB->message,
                                    'date'              => Carbon::parse($complaintDB->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                                    'image'             => $messageImage,
                                ]);
                            }
                            $title = "ICare";
                            $body = "Customer complain terjadi eskalasi";
                            $data = array(
                                "type_id" => 311,
                                "complaint_id" => $complaintDB->id,
                                "complaint_subject" => $complaintDB->subject,
                                "complaint_detail_model" => ComplaintDetailFunc::getComplaintDetailFunc($complaintDB->id),
                            );
                            //Push Notification to employee App.
                            $ProjectEmployees = ProjectEmployee::where('project_id', $complaintDB->project_id)
                                ->where('employee_roles_id', $complaintDB->employee_handler_role_id)
                                ->get();
                            if($ProjectEmployees->count() >= 0){
                                foreach ($ProjectEmployees as $ProjectEmployee){
                                    $user = \App\Models\User::where('employee_id', $ProjectEmployee->employee_id)->first();
                                    FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                                }
                            }
                        }
                    }
                }
                //else, inform upper role of handler employee
                else {
                    $trxDate = Carbon::parse(date_format($complaintDB->updated_at, 'j-F-Y H:i:s'));
                    if($now->gt($trxDate)){
//                        Log::channel('cronjob')
//                            ->info("complaint pending and process notification | complaint id= ".$complaintDB->id. " | now = ".
//                                $now." | trxDate = ".$trxDate);
                        $employeeDB = ProjectEmployee::where('project_id', $complaintDB->project_id)
                            ->where('employee_roles_id', '>=', $complaintDB->employee_handler_role_id)
                            ->where('employee_roles_id', '<', 8)
                            ->orderBy('employee_roles_id', 'asc')
                            ->first();
                        if(!empty($employeeDB)){
                            //mencari perbedaan waktu
                            $intervalMinute = $trxDate->diffInMinutes($now);
                            if($intervalMinute >= 30){
                                //send notif to upper role employee
                                $title = "ICare";
                                $body = "Komplain Customer belum diselesaikan, tolong ingatkan penanggung jawab komplain";
                                $data = array(
                                    "type_id" => 312,
                                    "complaint_id" => $complaintDB->id,
                                    "complaint_subject" => $complaintDB->subject,
                                    "complaint_detail_model" => ComplaintDetailFunc::getComplaintDetailFunc($complaintDB->id),
                                );
                                //Push Notification to employee App.
                                $ProjectEmployees = ProjectEmployee::where('project_id', $complaintDB->project_id)
                                    ->where('employee_roles_id', $complaintDB->employee_handler_role_id)
                                    ->get();
                                if($ProjectEmployees->count() >= 0){
                                    foreach ($ProjectEmployees as $ProjectEmployee){
                                        $user = \App\Models\User::where('employee_id', $ProjectEmployee->employee_id)->first();
                                        FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                                    }
                                }
                            }

                        }
                    }
                }
            }

            Log::channel('cronjob')->info($temp." = Sukses");
            return "Sukses";
        }
        catch (\Exception $ex){
            Log::channel('cronjob')->error("Cronjob Error : ". $ex);
            return "failed";
        }
    }
}

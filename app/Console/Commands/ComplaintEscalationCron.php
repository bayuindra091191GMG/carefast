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
//            $complaintDBs = Complaint::whereIn('status_id', [10, 11])->get();
            $complaintDBs = Complaint::where('status_id', 10)->get();
            $temp = Carbon::now('Asia/Jakarta');
            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

            foreach($complaintDBs as $complaintDB){
                $trxDate = Carbon::parse(date_format($complaintDB->response_limit_date, 'j-F-Y H:i:s'));
                // mencari perbedaan menit
                $intervalMinute = $trxDate->diffInMinutes($now);

                if($complaintDB->project_id == 1){
                    $trueOrFalse = "false";
//                    $isValid1 =  $trxDate < $now;
                    $isValid1 =  $trxDate->gt($now);
                    if($isValid1) $trueOrFalse= "true";
                    Log::channel('cronjob')
                        ->info("Test complaint 1 now = ".
                            $now." | trxDate = ".$trxDate." | check validation 1 (formated) = ".$trueOrFalse);

                    $trueOrFalse = "false";
//                    $isValid2 =  $complaintDB->response_limit_date < $temp;
                    $isValid2 =  $complaintDB->response_limit_date->gt($temp);
                    if($isValid2)
                    Log::channel('cronjob')
                        ->info("Test complaint 2 complaintDB->response_limit_date = ".
                            $complaintDB->response_limit_date." | temp = ".$temp." | check validation 2 (unformated) = ".$trueOrFalse);
                }
//                Log::channel('cronjob')
//                    ->info("complaint Number = ".$complaintDB->code." | now = ".$now." | trxDate = ".$trxDate." | intervalMinute = ".$intervalMinute);

                //kalau lebih dari x jam lewat minimal 1 menit
                if($intervalMinute >= 1){
                    $employeeDB = ProjectEmployee::where('project_id', $complaintDB->project_id)
                        ->where('employee_roles_id', '>', $complaintDB->employee_handler_role_id)
                        ->orderBy('employee_roles_id', 'asc')
                        ->first();
                    if(!empty($employeeDB)){
                        //mencari waktu eskalasi
                        $minute = 30;
                        if($employeeDB->employee_roles_id >= 7){
                            $minute = 60;
                        }
                        if($employeeDB->employee_roles_id = 8){
                            $minute = 300;
                        }

                        // update setelah pindah role
                        if(!empty($employeeDB)){
                            $complaintDB->employee_handler_role_id = $employeeDB->employee_roles_id;
                        }
                        $complaintDB->response_limit_date = $temp->addMinutes($minute)->toDateTimeString();
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
                            "type_id" => 300,
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


            Log::channel('cronjob')->info($temp." = Sukses");
            return "Sukses";
        }
        catch (\Exception $ex){
            Log::channel('cronjob')->error("Cronjob Error : ". $ex);
            return "failed";
        }
    }
}

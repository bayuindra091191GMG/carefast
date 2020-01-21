<?php

namespace App\Console\Commands;

use App\Models\Complaint;
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
            $complaintDBs = Complaint::where('status_id', 10)->get();
            $temp = Carbon::now('Asia/Jakarta');
            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

            foreach($complaintDBs as $complaintDB){
                $trxDate = Carbon::parse(date_format($complaintDB->response_limit_date, 'j-F-Y H:i:s'));
                $intervalMinute = $now->diffInMinutes($trxDate);

                //kalau lebih dari x jam lewat minimal 1 menit
                if($intervalMinute >= 1){
                    $employeeDB = ProjectEmployee::where('project_id', $complaintDB->project_id)
                        ->where('employee_roles_id', '>', $complaintDB->employee_handler_role_id)
                        ->orderBy('employee_roles_id', 'asc')
                        ->first();
                    if(!empty($employeeDB)){
                        $complaintDB->employee_handler_role_id = $employeeDB->employee_roles_id;
                    }
                    $complaintDB->response_limit_date = Carbon::now('Asia/Jakarta')->addHours(6)->toDateTimeString();

                    $complaintDB->save();

                    //send notif to employee escalation
                    $messageImage = empty($complaintDB->image) ? null : asset('storage/complaints/'. $complaintDB->image);
                    $customerComplaintDetailModel = ([
                        'customer_id'       => $complaintDB->customer_id,
                        'customer_name'     => $complaintDB->customer->name,
                        'customer_avatar'    => asset('storage/customers/'. $complaintDB->customer->image_path),
                        'employee_id'       => null,
                        'employee_name'     => "",
                        'employee_avatar'    => "",
                        'message'           => $complaintDB->message,
                        'image'             => $messageImage,
                        'date'              => Carbon::parse($complaintDB->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                    ]);
                    $title = "ICare";
                    $body = "Customer complaint terjadi eskalasi";
                    $data = array(
                        "type_id" => 301,
                        "complaint_id" => $complaintDB->id,
                        "complaint_detail_model" => $customerComplaintDetailModel,
                    );
                    //Push Notification to employee App.
                    $ProjectEmployees = ProjectEmployee::where('project_id', $complaintDB->project_id)
                        ->where('employee_roles_id', '!=', 1)
                        ->where('employee_roles_id', '<=', $complaintDB->employee_handler_role_id)
                        ->get();
                    if($ProjectEmployees->count() >= 0){
                        foreach ($ProjectEmployees as $ProjectEmployee){
                            $user = \App\Models\User::where('employee_id', $ProjectEmployee->employee_id)->first();
                            FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                        }
                    }
                }
            }

            return "Sukses";
        }
        catch (\Exception $ex){
            Log::channel('cronjob')->error($ex);
        }
    }
}
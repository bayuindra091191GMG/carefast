<?php

namespace App\Http\Controllers\Frontend;

use App\Imports\CustomerImport;
use App\Imports\DacImport;
use App\Imports\InitialDataImport;
use App\Imports\ProjectEmployeeImport;
use App\libs\Utilities;
use App\Mail\EmailVerification;
use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\ComplaintDetail;
use App\Models\ComplaintHeaderImage;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Place;
use App\Models\Project;
use App\Models\ProjectActivitiesDetail;
use App\Models\ProjectActivitiesHeader;
use App\Models\ProjectActivity;
use App\Models\ProjectEmployee;
use App\Models\ProjectObject;
use App\Models\Schedule;
use App\Models\ScheduleDetail;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
//        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        return redirect()->route('admin.dashboard');
        return view('frontend.home');
    }

    public function complaintEscalation(){
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
                            $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                            FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);
                        }
                    }
                }
            }

            return "Sukses";
        }
        catch (\Exception $ex){
            Log::error('Frontend/HomeController - complaintEscalation error EX: '. $ex);
            return "Gagal";
        }
    }

    public function form()
    {
        return view('admin.import.form');
    }

    public function importExcel(Request $request){
        try{
//            dd($request);
            $excel = request()->file('excel');
//            Excel::import(new InitialDataImport(), $excel);
//            Excel::import(new CustomerImport(), $excel);
            Excel::import(new DacImport(), $excel);
//            Excel::import(new ProjectUserImport(), $excel);

            Session::flash('success', 'Berhasil Import Data!');
            return redirect(route('admin.import.form'));
        }
        catch (\Exception $ex){
            dd($ex);
            Log::error('Frontend/HomeController - importExcel error EX: '. $ex);

            Session::flash('success', 'Gagal Import Data!');
            return redirect(route('admin.import.form'));
        }
    }

    public function testNotif(){
        return view('admin.test-notif');
    }
    public function testNotifSend(){
        //send notification beta
        $name = "yansen";
        $phone = "1111111111";
        $category = "Sampah Plastik";
        $weight = 10;

        //send notification
        $title = "Digital Waste Solution";
        $body = "Transaksi Baru dari kategori ".$category." seberat ".$weight." kilogram";
        $data = array(
            'category' => $category,
            'name' => $name,
            'weight' => $weight,
            'phone' => $phone,
            'point' => $weight*10,
        );
        $isSuccess = FCMNotification::SendNotification(3, 'browser', $title, $body, $data);

//        $title = "Digital Waste Solution";
//        $body = "Transaksi Baru";
//        $data = array(
//            'category' => "testing Category",
//            'item' => "Testing Item",
//            'weight' => "10",
//            'point' => 10*10,
//        );
//        dd($data);
//        $isSuccess = FCMNotification::SendNotification(8, 'apps', $title, $body, $data);
//        $isSuccess = FCMNotification::SendNotification(1, 'browser', $title, $body, $data);

        return $isSuccess;
    }
    public function testNotifSendToAndroid(){
        $newComplaint = Complaint::where('project_id', 1)
            ->orderBy('date', 'desc')
            ->first();
        $newComplaintDetail = ComplaintDetail::where('complaint_id', $newComplaint->id)
            ->whereNotNull('customer_id')
            ->orderBy('created_at', 'desc')
            ->first();
        $employeeDB = ProjectEmployee::where('project_id', $newComplaint->project_id)
            ->where('employee_roles_id', '>', 1)
            ->orderBy('employee_roles_id', 'asc')
            ->first();
        $type = 1;

        switch ($type){
            case 1:
                $complaintheaderImage = ComplaintHeaderImage::where('complaint_id', $newComplaint->id)->first();
                $messageImage = empty($complaintheaderImage) ? null : asset('storage/complaints/'. $complaintheaderImage->image);
//            $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);

                $customerComplaintDetailModel = ([
                    'customer_id'       => $newComplaintDetail->customer_id,
                    'customer_name'     => $newComplaintDetail->customer->name,
                    'customer_avatar'    => asset('storage/customers/'. $newComplaintDetail->customer->image_path),
                    'employee_id'       => null,
                    'employee_name'     => "",
                    'employee_avatar'    => "",
                    'message'           => $newComplaintDetail->message,
                    'image'             => $messageImage,
                    'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                ]);
                //Send notification to
                //Employee
                $title = "ICare";
                $body = "Customer membuat complaint baru";
                $notifData = array(
                    "type_id" => 301,
                    "complaint_id" => $newComplaint->id,
                    "complaint_subject" => $newComplaint->subject,
                    "complaint_detail_model" => $customerComplaintDetailModel,
                );
                //Push Notification to employee App.
                $ProjectEmployees = ProjectEmployee::where('project_id', $newComplaint->project_id)
                    ->where('employee_roles_id', $employeeDB->employee_roles_id)
                    ->get();
                if($ProjectEmployees->count() >= 0){
                    foreach ($ProjectEmployees as $ProjectEmployee){
                        $user = User::where('employee_id', $ProjectEmployee->employee_id)->first();
                        $isSuccess = FCMNotification::SendNotification($user->id, 'user', $title, $body, $notifData);
                    }
                }
                return $isSuccess;
                break;
            case 2:
                $messageImage = empty($newComplaintDetail->image) ? null : asset('storage/complaints/'. $newComplaintDetail->image);
                $employeeComplaintDetailModel = ([
                    'customer_id'       => null,
                    'customer_name'     => "",
                    'customer_avatar'    => "",
                    'employee_id'       => $newComplaintDetail->employee_id,
                    'employee_name'     => $newComplaintDetail->employee->first_name." ".$newComplaintDetail->employee->last_name,
                    'employee_avatar'    => asset('storage/employees/'. $newComplaintDetail->employee->image_path),
                    'message'           => $newComplaintDetail->message,
                    'image'             => $messageImage,
                    'date'              => Carbon::parse($newComplaintDetail->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                ]);

                //Send notification to
                //Customer
                $title = "ICare";
                $body = "Employee reply complaint ".$newComplaint->subject;
                $data = array(
                    "type_id" => 302,
                    "complaint_id" => $newComplaint->id,
                    "complaint_detail_model" => $employeeComplaintDetailModel,
                );
                $isSuccess = "false";
                //Push Notification to customer App.
                if(!empty($newComplaint->customer_id)){
                    $isSuccess = FCMNotification::SendNotification($newComplaint->customer_id, 'customer', $title, $body, $data);
                }
                return $isSuccess;
                break;
        }
    }
    public function testEmail(){
        try{
            $projectActivitiesGroups = DB::table('project_activities')
                ->groupBy('plotting_name')
                ->groupBy('place_id')
                ->get();
            foreach ($projectActivitiesGroups as $projectActivitiesGroup){

                $projectActivityHeader = ProjectActivitiesHeader::create([
                    'project_id'            => $projectActivitiesGroup->project_id,
                    'plotting_name'         => $projectActivitiesGroup->plotting_name,
                    'place_id'              => $projectActivitiesGroup->place_id,
                    'created_at'            => $projectActivitiesGroup->created_at,
                    'created_by'            => $projectActivitiesGroup->created_by,
                    'updated_at'            => $projectActivitiesGroup->updated_at,
                    'updated_by'            => $projectActivitiesGroup->updated_by,
                ]);
                $projectActivityDetails = ProjectActivity::where('project_id', 1)
                    ->where("plotting_name", $projectActivitiesGroup->plotting_name)
                    ->where("place_id", $projectActivitiesGroup->place_id)
                    ->get();
                foreach ($projectActivityDetails as $projectActivityDetail){
                    $projectActivityDetail = ProjectActivitiesDetail::create([
                        'activities_header_id'  => $projectActivityHeader->id,
                        'action_id'             => $projectActivityDetail->action_id,
                        'shift_type'            => $projectActivityDetail->shift_type,
                        'weeks'                 => $projectActivityDetail->weeks,
                        'days'                  => $projectActivityDetail->days,
                        'start'                 => $projectActivityDetail->start,
                        'finish'                => $projectActivityDetail->finish,
                        'period_type'           => $projectActivityDetail->period_type,
                        'created_at'            => $projectActivityDetail->created_at,
                        'created_by'            => $projectActivityDetail->created_by,
                        'updated_at'            => $projectActivityDetail->updated_at,
                        'updated_by'            => $projectActivityDetail->updated_by,
                    ]);
                }
            }
            dd("asdf");

            $exitCode = Artisan::call('cache:clear');
            $exitCode2 = Artisan::call('config:clear');

            $user = User::find('9');
            $emailVerify = new EmailVerification($user, '');
            //dd($user);
            Mail::to($user->email)->send($emailVerify);
            return true;
        }
        catch(\Exception $ex){
            return $ex;
        }
    }
    public function generalFunction(){
        try{
//            $places = Place::all();
//
//            foreach($places as $place){
//                if(empty($place->qr_code)){
//                    $number = Utilities::generateBarcodeNumber();
//                    $place->qr_code = $number;
//                    $place->save();
//                }
//            }
//            return "yes";

            $plottings = ProjectActivitiesHeader::whereIn('project_id', [119, 80, 45, 275, 299, 14, 313, 65, 10])->get();
            foreach ($plottings as $plotting){
                $projectActivity = ProjectActivitiesHeader::find($plotting);

                $projectEmployees = ProjectEmployee::where('project_id', $plotting->id)
                ->where('employee_roles_id', 1)
                ->get();


                foreach ($projectActivity->project_activities_details as $projectDetail){
                    $existSchedule = Schedule::where('project_activity_id', $projectActivity->id)
                        ->where('project_id', $projectActivity->project_id)
                        ->where('place_id', $projectActivity->place_id)
                        ->first();
                        if(empty($existSchedule)){
                            $schedule = Schedule::create([
                                'project_id'            => $projectActivity->project_id,
                                'employee_id'           => $employeeId,
                                'project_activity_id'   => $projectActivity->id,
                                'project_employee_id'   => $projectEmployeeCso->id,
                                'shift_type'            => $projectDetail->shift_type,
                                'place_id'              => $projectActivity->place_id,
                                'weeks'                 => $projectDetail->weeks,
                                'days'                  => $projectDetail->days,
    //                                'start'                 => $projectDetail->start,
    //                                'finish'                => $projectDetail->finish,
                                'status_id'             => 1,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => 1,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => 1,
                            ]);
                        $projectActivityDetails = ProjectActivitiesDetail::where("activities_header_id", $plotting)->get();
                        foreach ($projectActivityDetails as $projectActivityDetail){
                            $scheduleDetail = ScheduleDetail::create([
                                'schedule_id'           => $schedule->id,
                                'action_id'             => $projectActivityDetail->action_id,
                                'start'                 => $projectActivityDetail->start,
                                'finish'                => $projectActivityDetail->finish,
                                'created_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'created_by'            => 1,
                                'updated_at'            => Carbon::now('Asia/Jakarta')->toDateTimeString(),
                                'updated_by'            => 1,
                            ]);
                        }
                    }
                }
            }

//            $ids = [6033, 6034, 6035];
//            $ids = [6013, 6017, 6022, 6024, 6025,
//                6028, 6030, 6031, 6033, 6034, 6035];
//            $emails = ["heriekaputra.care@gmail.com",
//                "eddy_efendy@yahoo.com",
//                "yongki@carefast.co.id",
//                "saryanto@carefast.co.id",
//                "Sahattn76@yahoo.co.id",
//                "suprayitno@carefast.co.id",
//                "zulkahar@carefast.co.id",
//                "charles.adrian@carefast.co.id",
//                "sigit@carefast.co.id",
//                "anto@carefast.co.id",
//                "charles.iskandar@carefast.co.id"
//            ];
            $ids = [6026];
            $emails = ["amy@carefast.co.id"];

            $dateTimeNow = Carbon::now('Asia/Jakarta')->toDateTimeString();
            $projectEmployees = ProjectEmployee::where('employee_id', 6031)->get();
            for($i=0; $i<1; $i++){
                $employee = Employee::find($ids[$i]);

                $customer = Customer::create([
                    'name'              => $employee->first_name." ".$employee->last_name,
                    'category_id'       => 2,
                    'email'             => $emails[$i],
                    'image_path'         => '1_photo_20191007021015.png',
                    'phone'             => $employee->phone,
                    'status_id'         => 1,
                    'password'          => Hash::make('carefastid'),
                    'created_at'        => $dateTimeNow,
                    'updated_at'        => $dateTimeNow,
                ]);
                $customerID = $customer->id;

                foreach ($projectEmployees as $projectEmployee){
                    $project = Project::find($projectEmployee->project_id);
                    $projectCustomer = $project->customer_id.'#'.$customerID;
                    $project->customer_id = $projectCustomer;
                    $project->save();
                }
            }
            return "yes";

            $employee = Employee::find(9);
            $date = Carbon::now('Asia/Jakarta');
            $time = $date->format('H:i');
            $todayWeekOfMonth = $date->weekOfMonth;
            // dayOfWeekIso returns a number between 1 (monday) and 7 (sunday)
            $todayOfWeek = $date->dayOfWeekIso;
            $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)->first();

//            dd($projectEmployee->project_id, $projectEmployee->id, $time);
            $schedule = Schedule::where('project_id', $projectEmployee->project_id)
                ->where('project_employee_id', $projectEmployee->id)
                ->where('weeks', 'like', '%'.$todayWeekOfMonth.'%')
                ->where('days', 'like', '%'.$todayOfWeek.'%')
                ->whereTime('start', '<=', $time)
                ->whereTime('finish', '>=', $time)
                ->first();
            dd($schedule, $projectEmployee->project_id, $projectEmployee->id,$todayWeekOfMonth,$todayOfWeek, $time);
            //checking checkin with attendance
            $attendance = Attendance::where('employee_id', $employee->id)
                ->where('schedule_id', $schedule->id)
                ->first();
            dd($attendance);
        }
        catch(\Exception $ex){
            dd($ex);
        }
    }
    public function getLocation(){
        dd(\Request::ip());
        $asdf = geoip($ip = \Request::ip());
        dd($asdf);
    }

    public function getProvince(){
        $uri = 'https://api.rajaongkir.com/starter/province';
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', 'https://api.rajaongkir.com/starter/province',[
            'query' => ['key' => '49c2d8cab7d32fa5222c6355a07834d4']
        ]);
        $response = $response->getBody()->getContents();
        $currency = (array)json_decode($response);

        return $currency;
    }
}

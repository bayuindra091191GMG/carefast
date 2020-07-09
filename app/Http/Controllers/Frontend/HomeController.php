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
use App\Models\AttendanceAbsent;
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
use App\Models\TempInsysEmploye;
use App\Models\TempInsysProject;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
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

//            $data = json_encode(['Element 1','Element 2','Element 3','Element 4','Element 5']);
//            $attenDBs = AttendanceAbsent::all();
//            foreach($attenDBs as $attenDB){
//                $data =
//            }
            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            return $now;

            $data = "a\tini pake slash t\n";
            $data .= "a&nbsp;&nbsp;&nbsp;&nbsp;ini pake nbsp 4 kali\n";
            $data .= "a&nbsp;ini pake nbsp sekali\n";
            $data .= "a&ensp;ini pake ensp\n";
            $data .= "a&emsp;ini pake emsp\n";

            $file = time() .rand(). '_file.txt';
            $destinationPath=public_path()."/upload/";
            if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
            File::put($destinationPath.$file, $data);
            return response()->download($destinationPath.$file);

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

            $projectEmployeeOthers = ProjectEmployee::where('employee_roles_id', null)
                ->get();
//            dd($projectEmployeeOthers);
            foreach ($projectEmployeeOthers as $projectEmployeeOther){
                dd($projectEmployeeOther, $projectEmployeeOther->employee->employee_role_id);
                $projectEmployeeOther->employee_roles_id = $projectEmployeeOther->employee->employee_role_id;
                $projectEmployeeOther->save();
            }
            return "yesy";

            $projectEmployeeDBs = ProjectEmployee::all();
            foreach ($projectEmployeeDBs as $projectEmployeeDB){
                $projectEmployeeOthers = ProjectEmployee::where('project_id', $projectEmployeeDB->project_id)
                    ->where('employee_id', $projectEmployeeDB->employee_id)
                    ->where('employee_roles_id', $projectEmployeeDB->employee_roles_id)
                    ->where('id', "!=" , $projectEmployeeDB->id)
                    ->get();
                if(count($projectEmployeeOthers) > 0){
//                    dd($projectEmployeeDB, $projectEmployeeOthers);
                    foreach ($projectEmployeeOthers as $projectEmployeeOther){
                        $projectEmployeeOther->delete();
                    }
                }
            }
            return "yes";



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

    public function submitIntegrationEmployee(){
        try {
            $data = '';
            $employees = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - employees DATA : '.json_encode($employees));
            dd(count($employees));
            foreach ($employees as $employee) {

                try{
                    $tempEmployee = TempInsysEmploye::create([
                        'code' => $employee['code'],
                        'first_name' => $employee['first_name'],
                        'last_name' => $employee['last_name'],
                        'phone' => $employee['phone'],
                        'dob' => $employee['dob'],
                        'nik' => $employee['nik'],
                        'address' => $employee['address'],
                        'role' => $employee['role'],
                    ]);

                    $phone = "12345";
                    if(!empty($employee['phone'])){
                        if($employee['phone'] == "-" || $employee['phone'] == "--" ||
                            $employee['phone'] == " " || $employee['phone'] == "" ||
                            $employee['phone'] == "XXX" || $employee['phone'] == "12345"){
                            $phone = "12345".$tempEmployee->id;
                        }
                        else{
                            $phone = $employee['phone'];
                        }
                    }
                    $employeeChecking = Employee::where('code', $employee['code'])->first();
//                    if (!DB::table('employees')->where('code', $employee['code'])->exists()) {
                    if (empty($employeeChecking)) {
                        $nEmployee = Employee::create([
                            'code' => $employee['code'],
                            'first_name' => $employee['first_name'],
                            'last_name' => $employee['last_name'],
                            'phone' => $phone,
                            'dob' => $employee['dob'],
                            'nik' => $employee['nik'],
                            'address' => $employee['address'],
                            'employee_role_id' => $employee['role'],
                            'status_id' => 1
                        ]);

                        User::create([
                            'employee_id' => $nEmployee->id,
                            'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                            'phone' => $phone,
                            'password' => Hash::make('carefastid')
                        ]);
                    } else {
                        $employeeChecking = Employee::where('code', $employee['code'])->first();
                        $employeeChecking->first_name = $employee['first_name'];
                        $employeeChecking->last_name = $employee['last_name'] ?? "";
                        $employeeChecking->phone = $phone;
                        $employeeChecking->dob = $employee['dob'];
                        $employeeChecking->nik = $employee['nik'];
                        $employeeChecking->employee_role_id = $employee['role'];
                        $employeeChecking->address = $employee['address'] ?? "";
                        $employeeChecking->save();

                        $oUser = User::where('employee_id', $employeeChecking->id)->first();
                        if(empty($oUser)){
                            User::create([
                                'employee_id' => $employeeChecking->id,
                                'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                                'phone' => $phone,
                                'password' => Hash::make('carefastid')
                            ]);
                        }
                        else{
                            $oUser->phone = $phone;
                            $oUser->name = $employee['first_name'] . ' ' . $employee['last_name'];
                            $oUser->save();
                        }
                    }
                }
                catch (\Exception $ex){
                    Log::channel('in_sys')
                        ->error('API/IntegrationController - inside employees error data: '.json_encode($employee));
                    Log::channel('in_sys')->error('API/IntegrationController - inside employees error EX: '. $ex);
                    return Response::json([
                        'message' => $ex
                    ], 500);
                }
            }

            Log::channel('in_sys')
                ->info('API/IntegrationController - employees PROCESS DONE');
            return Response::json([
                'message' => 'Success Updating Employee Data!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - employees error EX: '. $ex);
//            Log::error('API/IntegrationController - employees error EX: '. $ex);
            return Response::json([
                'message' => $ex
            ], 500);
        }
    }

    public function submitIntegrationProject(){

        try {
            $data = '';
            $projects = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - projects DATA : '.json_encode($projects));

            foreach ($projects as $project) {
                //add to temp table

                TempInsysProject::create([
                    'code' => $project['code'],
                    'name' => $project['name'],
                    'phone' => $project['phone'],
                    'address' => $project['address'],
                    'description' => $project['description'],
                    'start_date' => $project['start_date'],
                    'finish_date' => $project['finish_date'],
                ]);

                if (!DB::table('projects')->where('code', $project['code'])->exists()) {
                    Project::create([
                        'code' => $project['code'],
                        'name' => $project['name'],
                        'phone' => $project['phone'] ?? "12345",
                        'address' => $project['address'] ?? " ",
                        'description' => $project['description'] ?? " ",
                        'start_date' => $project['start_date'],
                        'finish_date' => $project['finish_date'],
                        'status_id' => 1,
                        'customer_id'       => '2#4#62#63#64#65#66#67#68#69#70#71#72#73',
                        'latitude'          => '-6.1560448',
                        'longitude'         => '106.79019979999998',
                        'total_manday'      => 10,
                        'total_mp_onduty'   => 10,
                        'total_mp_off'      => 10,
                        'total_manpower'    => 10,
                        'total_manpower_used'=> 0,
                    ]);
                } else {
                    $oProject = Project::where('code', $project['code'])->first();
                    $oProject->name = $project['name'];
                    $oProject->phone = $project['phone'];
                    $oProject->address = $project['address'];
                    $oProject->description = $project['description'];
                    $oProject->start_date = $project['start_date'];
                    $oProject->finish_date = $project['finish_date'];
                    if(strpos($project['description'], "PUTUS KONTRAK") !== false){
                        $oProject->status_id = 2;
                    }
                    $oProject->save();
                }
            }

            Log::channel('in_sys')
                ->info('API/IntegrationController - projects PROCESS DONE');
            return Response::json([
                'message' => 'Success Updating Projects!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - projects error EX: '. $ex);
//            Log::error('API/IntegrationController - projects error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }

    public function submitIntegrationJobAssigment(){
        try{
            $data = '[{"project_code":"01060101","employee_codes":["13000638"]},{"project_code":"01060102","employee_codes":["1000173"]},{"project_code":"01060103","employee_codes":["1002818","1002828","1006256","1014144","1015208","1016171","1016635"]},{"project_code":"01070101","employee_codes":["1001997","1004619","1005764","1006687","1006937","1007330","1007893","1008309","1009083","1009085","1010182","1010549","1012066","1014152","1014684","1014940","1015098","1015317","1016703","1018135","1018535","1018537","1018538","1018541","1018543","1018544","1018545","1018548","1018549","1018842","1019198","1019454","1019898","1020054","1020281","1020344","1020408","1020718","1020719","1020725","1020906","1020923"]},{"project_code":"01070109","employee_codes":["1001412","1001504","1001505","1001895","1001912","1001916","1001924","1002327","1002628","1003085","1004003","1004765","1004964","1005994","1006444","1007120","1007273","1007622","1007896","1007897","1008087","1009175","1009272","1009519","1009590","1009764","1009977","1010631","1011625","1011734","1011774","1012162","1012403","1012625","1012809","1013178","1013230","1013770","1014403","1014917","1015234","1016694","1016871","1017311","1017480","1017690","1018137","1018213","1018214","1018357","1018359","1018362","1018363","1018364","1018365","1018366","1018367","1018368","1018372","1018374","1018376","1018378","1018379","1018380","1018382","1018385","1018387","1018389","1018392","1018395","1018397","1018400","1018401","1018403","1018406","1018407","1018408","1018414","1018416","1018417","1018419","1018426","1018427","1018428","1018430","1018432","1018434","1018435","1018441","1018449","1018453","1018455","1018457","1018462","1018463","1018464","1018469","1018470","1018473","1018474","1018475","1018476","1018477","1018478","1018479","1018480","1018485","1018486","1018487","1018489","1018491","1018492","1018493","1018496","1018499","1018500","1018505","1018510","1018511","1018512","1018513","1018515","1018517","1018518","1018519","1018521","1018524","1018525","1018526","1018843","1018858","1018859","1018864","1018868","1018873","1018874","1018925","1018973","1019094","1019096","1019113","1019194","1019195","1019199","1019201","1019450","1019453","1019520","1019630","1019671","1019674","1019677","1019702","1019705","1019706","1019741","1019742","1019743","1019902","1019903","1019913","1020046","1020049","1020053","1020271","1020275","1020276","1020277","1020280","1020318","1020346","1020399","1020400","1020401","1020402","1020407","1020716","1020720","1020722","1020723","1020724","1020770","1020771","1020795","1020796","1020797","1020907","1020911","1020912","1020914","1020915","1020916","1020917","1020920","1020970"]},{"project_code":"01070110","employee_codes":["1018550","1018551","1018850"]},{"project_code":"01070401","employee_codes":["1009913","1009915","1014199","1014200","1014202","1015101","1016556","1019678"]},{"project_code":"01070601","employee_codes":["5002764","5002765","5002766","5002767","5002769","5002770","5002774","5002777","5002779","5002782","5002783","5002785","5002787","5002789","5002790","5002795","5002797","5002799","5002801","5002804","5002805","5002806","5002811","5002815","5002816","5002817","5002818","5002823","5002894","5002897","5002912","5002917","5003009","5003055","5003056","5003101","5003338","5003518","5003624","5003693","5003703","5003705","5003796","5003819","5003820","5003861","5003889","5003960","5003961","5003998","5004100","5004101","5004102","5004144","5004306","5004450","5004451","5004533","5004534","5004535","5004665","5004687","5004713"]},{"project_code":"01080101","employee_codes":["1000784","1001620","1001649","1001650","1001651","1001653","1001661","1001712","1001749","1002008","1002009","1002321","1003104","1003237","1003550","1004113","1004148","1004602","1004615","1004653","1004987","1005032","1005065","1005232","1005236","1006092","1006490","1006597","1006715","1006725","1006967","1007094","1008817","1009405","1010269","1010270","1010282","1010965","1012285","1012295","1012297","1012368","1013865","1013931","1014485","1014722","1014753","1014894","1015204","1015525","1015686","1016057","1016076","1016077","1016597","1016968","1016969","1017114","1017186","1017188","1017570","1019187","1019592","1019940","1020009","1020479","1020488","1020746"]},{"project_code":"01080102","employee_codes":["1001892","1004049","1004463","1004649","1006205","1006582","1007011","1008041","1012787","1013769","1015664","1015798","1016218","1016535","1019472","1019474","1020730","1021140","1021153"]},{"project_code":"01080103","employee_codes":["1006939","1008156","1008229","1008605","1008721","1008723","1008724","1008726","1008730","1008732","1008733","1008736","1008739","1008741","1008742","1008745","1008748","1008749","1008750","1008754","1008760","1008761","1008763","1008766","1008781","1008784","1008795","1008799","1008801","1008802","1009068","1009352","1010791","1011142","1011458","1011665","1011685","1012017","1012253","1012302","1012650","1012675","1012957","1013029","1013170","1013491","1013493","1013653","1013856","1013857","1013861","1014141","1014385","1014420","1014546","1014643","1014734","1014735","1015127","1015176","1015227","1015585","1015668","1015688","1015689","1015702","1016078","1016094","1016270","1016595","1016800","1017034","1017072","1017458","1017501","1017661","1018163","1018309","1018603","1018824","1018945","1019052","1019082","1019135","1019166","1019565","1019591","1020936","1020978"]},{"project_code":"01080301","employee_codes":["1002425","1002593","1006847","1016251","1017022","1018308","1018341","1018606","1018607","1018608","1018774","1019146","1019426"]},{"project_code":"01080401","employee_codes":["1016771","1018600","1020207","1020548","1020549","1020551","1020552","1020553","1020554","1020555","1020556","1020557","1020558","1020559","1020560","1020561","1020562","1020563","1020564","1020565","1020566","1020567","1020568","1020569","1020570","1020572","1020573","1020574","1020576","1020577","1020578","1020579","1020580","1020581","1020582","1020583","1020584","1020585","1020587","1020588"]},{"project_code":"01080501","employee_codes":["5000000","5000006","5000023","5000040","5000041","5000042","5000043","5000047","5000083","5000085","5000086","5000092","5000150","5000154","5000251","5000431","5000579","5000584","5000586","5000591","5000600","5000663","5000711","5001008","5001060","5001377","5001989","5002178","5002380","5002434","5002549","5002560","5002916","5003111","5003197","5003198","5003366","5004211"]},{"project_code":"01080603","employee_codes":["1011332","1011334","1011343","1011347","1011349","1011350","1011877","1012637","1013092","1013137","1013185","1013186","1013197","1013206","1013215","1013354","1013547","1013863","1014059","1014063","1014065","1014075","1014158","1014159","1014726","1014912","1015129","1015157","1015179","1015187","1015325","1015326","1015328","1015753","1015978","1016030","1016119","1016510","1016657","1017104","1018139","1018192","1018650","1018670","1018703","1018749","1018913","1018914","1018985","1019037","1019071","1019174","1019353","1019483","1019667","1019668","1019689","1019987","1020482","1020979"]},{"project_code":"01080905","employee_codes":["1001511","1010759","1015343","1015344","1015424","1015728","1017101","1017192","1018609","1019562","1019595","1020422","1020423","1020792","1020972","1020973"]},{"project_code":"01090101","employee_codes":["1000038","1000040","1000053","1000232","1004690","1008247","1015864"]},{"project_code":"01090203","employee_codes":["1000048","1001694","1006149","1007776","1015312","1019004"]},{"project_code":"01090306","employee_codes":["1020680","1020681","1020682","1020684","1020685","1020686","1020687","1020688","1020689","1020690","1020891"]},{"project_code":"01090602","employee_codes":["1003901","1011161","1013288","1014038","1015146","1015268","1015269","1015893","1016887","1018915","1019161","1019347","1020741","13000035"]},{"project_code":"01090604","employee_codes":["1008397","1012219","1012397","1013452","1013920","1014472","1015756","1015945","1015979","1016182","1016183","1016200","1016312","1016321","1016673","1016674","1016676","1016792","1016817","1016953","1016971","1017015","1017105","1017119","1017131","1017133","1017134","1017138","1017178","1017307","1017325","1017379","1017608","1017609","1017614","1017631","1017632","1017633","1017646","1018170","1018208","1018275","1018329","1018996","1019008","1019053","1019147","1019601","1019857","1019976","1020030","1020283","1020284","1020447","1020645","1020678","1020895","1021137","1021138","1021141","1021142","1021145","1021146","1021150"]},{"project_code":"01090605","employee_codes":["1005323","1017733"]},{"project_code":"01100101","employee_codes":["1008253","1009635","1011968","1016587","1016696","1017338","1019321","1019381","1019657","1019962","1019983","1020763"]},{"project_code":"011010101","employee_codes":["1008046","1011740","1012473","1015972","1016970","1018223","1018235","1020021"]},{"project_code":"011070102","employee_codes":["1015796","1020201","1020202","1020203","1020204","1020205","1020206","1020208","1020414","1020471","1020751","1020764","1020933"]},{"project_code":"01110101","employee_codes":["1012540"]},{"project_code":"01110201","employee_codes":["1002366","1002389","1002394","1002514","1002515","1002522","1003052","1003057","1003778","4000448","4000864","4000937","4001012","4001174","4001483","4001492","4001498","4001499","4001503","4001507","4001520","4001563","4001627","4001786","4001822","4001835","4002013","4002110","4002124","4002125","4002184","4002188","4002358","4002359","4002425","4002434","4002535","4002550","4002568","4002576","4002577","4002614","4002680","4002697","4002698","4002699","4002701","4002728","4002744","4002745","4002813","4002875","4002876","4002900","4002961","4002971"]},{"project_code":"011140101","employee_codes":["5000038","5000378","5000386","5000420","5000670","5000672","5000777","5000936","5001124","5001750","5001855","5002067","5002143","5002559","5002831","5002832","5003420","5003471","5003513","5003665","5003943","5003987","5003988","5003989","5003990","5004011","5004046","5004147","5004156","5004204","5004212","5004233","5004466","5004702","5004703","5004704","5004705","5004710"]},{"project_code":"011170101","employee_codes":["4001138","4001721","4001950","4002760"]},{"project_code":"011180101","employee_codes":["1018172"]},{"project_code":"01120701","employee_codes":["1011205","1012021","1012386","1014334","1014406","1015663","1016108","1016110","1016737","1016738","1016739","1016740","1016743","1016744","1016745","1016746","1016747","1016748","1016749","1016750","1016751","1016752","1016753","1016754","1016756","1016757","1016758","1016759","1016760","1016761","1016763","1016764","1016765","1016768","1016769","1016770","1016772","1016773","1016774","1016775","1016778","1016779","1016780","1016781","1016804","1016806","1016877","1016965","1017071","1017446","1019476","1019500","1019560","1019981","1020124","1020221","1020395","1020663","1020743","1020952"]},{"project_code":"011210102","employee_codes":["1017744","1018731","1019956"]},{"project_code":"011220101","employee_codes":["5000452","5000559","5000680","5000694","5000784","5001064","5001655","5001861","5002092","5002209","5002245","5002544","5002620","5002671","5002693","5002749","5002850","5002905","5002970","5002994","5003091","5003092","5003181","5003188","5003343","5003486","5003565","5003568","5003626","5003670","5003697","5003708","5003857","5003858","5003859","5003934","5003935","5003965","5003976","5003977","5003978","5003986","5004096","5004126","5004136","5004138","5004161","5004173","5004361","5004362","5004385","5004404","5004431","5004433","5004435","5004492","5004493","5004509","5004510","5004524","5004525","5004660","5004661"]},{"project_code":"011220501","employee_codes":["5000884","5001106","5001311","5001767","5001939","5001941","5002186","5002188","5002189","5002426","5002622","5002714","5002715","5002739","5003067","5003167","5003178","5003480","5003481","5003707","5003905","5003933","5004183","5004296","5004381","5004459","5004505","5004506","5004668","5004695","5004715","5004716"]},{"project_code":"011260101","employee_codes":["1008807","1008814","1009890","1010981","1011570","1012370","1012371","1014212","1015250","1015576","1016185","1018988","1019467","1020700","1021073"]},{"project_code":"01130202","employee_codes":["4002754"]},{"project_code":"01130203","employee_codes":["4001776"]},{"project_code":"01130204","employee_codes":["3000306"]},{"project_code":"01130205","employee_codes":["4002415"]},{"project_code":"01130206","employee_codes":["4000002","4001788","4002486"]},{"project_code":"01130207","employee_codes":["1011174"]},{"project_code":"01130209","employee_codes":["1006426","1009664","1011193","1011200","1015937","1016303","1016647","1016999","1018722"]},{"project_code":"01130210","employee_codes":["1006231","1009343","1012683","1014975","1016730","1016869","1017526","1017605","1019157","1020419"]},{"project_code":"01130211","employee_codes":["5002849","5003149","5003681","5004411","5004412","5004413","5004414","5004724"]},{"project_code":"01130212","employee_codes":["1010465","1011145","1011146","1014902","1015526","1015527","1015744"]},{"project_code":"01130213","employee_codes":["1012428","1020522"]},{"project_code":"01130215","employee_codes":["1020315"]},{"project_code":"01130217","employee_codes":["1011476"]},{"project_code":"01130218","employee_codes":["4002423"]},{"project_code":"01130220","employee_codes":["4001478","4002031","4002506","4002581"]},{"project_code":"01130221","employee_codes":["4001196"]},{"project_code":"01130222","employee_codes":["4002537"]},{"project_code":"01130224","employee_codes":["4002983"]},{"project_code":"011400101","employee_codes":["1004595","1010029","1010042","1010047","1010048","1010049","1010052","1010055","1010061","1010068","1010071","1010078","1010087","1010102","1010351","1010463","1010464","1010530","1011463","1011845","1012303","1012304","1012307","1012309","1012311","1012314","1013188","1013263","1013505","1014242","1014556","1014700","1014724","1015143","1015222","1015383","1015450","1015577","1015578","1015851","1015961","1016066","1016195","1016816","1017010","1017202","1017317","1017331","1017385","1017452","1017502","1018633","1018698","1018738","1018753","1018775","1019086","1019950","1020034","1020139","1020668","1020703","1020776"]},{"project_code":"01140201","employee_codes":["1017590","1017619","1019739"]},{"project_code":"01140401","employee_codes":["1003423","1003772","1007543","1007665","1008421","1008977","1010418","1012258","1012423","1012565","1012813","1013376","1013839","1014130","1015569","1016544","1016560","1016723","1017539","1017711","1017769","1017871","1017876","1018178","1018688","1018695","1018823","1019011","1019012","1019189","1019660","1019686","1020085","1020128","1020131","1020132","1020446","1020790","1020983","1021071","1021148"]},{"project_code":"01140501","employee_codes":["1007084","1008200","1008204","1012236","1012924","1014435","1015484","1015698","1016316","1017364","1018138","1018779","1019185","1020036","1020514","1021149"]},{"project_code":"01140601","employee_codes":["1011434","1011436","1011439","1011800","1012720","1012732","1013318","1014424","1014649","1015690","1016718","1016741","1017084","1017510","1017521","1017577","1017599","1017601","1017611","1017722","1018168","1018207","1018657","1018658","1018816","1019087","1019165","1019335","1019482","1019942","1020017","1020019","1020020","1020086","1020127","1020375","1020413","1021135","1021152"]},{"project_code":"01141203","employee_codes":["1001091","1003324","1004627","1012721","1013631","1014246","1014248","1015048","1015070","1015071","1015743","1015904","1017830","1017833","1017835","1017836","1017838","1017843","1017845","1017847","1017848","1017851","1017853","1017856","1017857","1017858","1017860","1017861","1017862","1017863","1017865","1017866","1017867","1017868","1018150","1018236","1018616","1018784","1018785","1019072","1019547","1019607","1019613","1020035","1020095","1020108","1020368","1020384","1020406","1020411","1020452","1020732","1020774","1020785"]},{"project_code":"01141204","employee_codes":["1012131","1013319","1013794","1015464","1017469","1017872","1017878","1017880","1017887","1017888","1017889","1017891","1017892","1017893","1017895","1017897","1017899","1017903","1017906","1017909","1017913","1017914","1017915","1017916","1019065","1019168","1019459","1019493","1019566","1019568","1019946","1020117","1020295","1020296","1020410","1020653","1020654","1020655","1020660","1020666","1020775"]},{"project_code":"01141301","employee_codes":["1018748","1020543","1020544","1020545","1020546","1020547","13000808"]},{"project_code":"01141501","employee_codes":["1011213","1013045","1013629","1013648","1013967","1014040","1015254","1015314","1015400","1015421","1015514","1016724","1016996","1017634","1017921","1017924","1017926","1017927","1017945","1017947","1017952","1017954","1017978","1017982","1017986","1018008","1018010","1018013","1018016","1018037","1018041","1018047","1018246","1018263","1018265","1018266","1018295","1018678","1018689","1018706","1018770","1018771","1018794","1018902","1018930","1019015","1019031","1019040","1019319","1019372","1019445","1019465","1019524","1019612","1020000","1020129","1020264","1020704","1020740","1021136","1021147"]},{"project_code":"01141502","employee_codes":["1018057","1019546"]},{"project_code":"01141503","employee_codes":["1018130"]},{"project_code":"01141504","employee_codes":["1000314","1001397","1005250","1008034","1009566","1015773","1016147","1016622","1017826","1017877","1018054","1018101","1018102","1018111","1018123","1018129","1018285","1018297","1018299","1018333","1018632","1018679","1019379","1019460","1019549","1019574","1019669","1019887","1019936","1020772"]},{"project_code":"01141505","employee_codes":["1018096","1018105","1018109","1018121","1019935","1020361","1020382","1020893"]},{"project_code":"01141506","employee_codes":["1018085","1018880","1018912","1019315"]},{"project_code":"01141507","employee_codes":["1015063","1020530","1020531","1020533","1020534","1020537","1020538","1020540","1020541","1020664","1020706","1020954","1020955"]},{"project_code":"01141602","employee_codes":["1009360","1014732","1014851","1014928","1015902","1016607","1017345","1020692","1020698","1020897","1021061"]},{"project_code":"011420101","employee_codes":["13000232","13000233","13000235","13000236","13000238","13000240","13000242","13000252","13000253","13000254","13000260","13000261","13000262","13000263","13000269","13000270","13000271","13000273","13000274","13000276","13000278","13000281","13000297","13000306","13000322","13000323","13000325","13000326","13000327","13000328","13000329","13000330","13000331","13000332","13000465","13000471","13000516","13000532","13000534","13000535","13000538","13000553","13000627","13000628","13000629","13000630","13000635","13000636","13000658","13000659","13000681","13000829"]},{"project_code":"011470101","employee_codes":["1000046","1009330","1010483","1010509","1012279","1012925","1015279","1016149","1016217"]},{"project_code":"01150101","employee_codes":["1000744","1000761","1000770","1000774","1000979","1001153","1001162","1001172","1001173","1001199","1002541","1002928","1004318","1006999","1007265","1007778","1008312","1010723","1010725","1014064","1015605","1015784","1015941","1017098","1017118","1017822","1018159","1018225","1018247","1018676","1019885","1019886","1019970"]},{"project_code":"01150102","employee_codes":["1007449","1011448","1012102","1014343","1017321","1017473","1017673","1017820","1017821","1019432","1019692"]},{"project_code":"01150201","employee_codes":["3000621","3000631","3001387","3001388","3001389","3001390","3001391","3001392"]},{"project_code":"01150401","employee_codes":["3000469","3000658","3000671","3000675","3000677","3000687","3000689","3000692","3000693","3000698","3000701","3000889","3001378"]},{"project_code":"01150501","employee_codes":["3001117"]},{"project_code":"01150603","employee_codes":["3000950"]},{"project_code":"011510101","employee_codes":["13000290","6000002","6000003","6000005","6000007","6000008","6000009","6000010","6000011","6000015","6000016","6000017","6000101","6000102","6000107","6000108","6000109","6000110","6000111","6000183","6000186","6000212","6000216","6000222","6000223","6000224","6000245","6000256","6000268","6000280","6000290"]},{"project_code":"011510102","employee_codes":["13000307","6000089","6000090","6000091","6000092","6000093","6000095","6000096","6000143","6000144","6000146","6000147","6000206","6000207","6000340"]},{"project_code":"011510103","employee_codes":["6000087","6000248"]},{"project_code":"011510104","employee_codes":["6000088"]},{"project_code":"011510105","employee_codes":["6000139","6000141","6000345"]},{"project_code":"011510106","employee_codes":["6000084","6000136","6000328"]},{"project_code":"011510107","employee_codes":["6000085","6000346"]},{"project_code":"011510108","employee_codes":["6000149"]},{"project_code":"011510109","employee_codes":["6000086","6000142"]},{"project_code":"011510110","employee_codes":["6000208"]},{"project_code":"011510111","employee_codes":["6000083","6000133","6000283"]},{"project_code":"011510112","employee_codes":["6000098","6000099","6000100","6000148","6000150"]},{"project_code":"011510113","employee_codes":["6000051","6000052","6000054","6000059","6000065","6000067","6000074","6000075","6000079","6000104","6000113","6000118","6000121","6000123","6000124","6000125","6000126","6000131","6000240","6000265","6000282","6000287","6000347"]},{"project_code":"011510114","employee_codes":["6000048","6000056","6000062","6000116","6000117","6000231","6000329"]},{"project_code":"011510115","employee_codes":["6000060","6000061","6000114","6000122","6000289"]},{"project_code":"011510117","employee_codes":["6000050","6000058","6000077","6000081","6000082","6000330"]},{"project_code":"011510118","employee_codes":["6000042","6000044","6000046","6000064","6000120"]},{"project_code":"011510119","employee_codes":["6000043","6000049","6000053","6000115","6000232"]},{"project_code":"011510120","employee_codes":["6000047","6000066","6000069","6000070","6000078","6000119"]},{"project_code":"011510121","employee_codes":["6000055","6000057","6000072","6000073","6000331"]},{"project_code":"011540101","employee_codes":["1010878","1010900","1010905","1010911","1011502"]},{"project_code":"011540201","employee_codes":["1010865","1015104","1016704"]},{"project_code":"011540301","employee_codes":["1017004"]},{"project_code":"011550102","employee_codes":["13000264","13000267","13000268","13000550","13000685","13000747","13001042"]},{"project_code":"011570101","employee_codes":["5000598","5000604","5000605","5002306","5002433","5003064","5003915","5003968"]},{"project_code":"011570102","employee_codes":["5003177","5004009"]},{"project_code":"01160301","employee_codes":["13000743"]},{"project_code":"011630101","employee_codes":["4001440","4001717"]},{"project_code":"011650102","employee_codes":["1015381","1016813"]},{"project_code":"011660101","employee_codes":["5001558","5001566","5001716","5002451","5002664","5002745","5003123","5003170","5003171","5003469","5003500","5003562","5003769","5004014","5004016","5004025","5004072","5004124","5004157","5004443","5004444","5004445","5004485","5004518","5004683","5004688"]},{"project_code":"011670101","employee_codes":["1011288"]},{"project_code":"01170101","employee_codes":["1009892"]},{"project_code":"011730101","employee_codes":["1006580","1006855","1006858","1006898","1007064","1009241","1009540","1009785","1010947","1011881","1011883","1011884","1011887","1011888","1011889","1011890","1011892","1011894","1011895","1012808","1015090","1015727","1017755","13000319"]},{"project_code":"011730201","employee_codes":["1004116","1012965","1012973","1012974","1013158","1014090","1014285","1014736","1015609","1015703"]},{"project_code":"011800101","employee_codes":["4001137","4001197"]},{"project_code":"01180101","employee_codes":["1009124","1011246","1015151","1016820"]},{"project_code":"011870201","employee_codes":["1020823","1020824","1020825","1020827","1020830","1020833","1020837","1020840","1020843","1020866","1020868","1020871","1020873","1020874","1020885","1020886"]},{"project_code":"011870202","employee_codes":["1020828"]},{"project_code":"011870203","employee_codes":["1021016","1021017","1021018","1021019","1021020","1021021","1021022","1021023","1021024","1021025","1021026","1021027"]},{"project_code":"011870204","employee_codes":["1020847"]},{"project_code":"011890102","employee_codes":["5004048","5004050","5004052","5004057","5004059","5004061","5004062","5004068","5004069","5004073","5004077","5004078","5004079","5004080","5004081","5004082","5004107","5004706"]},{"project_code":"01190101","employee_codes":["1000143","1006871","1011029","1011344","1013016","1014725","1016179","1016711","1018052","1018281","1018574","1018575","1018655","1018781","1019480","1020123"]},{"project_code":"011950101","employee_codes":["13000545"]},{"project_code":"011990101","employee_codes":["1007054","1012742","1012744","1014267","1014504","1014874","1014898","1016732","1017547","1020752"]},{"project_code":"011990201","employee_codes":["1000470","1013906","1013907","1013908","1014287","1014754","1014887","1014913","1015007","1015243","1015409","1015747","1015807","1015915","1015923","1018293","1020513"]},{"project_code":"011990301","employee_codes":["1010069","1013126","1017203","1019434","1019767","1019770","1019806","1019820","1019830","1019841","1019844","1019866","1019867","1020524","1020971","1021062"]},{"project_code":"01200101","employee_codes":["1000377","1000400","1000404","1001032","1002034","1003993","1008580","1009087","1011534","1011983","1012104","1012719","1013175","1013962","1014171","1014335","1015287","1015303","1015414","1015969","1016122","1016192","1016665","1016725","1016925","1017365","1017712","1018987","1019979","1019993","1020006","1020032","1020033","1020037","1020099","1020294","1020477","1020943"]},{"project_code":"012010101","employee_codes":["1012255","1012390","1013151","1013324","1015262","1015470","1015541","1016989","1017179","1017505","1017688","1017790","1017801","1018141","1018586","1018989","1018991","1019590","1019648","1019929","1019944","1020103","1020133","1020472","1020483","1020676","1020747","1020805","1020974","1020996","1021066","1021143"]},{"project_code":"012020101","employee_codes":["5000596","5001575","5002304","5002307","5002678","5004476"]},{"project_code":"012060101","employee_codes":["5001674","5002357","5003786","5003817","5004042","5004115","5004467","5004468","5004469","5004470","5004471","5004484","5004511","5004712","5004718","5004719","5004720","5004721","5004722","5004723"]},{"project_code":"012070201","employee_codes":["1016943","1020630","1020631","1020713"]},{"project_code":"012080101","employee_codes":["4002257","4002259","4002260","4002262","4002263","4002264","4002266","4002267","4002268","4002269","4002270","4002272","4002273","4002274","4002275","4002276","4002277","4002281","4002282","4002285","4002295","4002343","4002357","4002540","4002709","4002711","4002804","4002805","4002980","4002981"]},{"project_code":"012090101","employee_codes":["1002405"]},{"project_code":"012110101","employee_codes":["1007090","1013385","1013388","1013390","1013398"]},{"project_code":"012140101","employee_codes":["1015919","1017677","1020737"]},{"project_code":"012170101","employee_codes":["5001466","5001735","5002472","5002474","5002475","5002476","5002711","5002934","5002935"]},{"project_code":"012170102","employee_codes":["13000767","13000768","13000769","13000770","13000771","13000772","13000774","13000775","13000776","13000777","13000778","13000779","13000780","13000781","13000782","13000783","13000785","13000786","13000787","13000788","13000790","13000791","13000794","13000795","13000797","13000798","13000799","13000801","13000802","13000803","13000804","13000805","13000807","13000809","13000810","13000811","13000812","13000813","13000814","13000815","13000816","13000817","13000818","13000819","13000820","13000821","13000825","13000826","13000827","13000828","13000831","13000832","13000833","13000834","13000835","13000836","13000837","13000838","13000839","13000840","13000841","13000842","13000843","13000844","13000845","13000846","13000847","13000848","13000849","13000850","13000851","13000852","13000854","13000855","13000857","13000858","13000859","13000861","13000862","13000863","13000864","13000865","13000867","13000868","13000869","13000870","13000872","13000873","13000874","13000875","13000876","13000877","13000878","13000879","13000880","13000881","13000882","13000883","13000884","13000885","13000886","13000887","13000889","13000891","13000892","13000893","13000894","13000895","13000896","13000898","13000899","13000900","13000902","13000903","13000904","13000905","13000906","13000907","13000909","13000981","13000982","13000983","13000984","13000985","13000986","13000987","13000988","13000989","13000990","13000991","13000992","13000994","13000995","13000996","13000997","13000998","13000999","13001000","13001001","13001002","13001003","13001004","13001005","13001006","13001007","13001008","13001009","13001010","13001011","13001012","13001013","13001014","13001015","13001016","13001017","13001018","13001019","13001020","13001021","13001022","13001023","13001025","13001026","13001027","13001028","13001029","13001030","13001032","13001033","13001034","13001035","13001036","13001037","13001038","13001039","13001040","13001041"]},{"project_code":"012210101","employee_codes":["13000536"]},{"project_code":"012230102","employee_codes":["1012056","1015077","1015309","1015415","1015917","1017562","1019891","1020314"]},{"project_code":"012260101","employee_codes":["1003180","1007785","1012630","1014391","1016708","1017388","1017766","1019464","1019931","1019966","1020317","1020777"]},{"project_code":"012290102","employee_codes":["1001407","1014604","1014606","1014607","1014611","1014612","1014616","1014617","1014618","1014671","1015079","1015252","1015417","1015599","1017466","1017563","1019108","1019716","1020398","1020493","1020494","1020924","1020925","1020926","1020927","1020928","1020929","1020930","1020931"]},{"project_code":"01230101","employee_codes":["1000222","1000230","1000231","1000291","1000355","1000608","1001079","1001675","1001678","1001793","1004263","1008188","1008350","1010243","1010289","1011629","1012192","1017036","1019973"]},{"project_code":"01230102","employee_codes":["1000221","1005291","1005292","1005293","1005294","1007248","1007619","1011454"]},{"project_code":"012310101","employee_codes":["2001182","2001217","2001341","2001576","2001579","2001583","2001629","2001635","2001743","2001814","2002147"]},{"project_code":"012370101","employee_codes":["1014882","1014946","1015158","1015805","1020101","1020265","1020642","1020745","1020932","1020948","1020951"]},{"project_code":"012460101","employee_codes":["1017589","1018206","1018276","1018730","1018736"]},{"project_code":"012470101","employee_codes":["4002497","4002501","4002502","4002557","4002619"]},{"project_code":"01250101","employee_codes":["1000172","1000264","1005628","1008263","1008868","1009001","1011372","1012070","1013800","1014208","1014686","1014986","1015240","1017732","1017780","1019561","1019572","1019604","1019605","1020351","1020449","1020711","1020975"]},{"project_code":"01250102","employee_codes":["13000666","13000692","13000693","13000694","13000695","13000696","13000705","13000709","13000723","13000727","13000728"]},{"project_code":"012540101","employee_codes":["1016306","1017045"]},{"project_code":"012600101","employee_codes":["1009824","1016153","1017731","1019089"]},{"project_code":"012600102","employee_codes":["1016307"]},{"project_code":"01260101","employee_codes":["1000065","1014716","1016258","1016259","1016784","1019312","1020326"]},{"project_code":"012610101","employee_codes":["1007299","1016299","1016300"]},{"project_code":"012610102","employee_codes":["1012398","1015909","1019924","1019963","1019965"]},{"project_code":"012700101","employee_codes":["1016937","1016938","1016939","1016940","1016941","1016942","1016944","1016945","1016946","1016949","1017013","1017489","1017491","1018157","1018158","1018324","1018919","1019027","1019153","1019154","1019155","1019525","1019527","1019758","1019759","1020031","1020288","1020462","1020672","1020673","1020674","1020798","1020799","1020800","1020801","1021154"]},{"project_code":"012740101","employee_codes":["1005341","1007679","1011471","1011545","1014742","1015543","1015861","1017078","1017080","1017082","1017083","1017085","1017089","1017201","1019573"]},{"project_code":"012780102","employee_codes":["1004063","1011307","1011978","1014188","1018255"]},{"project_code":"012800101","employee_codes":["1012076","1013116","1014614"]},{"project_code":"012810101","employee_codes":["1015203","1015398","1016224","1016683","1016951","1017511","1017715","1018143","1018144","1018166","1018167","1018302","1018596","1018598","1018664","1018752","1019026","1019336","1019390","1020004","1020126","1020135","1020475"]},{"project_code":"012810102","employee_codes":["2000833","2001115","2001351","2001790","2002091","2002154"]},{"project_code":"012860101","employee_codes":["1007007","1009156","1013933","1014132","1014872","1018939","1018949","1019309","1019444"]},{"project_code":"01290101","employee_codes":["4000003","4000004","4000005","4000010","4000014","4000024","4000025","4001025","4001027","4001260","4001792","4001793","4001819","4002086","4002156","4002158","4002484","4002541","4002666","4002668","4002669","4002670","4002671","4002714","4002807","4002808","4002941","4002942","4002943","4002944"]},{"project_code":"012940101","employee_codes":["1018557","1018953","1020671"]},{"project_code":"012950101","employee_codes":["1000502","1007102","1010852","1019212","1019215","1019216","1019223","1019237","1019290","1019292","1019293","1019620","1020074","1020624","1020637","1020806","1020809"]},{"project_code":"012990101","employee_codes":["13000730","13000731","13000732","13000733","13000734","13000735","13000736","13000737"]},{"project_code":"012990102","employee_codes":["13000913","13000914","13000915","13000916","13000917","13000918","13000919","13000920","13000921","13000922","13000923","13000924","13000925","13000926","13000927","13000928","13000929","13000930","13000931","13000932","13000933","13000934","13000935","13000936","13000937","13000938","13000939","13000940","13000941","13000942","13000943","13000944","13000945","13000946","13000947","13000948","13000949","13000950","13000951","13000952","13000953","13000954","13000955","13000956","13000957","13000958","13000959","13000960","13000961","13000962","13000963","13000964","13000965","13000966","13000967","13000968","13000969","13000970","13000971","13000972","13000973","13000974","13000975","13000976","13000977","13000980"]},{"project_code":"01300101","employee_codes":["3000001","3000017","3000019","3000138","3000230","3000304","3000435","3000574","3000902","3000971","3000984","3001031","3001033","3001114","3001221","3001383"]},{"project_code":"013010101","employee_codes":["5003948"]},{"project_code":"013050101","employee_codes":["1020038","1020039"]},{"project_code":"013050103","employee_codes":["13000761","13000830"]},{"project_code":"013060102","employee_codes":["1001177","1008746","1013099","1020590","1020593","1020595","1020602","1020603","1020604","1020606","1020608","1020609","1020610","1020612","1020613","1020616","1020617","1020622"]},{"project_code":"013080101","employee_codes":["1020427"]},{"project_code":"013100101","employee_codes":["4002891","4002892"]},{"project_code":"013110101","employee_codes":["13000755","13000756","13000757","13000978","13000979"]},{"project_code":"013150101","employee_codes":["5003145","5003622","5004542","5004543","5004544","5004545","5004546","5004547","5004548","5004549","5004550","5004551","5004552","5004553","5004554","5004555","5004556","5004557","5004558","5004559","5004560","5004561","5004562","5004564","5004565","5004566","5004567","5004568","5004569","5004570","5004571","5004572","5004573","5004574","5004575","5004576","5004577","5004578","5004579","5004580","5004581","5004582","5004583","5004584","5004585","5004586","5004587","5004588","5004589","5004590","5004591","5004592","5004593","5004594","5004595","5004596","5004597","5004598","5004599","5004600","5004601","5004602","5004603","5004604","5004605","5004606","5004607","5004608","5004609","5004610","5004611","5004613","5004614","5004615","5004616","5004617","5004618","5004619","5004620","5004621","5004622","5004623","5004624","5004625","5004626","5004627","5004628","5004629","5004630","5004631","5004632","5004633","5004634","5004635","5004636","5004637","5004638","5004639","5004640","5004641","5004642","5004643","5004644","5004645","5004646","5004647","5004648","5004649","5004650","5004651","5004652","5004653","5004654","5004655","5004657","5004696","5004708"]},{"project_code":"013170101","employee_codes":["1018306","1020900","1020901","1020902","1020903","1020904","1021070"]},{"project_code":"013180101","employee_codes":["1005775","1010866","1011795","1012549","1012865","1012997","1014867","1014879","1014935","1015431","1015780","1016900","1017370","1017435","1017436","1019273","1019307","1019416","1019506","1020433","1021028","1021030","1021032","1021033","1021034","1021035","1021036","1021037","1021038","1021039","1021040","1021041","1021043","1021044","1021045","1021047","1021048","1021049","1021051","1021052","1021053","1021054","1021055","1021056","1021057","1021058","1021059","1021060","1021076","1021077","1021078","1021088","1021089","1021090","1021091","1021092","1021093","1021156","1021157"]},{"project_code":"013190101","employee_codes":["5004528","5004529","5004531","5004532"]},{"project_code":"013220101","employee_codes":["4002984"]},{"project_code":"013230101","employee_codes":["3001187","3001386"]},{"project_code":"01330101","employee_codes":["3000442"]},{"project_code":"01340101","employee_codes":["3000941","3001113"]},{"project_code":"01360101","employee_codes":["4000045","4000046","4000050","4000051","4000052","4000055","4000942","4001223","4001529","4001539","4001585","4001586","4002075"]},{"project_code":"01360201","employee_codes":["4000121","4000124","4001995","4002335","4002631"]},{"project_code":"01360301","employee_codes":["4000654","4000657","4000660","4000662","4001004","4001104","4001668","4002393","4002426","4002586","4002588","4002613"]},{"project_code":"01360401","employee_codes":["4001065","4001069","4001075","4001085","4001089","4002185"]},{"project_code":"01360701","employee_codes":["1014232","1019768","1019802","1019803","1019816","1019862","1021006","1021007","1021008","1021009","1021010","1021011","1021012","1021013","1021014","1021015"]},{"project_code":"01390101","employee_codes":["1001138","1003228","1003343","1003392","1003456","1003525","1003531","1003736","1003817","1003842","1003974","1004126","1004256","1004289","1004466","1004721","1004745","1004754","1004898","1004938","1004958","1004961","1005096","1006013","1006079","1006225","1006393","1006465","1006666","1006729","1007280","1007596","1007597","1007786","1007909","1007966","1008033","1008249","1008349","1008368","1008371","1008404","1008902","1009008","1009191","1009227","1009329","1009333","1009381","1009485","1009527","1009610","1009638","1009679","1009859","1010149","1010172","1010361","1010385","1010403","1010415","1010728","1011016","1011031","1011251","1011540","1011592","1011824","1011898","1011959","1011972","1011992","1012222","1012272","1012598","1012779","1012899","1013010","1013238","1013496","1013564","1013818","1013841","1013842","1013884","1013888","1014018","1014125","1014137","1014143","1014169","1014179","1014182","1014184","1014330","1014331","1014452","1014506","1014743","1014949","1014981","1015002","1015008","1015166","1015392","1015465","1015535","1015571","1015694","1015732","1015910","1015984","1016032","1016096","1016105","1016201","1016531","1016601","1016929","1017434","1017516","1017541","1018635","1018674","1018702","1018923","1018952","1019169","1019190","1019337","1019351","1019386","1019462","1019488","1019545","1019599","1019943","1020024","1020625"]},{"project_code":"01390102","employee_codes":["1000138","1003257","1003304","1003363","1003378","1003577","1003589","1004205","1004403","1004837","1005345","1009296","1009391","1009448","1009466","1010708","1011440","1012777","1012778","1013083","1014575","1015946","1016241","1017514"]},{"project_code":"01540101","employee_codes":["5002581","5002685","5002710","5002741","5003139","5003191","5003374","5003449","5003482","5003591","5003592","5003615","5004045","5004214","5004441","5004461","5004462"]},{"project_code":"01560301","employee_codes":["1006498","1012097","1012830","1012907","1013043","1013362","1014227","1014229","1014500","1015021","1015602","1015695","1015697","1015900","1016655","1017125","1017522","1017523","1018241","1018311","1019188","1019494","1019889","1020092","1020093"]},{"project_code":"01560401","employee_codes":["1004789","1013072","1013597","1013602","1013607","1013610","1013686","1013709","1013754","1013814","1013827","1014082","1014299","1014328","1014349","1014576","1014694","1015355","1015871","1016178","1016276","1016570","1018787","1021094","1021095","1021096","1021097","1021098","1021099","1021100","1021101","1021102","1021103","1021104","1021105","1021106","1021107","1021108","1021109","1021110","1021111","1021112","1021113","1021114","1021115","1021116","1021117","1021118","1021119","1021120","1021121","1021122","1021123","1021124","1021125","1021126","1021127","1021128","1021129","1021130","1021131","1021132","1021133"]},{"project_code":"01560501","employee_codes":["1017635"]},{"project_code":"01560701","employee_codes":["1005142","1009262","1015074","1015985","1016909","1016910","1017312","1018398","1018963","1019720","1019721","1019722","1019723","1019724","1019725","1019726","1019727","1019728","1019729","1019730","1019732","1019733","1019734","1019735"]},{"project_code":"01570102","employee_codes":["1010472","1010473","1010474","1010622","1010994","1012315","1014908","1015494"]},{"project_code":"01570103","employee_codes":["1002542","1008081","1010116","1016955","1020571","1020937","1021151"]},{"project_code":"01570201","employee_codes":["1000769","1006262","1007525","1007607","1008177","1010688","1011477","1011788","1012133","1012231","1012843","1015729","1016602","1016808","1017090","1017286","1017719","1020744"]},{"project_code":"01570401","employee_codes":["1016675","1016876"]},{"project_code":"01570504","employee_codes":["13000562","13000645","13000700","13000758"]},{"project_code":"01570601","employee_codes":["5001084","5001086","5001087","5001381","5003488","5003641"]},{"project_code":"01570602","employee_codes":["13000501","13000502","13000503","13000504","13000505","13000506","13000510","13000511","13000512","13000584","13000664","13000665","13000689","13000729"]},{"project_code":"01570603","employee_codes":["1009770","1021079","1021080","1021081","1021082","1021083","1021084","1021085","1021086","1021087"]},{"project_code":"01570701","employee_codes":["1011003","1011004","1011006","1016508","1017741"]},{"project_code":"01570702","employee_codes":["13000284","13000285","13000286","13000682","13000714","13000740","13000823"]},{"project_code":"01570901","employee_codes":["1012126","1019600"]},{"project_code":"01571001","employee_codes":["1011696","1012118","1012497","1012693","1012694","1013127","1013242","1013251","1014240","1014305","1014554","1014906","1015196","1015390","1015435","1015675","1015764","1015795","1015954","1015962","1015997","1016161","1016907","1017348","1017390","1017477","1017548","1017819","1018279","1018288","1018761","1018947","1019477","1019478","1019679","1019995","1020018","1020094","1020130","1020365","1020390","1020409","1020644"]},{"project_code":"01571101","employee_codes":["1012595"]},{"project_code":"01571102","employee_codes":["1014109","1014412"]},{"project_code":"01571103","employee_codes":["1018597","1018940"]},{"project_code":"01571104","employee_codes":["1019533"]},{"project_code":"01571501","employee_codes":["1012300","1015257","1015680","1015783","1015789","1017302","1019842","1020001","1020226","1020227","1020228","1020229","1020230","1020232","1020233","1020235","1020236","1020237","1020238","1020239","1020240","1020241","1020242","1020243","1020244","1020245","1020512"]},{"project_code":"01571502","employee_codes":["1013832","1015681","1016246","1019531","1020247","1020248","1020249","1020253","1020254","1020255","1020256","1020257","1020258","1020259","1020261","1020262","1020298","1020303","1020304","1020670","1020942","1020987"]},{"project_code":"01571601","employee_codes":["13000762","13000763","13000764","13001043","13001044"]},{"project_code":"01571701","employee_codes":["5003375"]},{"project_code":"01571801","employee_codes":["5000580","5001473","5004711"]},{"project_code":"01600101","employee_codes":["1005372","1005373","1014106","1017028","1018164","1018728","13000624"]},{"project_code":"01610101","employee_codes":["1018353"]},{"project_code":"01610105","employee_codes":["1015092"]},{"project_code":"01610106","employee_codes":["1007159","1016039"]},{"project_code":"01610201","employee_codes":["1005386","1005451","1009920","1009934","1009935","1009937","1009940","1009942","1009944","1009945","1009946","1009949","1009958","1009961","1009963","1009967","1010290","1010292","1010293","1010298","1010305","1010306","1010308","1010309","1010312","1010316","1010317","1010320","1010321","1010322","1010362","1010366","1010467","1010564","1010680","1010681","1010682","1010762","1011199","1011488","1011551","1012036","1012055","1012319","1012320","1012323","1012607","1012611","1012989","1013130","1013351","1013584","1014956","1014957","1015239","1015338","1015481","1015857","1016024","1016089","1016304","1017310","1017636","1017637","1018725","1018726","1019092","1019422","1019423","1019449","1019717","1019890","1020396"]},{"project_code":"01610202","employee_codes":["1009936","1014608","1016445","1016448","1016449","1016454","1016456","1016457","1016458","1016461","1016465","1016466","1016468","1016469","1016470","1016471","1016472","1016473","1016474","1016476","1016477","1016479","1016480","1016481","1016482","1016483","1016485","1016486","1016487","1016488","1016489","1016490","1016493","1016494","1016495","1016638","1016639","1016641","1016642","1016643","1016645","1016908","1018554","1019421","1019715","1019899","1020040","1020041","1020313","1020803","1020804","1020993"]},{"project_code":"01610204","employee_codes":["1010372","1013179","1014371","1014609","1016554","1017481","1018320","1018321","1018837","1020814"]},{"project_code":"01610401","employee_codes":["1011483","1011693","1015918"]},{"project_code":"01610801","employee_codes":["1002800","1005013","1006085","1007352","1009795","1011512","1011612","1011896","1011922","1016514","1016519","1016520","1016935","1019239"]},{"project_code":"01610901","employee_codes":["1013026","1016323","1016325","1016326","1016327","1016328","1016372","1016439","1016443","1016576","1019009","1019496"]},{"project_code":"01690101","employee_codes":["3000428","3000506","3000537","3001272"]},{"project_code":"01690102","employee_codes":["3000122","3000972","3001036","3001038","3001112","3001268","3001371"]},{"project_code":"01690201","employee_codes":["3000460","3000462","3000466","3001041"]},{"project_code":"01700102","employee_codes":["1014692","1020209","1020210","1020211","1020212","1020213","1020214","1020215","1020216","1020217","1020218","1020219","1020220","1020224","1020373","1020374","1020995"]},{"project_code":"01710101","employee_codes":["1000656","1003836","1005721","1005722","1010737","1011459","1012101","1015145","1015462","1015565","1015968","1016215","1016253","1017059","1017583","1017626","1018188","1019005","1019339","1019430","1020389"]},{"project_code":"01710201","employee_codes":["1011254","1013062","1016239","1019320"]},{"project_code":"01790101","employee_codes":["13000037","13000038"]},{"project_code":"01820101","employee_codes":["4000275","4000433","4001374","4001839","4001979","4002366","4002401","4002422","4002429","4002459","4002511","4002565","4002610","4002683","4002725","4002748","4002765","4002775","4002801","4002873","4002874","4002897","4002909","4002910","4002951","4002956","4002963","4002969"]},{"project_code":"01840101","employee_codes":["1001688","1005919","1005920","1005921","1005925","1006054","1006367","1006619","1007028","1007190","1007754","1008788","1009140","1010213","1012249","1012753","1013034","1013882","1013921","1014497","1018201","1019553","1019594","1020465"]},{"project_code":"01870101","employee_codes":["1000099","1001041","1006528","1007517","1007518","1007521","1008653","1012087","1012244","1012643","1013209","1014373","1018734","1019160","1020266","1020415"]},{"project_code":"01870103","employee_codes":["1015342","1017734"]},{"project_code":"01870201","employee_codes":["1009990","1010160","1019883","1020802"]},{"project_code":"01870202","employee_codes":["1018724"]},{"project_code":"01890301","employee_codes":["1009291","13000722"]},{"project_code":"01890302","employee_codes":["13000739"]},{"project_code":"01900101","employee_codes":["13000686","13000687","13000688"]},{"project_code":"01900102","employee_codes":["1010656","1011815","13000135"]},{"project_code":"01930101","employee_codes":["1002455","1002478","1002610","1006946","1006949","1010628","1012156","1012339","1017674","1018815","1019365"]},{"project_code":"01930201","employee_codes":["4000960","4001649","4001847","4002016","4002187","4002553"]},{"project_code":"01940201","employee_codes":["4001266","4001292","4001294","4001414","4001752","4002020","4002523","4002524","4002542","4002573","4002732","4002733","4002747","4002886"]},{"project_code":"01940301","employee_codes":["6000018","6000019","6000021","6000024","6000033","6000034","6000037","6000182","6000264","6000288","6000336","6000338","6000344"]},{"project_code":"02040101","employee_codes":["2000030","2000038","2000984","2001189","2001617","2002062","2002201"]},{"project_code":"02040102","employee_codes":["2000065","2000251","2000598","2001129","2001375","2001784","2001966","2002437"]},{"project_code":"021970101","employee_codes":["2000847","2001471"]},{"project_code":"022720101","employee_codes":["1010015","1017216","1017225","1017233","1017239","1017246","1017260","1017263","1017277","1017288","1017290","1017295"]},{"project_code":"022720102","employee_codes":["13000752","13000753","13000824"]},{"project_code":"022720103","employee_codes":["1019299","1019300"]},{"project_code":"022720104","employee_codes":["1019763","1019764","1019766","1021068"]},{"project_code":"022720105","employee_codes":["13000856"]},{"project_code":"02300101","employee_codes":["2001030","2001361","2001551"]},{"project_code":"02300203","employee_codes":["2000771","2000974"]},{"project_code":"02300302","employee_codes":["2000975","2001560","2001799","2001802","2001803","2001804","2001845","2001853","2001881","2001887","2001889","2001890","2001891","2001897","2001903","2001904","2001905","2001907","2001908","2001915","2001930","2001981","2001982","2001990","2002002","2002078","2002184","2002193","2002337"]},{"project_code":"02300401","employee_codes":["2001494","2001631","2002108","2002113","2002146","2002598"]},{"project_code":"02300501","employee_codes":["2001765","2002152","2002199","2002226","2002344","2002345","2002450"]},{"project_code":"02300601","employee_codes":["2000845","2001420","2002160","2002355","2002356","2002357","2002358","2002359","2002360","2002361","2002362","2002363","2002364","2002365","2002366","2002367","2002368","2002369","2002370","2002371","2002372","2002373","2002374","2002375","2002377","2002378","2002379","2002380","2002381","2002382","2002383","2002384","2002385","2002386","2002387","2002388","2002389","2002390","2002391","2002392","2002424","2002455","2002470","2002659"]},{"project_code":"023120101","employee_codes":["2002568","2002574","2002575","2002580","2002587","2002596","2002597","2002603","2002605","2002606","2002609","2002614","2002617","2002621"]},{"project_code":"023120102","employee_codes":["2001026","2001315","2002471","2002474","2002479","2002480","2002483","2002490","2002492","2002496","2002497","2002499","2002500","2002510","2002514","2002517","2002519","2002524","2002527","2002538","2002540","2002551","2002556","2002561"]},{"project_code":"02360103","employee_codes":["1004079","1014767","1014768","1014769","1014770","1014772","1014775","1014780","1014783","1014785","1014787","1014790","1014793","1014794","1014795","1014797","1014798","1014799","1014801","1014802","1014803","1014804","1014805","1014809","1014810","1014811","1014815","1014816","1014818","1014819","1014820","1014822","1014823","1014824","1014825","1014826","1014830","1014831","1014832","1014834","1014835","1014836","1014839","1014842","1014951","1015018","1015662","1015837","1016137","1016168","1016204","1016296","1016297","1017165","1017756","1018878","1019101","1019102","1019458","1019718","1020042","1020890"]},{"project_code":"02380101","employee_codes":["2002022"]},{"project_code":"02390101","employee_codes":["2001681"]},{"project_code":"031660103","employee_codes":["5002609","5004113","5004149","5004516"]},{"project_code":"031660104","employee_codes":["5004446","5004700"]},{"project_code":"031660105","employee_codes":["5001559","5001561","5002870","5002936","5003368","5003594","5003760","5003766","5003774","5003775","5003777","5003783","5003994","5003996","5004063","5004447","5004473","5004487","5004488","5004519","5004520","5004689"]},{"project_code":"032390101","employee_codes":["3000509","3000510","3001027"]},{"project_code":"032390102","employee_codes":["3000626","3000737","3001016","3001025"]},{"project_code":"032390103","employee_codes":["3000517","3000530","3001273"]},{"project_code":"032570101","employee_codes":["3001040"]},{"project_code":"032570102","employee_codes":["3000749","3000768","3000917","3000921","3001134","3001138","3001143","3001157","3001162","3001173","3001236","3001246","3001262","3001263","3001356","3001357","3001359","3001361","3001393"]},{"project_code":"032570104","employee_codes":["13000711"]},{"project_code":"032570105","employee_codes":["13000725"]},{"project_code":"032920101","employee_codes":["13000712"]},{"project_code":"042350101","employee_codes":["4002375","4002378","4002383","4002443","4002494","4002609","4002737","4002749","4002759","4002782","4002791","4002948","4002962","4002968"]},{"project_code":"042380101","employee_codes":["4002069","4002400","4002414","4002440","4002563","4002584","4002602","4002611","4002645","4002682","4002705","4002720","4002751","4002752","4002753","4002756","4002767","4002769","4002783","4002785","4002870","4002890","4002901","4002904","4002937","4002938","4002946","4002957","4002958","4002959","4002960","4002967","4002977","4002982"]},{"project_code":"042590101","employee_codes":["4002604","4002866","4002882"]},{"project_code":"042730101","employee_codes":["4002640","4002655","4002656"]},{"project_code":"042730102","employee_codes":["4002916","4002917","4002918","4002919","4002920","4002921","4002922","4002923","4002924","4002925","4002926","4002927","4002928","4002929","4002930","4002931","4002932","4002933","4002934","4002935"]},{"project_code":"042770101","employee_codes":["4002778","4002885","4002896","4002979"]},{"project_code":"042830102","employee_codes":["4000268","4000279","4000372","4001761","4001986","4002379","4002446","4002452","4002496","4002521","4002597","4002601","4002719","4002761","4002770","4002797","4002814","4002815","4002816","4002817","4002826","4002827","4002846","4002863","4002864","4002914","4002945","4002976"]},{"project_code":"042930101","employee_codes":["4001310","4001373","4002520","4002596","4002764","4002871"]},{"project_code":"043030101","employee_codes":["13000748","13000749","13000750","13000751"]},{"project_code":"043040101","employee_codes":["1003885","1008652","1009847","1016284","1016952","1017075","1017612","1017705","1020146","1020152","1020153","1020155","1020156","1020157","1020160","1020166","1020167","1020174","1020176","1020179","1020183","1020186","1020187","1020191","1020192","1020464","1020675","1020750","1020984","1020994","1021134","1021139","1021155","13000590"]},{"project_code":"052260101","employee_codes":["5003206","5003210","5003211","5003215","5003216"]},{"project_code":"052260103","employee_codes":["5003467"]},{"project_code":"052260104","employee_codes":["5002366"]},{"project_code":"052260105","employee_codes":["5003205"]},{"project_code":"052260106","employee_codes":["5003203","5004237"]},{"project_code":"052260107","employee_codes":["5004240","5004242","5004243","5004244","5004246","5004249","5004253","5004254","5004255","5004256","5004258"]},{"project_code":"052260108","employee_codes":["5004460"]},{"project_code":"052260109","employee_codes":["5004265"]},{"project_code":"052260110","employee_codes":["5004266"]},{"project_code":"052260111","employee_codes":["5004268","5004270"]},{"project_code":"052260112","employee_codes":["5003202","5003204"]},{"project_code":"052260113","employee_codes":["5003767"]},{"project_code":"071220102","employee_codes":["5002956","5002988","5003223","5003683","5004092","5004194","5004277"]},{"project_code":"071220103","employee_codes":["5000499","5000529","5002960","5003684","5003844","5004190","5004308","5004370","5004416","5004538"]},{"project_code":"071220702","employee_codes":["5000331","5002283","5002310","5004174","5004177","5004178","5004184","5004185","5004186","5004301","5004309","5004310","5004311","5004313","5004314","5004315","5004316","5004318","5004319","5004321","5004322","5004323","5004324","5004326","5004331","5004387","5004388","5004389","5004390","5004497","5004498","5004499","5004500","5004540","5004541","5004662","5004663","5004664"]},{"project_code":"071220801","employee_codes":["5000513","5001765","5004164","5004320","5004327","5004328","5004329","5004330","5004333","5004335","5004340","5004342","5004343","5004347","5004348","5004352","5004355","5004372","5004373","5004374","5004377","5004378","5004379","5004380","5004383","5004399","5004418","5004420","5004421","5004502","5004503","5004539","5004666","5004667","5004669","5004670","5004677","5004694","5004698","5004699","5004707","5004717"]},{"project_code":"072330101","employee_codes":["5000556","5003144","5003339"]},{"project_code":"072330102","employee_codes":["5002833","5002834","5002835","5002840","5002845","5003012","5003014","5003015","5003020","5003029","5003050","5003051","5003070","5003072","5003081","5003082","5003083","5003094","5003128","5003129","5003130","5003132","5003146","5003165","5003340","5003422","5003453","5003454","5003456","5003502","5003700","5003710","5003749","5004371"]},{"project_code":"072580101","employee_codes":["1015034","5003219","5003220","5003229","5003231","5003233","5003243","5003247","5003257","5003265","5003294","5003297","5003303","5003305","5003323","5003328","5003401","5003403","5003406","5003431","5003477","5003523","5003548","5003552","5003579","5003606","5003608","5003717","5003722","5003724","5003752","5003755","5003804","5003833","5003837","5003877","5003880","5003929","5003982","5003999","5004006","5004021","5004090","5004168","5004207","5004209","5004365","5004409","5004410","5004439","5004491","5004671","5004693","5004697","5004714"]},{"project_code":"BKO","employee_codes":["1008672","2000745","3000575"]},{"project_code":"KCV","employee_codes":["1000004","1000031","1000055","1000061","1000096","1000285","1000462","1000701","1004689","1011465","1012452","1015281","1016742","1017122","1019555"]},{"project_code":"PPV","employee_codes":["1000084","1000086","1000575","1001120","1001790","1004145","1006318","1006524","1011255","1012003","1012493","1013254","1013853","1014136","1015241","1016612","1016623","1017582","1018162","1018303","1018304","1018801","1019471","1019921","1020003","1020010","1020022","1020121","1020363","1020523","1020707","1020708","1020709","1020712","1020735","1020956","1020963","1020965","1020986","1021003","1021004","1021072","1021074","1021144"]},{"project_code":"SBB","employee_codes":["1000094","13000228"]}]';
            $projects = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - jobAssignments DATA : '.json_encode($projects));
//            Log::channel('in_sys')
//                ->info('API/IntegrationController - jobAssignments PROJECT COUNT : '.$projects->count());

            foreach ($projects as $project){

                if(DB::table('projects')->where('code', $project['project_code'])->exists()){
                    $nProject = Project::where('code', $project['project_code'])->first();

                    $projectEmployees = ProjectEmployee::where('project_id', $nProject->id)->where('status_id', 1)->get();
                    foreach($projectEmployees as $projectEmployee){
                        $projectEmployee->status_id = 0;
                        $projectEmployee->save();
                    }
//                    Log::channel('in_sys')->info('API/IntegrationController - jobAssignments checkpoint 1 change status employee, project='.$nProject->code);

                    foreach ($project['employee_codes'] as $employee){
                        if(DB::table('employees')->where('code', $employee)->exists()){
                            $nEmployee = Employee::where('code', $employee)->first();

                            $projectEmployeeDB = ProjectEmployee::where('employee_id', $nEmployee->id)
                                ->where('project_id', $nProject->id)->first();
                            if(empty($projectEmployeeDB)){
                                ProjectEmployee::create([
                                    'project_id'        => $nProject->id,
                                    'employee_id'       => $nEmployee->id,
                                    'employee_roles_id' => $nEmployee->employee_role_id,
                                    'status_id'         => 1
                                ]);
                            }
                            else{
                                $projectEmployeeDB->employee_roles_id = $nEmployee->employee_role_id;
                                $projectEmployeeDB->status_id = 1;
                                $projectEmployeeDB->save();
                            }
                        }
                    }
//                    Log::channel('in_sys')->info('API/IntegrationController - jobAssignments checkpoint 2 create/edit employee, project='.$nProject->code);
                }

            }

            Log::channel('in_sys')
                ->info('API/IntegrationController - jobAssignments PROCESS DONE');
            return Response::json([
                'message' => 'Success Updating Job Assigment!'
            ], 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - jobAssignments error EX: '. $ex);
//            Log::error('API/IntegrationController - jobAssignments error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }
}

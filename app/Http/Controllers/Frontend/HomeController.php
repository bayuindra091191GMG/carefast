<?php

namespace App\Http\Controllers\Frontend;

use App\Imports\CustomerImport;
use App\Imports\DacImport;
use App\Imports\InitialDataImport;
use App\Imports\ProjectEmployeeImport;
use App\libs\AttendanceProcess;
use App\libs\ComplaintDetailFunc;
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
use Carbon\CarbonPeriod;
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
use Intervention\Image\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;

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

    public function testingFunction(){
        try{
            $complaintDBs = Complaint::where('id', '>', 0)->get();
            foreach ($complaintDBs as $complaintDB){

                $complaintDetails =  ComplaintDetail::where('complaint_id', $complaintDB->id);

                $complaintDetails = $complaintDetails
                    ->orderBy('created_at', 'asc')
                    ->first();
                $complaintDB->description = $complaintDetails->message;
                $complaintDB->save();
            }
            dd("success");

            //add pak charles, akiong dan zul ke semua project
            // 9094=charles, role= 8 | 9098=akiong, role= 7 | 9097=zul, role= 7

//            $projectDb = DB::table('projects')
//                ->select('id')
//                ->where('status_id', 1)
//                ->get();
//            foreach ($projectDb as $project){
//                $ProjectEmployeeCharles = ProjectEmployee::where('project_id', $project->id)
//                    ->where('employee_id', 9094)
//                    ->first();
//                if(empty($ProjectEmployeeCharles)){
//                    ProjectEmployee::create([
//                        'project_id'        => $project->id,
//                        'employee_id'       => 9094,
//                        'employee_roles_id' => 8,
//                        'status_id'         => 1
//                    ]);
//                }
//
//                $ProjectEmployeeZul = ProjectEmployee::where('project_id', $project->id)
//                    ->where('employee_id', 9097)
//                    ->first();
//                if(empty($ProjectEmployeeZul)){
//                    ProjectEmployee::create([
//                        'project_id'        => $project->id,
//                        'employee_id'       => 9097,
//                        'employee_roles_id' => 7,
//                        'status_id'         => 1
//                    ]);
//                }
//
//                $ProjectEmployeeAkiong = ProjectEmployee::where('project_id', $project->id)
//                    ->where('employee_id', 9098)
//                    ->first();
//                if(empty($ProjectEmployeeAkiong)){
//                    ProjectEmployee::create([
//                        'project_id'        => $project->id,
//                        'employee_id'       => 9098,
//                        'employee_roles_id' => 7,
//                        'status_id'         => 1
//                    ]);
//                }
//            }

            //testing escalation cron job function
            $complaintDBs = Complaint::whereIn('status_id', [10, 11])->get();
            dd($complaintDBs);
            $temp = Carbon::now('Asia/Jakarta');
            $now = Carbon::parse(date_format($temp,'j-F-Y H:i:s'));

            foreach($complaintDBs as $complaintDB){
                $trxDate = Carbon::parse(date_format($complaintDB->response_limit_date, 'j-F-Y H:i:s'));
                // mencari perbedaan menit
                $intervalMinute = $trxDate->diffInMinutes($now);
                dd($trxDate < $now);
                if($trxDate < $now){
                    dd("lebih kecil");
                }

                dd("complaint Number = ".$complaintDB->code." | now = ".$now." | trxDate = ".$trxDate." | intervalMinute = ".$intervalMinute);

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
                        $messageImage = empty($complaintDB->image) ? null : asset('storage/complaints/'. $complaintDB->image);
                        if(!empty($complaintDB->customer_id)){
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
                        }
                        else{
                            $customerComplaintDetailModel = ([
                                'customer_id'       => null,
                                'customer_name'     => "",
                                'customer_avatar'    => "",
                                'employee_id'       => $complaintDB->employee_id,
                                'employee_name'     => $complaintDB->employee->first_name." ".$complaintDB->employee->last_name,
                                'employee_avatar'    => asset('storage/employees/'. $complaintDB->employee->image_path),
                                'message'           => $complaintDB->message,
                                'image'             => $messageImage,
                                'date'              => Carbon::parse($complaintDB->created_at, 'Asia/Jakarta')->format('d M Y H:i:s'),
                            ]);
                        }
                        $title = "ICare";
                        $body = "Customer complain terjadi eskalasi";
                        $data = array(
                            "type_id" => 301,
                            "complaint_id" => $complaintDB->id,
                            "complaint_subject" => $complaintDB->subject,
                            "complaint_detail_model" => $customerComplaintDetailModel,
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


            Log::channel('cronjob')->info($now." = Sukses");
            return "Sukses";
        }
        catch (\Exception $ex){
            return $ex;
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

    public function AndroidIdform()
    {
        return view('admin.imei.form');
    }

    public function AndroidIdProcess(Request $request){
        try{

//            dd($request);
            if($request->input('employee_code') == "yansen626@gmail.com"){
                $employees = Employee::where('id', '>', 29)->get();
                foreach ($employees as $employee){
                    $user = User::where('employee_id', $employee->id)->first();
                    if(!empty($user->android_id)){
                        $user->android_id = null;
                        $user->first_imei = null;
                        $user->second_imei = null;
                        $user->save();
                    }
                }

                Session::flash('success', 'Berhasil Ganti Data');
                return redirect(route('imei.form'));
            }

            $employee = Employee::where('code',$request->input('employee_code'))->first();
            if(empty($employee)){
                Session::flash('error', 'Employee Tidak ditemukan!');
                return redirect(route('imei.form'));
            }
            $user = User::where('employee_id', $employee->id)->first();
            $user->android_id = null;
            $user->first_imei = null;
            $user->second_imei = null;
            $user->save();

            Session::flash('success', 'Berhasil Ganti Data '.$user->name);
            return redirect(route('imei.form'));
        }
        catch (\Exception $ex){
            dd($ex);
            Log::error('Frontend/HomeController - AndroidIdProcess error EX: '. $ex);

            Session::flash('error', 'Gagal Ganti Data!');
            return redirect(route('imei.form'));
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
        try{
            $string = "";
            $complaintDB = Complaint::where('id', 113)->first();
            $title = "ICare";
            $body = "Customer complain terjadi eskalasi";
            $data = array(
                "type_id" => 311,
                "complaint_id" => $complaintDB->id,
                "complaint_subject" => $complaintDB->subject,
                "complaint_detail_model" => ComplaintDetailFunc::getComplaintDetailFunc($complaintDB->id),
            );
            //Push Notification to employee App.
            $user = \App\Models\User::where('employee_id', 10088)->first();
            $result = FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);

            $string.= "notif 311 = ".$result." | ";

            $title = "ICare";
            $body = "Komplain Customer belum diselesaikan, tolong ingatkan penanggung jawab komplain";
            $data = array(
                "type_id" => 312,
                "complaint_id" => $complaintDB->id,
                "complaint_subject" => $complaintDB->subject,
                "complaint_detail_model" => ComplaintDetailFunc::getComplaintDetailFunc($complaintDB->id),
            );
            //Push Notification to employee App.
            //Push Notification to employee App.
            $user = \App\Models\User::where('employee_id', 10088)->first();
            $result2 = FCMNotification::SendNotification($user->id, 'user', $title, $body, $data);

            $string.= "notif 312 = ".$result2." | ";

            return $string;
        }
        catch (\Exception $ex){
            Log::error('Frontend/HomeController - testNotifSendToAndroid error EX: '. $ex);
            dd($ex);
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
//            $attendanceAbsents = AttendanceAbsent::where('id', '>', 36155)
//                ->where('id', '<', 37010)
//                ->get();
//            foreach($attendanceAbsents as $attendanceAbsent){
//                $attendanceAbsent->date = $attendanceAbsent->date->toDateTimeString()->subHours(8);
//                $attendanceAbsent->create_at = $attendanceAbsent->create_at->toDateTimeString()->subHours(8);
//                $attendanceAbsent->update_at = $attendanceAbsent->update_at->toDateTimeString()->subHours(8);
//                $attendanceAbsent->save();
//            }
//            return "success";

            $startDate = Carbon::parse('2020-01-01')->format('Y-m-d');
            $endDate = Carbon::now('Asia/Jakarta')->format('Y-m-d');
//            dd($startDate, $now);
            $data = AttendanceProcess::DownloadAttendanceValidationProcess($startDate, $endDate);

            $now = Carbon::now('Asia/Jakarta');
            $file = "rekap absensi per ".$now->format('d-F-Y G.i.s').'.xlsx';
            // checking attendance END

            $destinationPath = public_path()."/download_attendance/";
//        dd($destinationPath.$file);
            (new FastExcel($data))->export($destinationPath.$file);
            return response()->download($destinationPath.$file);


            //changing user formated phone
            $users = User::where('phone', 'like', '%-%')->get();
            foreach($users as $user){
                $phone = $user->phone;
                $phone = str_replace(' ', '', $phone);
                $phone = str_replace('-', '', $phone);
                $phone = str_replace('.', '', $phone);
                $phone = str_replace('+62 ', '0', $phone);
                $phone = str_replace('+62', '0', $phone);
                $user->phone =$phone;
                $user->save();
            }
            $employees = Employee::where('phone', 'like', '%-%')->get();
            foreach($employees as $employee){
                $phone = $employee->phone;
                $phone = str_replace(' ', '', $phone);
                $phone = str_replace('-', '', $phone);
                $phone = str_replace('.', '', $phone);
                $phone = str_replace('+62 ', '0', $phone);
                $phone = str_replace('+62', '0', $phone);
                $employee->phone =$phone;
                $employee->save();
            }

            $users2 = User::where('phone', 'like', '00%')->get();
            foreach($users2 as $user){
                $phone = $user->phone;
                $phone = str_replace('+62 ', '0', $phone);
                $phone = str_replace('+62', '0', $phone);
                $user->phone =$phone;
                $user->save();
            }
            $employees2 = Employee::where('phone', 'like', '00%')->get();
            foreach($employees2 as $employee){
                $phone = $employee->phone;
                $phone = str_replace('+62 ', '0', $phone);
                $phone = str_replace('+62', '0', $phone);
                $employee->phone =$phone;
                $employee->save();
            }

            $users3 = User::where('phone', 'like', '00%')->where('id', '>', 29)->get();
            foreach($users3 as $user){
                $phone = $user->phone;
                $phone = str_replace('00', '0', $phone);
                $user->phone =$phone;
                $user->save();
            }
            $employees3 = Employee::where('phone', 'like', '00%')->where('id', '>', 29)->get();
            foreach($employees3 as $employee){
                $phone = $employee->phone;
                $phone = str_replace('00', '0', $phone);
                $employee->phone =$phone;
                $employee->save();
            }

            dd('success');
            $employees = User::all();
            $arrTemp = collect();
            foreach($employees as $employee){
                $employeePhone = $employee->phone;
                if (strpos($employeePhone, '-') !== false) {
                    $phone = str_replace('-', '', $employeePhone);
                    $arr = [
                        'before' => $employeePhone,
                        'after' => $phone,
                    ];
                    $employee->phone = $phone;
                    $employee->save();
                    $arrTemp->push($arr);
                }
            }
            dd($arrTemp);
//            $data = json_encode(['Element 1','Element 2','Element 3','Element 4','Element 5']);
//            $attenDBs = AttendanceAbsent::all();
//            foreach($attenDBs as $attenDB){
//                $data =
//            }
            $now = Carbon::now('Asia/Jakarta')->toDateTimeString();
            Log::channel('in_sys')
                ->info('API/HomeController - datetime now = '.$now);
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


    public function logFunctionTesting(){

        $employeeNUC = Employee::where('code', "0002")->first();

        Log::channel('user_activity')
            ->info("\tApi/UserController - checkUserNUC\tPhone kosong ".$employeeNUC->phone."\tBelum ada nomor handphone");
        Log::channel('user_activity')
            ->info("\tApi/UserController - checkUserNUC\tPhone Sudah ada ".$employeeNUC->phone."\tBelum ada nomor handphone");
        Log::channel('user_activity')
            ->info("\tApi/UserController - checkUserNUC\tEmployee Code tidak ditemukan (0002)\tBelum ada nomor handphone");

        Log::channel('user_activity')
            ->info("\tApi/UserController - saveUserPhone\tNo Handphone sudah ada (".$employeeNUC->phone.")\tSudah ada nomor handphone");
        Log::channel('user_activity')
            ->info("\tApi/UserController - saveUserPhone\tNo Handphone sudah ada di employee lain (".$employeeNUC->phone.")\tSudah ada nomor handphone");
        Log::channel('user_activity')
            ->info("\tApi/UserController - saveUserPhone\tEmployee ".$employeeNUC->first_name.''.$employeeNUC->last_name."(".$employeeNUC->code.") mengganti nomor handphone ke 00000000 \tSuccess Save User new Phone");

        return "done";
    }

    //testing api function

    public function submitIntegrationEmployee(){
        try {
            $data = '[{"code":"10001","first_name":"Charles Iskandar","last_name":"","phone":null,"dob":"1989-03-04","nik":"10001","address":"-","role":1,"job_code":"CLEANER","job_name":"CS-CLEANER"}]';
            $employees = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - employees DATA : '.json_encode($employees));
//            dd(count($employees));

//            $nonActiveEmp = DB::statement("update employees set status_id = 2 where id > 29 and status_id = 1 and employee_role_id < 4");
//            sleep(60);

            $ct = 1;
            foreach ($employees as $employee) {
                if($ct %2000 == 0){
                    sleep(30);
                }
                $rules = array(
                    'code'          => 'required',
                    'first_name'    => 'required',
//                    'last_name'     => 'required',
//                    'phone'         => 'required',
                    'dob'           => 'required',
                    'nik'           => 'required',
//                    'address'       => 'required',
                    'role'          => 'required'
                );

                $validator = Validator::make($employee, $rules);

                if ($validator->fails()) {
                    return Response::json([
                        'errors'=> $validator->messages(),
                        'meta'  => [
                            'http_status' => 400
                        ]
                    ], 400);
                }

                try{
                    $phone = "";
                    if(!empty($employee['phone'])){
                        if($employee['phone'] == "-" || $employee['phone'] == "--" ||
                            $employee['phone'] == " " || $employee['phone'] == "" || $employee['phone'] == " " ||
                            $employee['phone'] == "XXX" || $employee['phone'] == "12345"){
                            $phone = "";
                        }
                        else{
                            $phone = $employee['phone'];
                        }
                    }
                    $phone = str_replace(' ', '', $phone);
                    $phone = str_replace('-', '', $phone);
                    $phone = str_replace('.', '', $phone);
                    $phone = str_replace('+62 ', '0', $phone);
                    $phone = str_replace('+62', '0', $phone);
                    $employeeChecking = Employee::where('code', $employee['code'])->first();
//                    if (!DB::table('employees')->where('code', $employee['code'])->exists()) {
                    if (empty($employeeChecking)) {
                        $nEmployee = Employee::create([
                            'code' => $employee['code'],
                            'first_name' => $employee['first_name'],
                            'last_name' => $employee['last_name'] ?? "",
//                            'phone' => $phone,
                            'dob' => $employee['dob'],
                            'nik' => $employee['nik'],
                            'address' => $employee['address'],
                            'employee_role_id' => $employee['role'],
                            'status_id' => 1
                        ]);

                        User::create([
                            'employee_id' => $nEmployee->id,
                            'name' => $employee['first_name'] . ' ' . $employee['last_name'] ?? "",
//                            'phone' => $phone,
                            'status_id' => 1,
                            'password' => Hash::make('carefastid')
                        ]);
                    }
                    else {
                        $employeeChecking = Employee::where('code', $employee['code'])->first();
                        $employeeChecking->first_name = $employee['first_name'];
                        $employeeChecking->last_name = $employee['last_name'] ?? "";
//                        $employeeChecking->phone = $phone;
                        $employeeChecking->dob = $employee['dob'];
                        $employeeChecking->nik = $employee['nik'];
                        $employeeChecking->employee_role_id = $employee['role'];
                        $employeeChecking->address = $employee['address'] ?? "";
                        $employeeChecking->status_id = 1;
                        $employeeChecking->save();

                        $oUser = User::where('employee_id', $employeeChecking->id)->first();
                        if(empty($oUser)){
                            User::create([
                                'employee_id' => $employeeChecking->id,
                                'name' => $employee['first_name'] . ' ' . $employee['last_name'] ?? "",
//                                'phone' => $phone,
                                'status_id' => 1,
                                'password' => Hash::make('carefastid')
                            ]);
                        }
                        else{
//                            $oUser->phone = $phone;
                            $oUser->name = $employee['first_name'] . ' ' . $employee['last_name'] ?? "";
                            $oUser->status_id = 1;
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
                $ct++;
            }

//            sleep(30);
//            $nonActiveEmpPhone = DB::statement("update employees set phone = '' where status_id = 2");
//            $nonActiveUserPhone = DB::statement("update users as a, employees as b set a.status_id = 2, a.phone = '' where a.employee_id = b.id and b.status_id = 2");

            Log::channel('in_sys')
                ->info('API/IntegrationController - employees PROCESS DONE');
            return Response::json([
                'message' => 'Success Updating Employee Data!'
            ], 200);
        }
        catch (\Exception $ex){
            dd($ex);
            Log::channel('in_sys')->error('API/IntegrationController - employees error EX: '. $ex);
//            Log::error('API/IntegrationController - employees error EX: '. $ex);
            return Response::json([
                'message' => $ex
            ], 500);
        }
    }

    public function submitIntegrationProject(){

        try {
            $data = '[{"code":"CFHO","name":"CAREFAST HO","description":"CAREFAST HO","phone":null,"start_date":"2014-09-30","finish_date":"2050-10-15","address":null}]';
            $projects = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - projects DATA : '.json_encode($projects));

            foreach ($projects as $project) {

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
            $data = '[{"project_code":"CFHO","employee_codes":["1000002","1000003","1000004","1000005","1000006","1000007"]}]';
            $projects = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - jobAssignments DATA : '.json_encode($projects));
//            Log::channel('in_sys')
//                ->info('API/IntegrationController - jobAssignments PROJECT COUNT : '.$projects->count());

            foreach ($projects as $project){

                if(DB::table('projects')->where('code', strval($project['project_code']))->exists()){
                    $nProject = Project::where('code', $project['project_code'])->first();

                    $projectEmployees = ProjectEmployee::where('project_id', $nProject->id)
                        ->where('status_id', 1)
                        ->where('employee_roles_id', '<', 4)
                        ->get();
                    foreach($projectEmployees as $projectEmployee){
                        $projectEmployee->status_id = 0;
                        $projectEmployee->save();
                    }
//                    Log::channel('in_sys')->info('API/IntegrationController - jobAssignments checkpoint 1 change status employee, project='.$nProject->code);

                    foreach ($project['employee_codes'] as $employee){
                        if(DB::table('employees')->where('code', strval($employee))->exists()){
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

    public function submitIntegrationGetAttendance(){
        try{
            $projectCode = '01060103';
            $startDate = '2021-02-16';
            $endDate = "2021-03-15";
//            Log::channel('in_sys')
//                ->info('API/IntegrationController - getAttendances data projectCode = '. $projectCode . " | beginDate = ".$startDate." | endDate = ".$endDate);
            $project = Project::where('code', $projectCode)->first();

            if(!DB::table('projects')->where('code', $projectCode)->exists()){
                return Response::json([
                    'error' => 'Project code not found!'
                ], 400);
            }
            if(empty($startDate) || empty($endDate)){
                return Response::json([
                    'error' => 'Please provide Begin Date and End Date!'
                ], 400);
            }

            $startDateMonth = Carbon::parse($startDate)->format('Y-m');
            $endDateMonth = Carbon::parse($endDate)->format('Y-m');

//            $dataModel = AttendanceProcess::DownloadAttendanceProcessV2($project, $startDate, $startDateMonth, $endDate, $endDateMonth);
            $dataModel = AttendanceProcess::DownloadAttendanceProcessV4($project, $startDate, $startDateMonth, $endDate, $endDateMonth);

            $date = Carbon::now('Asia/Jakarta')->timestamp;
            $returnModel = collect([
                'timestamp'     => $date,
                'projectCode'   => $projectCode,
                'beginDate'     => $startDate,
                'endDate'       => $endDate,
                'data'          => $dataModel,
            ]);
            return Response::json($returnModel, 200);
        }
        catch (\Exception $ex){
            Log::channel('in_sys')->error('API/IntegrationController - getAttendances error EX: '. $ex);
            return Response::json([
                'error' => $ex
            ], 500);
        }
    }
}

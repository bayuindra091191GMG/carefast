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
use Intervention\Image\Facades\Image;
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

            $now = Carbon::now('Asia/Jakarta');
            $data = "Employee Code\tEmployee Name\tEmployee Phone\tProject\tTotal Valid Absensi\tTotal Invalid Absensi\n";

            // checking for double user START
//            $asdf = DB::table('employees')
//                ->select('id','code','first_name','last_name','dob','address', DB::raw('count(*) as total'))
//                ->groupBy('first_name', DB::raw('`address` HAVING count(*)> 1 '))
//                ->get();
//            foreach ($asdf as $employee){
//                $countEmployee = DB::table('employees')
//                    ->Where('first_name', $employee->first_name)
//                    ->Where('address', $employee->address)
//                    ->Where('dob', $employee->dob)
//                    ->get();
//                foreach ($countEmployee as $count){
//                    $data .= $count->code."\t";
//                    $data .= $count->first_name." ".$count->last_name."\t";
//                    $data .= $count->address."\t";
//                    $data .= $count->dob."\t";
//
//                    $user = DB::table('users')
//                        ->where('employee_id', $count->id)
//                        ->first();
//                    $data .= "'".$user->phone."\n";
//                }
//            }
//            $file = "double-user_".$now->format('Y-m-d')."-".time().'.txt';
            // checking for double user END

            // checking attendance START
            $allEmployee = Employee::where('status_id', 1)->where('id', '>', 29)->get();
            foreach ($allEmployee as $employee){
                $data .= $employee->code."\t";
                $data .= $employee->first_name." ".$employee->last_name."\t";

                $user = DB::table('users')
                    ->where('employee_id', $employee->id)
                    ->first();
                $data .= "'".$user->phone."\t";

                $projectEmployeeCount = ProjectEmployee::where('employee_id', $employee->id)->count();

                if($projectEmployeeCount == 1){
                    $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)
//                    ->where('status_id', 1)
                        ->first();
                }
                else{
                    $projectEmployee = ProjectEmployee::where('employee_id', $employee->id)
                    ->where('status_id', 1)
                        ->first();
                }


                if(!empty($projectEmployee)){
                    $project = Project::where('id', $projectEmployee->project_id)
                        ->first();
                    $data .= $project->name."\t";
                }
                else{
                    $data .= "-"."\t";
                }

                if(DB::table('attendance_absents')
                    ->where('employee_id', $employee->id)
                    ->exists()){

                    $countA = DB::table('attendance_absents')
                        ->where('employee_id', $employee->id)
                        ->where('status_id', 6)
                        ->where('is_done', 1)
                        ->count();
                    $data .= $countA."\t";
                    $countB = DB::table('attendance_absents')
                        ->where('employee_id', $employee->id)
                        ->where('status_id', 6)
                        ->where('is_done', 0)
                        ->count();
                    $data .= $countB."\n";
                }
                else{
                    $data .= "-\t-\n";
                }
            }
            $file = "attendance-checking_".$now->format('Y-m-d')."-".time().'.txt';
            // checking attendance END

            $destinationPath=public_path()."/download_attendance/";
            if (!is_dir($destinationPath)) {  mkdir($destinationPath,0777,true);  }
            File::put($destinationPath.$file, $data);
            return response()->download($destinationPath.$file);


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


    //testing api function

    public function submitIntegrationEmployee(){
        try {
            $data = '';
            $employees = json_decode($data, true);
            Log::channel('in_sys')
                ->info('API/IntegrationController - employees DATA : '.json_encode($employees));
//            dd(count($employees));

            $nonActiveEmp = DB::statement("update employees set status_id = 2 where id > 29 and status_id = 1 and employee_role_id < 4");
            sleep(60);

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
//                    $tempEmployee = TempInsysEmploye::create([
//                        'code' => $employee['code'],
//                        'first_name' => $employee['first_name'],
//                        'last_name' => $employee['last_name'],
//                        'phone' => $employee['phone'],
//                        'dob' => $employee['dob'],
//                        'nik' => $employee['nik'],
//                        'address' => $employee['address'],
//                        'role' => $employee['role'],
//                    ]);


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

            sleep(30);
            $nonActiveEmpPhone = DB::statement("update employees set phone = '' where status_id = 2");
            $nonActiveUserPhone = DB::statement("update users as a, employees as b set a.status_id = 2, a.phone = '' where a.employee_id = b.id and b.status_id = 2");

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
            $data = '';
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
}

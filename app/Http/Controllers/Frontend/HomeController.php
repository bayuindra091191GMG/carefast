<?php

namespace App\Http\Controllers\Frontend;

use App\Imports\CustomerImport;
use App\Imports\InitialDataImport;
use App\Mail\EmailVerification;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Place;
use App\Models\ProjectEmployee;
use App\Models\Schedule;
use App\Models\User;
use App\Notifications\FCMNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;
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
            $excel = request()->file('excel');
//            Excel::import(new InitialDataImport(), $excel);
            Excel::import(new CustomerImport(), $excel);

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
    public function testEmail(){
        try{
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
            return $ex;
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

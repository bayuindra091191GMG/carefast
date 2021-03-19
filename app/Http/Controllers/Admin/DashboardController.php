<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard(){
        //general data
        $userAdmin = Auth::guard('admin')->user();
        $totalProjects = DB::table('projects')
            ->select('id')
            ->where('status_id', 1)
            ->count();
//        $totalProjects = Project::where('status_id', 1)->count();
        $totalEmployees = DB::table('employees')
            ->select('id')
            ->where('status_id', 1)
            ->count();
//        $totalEmployees = Employee::where('status_id', 1)->count();

//        $totalComplaintPendings = Complaint::where('status_id', 10)->count();
//        $totalComplaintOnprogress = Complaint::where('status_id', 11)->count();

        //complain process
        $filterDateStart = Carbon::today()->subMonths(1)->format('d M Y');
        $filterDateEnd = Carbon::today()->format('d M Y');
        $start = Carbon::createFromFormat('d M Y', $filterDateStart, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('d M Y', $filterDateEnd, 'Asia/Jakarta');
        $totalComplaintCustomers = DB::table('complaints')
            ->select('id')
            ->where('status_id', 10)
            ->where('customer_id', '!=', null)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->count();
//        $totalComplaintCustomers = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
//            ->where('status_id', 10)->where('customer_id', '!=', null)->count();
        $totalComplaintInternals = DB::table('complaints')
            ->select('id')
            ->where('status_id', 10)
            ->where('employee_id', '!=', null)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->count();
//        $totalComplaintInternals = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
//            ->where('status_id', 10)->where('employee_id', '!=', null)->count();

        //attendance process
        $totalAttendances = DB::table('attendance_absents')
            ->select('id')
            ->where('status_id', 6)
            ->where('is_done', 1)
            ->count();
//        $totalAttendances = AttendanceAbsent::where('status_id', 6)->where('is_done', 1)->count();
        $totalAttendanceMonths = DB::table('attendance_absents')
            ->select('id')
            ->where('status_id', 6)
            ->where('is_done', 1)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->count();
//        $totalAttendanceMonths = AttendanceAbsent::where('status_id', 6)->where('is_done', 1)
//            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
//            ->count();
        $startDateMonth = Carbon::parse($end)->format('Y-m-d');
        $totalAttendanceTodays = DB::table('attendance_absents')
            ->select('id')
            ->where('status_id', 6)
            ->where('is_done', 1)
            ->whereBetween('date', array($startDateMonth.' 00:00:00', $end->toDateTimeString()))
            ->count();

        $totalAttendanceTodayNotDone = DB::table('attendance_absents')
            ->select('id')
            ->where('status_id', 6)
            ->where('is_done', 0)
            ->whereBetween('date', array($startDateMonth.' 00:00:00', $end->toDateTimeString()))
            ->count();

        $data = [
            'userAdmin'                     => $userAdmin,
            'totalProjects'                => $totalProjects,
            'totalEmployees'               => $totalEmployees,
//            'totalComplaintPendings'       => $totalComplaintPendings,
//            'totalComplaintOnprogress'     => $totalComplaintOnprogress,
            'filterDateStart'     => $filterDateStart,
            'filterDateEnd'     => $filterDateEnd,
            'totalComplaintCustomers'       => $totalComplaintCustomers,
            'totalComplaintInternals'     => $totalComplaintInternals,
            'totalAttendances'     => $totalAttendances,
            'totalAttendanceMonths'     => $totalAttendanceMonths,
            'totalAttendanceTodays'     => $totalAttendanceTodays,
            'totalAttendanceTodayNotDone'     => $totalAttendanceTodayNotDone,
        ];
        return view('admin.dashboard')->with($data);
    }
}

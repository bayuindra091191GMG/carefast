<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard(){
        //general data
        $userAdmin = Auth::guard('admin')->user();
        $totalProjects = Project::where('status_id', 1)->count();
        $totalEmployees = Employee::where('status_id', 1)->count();

//        $totalComplaintPendings = Complaint::where('status_id', 10)->count();
//        $totalComplaintOnprogress = Complaint::where('status_id', 11)->count();

        //complain process
        $filterDateStart = Carbon::today()->subMonths(1)->format('d M Y');
        $filterDateEnd = Carbon::today()->format('d M Y');
        $start = Carbon::createFromFormat('d M Y', $filterDateStart, 'Asia/Jakarta');
        $end = Carbon::createFromFormat('d M Y', $filterDateEnd, 'Asia/Jakarta');
        $totalComplaintCustomers = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('status_id', 10)->where('customer_id', '!=', null)->count();
        $totalComplaintInternals = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('status_id', 10)->where('employee_id', '!=', null)->count();

        //attendance process
        $totalAttendances = AttendanceAbsent::where('status_id', 6)->where('is_done', 1)->count();
        $totalCheckins = AttendanceAbsent::where('status_id', 6)->where('is_done', 1)
            ->whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
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
            'totalCheckins'     => $totalCheckins,
        ];
        return view('admin.dashboard')->with($data);
    }
}

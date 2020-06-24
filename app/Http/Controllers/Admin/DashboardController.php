<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard(){
        $userAdmin = Auth::guard('admin')->user();
        $totalProjects = Project::where('status_id', 1)->count();
        $totalEmployees = Employee::where('status_id', 1)->count();
        $totalComplaintPendings = Complaint::where('status_id', 10)->count();
        $totalComplaintOnprogress = Complaint::where('status_id', 11)->count();

        $data = [
            'userAdmin'                     => $userAdmin,
            'totalProjects'                => $totalProjects,
            'totalEmployees'               => $totalEmployees,
            'totalComplaintPendings'       => $totalComplaintPendings,
            'totalComplaintOnprogress'     => $totalComplaintOnprogress,
        ];
        return view('admin.dashboard')->with($data);
    }
}

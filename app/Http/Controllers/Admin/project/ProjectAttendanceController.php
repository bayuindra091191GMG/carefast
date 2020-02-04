<?php


namespace App\Http\Controllers\Admin\project;


use App\Http\Controllers\Controller;
use App\Models\AttendanceAbsent;
use App\Models\Customer;
use App\Models\CustomerType;
use App\Models\EmployeeRole;
use App\Models\Project;
use App\Transformer\AttendanceTransformer;
use App\Transformer\CustomerTransformer;
use App\Transformer\EmployeeTransformer;
use App\Transformer\ProjectTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Yajra\DataTables\DataTables;

class ProjectAttendanceController extends Controller
{
    public function show($id){
        try{
            $project = Project::find($id);
            return view('admin.project.attendance.show', compact('project'));
        }
        catch (\Exception $ex){
            Log::error('Admin/information/ProjectAttendanceController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $id = $request->input('id');

        $customers = AttendanceAbsent::where('project_id', $id)->orderby('created_at', 'desc')->get();
        return DataTables::of($customers)
            ->setTransformer(new AttendanceTransformer())
            ->make(true);
    }
}

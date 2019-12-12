<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\Project;
use App\Transformer\ComplaintTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class ComplaintController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(Request $request){
        try{
            $filterDateStart = Carbon::today()->subMonths(1)->format('d M Y');
            $filterDateEnd = Carbon::today()->format('d M Y');
            $filterCategory = 0;
            $filterProject = 0;
            $filterStatus = 0;

            if($request->date_start != null && $request->date_end != null){
                $dateStartDecoded = rawurldecode($request->date_start);
                $dateEndDecoded = rawurldecode($request->date_end);
                $start = Carbon::createFromFormat('!d M Y', $dateStartDecoded, 'Asia/Jakarta');
                $end = Carbon::createFromFormat('!d M Y', $dateEndDecoded, 'Asia/Jakarta');
                $end = $end->addDays(1);

                if($end->greaterThanOrEqualTo($start)){
                    $filterDateStart = $dateStartDecoded;
                    $filterDateEnd = $dateEndDecoded;
                }
            }
            if($request->category_id != null){
                $filterCategory = $request->category_id;
            }
            if($request->project_id != null){
                $filterProject = $request->project_id;
            }
            if($request->status_id != null){
                $filterStatus = $request->status_id;
            }

            $projects = Project::where('status_id', 1)->orderBy('name')->get();
            $categories = ComplaintCategory::all();
            $data = [
                'projects'              => $projects,
                'categories'            => $categories,
                'filterDateStart'       => $filterDateStart,
                'filterDateEnd'         => $filterDateEnd,
                'filterCategory'        => $filterCategory,
                'filterProject'         => $filterProject,
                'filterStatus'          => $filterStatus
            ];

            if($request->type == "customers"){
                return view('admin.complaint.index-customers')->with($data);
            }
            else if($request->type == "internals"){
                return view('admin.complaint.index-internals')->with($data);
            }
            else if($request->type == "others"){
                return view('admin.complaint.index-others')->with($data);

            }
//            return view('admin.complaint.index')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/ComplaintHeaderController - index error EX: '. $ex);
            return 'Internal Server Error';
        }
    }

    public function getIndexCustomers(Request $request){
        $start = Carbon::createFromFormat('d M Y', $request->input('date_start'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('d M Y', $request->input('date_end'), 'Asia/Jakarta');
        $start->subDays(1);
        $end->addDays(1);
        $categoryId = $request->input('category_id');
        $projectId = $request->input('project_id');
        $statusId = $request->input('status_id');

        $complaints = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('customer_id', '!=', null);

        if($statusId != 0) {
            $complaints = $complaints->where('status_id', $statusId);
        }
        if($projectId != 0) {
            $complaints = $complaints->where('project_id', $projectId);
        }
        if($categoryId != 0) {
            $complaints = $complaints->where('category_id', $categoryId);
        }

        return DataTables::of($complaints)
            ->setTransformer(new ComplaintTransformer)
            ->make(true);
    }

    public function getIndexInternals(Request $request){
        $start = Carbon::createFromFormat('d M Y', $request->input('date_start'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('d M Y', $request->input('date_end'), 'Asia/Jakarta');
        $start->subDays(1);
        $end->addDays(1);
        $projectId = $request->input('project_id');
        $statusId = $request->input('status_id');

        $complaints = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->where('employee_id', '!=', null);

        if($statusId != 0) {
            $complaints = $complaints->where('status_id', $statusId);
        }
        if($projectId != 0) {
            $complaints = $complaints->where('project_id', $projectId);
        }

        return DataTables::of($complaints)
            ->setTransformer(new ComplaintTransformer)
            ->make(true);
    }

    public function getIndexOthers(Request $request){
        $start = Carbon::createFromFormat('d M Y', $request->input('date_start'), 'Asia/Jakarta');
        $end = Carbon::createFromFormat('d M Y', $request->input('date_end'), 'Asia/Jakarta');
        $start->subDays(1);
        $end->addDays(1);

        $complaints = Complaint::whereBetween('date', array($start->toDateTimeString(), $end->toDateTimeString()))
            ->get();

        return DataTables::of($complaints)
            ->setTransformer(new ComplaintTransformer)
            ->make(true);
    }

    public  function show(int $id){
        $complaint = Complaint::find($id);
        if(empty($complaint)){
            return redirect()->back();
        }
        $details = $complaint->complaint_details;
        $complaintDetailSorted = $details->sortByDesc('id');

        $data = [
            'complaint'     => $complaint,
            'complaintDetails'     => $complaintDetailSorted,
        ];

        return view('admin.complaint.show')->with($data);
    }
}

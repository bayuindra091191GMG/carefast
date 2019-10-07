<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Complaint;
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

            if($request->date_start != null && $request->date_end != null){
                $dateStartDecoded = rawurldecode($request->date_start);
                $dateEndDecoded = rawurldecode($request->date_end);
                $start = Carbon::createFromFormat('d M Y', $dateStartDecoded, 'Asia/Jakarta');
                $end = Carbon::createFromFormat('d M Y', $dateEndDecoded, 'Asia/Jakarta');

                if($end->greaterThanOrEqualTo($start)){
                    $filterDateStart = $dateStartDecoded;
                    $filterDateEnd = $dateEndDecoded;
                }
            }

            $data = [
                'filterDateStart'   => $filterDateStart,
                'filterDateEnd'     => $filterDateEnd
            ];

            return view('admin.complaint.index')->with($data);
        }
        catch (\Exception $ex){
            Log::error('Admin/ComplaintHeaderController - index error EX: '. $ex);
            return 'Internal Server Error';
        }
    }

    public function getIndex(Request $request){
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

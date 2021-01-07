<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\ImeiHistory;
use App\Transformer\ImeiTransformer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Rap2hpoutre\FastExcel\FastExcel;
use Yajra\DataTables\DataTables;

class ImeiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function show(){
        try{
            $histories = ImeiHistory::all();
            return view('admin.imei.show', compact('histories'));
        }
        catch (\Exception $ex){
            Log::error('Admin/ImeiController - index error EX: '. $ex);
            return "Something went wrong! Please contact administrator!";
        }
    }

    public function getIndex(Request $request){
        $id = $request->input('id');

        $customers = ImeiHistory::with(['employee']);

        return DataTables::of($customers)
            ->setTransformer(new ImeiTransformer())
            ->make(true);
    }

    public function downloadImeiHistory(Request $request)
    {
        $startDateRequest = $request->input('start_date');
        $startDate = Carbon::parse($startDateRequest)->format('Y-m-d H:i:s');
        $endDateRequest = $request->input('end_date');
        $endDate = Carbon::parse($endDateRequest)->format('Y-m-d H:i:s');

        $imeiHistories = DB::table('imei_histories')
            ->join('employees', 'imei_histories.employee_id', '=', 'employees.id')
            ->select('imei_histories.nuc as nuc',
                'imei_histories.imei_old as imei_old',
                'imei_histories.phone_type_old as phone_type_old',
                'imei_histories.imei_new as imei_new',
                'imei_histories.phone_type_new as phone_type_new',
                'imei_histories.created_at as created_at',
                'employees.id as employee_id',
                'employees.first_name as employee_first_name',
                'employees.last_name as employee_last_name')
            ->whereBetween('imei_histories.created_at', array($startDate.' 00:00:00', $endDate.' 23:59:00'))
            ->orderBy('imei_histories.created_at')
            ->get();

        $now = Carbon::now('Asia/Jakarta');
        $list = collect();
        foreach($imeiHistories as $imeiHistory){
            $userPhone = DB::table('users')
                ->select('users.phone as user_phone')
                ->where('users.employee_id', $imeiHistory->employee_id)
                ->first();

            $createdAt = Carbon::parse($imeiHistory->created_at);
            $singleData = ([
                'Employee NUC'     => $imeiHistory->nuc,
                'Employee Name'     => $imeiHistory->employee_first_name." ".$imeiHistory->employee_last_name,
                'Employee Phone'    => $userPhone->user_phone ?? "",
                'Changing Date'     => $createdAt,
                'Old Imei'          => $imeiHistory->imei_old,
                'Old Phone'         => $imeiHistory->phone_type_old,
                'New Imei'          => $imeiHistory->imei_new,
                'New Phone'         => $imeiHistory->phone_type_new,
            ]);
            $list->push($singleData);
        }

        $destinationPath = public_path()."/download_imei/";
        $file = 'Daftar_Ganti_Imei_'.$now->format('Y-m-d')."-".time().'.xlsx';

        (new FastExcel($list))->export($destinationPath.$file);

        return response()->download($destinationPath.$file);
    }
}

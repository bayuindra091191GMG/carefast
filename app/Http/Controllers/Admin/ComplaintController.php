<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\CustomerComplaint;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ComplaintController extends Controller
{
    public function index(){
        return view('admin.customer_complaint.index');
    }

    public function getIndex(Request $request){
        $complaints = Complaint::all();
        return DataTables::of($complaints)
            ->setTransformer(new EmployeeTransformer())
            ->make(true);
    }
}

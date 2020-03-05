<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function dashboard(){
        $userAdmin = Auth::guard('admin')->user();

        $data = [
            'userAdmin'                     => $userAdmin,
        ];
        return view('admin.dashboard')->with($data);
    }
}

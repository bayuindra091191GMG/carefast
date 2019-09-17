<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ScriptController extends Controller
{
    public function scriptCreateUsers(){
        $employees = Employee::where('employee_role_id', '!=', 9)->get();

        foreach ($employees as $employee){
            $employee->phone = '123456789';
            $employee->save();

            $name = $employee->first_name. ' '. $employee->last_name;
            User::create([
                'employee_id'       => $employee->id,
                'name'              => $name,
                'password'          => Hash::make('admin'),
                'phone'             => '123456789',
                'status_id'         => 1
            ]);
        }

        return 'SCRIPT SUCCESS!!';
    }
}

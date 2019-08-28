<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function getEmployees()
    {
        error_log("exception");
        try{

            $employees = Employee::all();

            return Response::json([
                'message' => "Success Getting Employee Data!",
                'model'     => json_encode($employees)
            ], 200);
        }
        catch(\Exception $ex){
            error_log($ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @param $id
     * @return JsonResponse
     */
    public function getEmployeeDetail($id)
    {
        error_log("exception");
        try{

            $employee = Employee::find($id)->get();

            return Response::json([
                'message' => "Success Getting Employee Detail!",
                'model'     => json_encode($employee)
            ], 200);
        }
        catch(\Exception $ex){
            error_log($ex);
            return Response::json([
                'message' => "Sorry Something went Wrong!",
                'ex' => $ex,
            ], 500);
        }
    }
}

<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

class SalesOrderHeaderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){

    }

    public function getIndex(){

    }
}
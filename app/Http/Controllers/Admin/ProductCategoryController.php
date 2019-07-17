<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

class ProductCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index(){

    }
}
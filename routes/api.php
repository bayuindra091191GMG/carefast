<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/get-users', 'Api\UserController@index');

//User Management
Route::middleware('auth:api')->group(function(){
    Route::get('/get-users', 'Api\UserController@index');
    Route::get('/registration', 'Api\RegisterController@registrationData');
    Route::post('/register', 'Api\RegisterController@register');
});
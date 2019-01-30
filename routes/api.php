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

Route::middleware('auth:api')->get('/users', 'Api\UserController@index');
Route::get('/noauth/users', 'Api\UserController@index');

//User Management
Route::middleware('auth:api')->group(function(){
    Route::get('/get-users', 'Api\UserController@index');
    Route::post('/get-user-data', 'Api\UserController@show');
    Route::get('/waste-banks', 'Api\WasteBankController@getData');
    Route::get('/check-category', 'Api\GeneralController@checkCategory');
    Route::get('/dws-category', 'Api\DwsWasteController@getData');
    Route::get('/masaro-category', 'Api\MasaroWasteController@getData');
    Route::post('/get-transactions', 'Api\TransactionHeaderController@getTransactions');
    Route::post('/get-transaction-details', 'Api\TransactionHeaderController@getTransactionDetails');
    Route::post('/get-dws-items', 'Api\DwsWasteController@getItems');
    Route::post('/closest-waste-banks', 'Api\WasteBankController@getClosestWasteBanks');
});

Route::post('/register', 'Api\RegisterController@register');
Route::get('/verifyemail/{token}', 'Api\RegisterController@verify');

//Forgot Password
Route::post('/checkEmail', 'Api\ForgotPasswordController@checkEmail');
Route::post('/sendResetLinkEmail', 'Api\ForgotPasswordController@sendResetLinkEmail');
Route::post('/setNewPassword', 'Api\ForgotPasswordController@setNewPassword');
Route::get('/registration', 'Api\RegisterController@registrationData');

Route::group(['namespace' => 'Api', 'middleware' => 'api', 'prefix' => 'password'], function () {
    Route::post('forgotpassword', 'ForgotPasswordController@forgotPassword');
    Route::get('find/{token}', 'ForgotPasswordController@find');
    Route::post('reset', 'ForgotPasswordController@reset');
});

//Beta
Route::post('/subscribe', 'Api\SubscribeController@save');
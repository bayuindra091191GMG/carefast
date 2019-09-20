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
Route::post('/closest-waste-banks', 'Api\WasteBankController@getClosestWasteBanks');
Route::get('/dws-category', 'Api\DwsWasteController@getData');
Route::get('/masaro-category', 'Api\MasaroWasteController@getData');
//Route::post('/waste-banks/get-schedules', 'Api\WasteBankController@getWasteBankSchedules');
//Route::post('/on-demand/create', 'Api\TransactionHeaderController@createTransaction');
//Route::post('/routine-pickup', 'Api\UserController@changeRoutinePickup');

// Register
Route::post('/register', 'Api\RegisterController@register');
Route::get('/verifyemail/{token}', 'Api\RegisterController@verify');
Route::post('/external-register', 'Api\RegisterController@externalAuth');
Route::post('/register/exist/email', 'Api\RegisterController@isEmailExist');
Route::post('/register/exist/phone', 'Api\RegisterController@isPhoneExist');

//User Management
//Route::group(['namespace' => 'Api', 'middleware' => 'api', 'prefix' => 'user'], function () {
Route::middleware('auth:api')->prefix('user')->group(function(){
    //New Route Start
    //Place
    Route::get('/places/get/qr-code', 'Api\PlaceController@qrCode');

    // Attendance
    Route::post('/attendance/submit', 'Api\AttendanceController@submit');
    Route::post('/attendance/checking', 'Api\AttendanceController@checkinChecking');

    //Employee
    Route::get('/employee/get', 'Api\EmployeeController@getEmployees');
    Route::get('/employee/schedule', 'Api\EmployeeController@employeeSchedule');
    Route::post('/employee/get-detail/', 'Api\EmployeeController@getEmployeeDetail');
    //New Route Finish

    Route::get('/testing', 'Api\UserController@testingAuthToken');
    Route::get('/get-users', 'Api\UserController@index');
    Route::get('/get-user-data', 'Api\UserController@show');
    Route::post('/save-user-device', 'Api\UserController@saveUserToken');
    Route::get('/waste-banks', 'Api\WasteBankController@getData');
    Route::get('/check-category', 'Api\GeneralController@checkCategory');
    Route::get('/address', 'Api\UserController@getAddress');
    Route::post('/set-address', 'Api\UserController@setAddress');
    Route::post('/profile/update', 'Api\UserController@updateProfile');

    //Transactions
    Route::get('/get-transactions', 'Api\TransactionHeaderController@getTransactions');
    Route::post('/get-transaction-details', 'Api\TransactionHeaderController@getTransactionDetails');
    Route::post('/get-transaction-data', 'Api\TransactionHeaderController@getTransactionData');

    //Antar Sendiri
    Route::post('/antar-sendiri/transactions', 'Api\TransactionHeaderController@getTransactionAntarSendiriForCustomer');
    Route::post('/antar-sendiri/confirm', 'Api\TransactionHeaderController@confirmTransactionByUserAntarSendiri');
    Route::post('/antar-sendiri/cancel', 'Api\TransactionHeaderController@cancelTransactionByUserAntarSendiri');

    //On Demand
    Route::post('/on-demand/transactions', 'Api\TransactionHeaderOnDemandController@getTransactionOnDemandForCustomer');
    Route::post('/on-demand/create', 'Api\TransactionHeaderController@createTransaction');
    Route::post('/on-demand/create/dev', 'Api\TransactionHeaderController@createTransactionDev');
//    Route::post('/waste-collector/on-demand/confirm', 'Api\TransactionHeaderController@confirmTransactionByDriver');
    Route::post('/on-demand/confirm', 'Api\TransactionHeaderController@confirmTransactionByUser');
    Route::post('/on-demand/reject', 'Api\TransactionHeaderController@cancelTransactionByUserOnDemand');

    //Routine
    Route::post('/routine/transactions', 'Api\TransactionHeaderRoutineController@getTransactionRoutineForCustomer');
    Route::post('/routine/confirm', 'Api\TransactionHeaderController@confirmTransactionByUserRoutinePickup');
    Route::post('/routine/cancel', 'Api\TransactionHeaderController@cancelTransactionByUserRoutinePickup');

    //Voucher
    Route::get('/voucher-categories', 'Api\VoucherController@getCategories');
    Route::post('/vouchers', 'Api\VoucherController@get');
    Route::post('/vouchers/all', 'Api\VoucherController@getAll');
    Route::post('/vouchers/buy', 'Api\VoucherController@buy');
    Route::post('/vouchers/redeem', 'Api\VoucherController@redeem');
    Route::get('/vouchers/list', 'Api\VoucherController@getAllUserVoucher');

    //Routine Pickup
    Route::post('/change-routine-pickup', 'Api\UserController@changeRoutinePickup');
    Route::get('/waste-banks/get-schedules', 'Api\WasteBankController@getWasteBankSchedules');
    Route::get('/waste-banks/get-schedule-today', 'Api\WasteBankController@getWasteBankSchedule');

    //Point
    Route::get('/point/get', 'Api\PoinController@getCustomerPoint');
    Route::post('/redeem-poin', 'Api\PoinController@redeem');
});


//Route::group(['namespace' => 'Api', 'middleware' => 'waste_collector', 'prefix' => 'waste-collector'], function () {
Route::middleware('auth:customer')->prefix('customer')->group(function(){
    //customer complain
    Route::post('/complain-submit', 'Api\ComplainController@submit');

    Route::post('/save-customer-device', 'Api\CustomerController@saveCustomerToken');
    Route::get('/get-data', 'Api\CustomerController@show');
});


//Admin Wastebank
Route::middleware('auth:admin')->group(function(){
    //Antar Sendiri
    Route::post('/admin/confirm-transaction', 'Api\AdminController@confirmTransactionAntarSendiri');
    Route::get('/admin/transactions/get', 'Api\AdminController@getTransactionList');
    Route::post('/admin/set-transaction', 'Api\AdminController@setTransactionToUser');
    Route::get('/admin/antar-sendiri/pending-transactions', 'Api\TransactionHeaderController@getTransactionAntarSendiriForAdmin');
});



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
Route::post('/submit-demo', 'Api\SubscribeController@demoSubmit');

//Coba2
Route::post('/test', 'Api\TransactionHeaderController@test');


// Yifang

// Dashboard
Route::get('/dashboard', 'Api\DashboardController@getData');

Route::middleware('auth:api')->group(function() {
    // Cart
    Route::get('/cart', 'Api\CartController@getCart');
    Route::post('/cart/add', 'Api\CartController@addToCart');
    Route::post('/cart/update', 'Api\CartController@updateCart');
    Route::post('/cart/delete', 'Api\CartController@deleteCart');

    //Orders
    Route::post('/order', 'Api\TransactionHeaderController@getTransactions');
    Route::post('/order/detail', 'Api\TransactionHeaderController@getTransactionData');
});

// Product
Route::get('/product/get', 'Api\ProductController@getAllProduct');
Route::get('/product/show', 'Api\ProductController@show');

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

// External API (For InSys)
Route::middleware('auth:external', 'throttle:5000,1')->prefix('integration')->group(function() {
    Route::post('/employees', 'Api\IntegrationController@employees');
    Route::post('/projects', 'Api\IntegrationController@projects');
    Route::post('/job_assignments', 'Api\IntegrationController@jobAssignments');
    Route::get('/attendance', 'Api\IntegrationController@getAttendances');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->get('/users', 'Api\UserController@index');
Route::get('/noauth/users', 'Api\UserController@index');


// Register
Route::post('/register', 'Api\RegisterController@register');
Route::get('/verifyemail/{token}', 'Api\RegisterController@verify');
Route::post('/external-register', 'Api\RegisterController@externalAuth');
Route::post('/register/exist/email', 'Api\RegisterController@isEmailExist');
Route::post('/register/exist/phone', 'Api\RegisterController@isPhoneExist');

Route::post('/places/get/qr-code', 'Api\PlaceController@qrCode');
Route::get('/complaint-categories', 'Api\ComplainController@complaintCategories');

//User Management
//Route::group(['namespace' => 'Api', 'middleware' => 'api', 'prefix' => 'user'], function () {
Route::middleware('auth:api')->prefix('user')->group(function(){
    Route::get('/get-user-data', 'Api\UserController@show');
    Route::post('/save-user-device', 'Api\UserController@saveUserToken');
    Route::post('/logout', 'Api\UserController@logout');

    //Place
    Route::post('/places/get/place-by-qr', 'Api\PlaceController@getPlaceByQr');

    // Attendance job
    Route::get('/attendance/checkin/checking', 'Api\AttendanceController@checkinChecking');
    Route::get('/attendance/checking', 'Api\AttendanceAbsentController@attendanceChecking');
    Route::post('/attendance/checkin', 'Api\AttendanceController@submitCheckin');
    Route::post('/attendance/checkout', 'Api\AttendanceController@submitCheckout');
    Route::post('/attendance/leader/checkin', 'Api\AttendanceController@submitCheckinByLeader');
    Route::post('/attendance/leader/checkout', 'Api\AttendanceController@submitCheckoutByLeader');
    Route::post('/attendance/leader/assessment', 'Api\AttendanceController@leaderSubmit');

    // Attendance absent
    Route::post('/attendance/qrcode', 'Api\AttendanceAbsentController@getProjectCodeEncrypted');
    Route::post('/attendance/in', 'Api\AttendanceAbsentController@attendanceIn');
    Route::post('/attendance/out', 'Api\AttendanceAbsentController@attendanceOut');
//    Route::post('/attendance/absent/checkout', 'Api\AttendanceAbsentController@submitCheckout');

    //Employee
    Route::get('/employee/get', 'Api\EmployeeController@getEmployees');
    Route::get('/employee/schedule', 'Api\EmployeeController@employeeSchedule');
    Route::post('/employee/leader/schedule', 'Api\EmployeeController@employeeScheduleByLeader');
    Route::post('/employee/get-detail/', 'Api\EmployeeController@getEmployeeDetail');
    Route::get('/employee/get-direct-cso/', 'Api\EmployeeController@getEmployeeCSO');
    Route::post('/employee/get-cso-by-project/', 'Api\EmployeeController@getEmployeeCSOByProject');
    Route::get('/employee/get-assessment-history/', 'Api\EmployeeController@employeeAssessments');

    // Complaint
    Route::post('/get-complaints', 'Api\ComplainController@getComplaintEmployee');
    Route::post('/get-complaint-header', 'Api\ComplainController@getComplaintHeader');
    Route::post('/get-complaint-details', 'Api\ComplainController@getComplaintDetail');
    Route::get('/get-projects', 'Api\ComplainController@getProjectListEmployee');
    Route::post('/complaint-close', 'Api\ComplainController@closeComplaintEmployee');
    Route::post('/complaint-create', 'Api\ComplainController@createComplaintEmployee');
    Route::post('/complaint-reply', 'Api\ComplainController@replyComplaintEmployee');

    //Plotting
    Route::get('/plotting/get-employees', 'Api\EmployeeController@getEmployeeCSO');
    Route::get('/plotting/get-plottings', 'Api\EmployeeController@getPlottings');
    Route::get('/plotting/get-dacs', 'Api\EmployeeController@getDacs');
    Route::post('/plotting/submit-plottings', 'Api\EmployeeController@submitPlottings');

});


//Route::group(['namespace' => 'Api', 'middleware' => 'waste_collector', 'prefix' => 'waste-collector'], function () {
Route::middleware('auth:customer')->prefix('customer')->group(function(){
    Route::post('/save-customer-device', 'Api\CustomerController@saveCustomerToken');
    Route::get('/get-data', 'Api\CustomerController@show');

    //customer complain
    Route::post('/complaint-create', 'Api\ComplainController@createComplaintCustomer');
    Route::post('/complaint-reply', 'Api\ComplainController@replyComplaintCustomer');
    Route::post('/complaint-close', 'Api\ComplainController@closeComplaint');
    Route::get('/get-projects', 'Api\ComplainController@getProjectListCustomer');
    Route::post('/get-complaints', 'Api\ComplainController@getComplaint');
    Route::post('/get-complaint-header', 'Api\ComplainController@getComplaintHeader');
    Route::post('/get-complaint-details', 'Api\ComplainController@getComplaintDetail');
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

// Dashboard
Route::get('/dashboard', 'Api\DashboardController@getData');

Route::middleware('auth:api')->group(function() {
    // Cart
    Route::get('/cart', 'Api\CartController@getCart');
    Route::post('/cart/add', 'Api\CartController@addToCart');
    Route::post('/cart/update', 'Api\CartController@updateCart');
    Route::post('/cart/delete', 'Api\CartController@deleteCart');

});

// Product
Route::get('/product/get', 'Api\ProductController@getAllProduct');
Route::get('/product/show', 'Api\ProductController@show');

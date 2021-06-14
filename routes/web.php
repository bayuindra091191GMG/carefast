<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Route::get('/', function () {
//    return view('welcome');
//});

use Illuminate\Support\Facades\Redirect;

Auth::routes();


//Route::get('/', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
Route::get('/', function () {
    return Redirect::away('https://www.carefast.co.id/');
});
Route::get('/icare', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
//Route::get('/test-notif', 'Frontend\HomeController@testNotif')->name('testNotif');
//Route::get('/test-email', 'Frontend\HomeController@testEmail')->name('testEmail');
//Route::get('/test-notif-send', 'Frontend\HomeController@testNotifSend')->name('testNotifSend');
Route::get('/android-notif-send', 'Frontend\HomeController@testNotifSendToAndroid')->name('testNotifSend');
//Route::get('/integration-project', 'Frontend\HomeController@submitIntegrationProject');
//Route::get('/integration-employee', 'Frontend\HomeController@submitIntegrationEmployee');
//Route::get('/integration-jobassign', 'Frontend\HomeController@submitIntegrationJobAssigment');
Route::get('/integration/get-attendance', 'Frontend\HomeController@submitIntegrationGetAttendance');
//Route::get('/checkin', 'Frontend\HomeController@attendanceIn');
//Route::get('/test-general', 'Frontend\HomeController@generalFunction');
Route::get('/test-function', 'Frontend\HomeController@testingFunction');
//Route::get('/test-logging', 'Frontend\HomeController@logFunctionTesting');
//Route::get('download-jsonfile', array('as'=> 'file.download', 'uses' => 'Frontend\HomeController@generalFunction'));

// Import
//Route::get('/import/form', 'Frontend\HomeController@form')->name('import.form');
//Route::post('/import/form/submit', 'Frontend\HomeController@importExcel')->name('import.submit');
// Android Id Change
Route::get('/imei/form', 'Frontend\HomeController@AndroidIdform')->name('imei.form');
Route::post('/imei/form/submit', 'Frontend\HomeController@AndroidIdProcess')->name('imei.submit');

// ADMIN ROUTE
// ====================================================================================================================

Route::post('/keluar', 'Admin\AdminController@logout')->name('admin.keluar');

Route::prefix('admin')->group(function(){
    Route::get('/testing', 'Admin\AdminController@test')->name('admin.test');
//    Route::get('/', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
    Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
    Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');

    // Android Id
    Route::get('/imei/show', 'Admin\ImeiController@show')->name('admin.imei.show');
    Route::post('/imei/download', 'Admin\ImeiController@downloadImeiHistory')->name('admin.imei.download');

    // Setting
    Route::get('/setting', 'Admin\AdminController@showSetting')->name('admin.setting');
    Route::post('/setting-update', 'Admin\AdminController@saveSetting')->name('admin.setting.update');
    Route::get('/setting/password', 'Admin\SettingController@editPassword')->name('admin.setting.password.edit');
    Route::post('/setting/password/update', 'Admin\SettingController@updatePassword')->name('admin.setting.password.update');

    // Token
    Route::post('/save-token', 'Admin\AdminController@saveUserToken')->name('admin.save.token');

    // Admin User
    Route::get('/admin-users', 'Admin\AdminUserController@index')->name('admin.admin-users.index');
    Route::get('/admin-users/create', 'Admin\AdminUserController@create')->name('admin.admin-users.create');
    Route::post('/admin-users/store', 'Admin\AdminUserController@store')->name('admin.admin-users.store');
    Route::get('/admin-users/edit/{item}', 'Admin\AdminUserController@edit')->name('admin.admin-users.edit');
    Route::post('/admin-users/update', 'Admin\AdminUserController@update')->name('admin.admin-users.update');
    Route::post('/admin-users/delete', 'Admin\AdminUserController@destroy')->name('admin.admin-users.destroy');

    // User
    Route::get('/users', 'Admin\UserController@index')->name('admin.users.index');
    Route::get('/users/create', 'Admin\UserController@create')->name('admin.users.create');
    Route::post('/users/store', 'Admin\UserController@store')->name('admin.users.store');
    Route::get('/users/edit/{item}', 'Admin\UserController@edit')->name('admin.users.edit');
    Route::post('/users/update', 'Admin\UserController@update')->name('admin.users.update');
    Route::post('/users/delete', 'Admin\UserController@destroy')->name('admin.users.destroy');

    // User Category
    Route::get('/user_categories', 'Admin\UserCategoryController@index')->name('admin.user_categories.index');
    Route::get('/user_categories/create', 'Admin\UserCategoryController@create')->name('admin.user_categories.create');
    Route::post('/user_categories/store', 'Admin\UserCategoryController@store')->name('admin.user_categories.store');
    Route::get('/user_categories/edit/{id}', 'Admin\UserCategoryController@edit')->name('admin.user_categories.edit');
    Route::post('/user_categories/update/{id}', 'Admin\UserCategoryController@update')->name('admin.user_categories.update');
    Route::post('/user_categories/delete', 'Admin\UserCategoryController@destroy')->name('admin.user_categories.destroy');

    // Category
    Route::get('/categories', 'Admin\CategoryController@index')->name('admin.categories.index');
    Route::get('/categories/create', 'Admin\CategoryController@create')->name('admin.categories.create');
    Route::post('/categories/store', 'Admin\CategoryController@store')->name('admin.categories.store');
    Route::get('/categories/edit/{item}', 'Admin\CategoryController@edit')->name('admin.categories.edit');
    Route::post('/categories/update', 'Admin\CategoryController@update')->name('admin.categories.update');
    Route::post('/categories/delete', 'Admin\CategoryController@destroy')->name('admin.categories.destroy');


    // Import
    Route::get('/import/form', 'Admin\ImportController@form')->name('admin.import.form');
    Route::post('/import/form/submit', 'Admin\ImportController@importExcel')->name('admin.import.submit');
    Route::get('/import/address/auto', 'Admin\ImportController@autoAddress')->name('admin.import.address.auto');

    // Employee
    Route::get('/employee', 'Admin\EmployeeController@index')->name('admin.employee.index');
    Route::get('/employee/create', 'Admin\EmployeeController@create')->name('admin.employee.create');
    Route::get('/employee/show/{id}', 'Admin\EmployeeController@show')->name('admin.employee.show');
    Route::get('/employee/edit/{id}', 'Admin\EmployeeController@edit')->name('admin.employee.edit');
    Route::post('/employee/store', 'Admin\EmployeeController@store')->name('admin.employee.store');
    Route::post('/employee/update/{id}', 'Admin\EmployeeController@update')->name('admin.employee.update');
    Route::post('/employee/destroy', 'Admin\EmployeeController@destroy')->name('admin.employee.destroy');
    Route::get('/employee/detail/{id}', 'Admin\EmployeeController@detail')->name('admin.employee.detail-attendance');
    Route::post('/employee/downloadNucPhone', 'Admin\EmployeeController@downloadNucPhone')->name('admin.employee.download-nucphone');

    Route::get('/employee/schedule/set/{employee_id}', 'Admin\EmployeeController@scheduleEdit')->name('admin.employee.set-schedule');
    Route::post('/employee/schedule/store/{employee_id}', 'Admin\EmployeeController@scheduleStore')->name('admin.employee.store-schedule');


    // Employee Role
    Route::get('/employee_role', 'Admin\EmployeeRoleController@index')->name('admin.employee_role.index');
    Route::get('/employee_role/create', 'Admin\EmployeeRoleController@create')->name('admin.employee_role.create');
    Route::get('/employee_role/show/{id}', 'Admin\EmployeeRoleController@show')->name('admin.employee_role.show');
    Route::get('/employee_role/edit/{id}', 'Admin\EmployeeRoleController@edit')->name('admin.employee_role.edit');
    Route::post('/employee_role/store', 'Admin\EmployeeRoleController@store')->name('admin.employee_role.store');
    Route::post('/employee_role/update/{id}', 'Admin\EmployeeRoleController@update')->name('admin.employee_role.update');
    Route::post('/employee_role/destroy', 'Admin\EmployeeRoleController@destroy')->name('admin.employee_role.destroy');

    // Customer
    Route::get('/customer', 'Admin\CustomerController@index')->name('admin.customer.index');
    Route::get('/customer/create', 'Admin\CustomerController@create')->name('admin.customer.create');
    Route::get('/customer/show/{id}', 'Admin\CustomerController@show')->name('admin.customer.show');
    Route::get('/customer/edit/{id}', 'Admin\CustomerController@edit')->name('admin.customer.edit');
    Route::post('/customer/store', 'Admin\CustomerController@store')->name('admin.customer.store');
    Route::post('/customer/update/{id}', 'Admin\CustomerController@update')->name('admin.customer.update');
    Route::post('/customer/destroy', 'Admin\CustomerController@destroy')->name('admin.customer.destroy');

    // Unit
    Route::get('/unit', 'Admin\UnitController@index')->name('admin.unit.index');
    Route::get('/unit/create', 'Admin\UnitController@create')->name('admin.unit.create');
    Route::get('/unit/edit/{id}', 'Admin\UnitController@edit')->name('admin.unit.edit');
    Route::post('/unit/store', 'Admin\UnitController@store')->name('admin.unit.store');
    Route::post('/unit/update/{id}', 'Admin\UnitController@update')->name('admin.unit.update');
    Route::post('/unit/destroy', 'Admin\UnitController@destroy')->name('admin.unit.destroy');

    //Sub1Unit
    Route::get('/sub1unit', 'Admin\Sub1UnitController@index')->name('admin.sub1unit.index');
    Route::get('/sub1unit/create', 'Admin\Sub1UnitController@create')->name('admin.sub1unit.create');
    Route::get('/sub1unit/edit/{id}', 'Admin\Sub1UnitController@edit')->name('admin.sub1unit.edit');
    Route::post('/sub1unit/store', 'Admin\Sub1UnitController@store')->name('admin.sub1unit.store');
    Route::post('/sub1unit/update/{id}', 'Admin\Sub1UnitController@update')->name('admin.sub1unit.update');
    Route::post('/sub1unit/destroy', 'Admin\Sub1UnitController@destroy')->name('admin.sub1unit.destroy');

    //Sub2Unit
    Route::get('/sub2unit', 'Admin\Sub2UnitController@index')->name('admin.sub2unit.index');
    Route::get('/sub2unit/create', 'Admin\Sub2UnitController@create')->name('admin.sub2unit.create');
    Route::get('/sub2unit/edit/{id}', 'Admin\Sub2UnitController@edit')->name('admin.sub2unit.edit');
    Route::post('/sub2unit/store', 'Admin\Sub2UnitController@store')->name('admin.sub2unit.store');
    Route::post('/sub2unit/update/{id}', 'Admin\Sub2UnitController@update')->name('admin.sub2unit.update');
    Route::post('/sub2unit/destroy', 'Admin\Sub2UnitController@destroy')->name('admin.sub2unit.destroy');

    // Place
    Route::get('/place', 'Admin\PlaceController@index')->name('admin.place.index');
    Route::get('/place/create', 'Admin\PlaceController@create')->name('admin.place.create');
    Route::get('/place/edit/{id}', 'Admin\PlaceController@edit')->name('admin.place.edit');
    Route::post('/place/store', 'Admin\PlaceController@store')->name('admin.place.store');
    Route::post('/place/update/{id}', 'Admin\PlaceController@update')->name('admin.place.update');
    Route::post('/place/destroy', 'Admin\PlaceController@destroy')->name('admin.place.destroy');

    // Action
    Route::get('/action', 'Admin\ActionController@index')->name('admin.action.index');
    Route::get('/action/create', 'Admin\ActionController@create')->name('admin.action.create');
    Route::get('/action/edit/{id}', 'Admin\ActionController@edit')->name('admin.action.edit');
    Route::post('/action/store', 'Admin\ActionController@store')->name('admin.action.store');
    Route::post('/action/update/{id}', 'Admin\ActionController@update')->name('admin.action.update');
    Route::post('/action/destroy', 'Admin\ActionController@destroy')->name('admin.action.destroy');

    // Project
    Route::get('/project', 'Admin\project\ProjectController@index')->name('admin.project.information.index');
    Route::get('/project/show/{id}', 'Admin\project\ProjectController@show')->name('admin.project.information.show');
    Route::get('/project/create', 'Admin\project\ProjectController@create')->name('admin.project.information.create');
    Route::get('/project/edit/{id}', 'Admin\project\ProjectController@edit')->name('admin.project.information.edit');
    Route::post('/project/store', 'Admin\project\ProjectController@store')->name('admin.project.information.store');
    Route::post('/project/update/{id}', 'Admin\project\ProjectController@update')->name('admin.project.information.update');
    Route::post('/project/destroy', 'Admin\project\ProjectController@destroy')->name('admin.project.information.destroy');

    // Project Object
    Route::get('/project/object/show/{id}', 'Admin\project\ProjectObjectController@show')->name('admin.project.object.show');
    Route::get('/project/object/create/{id}', 'Admin\project\ProjectObjectController@create')->name('admin.project.object.create');
    Route::get('/project/object/edit/{id}', 'Admin\project\ProjectObjectController@edit')->name('admin.project.object.edit');
    Route::post('/project/object/store', 'Admin\project\ProjectObjectController@store')->name('admin.project.object.store');
    Route::post('/project/object/update/{id}', 'Admin\project\ProjectObjectController@update')->name('admin.project.object.update');
    Route::get('/project/object/qr_code/{id}', 'Admin\project\ProjectObjectController@qrcode')->name('admin.project.object.qrcode');

    // Project Employee
    Route::get('/project/employee/show/{project_id}', 'Admin\project\ProjectEmployeeController@show')->name('admin.project.employee.show');
    Route::get('/project/employee/show/{project_id}', 'Admin\project\ProjectEmployeeController@show')->name('admin.project.employee.show');
    Route::get('/project/employee/create/{project_id}', 'Admin\project\ProjectEmployeeController@create')->name('admin.project.employee.create');
    Route::get('/project/employee/edit/{project_id}', 'Admin\project\ProjectEmployeeController@edit')->name('admin.project.employee.edit');
    Route::get('/project/employee/edit-employee/{project_id}', 'Admin\project\ProjectEmployeeController@editEmployee')->name('admin.project.employee.edit-employee');
    Route::get('/project/employee/set/{project_id}', 'Admin\project\ProjectEmployeeController@edit')->name('admin.project.employee.set');
    Route::post('/project/employee/store/{project_id}', 'Admin\project\ProjectEmployeeController@store')->name('admin.project.employee.store');
    Route::post('/project/employee/update/{project_id}', 'Admin\project\ProjectEmployeeController@update')->name('admin.project.employee.update');


    // Project Attendance
    Route::get('/project/attendance/show/{id}', 'Admin\project\ProjectAttendanceController@show')->name('admin.project.attendance.show');
    Route::post('/project/attendance/download', 'Admin\project\ProjectAttendanceController@downloadAttendance')->name('admin.project.attendance.download');
    Route::get('/project/attendance/download-form', 'Admin\project\ProjectAttendanceController@downloadForm')->name('admin.project.attendance.download-form');
    Route::post('/project/attendance/download-all', 'Admin\project\ProjectAttendanceController@downloadAllAttendance')->name('admin.project.attendance.download-all');
    Route::get('/project/attendance/download-file/{filename}', 'Admin\project\ProjectAttendanceController@downloadAllAttendanceFile')->name('admin.project.attendance.download-file');


    // Project Plotting
    Route::get('/project/activity/show/{id}', 'Admin\project\ActivityController@show')->name('admin.project.activity.show');
    Route::get('/project/activity/copy/{id}', 'Admin\project\ActivityController@show')->name('admin.project.activity.copy');

    Route::get('/project/activity/create-step-1/{id}', 'Admin\project\ActivityController@createStepOne')->name('admin.project.activity.create');
    Route::post('/project/activity/create/', 'Admin\project\ActivityController@submitCreateOne')->name('admin.project.activity.store-one');
//    Route::get('/project/activity/create-step-2/', 'Admin\project\ActivityController@createStepTwo')->name('admin.project.activity.create-two');
    Route::post('/project/activity/store', 'Admin\project\ActivityController@store')->name('admin.project.activity.store');

    Route::get('/project/activity/edit/{id}', 'Admin\project\ActivityController@edit')->name('admin.project.activity.edit');
    Route::post('/project/activity/update/{id}', 'Admin\project\ActivityController@update')->name('admin.project.activity.update');

    // Project Schedule
//    Route::get('/project/schedule/show/{id}', 'Admin\project\ProjectScheduleController@show')->name('admin.project.schedule.show');
//    Route::get('/project/schedule/create/{employee_id}', 'Admin\project\ProjectScheduleController@create')->name('admin.project.schedule.create');
    Route::get('/project/schedule/edit/{employee_id}', 'Admin\project\ProjectScheduleController@edit')->name('admin.project.schedule.edit');
//    Route::post('/project/schedule/store', 'Admin\project\ProjectScheduleController@store')->name('admin.project.schedule.store');
    Route::post('/project/schedule/update/{employee_id}', 'Admin\project\ProjectScheduleController@update')->name('admin.project.schedule.update');

    // Project Schedule New
    //set project employee's schedule
//    Route::get('/project/schedule/set/{id}', 'Admin\project\ScheduleController@scheduleEdit')->name('admin.project.set-schedule');
    Route::get('/project/schedule/set/{id}', 'Admin\project\ScheduleController@scheduleEditv2')->name('admin.project.set-schedule');
    Route::post('/project/schedule/upload/{id}', 'Admin\project\ScheduleController@scheduleUploadExcel')->name('admin.project.upload-schedule');
    Route::get('/project/schedule/edit-employee/{id}', 'Admin\project\ScheduleController@scheduleEditEmployee')->name('admin.project.schedule-edit-employee');
    Route::post('/project/schedule/update-employee/{id}', 'Admin\project\ScheduleController@scheduleUpdateEmployee')->name('admin.project.schedule-update-employee');
    Route::get('/project/schedule/upload/download-template/{id}', 'Admin\project\ScheduleController@scheduleDownloadExcelTemplate')->name('admin.project.upload-template-download');
    Route::get('/project/schedule/download/{id}', 'Admin\project\ScheduleController@scheduleDownloadExcel')->name('admin.project.download-schedule');
    Route::post('/project/schedule/store/{id}', 'Admin\project\ScheduleController@scheduleStore')->name('admin.project.store-schedule');

    Route::get('/project/schedule/edit-shift/{id}', 'Admin\project\ScheduleController@editProjectShift')->name('admin.project.edit-shift');
    Route::post('/project/schedule/update-shift/{id}', 'Admin\project\ScheduleController@updateProjectShift')->name('admin.project.update-shift');

    Route::get('/project/schedule/show/{id}', 'Admin\project\ScheduleController@show')->name('admin.project.schedule.show');
    Route::get('/project/schedule/create/{id}', 'Admin\project\ScheduleController@create')->name('admin.project.schedule.create');
    Route::post('/project/schedule/store', 'Admin\project\ScheduleController@store')->name('admin.project.schedule.store');

    Route::get('/project/schedule/create-detail/{employee_id}', 'Admin\project\ProjectScheduleController@createDetail')->name('admin.project.schedule.create-detail');
    Route::get('/project/schedule/edit-detail/{employee_id}', 'Admin\project\ProjectScheduleController@editDetail')->name('admin.project.schedule.edit-detail');
    Route::post('/project/schedule/store-detail', 'Admin\project\ProjectScheduleController@storeDetail')->name('admin.project.schedule.store-detail');
    Route::post('/project/schedule/update-detail/{employee_id}', 'Admin\project\ProjectScheduleController@updateDetail')->name('admin.project.schedule.update-detail');

    // Report
    Route::get('/transaction/report', 'Admin\ReportController@transactionReport')->name('admin.transaction.report');
    Route::post('/transaction/report/submit', 'Admin\ReportController@transactionReportSubmit')->name('admin.transaction.report.submit');

    // Complaint
    Route::get('/complaint', 'Admin\ComplaintController@index')->name('admin.complaint.index');
    Route::get('/complaint/show/{id}', 'Admin\ComplaintController@show')->name('admin.complaint.show');
});

Route::get('/verifyemail/{token}', 'Auth\RegisterController@verify');

Route::view('/send-email', 'auth.send-email');

// Datatables
Route::get('/datatables-menu-headers', 'Admin\MenuHeaderController@getIndex')->name('datatables.menu_headers');
Route::get('/datatables-menus', 'Admin\MenuController@getIndex')->name('datatables.menus');
Route::get('/datatables-menu-subs', 'Admin\SubMenuController@getIndex')->name('datatables.menu-subs');
Route::get('/datatables-admin-users', 'Admin\AdminUserController@getIndex')->name('datatables.admin_users');
Route::get('/datatables-admin-products', 'Admin\ProductController@getIndex')->name('datatables.admin_products');
Route::get('/datatables-users', 'Admin\UserController@getIndex')->name('datatables.users');
Route::get('/datatables-categories', 'Admin\CategoryController@getIndex')->name('datatables.categories');
Route::get('/datatables-orders', 'Admin\OrderController@getIndex')->name('datatables.orders');
Route::get('/datatables-user-categories', 'Admin\UserCategoryController@getIndex')->name('datatables.user_categories');
Route::get('/datatables-faqs', 'Admin\FaqController@getIndex')->name('datatables.faqs');
Route::get('/datatables-permission-menus', 'Admin\PermissionMenuController@getIndex')->name('datatables.permission-menus');
Route::get('/datatables-products', 'Admin\ProductController@getIndex')->name('datatables.products');
Route::get('/datatables-product-categories', 'Admin\ProductCategoryController@getIndex')->name('datatables.product.categories');
Route::get('/datatables-product-brands', 'Admin\ProductBrandController@getIndex')->name('datatables.product.brands');
Route::get('/datatables-product-customizations', 'Admin\ProductController@getIndexCustomize')->name('datatables.product.customizations');
Route::get('/datatables-employee', 'Admin\EmployeeController@getIndex')->name('datatables.employees');
Route::get('/datatables-employee_role', 'Admin\EmployeeRoleController@getIndex')->name('datatables.employee_roles');
Route::get('/datatables-banner', 'Admin\BannerController@getIndex')->name('datatables.banners');
Route::get('/datatables-place', 'Admin\PlaceController@getIndex')->name('datatables.places');
Route::get('/datatables-unit', 'Admin\UnitController@getIndex')->name('datatables.units');
Route::get('/datatables-action', 'Admin\ActionController@getIndex')->name('datatables.actions');
Route::get('/datatables-customer', 'Admin\CustomerController@getIndex')->name('datatables.customers');
Route::get('/datatables-project', 'Admin\project\ProjectController@getIndex')->name('datatables.projects');
Route::get('/datatables-sub1unit', 'Admin\Sub1UnitController@getIndex')->name('datatables.sub1_units');
Route::get('/datatables-sub2unit', 'Admin\Sub2UnitController@getIndex')->name('datatables.sub2_units');
Route::get('/datatables-project_schedule_employees', 'Admin\project\ProjectScheduleController@getScheduleEmployees')->name('datatables.project_schedule_employees');
Route::get('/datatables-attendances', 'Admin\AttendanceController@getIndex')->name('datatables.attendances');
Route::get('/datatables-complaint-customers', 'Admin\ComplaintController@getIndexCustomers')->name('datatables.complaint-customers');
Route::get('/datatables-complaint-internals', 'Admin\ComplaintController@getIndexInternals')->name('datatables.complaint-internals');
Route::get('/datatables-complaint-others', 'Admin\ComplaintController@getIndexOthers')->name('datatables.complaint-others');
Route::get('/datatables-project-activity', 'Admin\project\ActivityController@getIndexActivities')->name('datatables.project-activity');
Route::get('/datatables-project-attendance', 'Admin\project\ProjectAttendanceController@getIndex')->name('datatables.project-attendance');
Route::get('/datatables-imei-history', 'Admin\ImeiController@getIndex')->name('datatables.imei-history');

// Select2
Route::get('/select-customers', 'Admin\CustomerController@getCustomers')->name('select.customers');
Route::get('/select-places', 'Admin\PlaceController@getPlaces')->name('select.places');
Route::get('/select-placeProjects', 'Admin\PlaceController@getPlaceProjects')->name('select.placeProjects');
Route::get('/select-units', 'Admin\UnitController@getUnits')->name('select.units');
Route::get('/select-sub1unit-dropdown', 'Admin\Sub1UnitController@getSub1UnitDropdowns')->name('select.sub1unit-dropdown');
Route::get('/select-sub1units', 'Admin\Sub1UnitController@getSub1Units')->name('select.sub1units');
Route::get('/select-sub2unit-dropdown', 'Admin\Sub2UnitController@getSub2UnitDropdowns')->name('select.sub2unit-dropdown');
Route::get('/select-sub2units', 'Admin\Sub2UnitController@getSub2Units')->name('select.sub2units');
Route::get('/select-upper-employees', 'Admin\EmployeeController@getUpperEmployees')->name('select.upper.employees');
Route::get('/select-cleaner-employees', 'Admin\EmployeeController@getCleanerEmployees')->name('select.cleaner.employees');
Route::get('/select-employees', 'Admin\EmployeeController@getEmployees')->name('select.employees');
Route::get('/select-employees', 'Admin\EmployeeController@getEmployees')->name('select.employees');
Route::get('/select-projectObjects', 'Admin\project\ProjectObjectController@getProjectObjects2')->name('select.projectObjects');
Route::get('/select-projectObjectActivities', 'Admin\project\ProjectObjectController@getProjectObjectActivities')->name('select.projectObjectActivities');
Route::get('/select-actions', 'Admin\ActionController@getActions')->name('select.actions');
Route::get('/select-projects', 'Admin\project\ProjectController@getProjects')->name('select.projects');
Route::get('/select-fms', 'Admin\project\ProjectController@getFms')->name('select.fms');

Route::get('/select-admin-users', 'Admin\AdminUserController@getAdminUsers')->name('select.admin-users');
Route::get('/select-user-categories', 'Admin\UserCategoryController@getCategories')->name('select.user-categories');
Route::get('/select-products', 'Admin\ProductController@getProducts')->name('select.products');
Route::get('/select-brands', 'Admin\ProductBrandController@getProductBrands')->name('select.banners');

// Email Aauth
Route::get('/request-verification/{email}', 'Auth\RegisterController@RequestVerification')->name('request-verification');

// Script
Route::get('/script/create-users', 'Admin\ScriptController@scriptCreateUsers');

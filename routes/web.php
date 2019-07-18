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

Auth::routes();


Route::get('/', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
// ADMIN ROUTE
// ====================================================================================================================

Route::post('/keluar', 'Admin\AdminController@logout')->name('admin.keluar');
Route::prefix('admin')->group(function(){
    Route::get('/testing', 'Admin\AdminController@test')->name('admin.test');
//    Route::get('/', 'Admin\DashboardController@dashboard')->name('admin.dashboard');
    Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('admin.login');
    Route::post('/login', 'Auth\AdminLoginController@login')->name('admin.login.submit');


    // Setting
    Route::get('/setting', 'Admin\AdminController@showSetting')->name('admin.setting');
    Route::post('/setting-update', 'Admin\AdminController@saveSetting')->name('admin.setting.update');

    // Token
    Route::post('/save-token', 'Admin\AdminController@saveUserToken')->name('admin.save.token');

    // Permission Menus
    Route::get('/permission-menus', 'Admin\PermissionMenuController@index')->name('admin.permission-menus.index');
    Route::get('permission-menus/detail/{permission_menu}', 'Admin\PermissionMenuController@show')->name('admin.permission-menus.show');
    Route::get('/permission-menus/create', 'Admin\PermissionMenuController@create')->name('admin.permission-menus.create');
    Route::post('/permission-menus/store', 'Admin\PermissionMenuController@store')->name('admin.permission-menus.store');
    Route::get('/permission-menus/edit/{permission_menu}', 'Admin\PermissionMenuController@edit')->name('admin.permission-menus.edit');
    Route::post('/permission-menus/update', 'Admin\PermissionMenuController@update')->name('admin.permission-menus.update');
    Route::post('/permission-menus/delete', 'Admin\PermissionMenuController@destroy')->name('admin.permission-menus.destroy');

    // Menus
    Route::get('/menus', 'Admin\MenuController@index')->name('admin.menus.index');
    Route::get('menus/detail/{menu}', 'Admin\MenuController@show')->name('admin.menus.show');
    Route::get('/menus/create', 'Admin\MenuController@create')->name('admin.menus.create');
    Route::post('/menus/store', 'Admin\MenuController@store')->name('admin.menus.store');
    Route::get('/menus/edit/{menu}', 'Admin\MenuController@edit')->name('admin.menus.edit');
    Route::post('/menus/update', 'Admin\MenuController@update')->name('admin.menus.update');
    Route::post('/menus/delete', 'Admin\MenuController@destroy')->name('admin.menus.destroy');

    // Sub Menus
    Route::get('/menu-subs', 'Admin\SubMenuController@index')->name('admin.menu-subs.index');
    Route::get('menu-subs/detail/{menu}', 'Admin\SubMenuController@show')->name('admin.menu-subs.show');
    Route::get('/menu-subs/create', 'Admin\SubMenuController@create')->name('admin.menu-subs.create');
    Route::post('/menu-subs/store', 'Admin\SubMenuController@store')->name('admin.menu-subs.store');
    Route::get('/menu-subs/edit/{menu}', 'Admin\SubMenuController@edit')->name('admin.menu-subs.edit');
    Route::post('/menu-subs/update', 'Admin\SubMenuController@update')->name('admin.menu-subs.update');
    Route::post('/menu-subs/delete', 'Admin\SubMenuController@destroy')->name('admin.menu-subs.destroy');

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
    Route::get('/user_categories/edit/{item}', 'Admin\UserCategoryController@edit')->name('admin.user_categories.edit');
    Route::post('/user_categories/update', 'Admin\UserCategoryController@update')->name('admin.user_categories.update');
    Route::post('/user_categories/delete', 'Admin\UserCategoryController@destroy')->name('admin.user_categories.destroy');

    // Category
    Route::get('/categories', 'Admin\CategoryController@index')->name('admin.categories.index');
    Route::get('/categories/create', 'Admin\CategoryController@create')->name('admin.categories.create');
    Route::post('/categories/store', 'Admin\CategoryController@store')->name('admin.categories.store');
    Route::get('/categories/edit/{item}', 'Admin\CategoryController@edit')->name('admin.categories.edit');
    Route::post('/categories/update', 'Admin\CategoryController@update')->name('admin.categories.update');
    Route::post('/categories/delete', 'Admin\CategoryController@destroy')->name('admin.categories.destroy');

    // FAQ
    Route::get('/faqs', 'Admin\FaqController@index')->name('admin.faqs.index');
    Route::get('/faqs/create', 'Admin\FaqController@create')->name('admin.faqs.create');
    Route::post('/faqs/store', 'Admin\FaqController@store')->name('admin.faqs.store');
    Route::get('/faqs/edit/{item}', 'Admin\FaqController@edit')->name('admin.faqs.edit');
    Route::post('/faqs/update', 'Admin\FaqController@update')->name('admin.faqs.update');
    Route::post('/faqs/delete', 'Admin\FaqController@destroy')->name('admin.faqs.destroy');

    // Product
    Route::get('/product/', 'Admin\ProductController@index')->name('admin.product.index');
    Route::get('/product/show/{item}', 'Admin\ProductController@show')->name('admin.product.show');
    Route::get('/product/create', 'Admin\ProductController@create')->name('admin.product.create');
    Route::post('/product/store', 'Admin\ProductController@store')->name('admin.product.store');

    Route::get('/product/create-customize/{item}', 'Admin\ProductController@createCustomize')->name('admin.product.create.customize');
    Route::post('/product/store-customize/{item}', 'Admin\ProductController@storeCustomize')->name('admin.product.store.customize');
    Route::get('/product/edit-customize/{item}', 'Admin\ProductController@editCustomize')->name('admin.product.edit.customize');
    Route::post('/product/update-customize/{item}', 'Admin\ProductController@updateCustomize')->name('admin.product.update.customize');
    Route::get('/product/edit/{item}', 'Admin\ProductController@edit')->name('admin.product.edit');

    // Import
    Route::get('/import/form', 'Admin\ImportController@form')->name('admin.import.form');
    Route::post('/import/form/submit', 'Admin\ImportController@importExcel')->name('admin.import.submit');
    Route::get('/import/address/auto', 'Admin\ImportController@autoAddress')->name('admin.import.address.auto');

    // Report
    Route::get('/transaction/report', 'Admin\ReportController@transactionReport')->name('admin.transaction.report');
    Route::post('/transaction/report/submit', 'Admin\ReportController@transactionReportSubmit')->name('admin.transaction.report.submit');
});

Route::get('/verifyemail/{token}', 'Auth\RegisterController@verify');

Route::view('/send-email', 'auth.send-email');

// Datatables
Route::get('/datatables-menus', 'Admin\MenuController@getIndex')->name('datatables.menus');
Route::get('/datatables-menu-subs', 'Admin\SubMenuController@getIndex')->name('datatables.menu-subs');
Route::get('/datatables-admin-users', 'Admin\AdminUserController@getIndex')->name('datatables.admin_users');
Route::get('/datatables-admin-products', 'Admin\ProductController@getIndex')->name('datatables.admin_products');
Route::get('/datatables-users', 'Admin\UserController@getIndex')->name('datatables.users');
Route::get('/datatables-categories', 'Admin\CategoryController@getIndex')->name('datatables.categories');
Route::get('/datatables-user-categories', 'Admin\UserCategoryController@getIndex')->name('datatables.user_categories');
Route::get('/datatables-faqs', 'Admin\FaqController@getIndex')->name('datatables.faqs');
Route::get('/datatables-permission-menus', 'Admin\PermissionMenuController@getIndex')->name('datatables.permission-menus');

// Select2
Route::get('/select-admin-users', 'Admin\AdminUserController@getAdminUsers')->name('select.admin-users');
Route::get('/select-user-categories', 'Admin\UserCategoryController@getCategories')->name('select.user-categories');
Route::get('/select-products', 'Admin\ProductController@getProducts')->name('select.products');

// Third Party API
Route::get('/update-currency', 'Admin\CurrencyController@getCurrenciesUpdate')->name('update-currencies');

// Email Aauth
Route::get('/request-verification/{email}', 'Auth\RegisterController@RequestVerification')->name('request-verification');
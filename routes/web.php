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
    Route::get('/setting/password', 'Admin\SettingController@editPassword')->name('admin.setting.password.edit');
    Route::post('/setting/password/update', 'Admin\SettingController@updatePassword')->name('admin.setting.password.update');

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

    // Menu Headers
    Route::get('/menu_headers', 'Admin\MenuHeaderController@index')->name('admin.menu_headers.index');
    Route::get('menu_headers/detail/{menu}', 'Admin\MenuHeaderController@show')->name('admin.menu_headers.show');
    Route::get('/menu_headers/create', 'Admin\MenuHeaderController@create')->name('admin.menu_headers.create');
    Route::post('/menu_headers/store', 'Admin\MenuHeaderController@store')->name('admin.menu_headers.store');
    Route::get('/menu_headers/edit/{menu}', 'Admin\MenuHeaderController@edit')->name('admin.menu_headers.edit');
    Route::post('/menu_headers/update', 'Admin\MenuHeaderController@update')->name('admin.menu_headers.update');
    Route::post('/menu_headers/delete', 'Admin\MenuHeaderController@destroy')->name('admin.menu_headers.destroy');

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

    // FAQ
    Route::get('/faqs', 'Admin\FaqController@index')->name('admin.faqs.index');
    Route::get('/faqs/create', 'Admin\FaqController@create')->name('admin.faqs.create');
    Route::post('/faqs/store', 'Admin\FaqController@store')->name('admin.faqs.store');
    Route::get('/faqs/edit/{item}', 'Admin\FaqController@edit')->name('admin.faqs.edit');
    Route::post('/faqs/update', 'Admin\FaqController@update')->name('admin.faqs.update');
    Route::post('/faqs/delete', 'Admin\FaqController@destroy')->name('admin.faqs.destroy');

    // Product
    Route::get('/product/', 'Admin\ProductController@index')->name('admin.product.index');
    Route::get('/product/show/{id}', 'Admin\ProductController@show')->name('admin.product.show');
    Route::get('/product/create', 'Admin\ProductController@create')->name('admin.product.create');
    Route::post('/product/store', 'Admin\ProductController@store')->name('admin.product.store');
    Route::get('/product/edit/{id}', 'Admin\ProductController@edit')->name('admin.product.edit');
    Route::post('/product/update/{id}', 'Admin\ProductController@update')->name('admin.product.update');

    Route::get('/product/customize', 'Admin\ProductController@indexCustomize')->name('admin.product.customize.index');
    Route::get('/product/customize/create/{product_id}', 'Admin\ProductController@createCustomize')->name('admin.product.customize.create');
    Route::post('/product/customize/store', 'Admin\ProductController@storeCustomize')->name('admin.product.customize.store');
    Route::get('/product/customize/edit', 'Admin\ProductController@editCustomize')->name('admin.product.customize.edit');
    Route::post('/product/customize/update', 'Admin\ProductController@updateCustomize')->name('admin.product.customize.update');

    // Product Category
    Route::get('/product/category', 'Admin\ProductCategoryController@index')->name('admin.product.category.index');
    Route::get('/product/category/create', 'Admin\ProductCategoryController@create')->name('admin.product.category.create');
    Route::get('/product/category/edit/{id}', 'Admin\ProductCategoryController@edit')->name('admin.product.category.edit');
    Route::post('/product/category/store', 'Admin\ProductCategoryController@store')->name('admin.product.category.store');
    Route::post('/product/category/update/{id}', 'Admin\ProductCategoryController@update')->name('admin.product.category.update');
    Route::post('/product/category/destroy', 'Admin\ProductCategoryController@destroy')->name('admin.product.category.destroy');

    //Product Brand
    Route::get('/product/brand', 'Admin\ProductBrandController@index')->name('admin.product.brand.index');
    Route::get('/product/brand/create', 'Admin\ProductBrandController@create')->name('admin.product.brand.create');
    Route::get('/product/brand/edit/{id}', 'Admin\ProductBrandController@edit')->name('admin.product.brand.edit');
    Route::post('/product/brand/store', 'Admin\ProductBrandController@store')->name('admin.product.brand.store');
    Route::post('/product/brand/update/{id}', 'Admin\ProductBrandController@update')->name('admin.product.brand.update');
    Route::post('/product/brand/destroy', 'Admin\ProductBrandController@destroy')->name('admin.product.brand.destroy');

    //Banner
    Route::get('/product/banner', 'Admin\BannerController@index')->name('admin.banner.index');
    Route::get('/product/banner/create', 'Admin\BannerController@create')->name('admin.banner.create');
    Route::get('/product/banner/edit/{id}', 'Admin\BannerController@edit')->name('admin.banner.edit');
    Route::post('/product/banner/store', 'Admin\BannerController@store')->name('admin.banner.store');
    Route::post('/product/banner/update/{id}', 'Admin\BannerController@update')->name('admin.banner.update');
    Route::post('/product/banner/destroy', 'Admin\BannerController@destroy')->name('admin.banner.destroy');

    // Orders
    Route::get('/orders', 'Admin\OrderController@index')->name('admin.orders.index');
    Route::get('/orders/detail/{item}', 'Admin\OrderController@show')->name('admin.orders.detail');
    Route::post('/orders/order-process/', 'Admin\OrderController@confirmOrderProcess')->name('admin.orders.processing');

    // Sales Order
    Route::get('/sales_order', 'Admin\SalesOrderHeaderController@index')->name('admin.sales_order.index');
    Route::get('/sales_order/show/{id}', 'Admin\SalesOrderHeaderController@show')->name('admin.sales_order.create');
    Route::get('/sales_order/create', 'Admin\SalesOrderHeaderController@create')->name('admin.sales_order.create');
    Route::get('/sales_order/edit/{id}', 'Admin\SalesOrderHeaderController@edit')->name('admin.sales_order.edit');

    //Kajian Order
    Route::get('/product/kajian_order', 'Admin\BannerController@index')->name('admin.kajian_order.index');
    Route::get('/product/kajian_order/create', 'Admin\BannerController@create')->name('admin.kajian_order.create');
    Route::get('/product/kajian_order/edit/{id}', 'Admin\BannerController@edit')->name('admin.kajian_order.edit');
    Route::post('/product/kajian_order/store', 'Admin\BannerController@store')->name('admin.kajian_order.store');
    Route::post('/product/kajian_order/update/{id}', 'Admin\BannerController@update')->name('admin.kajian_order.update');

    // Import
    Route::get('/import/form', 'Admin\ImportController@form')->name('admin.import.form');
    Route::post('/import/form/submit', 'Admin\ImportController@importExcel')->name('admin.import.submit');
    Route::get('/import/address/auto', 'Admin\ImportController@autoAddress')->name('admin.import.address.auto');

    // Employee
    Route::get('/employee', 'Admin\EmployeeController@index')->name('admin.employee.index');
    Route::get('/employee/create', 'Admin\EmployeeController@create')->name('admin.employee.create');
    Route::get('/employee/edit/{id}', 'Admin\EmployeeController@edit')->name('admin.employee.edit');
    Route::post('/employee/store', 'Admin\EmployeeController@store')->name('admin.employee.store');
    Route::post('/employee/update/{id}', 'Admin\EmployeeController@update')->name('admin.employee.update');
    Route::post('/employee/destroy', 'Admin\EmployeeController@destroy')->name('admin.employee.destroy');

    // Unit
    Route::get('/unit', 'Admin\UnitController@index')->name('admin.unit.index');
    Route::get('/unit/create', 'Admin\UnitController@create')->name('admin.unit.create');
    Route::get('/unit/edit/{id}', 'Admin\UnitController@edit')->name('admin.unit.edit');
    Route::post('/unit/store', 'Admin\UnitController@store')->name('admin.unit.store');
    Route::post('/unit/update/{id}', 'Admin\UnitController@update')->name('admin.unit.update');
    Route::post('/unit/destroy', 'Admin\UnitController@destroy')->name('admin.unit.destroy');

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

    // Report
    Route::get('/transaction/report', 'Admin\ReportController@transactionReport')->name('admin.transaction.report');
    Route::post('/transaction/report/submit', 'Admin\ReportController@transactionReportSubmit')->name('admin.transaction.report.submit');
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
Route::get('/datatables-brands', 'Admin\BrandController@getIndex')->name('datatables.brands');
Route::get('/datatables-orders', 'Admin\OrderController@getIndex')->name('datatables.orders');
Route::get('/datatables-user-categories', 'Admin\UserCategoryController@getIndex')->name('datatables.user_categories');
Route::get('/datatables-faqs', 'Admin\FaqController@getIndex')->name('datatables.faqs');
Route::get('/datatables-permission-menus', 'Admin\PermissionMenuController@getIndex')->name('datatables.permission-menus');
Route::get('/datatables-products', 'Admin\ProductController@getIndex')->name('datatables.products');
Route::get('/datatables-product-categories', 'Admin\ProductCategoryController@getIndex')->name('datatables.product.categories');
Route::get('/datatables-product-brands', 'Admin\ProductBrandController@getIndex')->name('datatables.product.brands');
Route::get('/datatables-product-customizations', 'Admin\ProductController@getIndexCustomize')->name('datatables.product.customizations');
Route::get('/datatables-employee', 'Admin\EmployeeController@getIndex')->name('datatables.employees');
Route::get('/datatables-banner', 'Admin\BannerController@getIndex')->name('datatables.banners');
Route::get('/datatables-place', 'Admin\PlaceController@getIndex')->name('datatables.places');
Route::get('/datatables-unit', 'Admin\UnitController@getIndex')->name('datatables.units');

// Select2
Route::get('/select-admin-users', 'Admin\AdminUserController@getAdminUsers')->name('select.admin-users');
Route::get('/select-user-categories', 'Admin\UserCategoryController@getCategories')->name('select.user-categories');
Route::get('/select-products', 'Admin\ProductController@getProducts')->name('select.products');
Route::get('/select-brands', 'Admin\ProductBrandController@getProductBrands')->name('select.banners');

// Third Party API
Route::get('/update-currency', 'Admin\CurrencyController@getCurrenciesUpdate')->name('update-currencies');

// Email Aauth
Route::get('/request-verification/{email}', 'Auth\RegisterController@RequestVerification')->name('request-verification');

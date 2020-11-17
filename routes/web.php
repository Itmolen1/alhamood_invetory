<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/admin','AdminController@index');
route::get('/','AdminController@login');
route::get('/register','AdminController@register');

route::resource('companies','CompanyController');
route::resource('customers','CustomerController');
route::resource('suppliers','SupplierController');
route::resource('customer_advances','CustomerAdvanceController');
route::resource('supplier_advances','SupplierAdvanceController');
route::resource('vehicles','VehicleController');
route::resource('drivers','DriverController');
route::resource('users','UserController');
route::resource('roles','RoleController');






///////////// sales samples ////////////////////
route::view('sales','admin.sale.create');
route::view('sales/index','admin.sale.index');
route::view('sales/edit','admin.sale.edit');

route::view('expenses','admin.expense.create');
route::view('expenses/edit','admin.expense.edit');
route::view('expenses/index','admin.expense.index');


route::view('purchases','admin.purchase.create');
route::view('purchases/edit','admin.purchase.edit');
route::view('purchases/index','admin.purchase.index');


route::view('add_meter','admin.meter.create');

route::view('meterReading','admin.meterReading.create');
route::view('meterReading/index','admin.meterReading.index');
route::view('meterReading/edit','admin.meterReading.edit');


route::view('loan','admin.loan.create');
route::view('loan/edit','admin.loan.edit');
route::view('loan/index','admin.loan.index');

route::view('welcome','welcome');


/// end of sales samples /////////////////

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

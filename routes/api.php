<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::apiResource('/Bank', 'api\BankController');
Route::get('/Bank/restore/{Id}', 'BankController@restore')->name('Bank_restore');
Route::get('/BankTrashed', 'BankController@trash');
Route::get('/Bank/paginate/{page_no}/{page_size}','BankController@paginate');

Route::post('Login', 'api\UserController@login');
Route::post('Logout', 'api\UserController@logout');

Route::apiResource('/Driver', 'api\DriverController');
Route::apiResource('/Vehicle', 'api\VehicleController');
Route::apiResource('/Customer', 'api\CustomerController');
Route::apiResource('/Supplier', 'api\SupplierController');
Route::apiResource('/Unit', 'api\UnitController');
Route::apiResource('/Product', 'api\ProductController');
Route::apiResource('/Employee', 'api\EmployeeController');

Route::group(['middleware' => 'auth:api'], function () {
    // employee api
    Route::get('/Employee/restore/{Id}', 'EmployeeController@restore');
    Route::get('/EmployeeTrashed', 'EmployeeController@trash');
    Route::get('/Employee/paginate/{page_no}/{page_size}','EmployeeController@paginate');
});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

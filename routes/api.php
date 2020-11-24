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

Route::post('Login', 'api\UserController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('Logout', 'api\UserController@logout');

    Route::get('/Employee/restore/{Id}', 'EmployeeController@restore');
    Route::get('/EmployeeTrashed', 'EmployeeController@trash');
    Route::get('/Employee/paginate/{page_no}/{page_size}','EmployeeController@paginate');

    Route::apiResource('/Bank', 'api\BankController');
    Route::get('/Bank/restore/{Id}', 'BankController@restore')->name('Bank_restore');
    Route::get('/BankTrashed', 'BankController@trash');
    Route::get('/Bank/paginate/{page_no}/{page_size}','api\BankController@paginate');

    Route::apiResource('/Driver', 'api\DriverController');
    Route::get('/Driver/paginate/{page_no}/{page_size}','api\DriverController@paginate');

    Route::apiResource('/Vehicle', 'api\VehicleController');
    Route::get('/Vehicle/paginate/{page_no}/{page_size}','api\VehicleController@paginate');

    Route::apiResource('/Customer', 'api\CustomerController');
    Route::get('/Customer/paginate/{page_no}/{page_size}','api\CustomerController@paginate');

    Route::apiResource('/Supplier', 'api\SupplierController');
    Route::get('/Supplier/paginate/{page_no}/{page_size}','api\SupplierController@paginate');

    Route::apiResource('/Unit', 'api\UnitController');
    Route::get('/Unit/paginate/{page_no}/{page_size}','api\UnitController@paginate');

    Route::apiResource('/Product', 'api\ProductController');
    Route::get('/Product/paginate/{page_no}/{page_size}','api\ProductController@paginate');

    Route::apiResource('/Employee', 'api\EmployeeController');
    Route::get('/Employee/paginate/{page_no}/{page_size}','api\EmployeeController@paginate');

    Route::apiResource('/Purchase', 'api\PurchaseController');
    Route::get('/Purchase/paginate/{page_no}/{page_size}','api\PurchaseController@paginate');
});


//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

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

Route::apiResource('/Bank', 'API\BankController');
Route::get('/Bank/restore/{Id}', 'BankController@restore')->name('Bank_restore');
Route::get('/BankTrashed', 'BankController@trash');
Route::get('/Bank/paginate/{page_no}/{page_size}','BankController@paginate');

Route::post('Login', 'API\UserController@login');

Route::apiResource('/Driver', 'API\DriverController');
Route::apiResource('/Vehicle', 'API\VehicleController');
Route::apiResource('/Customer', 'API\CustomerController');
Route::apiResource('/Supplier', 'API\SupplierController');
Route::apiResource('/Unit', 'API\UnitController');
Route::apiResource('/Product', 'API\ProductController');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

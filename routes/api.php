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
    Route::post('UserUpdate', 'api\UserController@UserUpdate');
    Route::post('UserChangePassword', 'api\UserController@UserChangePassword');
    Route::get('UserDetail/{id}', 'api\UserController@UserDetail');
    Route::delete('UserDelete/{id}', 'api\UserController@destroy');
    Route::post('UserUpdateProfilePicture', 'api\UserController@UserUpdateProfilePicture');
    Route::get('AllUsers/', 'api\UserController@AllUsers');

    Route::get('/Employee/restore/{Id}', 'EmployeeController@restore');
    Route::get('/EmployeeTrashed', 'EmployeeController@trash');
    Route::get('/Employee/paginate/{page_no}/{page_size}','EmployeeController@paginate');

    Route::apiResource('/Bank', 'api\BankController');
    Route::get('/Bank/restore/{Id}', 'BankController@restore')->name('Bank_restore');
    Route::get('/BankTrashed', 'BankController@trash');
    Route::get('/Bank/paginate/{page_no}/{page_size}','api\BankController@paginate');
    Route::get('/Bank/ActivateDeactivate/{id}','api\BankController@ActivateDeactivate');

    Route::apiResource('/Driver', 'api\DriverController');
    Route::get('/Driver/paginate/{page_no}/{page_size}','api\DriverController@paginate');
    Route::get('/Driver/ActivateDeactivate/{id}','api\DriverController@ActivateDeactivate');

    Route::apiResource('/Vehicle', 'api\VehicleController');
    Route::get('/Vehicle/paginate/{page_no}/{page_size}','api\VehicleController@paginate');
    Route::get('/Vehicle/ActivateDeactivate/{id}','api\VehicleController@ActivateDeactivate');

    Route::apiResource('/Customer', 'api\CustomerController');
    Route::get('/Customer/paginate/{page_no}/{page_size}','api\CustomerController@paginate');
    Route::get('/Customer/ActivateDeactivate/{id}','api\CustomerController@ActivateDeactivate');

    Route::apiResource('/Company', 'api\CompanyController');
    Route::get('/Company/paginate/{page_no}/{page_size}','api\CompanyController@paginate');
    Route::get('/Company/ActivateDeactivate/{id}','api\CompanyController@ActivateDeactivate');

    Route::apiResource('/ExpenseCategory', 'api\ExpenseCategory');
    Route::get('/ExpenseCategory/paginate/{page_no}/{page_size}','api\ExpenseCategory@paginate');
    Route::get('/ExpenseCategory/ActivateDeactivate/{id}','api\ExpenseCategory@ActivateDeactivate');

    Route::apiResource('/Supplier', 'api\SupplierController');
    Route::get('/Supplier/paginate/{page_no}/{page_size}','api\SupplierController@paginate');
    Route::get('/Supplier/ActivateDeactivate/{id}','api\SupplierController@ActivateDeactivate');

    Route::apiResource('/Unit', 'api\UnitController');
    Route::get('/Unit/paginate/{page_no}/{page_size}','api\UnitController@paginate');
    Route::get('/Unit/ActivateDeactivate/{id}','api\UnitController@ActivateDeactivate');

    Route::apiResource('/Product', 'api\ProductController');
    Route::get('/Product/paginate/{page_no}/{page_size}','api\ProductController@paginate');
    Route::get('/Product/ActivateDeactivate/{id}','api\ProductController@ActivateDeactivate');

    Route::apiResource('/Employee', 'api\EmployeeController');
    Route::get('/Employee/paginate/{page_no}/{page_size}','api\EmployeeController@paginate');
    Route::get('/Employee/ActivateDeactivate/{id}','api\EmployeeController@ActivateDeactivate');

    Route::apiResource('/Meter', 'api\MeterReaderController');
    Route::get('/Meter/paginate/{page_no}/{page_size}','api\MeterReaderController@paginate');
    Route::get('/Meter/ActivateDeactivate/{id}','api\MeterReaderController@ActivateDeactivate');

    Route::apiResource('/Country', 'api\CountryController');
    Route::get('/Country/paginate/{page_no}/{page_size}','api\CountryController@paginate');
    Route::get('/Country/ActivateDeactivate/{id}','api\CountryController@ActivateDeactivate');

    Route::apiResource('/State', 'api\StateController');
    Route::get('/State/paginate/{page_no}/{page_size}','api\StateController@paginate');
    Route::get('/State/ActivateDeactivate/{id}','api\StateController@ActivateDeactivate');

    Route::apiResource('/City', 'api\CityController');
    Route::get('/City/paginate/{page_no}/{page_size}','api\CityController@paginate');
    Route::get('/City/ActivateDeactivate/{id}','api\CityController@ActivateDeactivate');

    Route::apiResource('/Region', 'api\RegionController');
    Route::get('/Region/paginate/{page_no}/{page_size}','api\RegionController@paginate');
    Route::get('/Region/ActivateDeactivate/{id}','api\RegionController@ActivateDeactivate');

    Route::apiResource('/CustomerAdvance', 'api\CustomerAdvanceController');
    Route::get('/CustomerAdvance/paginate/{page_no}/{page_size}','api\CustomerAdvanceController@paginate');

    Route::apiResource('/SupplierAdvance', 'api\SupplierAdvanceController');
    Route::get('/SupplierAdvance/paginate/{page_no}/{page_size}','api\SupplierAdvanceController@paginate');

    Route::apiResource('/Loan', 'api\LoanController');
    Route::get('/Loan/paginate/{page_no}/{page_size}','api\LoanController@paginate');

    Route::apiResource('/Purchase', 'api\PurchaseController');
    Route::get('/Purchase/paginate/{page_no}/{page_size}','api\PurchaseController@paginate');
    Route::get('/getPurchaseBaseList', 'api\PurchaseController@BaseList');
    Route::post('PurchaseDocumentsUpload', 'api\PurchaseController@PurchaseDocumentsUpload');
    Route::get('/Purchase/print/{Id}', 'api\PurchaseController@print');

    Route::apiResource('/Expense', 'api\ExpenseController');
    Route::get('/Expense/paginate/{page_no}/{page_size}','api\ExpenseController@paginate');
    Route::get('/getExpenseBaseList', 'api\ExpenseController@BaseList');
    Route::post('ExpenseDocumentsUpload', 'api\ExpenseController@ExpenseDocumentsUpload');
    Route::get('/Expense/print/{Id}', 'api\ExpenseController@print');

    Route::apiResource('/MeterReading', 'api\MeterReadingController');
    Route::get('/MeterReading/paginate/{page_no}/{page_size}','api\MeterReadingController@paginate');
    Route::get('/getMeterReadingBaseList', 'api\MeterReadingController@BaseList');
});


//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

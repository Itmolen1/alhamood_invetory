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

Route::post('customer/customer_login', 'api\customer\CustomerController@customer_login');
Route::post('customer/customer_logout', 'api\customer\CustomerController@customer_logout');
Route::post('customer/customer_change_password', 'api\customer\CustomerController@customer_change_password');
Route::post('customer/my_vehicles', 'api\customer\CustomerController@my_vehicles');
Route::post('customer/my_purchase/{page_no}/{page_size}', 'api\customer\CustomerController@my_purchase');
Route::post('customer/my_purchase_by_vehicle', 'api\customer\CustomerController@my_purchase_by_vehicle');
Route::post('customer/my_account_status', 'api\customer\CustomerController@my_account_status');
Route::post('customer/my_payments/{page_no}/{page_size}', 'api\customer\CustomerController@my_payments');
Route::post('customer/my_advances/{page_no}/{page_size}', 'api\customer\CustomerController@my_advances');
Route::post('customer/my_account_statement', 'api\customer\CustomerController@my_account_statement');
Route::post('customer/check_app_access_status', 'api\customer\CustomerController@check_app_access_status');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('Logout', 'api\UserController@logout');
    Route::post('UserUpdate', 'api\UserController@UserUpdate');
    Route::post('UserChangePassword', 'api\UserController@UserChangePassword');
    Route::get('UserDetail/{id}', 'api\UserController@UserDetail');
    Route::delete('UserDelete/{id}', 'api\UserController@destroy');
    Route::post('UserUpdateProfilePicture', 'api\UserController@UserUpdateProfilePicture');
    Route::get('AllUsers/', 'api\UserController@AllUsers');
    Route::get('ForgotPassword/{id}', 'api\UserController@ForgotPassword');

    Route::get('/Employee/restore/{Id}', 'EmployeeController@restore');
    Route::get('/EmployeeTrashed', 'EmployeeController@trash');
    Route::get('/Employee/paginate/{page_no}/{page_size}','EmployeeController@paginate');

    Route::apiResource('/Bank', 'api\BankController');
    Route::get('/Bank/restore/{Id}', 'BankController@restore')->name('Bank_restore');
    Route::get('/BankTrashed', 'BankController@trash');
    Route::get('/Bank/paginate/{page_no}/{page_size}','api\BankController@paginate');
    Route::get('/Bank/ActivateDeactivate/{id}','api\BankController@ActivateDeactivate');

    Route::apiResource('/Driver', 'api\DriverController');
    Route::post('/DriverSearch','api\DriverController@DriverSearch');
    Route::get('/Driver/paginate/{page_no}/{page_size}','api\DriverController@paginate');
    Route::get('/Driver/ActivateDeactivate/{id}','api\DriverController@ActivateDeactivate');

    Route::apiResource('/Vehicle', 'api\VehicleController');
    Route::post('/VehicleSearch','api\VehicleController@VehicleSearch');
    Route::get('/Vehicle/paginate/{page_no}/{page_size}','api\VehicleController@paginate');
    Route::get('/Vehicle/ActivateDeactivate/{id}','api\VehicleController@ActivateDeactivate');
    Route::get('/VehicleByCustomer/{id}','api\VehicleController@VehicleByCustomer');

    Route::apiResource('/Customer', 'api\CustomerController');
    Route::post('/CustomerSearch','api\CustomerController@CustomerSearch');
    Route::get('/Customer/paginate/{page_no}/{page_size}','api\CustomerController@paginate');
    Route::get('/Customer/ActivateDeactivate/{id}','api\CustomerController@ActivateDeactivate');
    Route::get('/CustomerBaseList', 'api\CustomerController@BaseList');

    Route::apiResource('/Company', 'api\CompanyController');
    Route::get('/Company/paginate/{page_no}/{page_size}','api\CompanyController@paginate');
    Route::get('/Company/ActivateDeactivate/{id}','api\CompanyController@ActivateDeactivate');

    Route::apiResource('/CompanyType', 'api\CompanyTypeController');
    Route::get('/CompanyType/paginate/{page_no}/{page_size}','api\CompanyTypeController@paginate');
    Route::get('/CompanyType/ActivateDeactivate/{id}','api\CompanyTypeController@ActivateDeactivate');

    Route::apiResource('/PaymentType', 'api\PaymentTypeController');
    Route::get('/PaymentType/paginate/{page_no}/{page_size}','api\PaymentTypeController@paginate');
    Route::get('/PaymentType/ActivateDeactivate/{id}','api\PaymentTypeController@ActivateDeactivate');

    Route::apiResource('/PaymentTerm', 'api\PaymentTermController');
    Route::get('/PaymentTerm/paginate/{page_no}/{page_size}','api\PaymentTermController@paginate');
    Route::get('/PaymentTerm/ActivateDeactivate/{id}','api\PaymentTermController@ActivateDeactivate');

    Route::apiResource('/ExpenseCategory', 'api\ExpenseCategory');
    Route::get('/ExpenseCategory/paginate/{page_no}/{page_size}','api\ExpenseCategory@paginate');
    Route::get('/ExpenseCategory/ActivateDeactivate/{id}','api\ExpenseCategory@ActivateDeactivate');

    Route::apiResource('/Supplier', 'api\SupplierController');
    Route::post('/SupplierSearch','api\SupplierController@SupplierSearch');
    Route::get('/Supplier/paginate/{page_no}/{page_size}','api\SupplierController@paginate');
    Route::get('/Supplier/ActivateDeactivate/{id}','api\SupplierController@ActivateDeactivate');
    Route::get('/SupplierBaseList', 'api\SupplierController@BaseList');

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
    Route::get('get_all_country','api\RegionController@get_detail_list');

    Route::apiResource('/CustomerAdvance', 'api\CustomerAdvanceController');
    Route::get('/getCustomerAdvanceBaseList', 'api\CustomerAdvanceController@BaseList');
    Route::get('/CustomerAdvance/paginate/{page_no}/{page_size}','api\CustomerAdvanceController@paginate');
    Route::get('customer_advances_push/{Id}','api\CustomerAdvanceController@customer_advances_push');
    Route::post('CustomerAdvanceUpdate','api\CustomerAdvanceController@CustomerAdvanceUpdate');

    Route::apiResource('/SupplierAdvance', 'api\SupplierAdvanceController');
    Route::get('/getSupplierAdvanceBaseList', 'api\SupplierAdvanceController@BaseList');
    Route::get('/SupplierAdvance/paginate/{page_no}/{page_size}','api\SupplierAdvanceController@paginate');
    Route::get('supplier_advances_push/{Id}','api\SupplierAdvanceController@supplier_advances_push');
    Route::post('SupplierAdvanceUpdate','api\SupplierAdvanceController@SupplierAdvanceUpdate');

    Route::apiResource('/Loan', 'api\LoanController');
    Route::get('/Loan/paginate/{page_no}/{page_size}','api\LoanController@paginate');

    Route::apiResource('/Purchase', 'api\PurchaseController');
    Route::post('/PurchaseUpdate', 'api\PurchaseController@update');
    Route::post('/PurchaseSearchByPad', 'api\PurchaseController@PurchaseSearchByPad');
    Route::get('/Purchase/paginate/{page_no}/{page_size}','api\PurchaseController@paginate');
    Route::get('/getPurchaseBaseList', 'api\PurchaseController@BaseList');
    Route::post('PurchaseDocumentsUpload', 'api\PurchaseController@PurchaseDocumentsUpload');
    Route::get('/Purchase/print/{Id}', 'api\PurchaseController@print');
    Route::get('/supplierPurchaseDetails/{Id}', 'api\PurchaseController@supplierPurchaseDetails');

    Route::apiResource('/Sales', 'api\SalesController');
    Route::post('/SalesUpdate', 'api\SalesController@update');
    Route::post('/SaleSearchByPad', 'api\SalesController@SaleSearchByPad');
    Route::get('/Sales/paginate/{page_no}/{page_size}','api\SalesController@paginate');
    Route::get('/getSalesBaseList', 'api\SalesController@BaseList');
    Route::post('SalesDocumentsUpload', 'api\SalesController@SalesDocumentsUpload');
    Route::get('/Sales/print/{Id}', 'api\SalesController@print');
    Route::get('/customerSaleDetails/{Id}', 'api\SalesController@customerSaleDetails');

    Route::apiResource('/Expense', 'api\ExpenseController');
    Route::post('/ExpenseUpdate', 'api\ExpenseController@update');
    Route::post('/ExpenseSearchByRef', 'api\ExpenseController@ExpenseSearchByRef');
    Route::get('/Expense/paginate/{page_no}/{page_size}','api\ExpenseController@paginate');
    Route::get('/getExpenseBaseList', 'api\ExpenseController@BaseList');
    Route::post('ExpenseDocumentsUpload', 'api\ExpenseController@ExpenseDocumentsUpload');
    Route::get('/Expense/print/{Id}', 'api\ExpenseController@print');

    Route::apiResource('/MeterReading', 'api\MeterReadingController');
    Route::get('/MeterReading/paginate/{page_no}/{page_size}','api\MeterReadingController@paginate');
    Route::get('/getMeterReadingBaseList', 'api\MeterReadingController@BaseList');

    Route::apiResource('/PaymentReceive', 'api\PaymentReceiveController');
    Route::get('/PaymentReceive/paginate/{page_no}/{page_size}','api\PaymentReceiveController@paginate');
    Route::get('/getPaymentReceiveBaseList', 'api\PaymentReceiveController@BaseList');
    Route::get('customer_payments_push/{Id}','api\PaymentReceiveController@customer_payments_push');
    Route::post('PaymentReceiveUpdate','api\PaymentReceiveController@PaymentReceiveUpdate');

    Route::apiResource('/SupplierPayment', 'api\SupplierPaymentController');
    Route::get('/SupplierPayment/paginate/{page_no}/{page_size}','api\SupplierPaymentController@paginate');
    Route::get('/getSupplierPaymentBaseList', 'api\SupplierPaymentController@BaseList');
    Route::get('supplier_payments_push/{Id}','api\SupplierPaymentController@supplier_payments_push');
    Route::post('SupplierPaymentUpdate','api\SupplierPaymentController@SupplierPaymentUpdate');

    Route::post('/SalesReport','api\ReportController@SalesReport');
    Route::post('/SalesReportByVehicle','api\ReportController@SalesReportByVehicle');
    Route::post('/SalesReportByCustomerVehicle','api\ReportController@SalesReportByCustomerVehicle');
    Route::post('/PurchaseReport','api\ReportController@PurchaseReport');
    Route::post('/ExpenseReport','api\ReportController@ExpenseReport');
    Route::post('/CashReport','api\ReportController@CashReport');
    Route::post('/BankReport','api\ReportController@BankReport');
    Route::get('/GetBalanceSheet','api\ReportController@GetBalanceSheet');
});

//Route::fallback(function(){
//    return response()->json([
//        'message' => 'Page Not Found. If error persists, contact info@website.com'], 404);
//});

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

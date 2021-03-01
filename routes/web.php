<?php

use Illuminate\Support\Facades\Auth;
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

//Route::prefix('admin')->middleware(['auth'])->group(function () {
Route::middleware(['auth'])->group(function () {
route::resource('companies','CompanyController');
route::get('/','AdminController@index');

Route::get('/admin','AdminController@index')->name('admin');
route::get('/register','AdminController@register');

route::get('/UserChangePassword','UserController@UserChangePassword')->name('UserChangePassword');
route::PUT('UserUpdatePassword/{id}','UserController@UserUpdatePassword')->name('UserUpdatePassword');

route::resource('customers','CustomerController');
route::get('customerDetails/{id}','CustomerController@customerDetails');
route::resource('company_types','CompanyTypeController');
route::resource('payment_types','PaymentTypeController');
route::resource('payment_terms','PaymentTermController');

route::resource('suppliers','SupplierController');
route::resource('customer_advances','CustomerAdvanceController');
route::PUT('customer_advances_push/{Id}','CustomerAdvanceController@customer_advances_push');
route::get('customer_advances_get_disburse/{Id}','CustomerAdvanceController@customer_advances_get_disburse')->name('customer_advances_get_disburse');
route::POST('customer_advances_save_disburse','CustomerAdvanceController@customer_advances_save_disburse')->name('customer_advances_save_disburse');
route::resource('supplier_advances','SupplierAdvanceController');
route::PUT('supplier_advances_push/{Id}','SupplierAdvanceController@supplier_advances_push');

route::resource('vehicles','VehicleController');
route::get('getVehicleList','VehicleController@getVehicleList')->name('getVehicleList');
route::post('PrintVehicleList','VehicleController@PrintVehicleList')->name('PrintVehicleList');

route::POST('/CheckVehicleExist','VehicleController@CheckVehicleExist');
route::resource('drivers','DriverController');
route::resource('users','UserController');
route::resource('roles','RoleController');
route::resource('banks','BankController');
route::resource('deposits','DepositController');
route::get('getBankAccountDetail/{id}','BankController@getBankAccountDetail');
route::resource('countries','CountryController');
route::resource('states','StateController');
route::resource('cities','CityController');

route::resource('regions','RegionController');
route::get('locationDetails/{id}','RegionController@locationDetails');

route::resource('units','UnitController');
route::resource('products','ProductController');
route::get('productsDetails/{Id}','ProductController@productDetails');

////////// purchase section //////////////////////////
route::resource('purchases','PurchaseController');
route::get('purchasePrint/{id}','PurchaseController@print');

route::get('supplierDetails/{id}','SupplierController@supplierDetails');
Route::post('purchaseUpdate/{Id}','PurchaseController@purchaseUpdate');


//////////////expense /////////////////
route::resource('expenses','ExpenseController');
route::resource('expense_categories','ExpenseCategoryController');
route::post('expenseUpdate/{id}','ExpenseController@expenseUpdate');
route::POST('/CheckExpenseReferenceExist','ExpenseController@CheckExpenseReferenceExist');
route::resource('employees','EmployeeController');

////////////// sales /////////////////////
route::resource('sales','SaleController');
route::post('salesUpdate/{Id}','SaleController@salesUpdate');
route::get('get_data','SaleController@get_data')->name('get_data');
route::get('getCustomerVehicleDetails/{$Id}','CustomerController@getCustomerVehicle');
route::get('getSalesByDate/{id}','SaleController@salesByDateDetails');
route::resource('customer_prices','CustomerPriceController');

//////////////// meterReading ///////////////
route::resource('meter_readers','MeterReaderController');
route::resource('meter_readings','MeterReadingController');
route::post('meterReadingUpdate/{Id}','MeterReadingController@meterReadingUpdate');

/////// loan ///////////////
route::resource('loans','LoanController');
route::get('customerRemaining/{Id}','LoanController@customerRemaining');
route::get('employeeRemaining/{Id}','LoanController@employeeRemaining');

route::resource('payment_receives','PaymentReceiveController');
route::PUT('customer_payments_push/{Id}','PaymentReceiveController@customer_payments_push');
route::get('customerSaleDetails/{Id}','SaleController@customerSaleDetails');
//route::view('customer_receives','admin.customer_payment_receive.index');

route::resource('supplier_payments','SupplierPaymentController');
route::PUT('supplier_payment_push/{Id}','SupplierPaymentController@supplier_payments_push');
route::get('supplierSaleDetails/{Id}','PurchaseController@supplierSaleDetails');

////////reports////////////
route::get('GetCustomerStatement','ReportController@GetCustomerStatement')->name('GetCustomerStatement');
route::get('PrintCustomerStatement','ReportController@PrintCustomerStatement')->name('PrintCustomerStatement');

route::get('GetSupplierStatement','ReportController@GetSupplierStatement')->name('GetSupplierStatement');
route::get('PrintSupplierStatement','ReportController@PrintSupplierStatement')->name('PrintSupplierStatement');

route::get('GetPaidAdvancesSummary','ReportController@GetPaidAdvancesSummary')->name('GetPaidAdvancesSummary');
route::get('PrintPaidAdvancesSummary','ReportController@PrintPaidAdvancesSummary')->name('PrintPaidAdvancesSummary');

route::get('GetReceivedAdvancesSummary','ReportController@GetReceivedAdvancesSummary')->name('GetReceivedAdvancesSummary');
route::get('PrintReceivedAdvancesSummary','ReportController@PrintReceivedAdvancesSummary')->name('PrintReceivedAdvancesSummary');

route::get('GetDetailCustomerStatement','ReportController@GetDetailCustomerStatement')->name('GetDetailCustomerStatement');
route::post('PrintDetailCustomerStatement','ReportController@PrintDetailCustomerStatement')->name('PrintDetailCustomerStatement');
route::post('ViewDetailCustomerStatement','ReportController@ViewDetailCustomerStatement')->name('ViewDetailCustomerStatement');

route::get('GetDetailSupplierStatement','ReportController@GetDetailSupplierStatement')->name('GetDetailSupplierStatement');
route::post('PrintDetailSupplierStatement','ReportController@PrintDetailSupplierStatement')->name('PrintDetailSupplierStatement');
route::post('ViewDetailSupplierStatement','ReportController@ViewDetailSupplierStatement')->name('ViewDetailSupplierStatement');

route::get('SalesReport','ReportController@SalesReport')->name('SalesReport');
route::post('PrintSalesReport','ReportController@PrintSalesReport')->name('PrintSalesReport');

route::get('SalesReportByVehicle','ReportController@SalesReportByVehicle')->name('SalesReportByVehicle');
route::post('PrintSalesReportByVehicle','ReportController@PrintSalesReportByVehicle')->name('PrintSalesReportByVehicle');

route::get('SalesReportByCustomer','ReportController@SalesReportByCustomer')->name('SalesReportByCustomer');
route::post('PrintSalesReportByCustomer','ReportController@PrintSalesReportByCustomer')->name('PrintSalesReportByCustomer');

route::get('PurchaseReport','ReportController@PurchaseReport')->name('PurchaseReport');
route::post('PrintPurchaseReport','ReportController@PrintPurchaseReport')->name('PrintPurchaseReport');

route::get('ExpenseReport','ReportController@ExpenseReport')->name('ExpenseReport');
route::post('PrintExpenseReport','ReportController@PrintExpenseReport')->name('PrintExpenseReport');

route::get('CashReport','ReportController@CashReport')->name('CashReport');
route::post('PrintCashReport','ReportController@PrintCashReport')->name('PrintCashReport');
route::post('ViewCashReport','ReportController@ViewCashReport')->name('ViewCashReport');

route::get('BankReport','ReportController@BankReport')->name('BankReport');
route::post('PrintBankReport','ReportController@PrintBankReport')->name('PrintBankReport');
route::post('ViewBankReport','ReportController@ViewBankReport')->name('ViewBankReport');

route::get('GeneralLedger','ReportController@GeneralLedger')->name('GeneralLedger');
route::post('PrintGeneralLedger','ReportController@PrintGeneralLedger')->name('PrintGeneralLedger');

route::get('Profit_loss','ReportController@Profit_loss')->name('Profit_loss');
route::post('PrintProfit_loss','ReportController@PrintProfit_loss')->name('PrintProfit_loss');

route::get('Garage_value','ReportController@Garage_value')->name('Garage_value');
route::post('PrintGarage_value','ReportController@PrintGarage_value')->name('PrintGarage_value');

});

route::view('welcome','welcome');

Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);

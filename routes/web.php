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


route::resource('customers','CustomerController');
route::get('customerDetails/{id}','DriverController@customerDetails');
route::resource('company_types','CompanyTypeController');
route::resource('payment_types','PaymentTypeController');
    route::resource('payment_terms','PaymentTermController');



route::resource('suppliers','SupplierController');
route::resource('customer_advances','CustomerAdvanceController');
route::PUT('customer_advances_push/{Id}','CustomerAdvanceController@customer_advances_push');
route::resource('supplier_advances','SupplierAdvanceController');
route::PUT('supplier_advances_push/{Id}','SupplierAdvanceController@supplier_advances_push');
route::resource('vehicles','VehicleController');
route::resource('drivers','DriverController');
route::resource('users','UserController');
route::resource('roles','RoleController');
route::resource('banks','BankController');
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
route::resource('employees','EmployeeController');


////////////// sales /////////////////////
route::resource('sales','SaleController');
route::post('salesUpdate/{Id}','SaleController@salesUpdate');
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
route::get('GetBalanceSheet','ReportController@GetBalanceSheet')->name('GetBalanceSheet');
route::get('PrintBalanceSheet','ReportController@PrintBalanceSheet')->name('PrintBalanceSheet');
route::get('SalesReport','ReportController@SalesReport')->name('SalesReport');
route::post('PrintSalesReport','ReportController@PrintSalesReport')->name('PrintSalesReport');
route::get('SalesReportByVehicle','ReportController@SalesReportByVehicle')->name('SalesReportByVehicle');
route::post('PrintSalesReportByVehicle','ReportController@PrintSalesReportByVehicle')->name('PrintSalesReportByVehicle');
route::get('PurchaseReport','ReportController@PurchaseReport')->name('PurchaseReport');
route::post('PrintPurchaseReport','ReportController@PrintPurchaseReport')->name('PrintPurchaseReport');
route::get('ExpenseReport','ReportController@ExpenseReport')->name('ExpenseReport');
route::post('PrintExpenseReport','ReportController@PrintExpenseReport')->name('PrintExpenseReport');
route::get('CashReport','ReportController@CashReport')->name('CashReport');
route::post('PrintCashReport','ReportController@PrintCashReport')->name('PrintCashReport');
route::get('BankReport','ReportController@BankReport')->name('BankReport');
route::post('PrintBankReport','ReportController@PrintBankReport')->name('PrintBankReport');
});




///////////// sales samples ////////////////////
//route::view('sales1','admin.sale.create');
//route::view('sales/index','admin.sale.index');
//route::view('sales/edit','admin.sale.edit');
//
//route::view('expenses1','admin.expense.create');
//route::view('expenses/edit','admin.expense.edit');
//route::view('expenses/index','admin.expense.index');


//route::view('purchases1','admin.purchase.create');
//route::view('purchases/edit','admin.purchase.edit');
//route::view('purchases/index','admin.purchase.index');


//route::view('add_meter','admin.meter.create');
//
//route::view('meterReading1','admin.meterReading.create');
//route::view('meterReading/index','admin.meterReading.index');
//route::view('meterReading/edit','admin.meterReading.edit');

//
//route::view('loan','admin.loan.create');
//route::view('loan/edit','admin.loan.edit');
//route::view('loan/index','admin.loan.index');

route::view('welcome','welcome');


/// end of sales samples /////////////////


Auth::routes([
    'register' => false, // Registration Routes...
    'reset' => false, // Password Reset Routes...
    'verify' => false, // Email Verification Routes...
]);
//
//Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

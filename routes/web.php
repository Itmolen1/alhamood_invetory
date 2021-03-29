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
route::get('ChangeCustomerStatus/{id}','CustomerController@ChangeCustomerStatus');
route::resource('financer','FinancerController');
route::get('customerDetails/{id}','CustomerController@customerDetails');
route::get('salesCustomerDetails/{id}','CustomerController@salesCustomerDetails');
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
route::get('supplier_advances_get_disburse/{Id}','SupplierAdvanceController@supplier_advances_get_disburse')->name('supplier_advances_get_disburse');
route::POST('supplier_advances_save_disburse','SupplierAdvanceController@supplier_advances_save_disburse')->name('supplier_advances_save_disburse');

route::resource('vehicles','VehicleController');
route::get('getVehicleList','VehicleController@getVehicleList')->name('getVehicleList');
route::post('PrintVehicleList','VehicleController@PrintVehicleList')->name('PrintVehicleList');
route::get('ChangeVehicleStatus/{id}','VehicleController@ChangeVehicleStatus');

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
Route::post('all_expenses', 'ExpenseController@all_expenses' )->name('all_expenses');
route::resource('expense_categories','ExpenseCategoryController');
route::post('expenseUpdate/{id}','ExpenseController@expenseUpdate');
route::POST('/CheckExpenseReferenceExist','ExpenseController@CheckExpenseReferenceExist');
route::resource('employees','EmployeeController');
route::get('getExpenseDetail/{Id}','ExpenseController@getExpenseDetail');

////////////// sales /////////////////////
route::resource('sales','SaleController');
route::post('salesUpdate/{Id}','SaleController@salesUpdate');
Route::post('all_sales', 'SaleController@all_sales' )->name('all_sales');
route::get('get_data','SaleController@get_data')->name('get_data');
route::get('get_today_sale','SaleController@get_today_sale')->name('get_today_sale');
route::get('get_sale_of_date','SaleController@get_sale_of_date')->name('get_sale_of_date');
route::post('view_sale_of_date', 'SaleController@view_sale_of_date' )->name('view_sale_of_date');
route::get('view_result_sale_of_date', 'SaleController@view_result_sale_of_date' )->name('view_result_sale_of_date');
route::get('getCustomerVehicleDetails/{$Id}','CustomerController@getCustomerVehicle');
route::get('getSalesByDate/{id}','SaleController@salesByDateDetails');
route::resource('customer_prices','CustomerPriceController');
route::POST('/CheckPadExist','SaleController@CheckPadExist');

//////////////// meterReading ///////////////
route::resource('meter_readers','MeterReaderController');
route::resource('meter_readings','MeterReadingController');
route::post('meterReadingUpdate/{Id}','MeterReadingController@meterReadingUpdate');

/////// loan ///////////////
route::resource('loans','LoanController');
route::get('customerRemaining/{Id}','LoanController@customerRemaining');
route::get('employeeRemaining/{Id}','LoanController@employeeRemaining');

route::resource('inward_loans','InwardLoanController');
route::PUT('inward_loan_push/{Id}','InwardLoanController@inward_loan_push');
route::get('inward_loan_payment/{Id}','InwardLoanController@inward_loan_payment');
route::PUT('inward_loan_save_payment/{Id}','InwardLoanController@inward_loan_save_payment');
route::resource('outward_loans','OutwardLoanController');
route::PUT('outward_loan_push/{Id}','OutwardLoanController@outward_loan_push');
route::get('outward_loan_payment/{Id}','OutwardLoanController@outward_loan_payment');
route::PUT('outward_loan_save_payment/{Id}','OutwardLoanController@outward_loan_save_payment');

route::resource('payment_receives','PaymentReceiveController');
route::post('payment_receivesUpdate','PaymentReceiveController@payment_receivesUpdate');
route::PUT('customer_payments_push/{Id}','PaymentReceiveController@customer_payments_push');
route::get('customerSaleDetails/{Id}','SaleController@customerSaleDetails');
route::get('getCustomerPaymentDetail/{Id}','PaymentReceiveController@getCustomerPaymentDetail');

route::resource('supplier_payments','SupplierPaymentController');
route::PUT('supplier_payment_push/{Id}','SupplierPaymentController@supplier_payments_push');
route::get('supplierSaleDetails/{Id}','PurchaseController@supplierSaleDetails');
route::get('getSupplierPaymentDetail/{Id}','SupplierPaymentController@getSupplierPaymentDetail');
////////reports////////////
route::get('GetCustomerStatement','ReportController@GetCustomerStatement')->name('GetCustomerStatement');
route::get('PrintCustomerStatement','ReportController@PrintCustomerStatement')->name('PrintCustomerStatement');

route::get('GetReceivableSummaryAnalysis','ReportController@GetReceivableSummaryAnalysis')->name('GetReceivableSummaryAnalysis');
route::post('ViewReceivableSummaryAnalysis','ReportController@ViewReceivableSummaryAnalysis')->name('ViewReceivableSummaryAnalysis');

route::get('GetExpenseAnalysis','ReportController@GetExpenseAnalysis')->name('GetExpenseAnalysis');
route::post('ViewExpenseAnalysis','ReportController@ViewExpenseAnalysis')->name('ViewExpenseAnalysis');

route::get('GetExpenseAnalysisByCategory','ReportController@GetExpenseAnalysisByCategory')->name('GetExpenseAnalysisByCategory');
route::post('ViewExpenseAnalysisByCategory','ReportController@ViewExpenseAnalysisByCategory')->name('ViewExpenseAnalysisByCategory');

route::get('GetExpenseAnalysisByEmployee','ReportController@GetExpenseAnalysisByEmployee')->name('GetExpenseAnalysisByEmployee');
route::post('ViewExpenseAnalysisByEmployee','ReportController@ViewExpenseAnalysisByEmployee')->name('ViewExpenseAnalysisByEmployee');

route::get('GetExpenseAnalysisBySupplier','ReportController@GetExpenseAnalysisBySupplier')->name('GetExpenseAnalysisBySupplier');
route::post('ViewExpenseAnalysisBySupplier','ReportController@ViewExpenseAnalysisBySupplier')->name('ViewExpenseAnalysisBySupplier');

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

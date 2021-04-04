<?php

namespace App\Providers;

use App\WebRepositories\BankRepository;
use App\WebRepositories\BankToBankRepository;
use App\WebRepositories\CityRepository;
use App\WebRepositories\CompanyRepository;
use App\WebRepositories\CompanyTypeRepository;
use App\WebRepositories\CountryRepository;
use App\WebRepositories\CustomerAdvanceRepository;
use App\WebRepositories\CustomerPricesRepository;
use App\WebRepositories\CustomerRepository;
use App\WebRepositories\DepositRepository;
use App\WebRepositories\DriverRepository;
use App\WebRepositories\EmployeeRepository;
use App\WebRepositories\ExpenseCategoryRepository;
use App\WebRepositories\ExpenseDetailRepository;
use App\WebRepositories\ExpenseDetailsRepository;
use App\WebRepositories\ExpenseRepository;
use App\WebRepositories\ExpensesRepository;
use App\WebRepositories\FinancerRepository;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use App\WebRepositories\Interfaces\IBankToBankRepositoryInterface;
use App\WebRepositories\Interfaces\ICityRepositoryInterface;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use App\WebRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use App\WebRepositories\Interfaces\ICountryRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerPricesRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use App\WebRepositories\Interfaces\IDepositRepositoryInterface;
use App\WebRepositories\Interfaces\IDriverRepositoryInterface;
use App\WebRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\WebRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use App\WebRepositories\Interfaces\IExpenseDetailRepositoryInterface;
use App\WebRepositories\Interfaces\IExpenseDetailsRepositoryInterface;
use App\WebRepositories\Interfaces\IExpenseRepositoryInterface;
use App\WebRepositories\Interfaces\IExpensesRepositoryInterface;
use App\WebRepositories\Interfaces\IFinancerRepositoryInterface;
use App\WebRepositories\Interfaces\IInwardLoanRepositoryInterface;
use App\WebRepositories\Interfaces\ILoanRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReadingDetailRepositoryInterface;
use App\WebRepositories\Interfaces\IMeterReadingRepositoryInterface;
use App\WebRepositories\Interfaces\IOutwardLoandRepositoryInterface;
use App\WebRepositories\Interfaces\IPaymentReceiveDetailRepositoryInterface;
use App\WebRepositories\Interfaces\IPaymentReceiveRepositoryInterface;
use App\WebRepositories\Interfaces\IPaymentTermRepositoryInterface;
use App\WebRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use App\WebRepositories\Interfaces\IProductRepositoryInterface;
use App\WebRepositories\Interfaces\IPurchaseDetailRepositoryInterface;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\WebRepositories\Interfaces\IRegionRepositoryInterface;
use App\WebRepositories\Interfaces\IReportRepositoryInterface;
use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use App\WebRepositories\Interfaces\ISaleDetailsRepositoryInterface;
use App\WebRepositories\Interfaces\ISaleRepositoryInterface;
use App\WebRepositories\Interfaces\IStatesRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryDetailInterface;
use App\WebRepositories\Interfaces\ISupplierPaymentRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use App\WebRepositories\Interfaces\IUnitRepositoryInterface;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use App\WebRepositories\InwardLoanRepository;
use App\WebRepositories\LoanRepository;
use App\WebRepositories\MeterReaderRepository;
use App\WebRepositories\MeterReadingDetailRepository;
use App\WebRepositories\MeterReadingRepository;
use App\WebRepositories\OutwardLoanRepository;
use App\WebRepositories\PaymentReceiveDetailRepository;
use App\WebRepositories\PaymentReceiveRepository;
use App\WebRepositories\PaymentTermRepository;
use App\WebRepositories\PaymentTypeRepository;
use App\WebRepositories\ProductRepository;
use App\WebRepositories\PurchaseDetailRepository;
use App\WebRepositories\PurchaseRepository;
use App\WebRepositories\RegionRepository;
use App\WebRepositories\ReportRepository;
use App\WebRepositories\RoleRepository;
use App\WebRepositories\SaleDetailsRepository;
use App\WebRepositories\SaleRepository;
use App\WebRepositories\StateRepository;
use App\WebRepositories\SupplierAdvanceRepository;
use App\WebRepositories\SupplierPaymentDetailRepository;
use App\WebRepositories\SupplierPaymentRepository;
use App\WebRepositories\SupplierRepository;
use App\WebRepositories\UnitRepository;
use App\WebRepositories\UserRepository;
use App\WebRepositories\VehicleRepository;
use Illuminate\Support\ServiceProvider;

class WebRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IUserRepositoryInterface::class,UserRepository::class);
        $this->app->bind(IRoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ICompanyRepositoryInterface::class,CompanyRepository::class);
        $this->app->bind(ICustomerRepositoryInterface::class,CustomerRepository::class);
        $this->app->bind(IDriverRepositoryInterface::class, DriverRepository::class);
        $this->app->bind(IVehicleRepositoryInterface::class,VehicleRepository::class);
        $this->app->bind(ICustomerAdvanceRepositoryInterface::class,CustomerAdvanceRepository::class);
        $this->app->bind(ISupplierRepositoryInterface::class, SupplierRepository::class);
        $this->app->bind(ISupplierAdvanceRepositoryInterface::class, SupplierAdvanceRepository::class);
        $this->app->bind(IBankRepositoryInterface::class, BankRepository::class);
        $this->app->bind(ICountryRepositoryInterface::class, CountryRepository::class);
        $this->app->bind(IStatesRepositoryInterface::class, StateRepository::class);
        $this->app->bind(ICityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(IRegionRepositoryInterface::class, RegionRepository::class);
        $this->app->bind(IUnitRepositoryInterface::class, UnitRepository::class);
        $this->app->bind(IProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(IPurchaseRepositoryInterface::class, PurchaseRepository::class);
        $this->app->bind(IPurchaseDetailRepositoryInterface::class,PurchaseDetailRepository::class);
        $this->app->bind(IExpenseCategoryRepositoryInterface::class, ExpenseCategoryRepository::class);
        $this->app->bind(IEmployeeRepositoryInterface::class,EmployeeRepository::class);
        $this->app->bind(IExpensesRepositoryInterface::class, ExpensesRepository::class);
        $this->app->bind(IExpenseDetailsRepositoryInterface::class,ExpenseDetailsRepository::class);
        $this->app->bind(ISaleRepositoryInterface::class,SaleRepository::class);
        $this->app->bind(ISaleDetailsRepositoryInterface::class,SaleDetailsRepository::class);
        $this->app->bind(IMeterReaderRepositoryInterface::class, MeterReaderRepository::class);
        $this->app->bind(IMeterReadingRepositoryInterface::class, MeterReadingRepository::class);
        $this->app->bind(IMeterReadingDetailRepositoryInterface::class, MeterReadingDetailRepository::class);
        $this->app->bind(ILoanRepositoryInterface::class,LoanRepository::class);
        $this->app->bind(ICustomerPricesRepositoryInterface::class, CustomerPricesRepository::class);
        $this->app->bind(ICompanyTypeRepositoryInterface::class, CompanyTypeRepository::class);
        $this->app->bind(IPaymentTypeRepositoryInterface::class, PaymentTypeRepository::class);
        $this->app->bind(IPaymentTermRepositoryInterface::class,PaymentTermRepository::class);
        $this->app->bind(IPaymentReceiveRepositoryInterface::class, PaymentReceiveRepository::class);
        $this->app->bind(IPaymentReceiveDetailRepositoryInterface::class, PaymentReceiveDetailRepository::class);
        $this->app->bind(ISupplierPaymentRepositoryInterface::class, SupplierPaymentRepository::class);
        $this->app->bind(ISupplierPaymentRepositoryDetailInterface::class, SupplierPaymentDetailRepository::class);
        $this->app->bind(IReportRepositoryInterface::class,ReportRepository::class);
        $this->app->bind(IDepositRepositoryInterface::class,DepositRepository::class);
        $this->app->bind(IFinancerRepositoryInterface::class,FinancerRepository::class);
        $this->app->bind(IInwardLoanRepositoryInterface::class,InwardLoanRepository::class);
        $this->app->bind(IOutwardLoandRepositoryInterface::class,OutwardLoanRepository::class);
        $this->app->bind(IBankToBankRepositoryInterface::class,BankToBankRepository::class);
    }

    public function boot()
    {
        //
    }
}

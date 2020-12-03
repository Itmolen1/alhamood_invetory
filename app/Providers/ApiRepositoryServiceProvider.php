<?php


namespace App\Providers;


use App\ApiRepositories\CityRepository;
use App\ApiRepositories\CompanyRepository;
use App\ApiRepositories\CompanyTypeRepository;
use App\ApiRepositories\CountryRepository;
use App\ApiRepositories\CustomerAdvanceRepository;
use App\ApiRepositories\CustomerRepository;
use App\ApiRepositories\DriverRepository;
use App\ApiRepositories\EmployeeRepository;
use App\ApiRepositories\ExpenseCategoryRepository;
use App\ApiRepositories\ExpenseRepository;
use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\ApiRepositories\Interfaces\ICityRepositoryInterface;
use App\ApiRepositories\Interfaces\ICompanyRepositoryInterface;
use App\ApiRepositories\Interfaces\ICompanyTypeRepositoryInterface;
use App\ApiRepositories\Interfaces\ICountryRepositoryInterface;
use App\ApiRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\ApiRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\ApiRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use App\ApiRepositories\Interfaces\IExpenseRepositoryInterface;
use App\ApiRepositories\Interfaces\ILoanRepositoryInterface;
use App\ApiRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\ApiRepositories\Interfaces\IMeterReadingRepositoryInterface;
use App\ApiRepositories\Interfaces\IPaymentTermRepositoryInterface;
use App\ApiRepositories\Interfaces\IPaymentTypeRepositoryInterface;
use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\ApiRepositories\Interfaces\IRegionRepositoryInterface;
use App\ApiRepositories\Interfaces\ISalesRepositoryInterface;
use App\ApiRepositories\Interfaces\IStateRepositoryInterface;
use App\ApiRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\ApiRepositories\Interfaces\IUnitRepositoryInterface;
use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\ApiRepositories\LoanRepository;
use App\ApiRepositories\MeterReaderRepository;
use App\ApiRepositories\MeterReadingRepository;
use App\ApiRepositories\PaymentTermRepository;
use App\ApiRepositories\PaymentTypeRepository;
use App\ApiRepositories\ProductRepository;
use App\ApiRepositories\PurchaseRepository;
use App\ApiRepositories\RegionRepository;
use App\ApiRepositories\SalesRepository;
use App\ApiRepositories\StateRepository;
use App\ApiRepositories\SupplierAdvanceRepository;
use App\ApiRepositories\SupplierRepository;
use App\ApiRepositories\UnitRepository;
use App\ApiRepositories\UserRepository;
use App\ApiRepositories\VehicleRepository;
use Illuminate\Support\ServiceProvider;

class ApiRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IBankRepositoryInterface::class,\App\ApiRepositories\BankRepository::class);
        $this->app->bind(IUserRepositoryInterface::class,UserRepository::class);
        $this->app->bind(IDriverRepositoryInterface::class,DriverRepository::class);
        $this->app->bind(IVehicleRepositoryInterface::class,VehicleRepository::class);
        $this->app->bind(ICustomerRepositoryInterface::class,CustomerRepository::class);
        $this->app->bind(ISupplierRepositoryInterface::class,SupplierRepository::class);
        $this->app->bind(IUnitRepositoryInterface::class,UnitRepository::class);
        $this->app->bind(IProductRepositoryInterface::class,ProductRepository::class);
        $this->app->bind(IEmployeeRepositoryInterface::class,EmployeeRepository::class);
        $this->app->bind(IPurchaseRepositoryInterface::class,PurchaseRepository::class);
        $this->app->bind(ICompanyRepositoryInterface::class,CompanyRepository::class);
        $this->app->bind(IExpenseCategoryRepositoryInterface::class,ExpenseCategoryRepository::class);
        $this->app->bind(IExpenseRepositoryInterface::class,ExpenseRepository::class);
        $this->app->bind(IMeterReaderRepositoryInterface::class,MeterReaderRepository::class);
        $this->app->bind(IMeterReadingRepositoryInterface::class,MeterReadingRepository::class);
        $this->app->bind(ICountryRepositoryInterface::class,CountryRepository::class);
        $this->app->bind(IStateRepositoryInterface::class,StateRepository::class);
        $this->app->bind(ICityRepositoryInterface::class,CityRepository::class);
        $this->app->bind(IRegionRepositoryInterface::class,RegionRepository::class);
        $this->app->bind(ICustomerAdvanceRepositoryInterface::class,CustomerAdvanceRepository::class);
        $this->app->bind(ISupplierAdvanceRepositoryInterface::class,SupplierAdvanceRepository::class);
        $this->app->bind(ILoanRepositoryInterface::class,LoanRepository::class);
        $this->app->bind(ISalesRepositoryInterface::class,SalesRepository::class);
        $this->app->bind(ICompanyTypeRepositoryInterface::class,CompanyTypeRepository::class);
        $this->app->bind(IPaymentTypeRepositoryInterface::class,PaymentTypeRepository::class);
        $this->app->bind(IPaymentTermRepositoryInterface::class,PaymentTermRepository::class);
    }
    public function boot()
    {
        //
    }
}

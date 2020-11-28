<?php


namespace App\Providers;


use App\ApiRepositories\CompanyRepository;
use App\ApiRepositories\CountryRepository;
use App\ApiRepositories\CustomerRepository;
use App\ApiRepositories\DriverRepository;
use App\ApiRepositories\EmployeeRepository;
use App\ApiRepositories\ExpenseCategoryRepository;
use App\ApiRepositories\ExpenseRepository;
use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\ApiRepositories\Interfaces\ICompanyRepositoryInterface;
use App\ApiRepositories\Interfaces\ICountryRepositoryInterface;
use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\ApiRepositories\Interfaces\IEmployeeRepositoryInterface;
use App\ApiRepositories\Interfaces\IExpenseCategoryRepositoryInterface;
use App\ApiRepositories\Interfaces\IExpenseRepositoryInterface;
use App\ApiRepositories\Interfaces\IMeterReaderRepositoryInterface;
use App\ApiRepositories\Interfaces\IMeterReadingRepositoryInterface;
use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\ApiRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\ApiRepositories\Interfaces\IStateRepositoryInterface;
use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\ApiRepositories\Interfaces\IUnitRepositoryInterface;
use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\ApiRepositories\MeterReaderRepository;
use App\ApiRepositories\MeterReadingRepository;
use App\ApiRepositories\ProductRepository;
use App\ApiRepositories\PurchaseRepository;
use App\ApiRepositories\StateRepository;
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
    }
    public function boot()
    {
        //
    }
}

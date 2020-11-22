<?php

namespace App\Providers;

use App\WebRepositories\BankRepository;
use App\WebRepositories\CityRepository;
use App\WebRepositories\CompanyRepository;
use App\WebRepositories\CountryRepository;
use App\WebRepositories\CustomerAdvanceRepository;
use App\WebRepositories\CustomerRepository;
use App\WebRepositories\DriverRepository;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use App\WebRepositories\Interfaces\ICityRepositoryInterface;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use App\WebRepositories\Interfaces\ICountryRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use App\WebRepositories\Interfaces\IDriverRepositoryInterface;
use App\WebRepositories\Interfaces\IProductRepositoryInterface;
use App\WebRepositories\Interfaces\IPurchaseDetailRepositoryInterface;
use App\WebRepositories\Interfaces\IPurchaseRepositoryInterface;
use App\WebRepositories\Interfaces\IRegionRepositoryInterface;
use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use App\WebRepositories\Interfaces\IStatesRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use App\WebRepositories\Interfaces\IUnitRepositoryInterface;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use App\WebRepositories\ProductRepository;
use App\WebRepositories\PurchaseDetailRepository;
use App\WebRepositories\PurchaseRepository;
use App\WebRepositories\RegionRepository;
use App\WebRepositories\RoleRepository;
use App\WebRepositories\StateRepository;
use App\WebRepositories\SupplierAdvanceRepository;
use App\WebRepositories\SupplierRepository;
use App\WebRepositories\UnitRepository;
use App\WebRepositories\UserRepository;
use App\WebRepositories\VehicleRepository;
use Illuminate\Support\ServiceProvider;

class WebRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
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
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

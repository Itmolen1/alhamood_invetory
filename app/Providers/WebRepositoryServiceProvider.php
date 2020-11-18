<?php

namespace App\Providers;

use App\WebRepositories\BankRepository;
use App\WebRepositories\CompanyRepository;
use App\WebRepositories\CustomerAdvanceRepository;
use App\WebRepositories\CustomerRepository;
use App\WebRepositories\DriverRepository;
use App\WebRepositories\Interfaces\IBankRepositoryInterface;
use App\WebRepositories\Interfaces\ICompanyRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ICustomerRepositoryInterface;
use App\WebRepositories\Interfaces\IDriverRepositoryInterface;
use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierAdvanceRepositoryInterface;
use App\WebRepositories\Interfaces\ISupplierRepositoryInterface;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use App\WebRepositories\Interfaces\IVehicleRepositoryInterface;
use App\WebRepositories\RoleRepository;
use App\WebRepositories\SupplierAdvanceRepository;
use App\WebRepositories\SupplierRepository;
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

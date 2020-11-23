<?php


namespace App\Providers;


use App\ApiRepositories\CustomerRepository;
use App\ApiRepositories\DriverRepository;
use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\ApiRepositories\Interfaces\ICustomerRepositoryInterface;
use App\ApiRepositories\Interfaces\IDriverRepositoryInterface;
use App\ApiRepositories\Interfaces\IProductRepositoryInterface;
use App\ApiRepositories\Interfaces\ISupplierRepositoryInterface;
use App\ApiRepositories\Interfaces\IUnitRepositoryInterface;
use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\ApiRepositories\Interfaces\IVehicleRepositoryInterface;
use App\ApiRepositories\ProductRepository;
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
    }
    public function boot()
    {
        //
    }
}

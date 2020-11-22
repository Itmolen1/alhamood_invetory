<?php


namespace App\Providers;


use App\ApiRepositories\Interfaces\IBankRepositoryInterface;
use App\ApiRepositories\Interfaces\IUserRepositoryInterface;
use App\ApiRepositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class ApiRepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IBankRepositoryInterface::class,\App\ApiRepositories\BankRepository::class);
        $this->app->bind(IUserRepositoryInterface::class,UserRepository::class);
    }
    public function boot()
    {
        //
    }
}

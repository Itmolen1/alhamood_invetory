<?php

namespace App\Providers;

use App\WebRepositories\Interfaces\IRoleRepositoryInterface;
use App\WebRepositories\Interfaces\IUserRepositoryInterface;
use App\WebRepositories\RoleRepository;
use App\WebRepositories\UserRepository;
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

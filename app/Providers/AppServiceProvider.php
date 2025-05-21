<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PasienRepositoryInterface;
use App\Repositories\PasienRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
           $this->app->bind(PasienRepositoryInterface::class, PasienRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

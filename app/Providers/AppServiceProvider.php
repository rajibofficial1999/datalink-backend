<?php

namespace App\Providers;

use App\Interfaces\AccountManage;
use App\Services\ServiceImplementation\AccountInformationProcess;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AccountManage::class, AccountInformationProcess::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}

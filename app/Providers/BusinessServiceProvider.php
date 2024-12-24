<?php

namespace App\Providers;

use App\Services\Business\ResponseService;
use Illuminate\Support\ServiceProvider;

class BusinessServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(
            ResponseService::class,
            ResponseService::class
        );
    }

}

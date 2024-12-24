<?php


namespace App\Providers;


use App\Services\External\SnowFlake\SnowFlake;
use Illuminate\Support\ServiceProvider;

class ExternalServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
    }


    public function register()
    {

        /**
         * 雪花算法生成UID
         */
        $this->app->singleton(SnowFlake::class, function() {
            return new SnowFlake();
        });

    }
}
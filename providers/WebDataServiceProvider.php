<?php

namespace App\AnuVi\Providers;

use Illuminate\Support\ServiceProvider;

use App\AnuVi\WebDataManager;

class WebDataServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('App\AnuVi\WebDataManager', function ($app) {
            return new WebDataManager($app);
        });
    }
}

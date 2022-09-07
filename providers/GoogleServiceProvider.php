<?php

namespace App\AnuVi\Providers;

use Illuminate\Support\ServiceProvider;

use App\AnuVi\GoogleManager;

class GoogleServiceProvider extends ServiceProvider
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
        $this->app->singleton('App\AnuVi\GoogleManager', function ($app) {
            return new GoogleManager($app);
        });
    }
}

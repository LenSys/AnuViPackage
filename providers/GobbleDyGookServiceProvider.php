<?php

namespace App\AnuVi\Providers;

use Illuminate\Support\ServiceProvider;

use App\AnuVi\GobbleDyGookManager;

class GobbleDyGookServiceProvider extends ServiceProvider
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
        $this->app->singleton('App\AnuVi\GobbleDyGookManager', function ($app) {
            return new GobbleDyGookManager($app);
        });
    }
}

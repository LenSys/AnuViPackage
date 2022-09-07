<?php

namespace App\AnuVi\Providers;

use Illuminate\Support\ServiceProvider;

use App\AnuVi\PixabayManager;

class PixabayServiceProvider extends ServiceProvider
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
        $this->app->singleton('App\AnuVi\PixabayManager', function ($app) {
            return new PixabayManager($app);
        });
    }
}

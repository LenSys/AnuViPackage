<?php

namespace App\AnuVi\Providers;

use Illuminate\Support\ServiceProvider;

use App\AnuVi\WordPressManager;

class WordPressServiceProvider extends ServiceProvider
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
        $this->app->singleton('App\AnuVi\WordPressManager', function ($app) {
            return new WordPressManager($app);
        });
    }
}

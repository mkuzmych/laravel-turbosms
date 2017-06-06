<?php

namespace Uapixart\LaravelTurbosms;

use Illuminate\Support\ServiceProvider;

class TurbosmsServiceProvider extends ServiceProvider
{
    /**
    * Perform post-registration booting of services.
    *
    * @return void
    */
    public function boot()
    {
        // Configuration
        $this->publishes([
            __DIR__ . '/../config/turbosms.php' => config_path('turbosms.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->app->make('config');

        /**
        * Register main turbosms service
        */
        $this->app->bind('laravel-turbosms', function($app) {
            return new Turbosms();
        });
    }
}

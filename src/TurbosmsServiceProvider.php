<?php

namespace Uapixart\LaravelTurbosms;

use Illuminate\Support\ServiceProvider;

class TurbosmsServiceProvider extends ServiceProvider
{
    /**
<<<<<<< HEAD
=======
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
>>>>>>> 2d459bb11f62c37fecf1a1ab345a002f595a288b
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
<<<<<<< HEAD
=======
            $confer = 1;
>>>>>>> 2d459bb11f62c37fecf1a1ab345a002f595a288b
            return new Turbosms();
        });
    }
}

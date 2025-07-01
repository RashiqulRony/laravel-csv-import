<?php

namespace Rashiqulrony\CSVImport\Providers;

use Illuminate\Support\ServiceProvider;
use Rashiqulrony\CSVUpload\CSVUpload;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/csvimport.php', 'csvimport');

        $this->app->bind('csvimport', function () {
            return new CSVUpload();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/csvimport.php' => config_path('csvimport.php'),
        ], 'config');
    }
}

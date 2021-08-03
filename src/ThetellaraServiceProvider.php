<?php

/*
 * This file is part of Thetellara package.
 *
 * (c) Mumuni Mohammed <mumunim10@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kalkulus\Thetellara;

use Illuminate\Support\ServiceProvider;

class ThetellaraServiceProvider extends ServiceProvider
{
    /*
    * Indicates if loading of the provider is deferred.
    *
    * @var bool
    */
    protected $defer = false;

    /**
    * Publishes all the config file this package needs to function
    */
    public function boot()
    {
        $config = realpath(__DIR__.'/../resources/config/theteller.php');

        $this->publishes([
            $config => config_path('theteller.php')
        ]);
    }

    /**
    * Register the application services.
    */
    public function register()
    {
        $this->app->bind('thetellara', function () {

            return new Thetellara;

        });
    }

    /**
    * Get the services provided by the provider
    * @return array
    */
    public function provides()
    {
        return ['thetellara'];
    }
}

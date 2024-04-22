<?php

namespace multboy\desafio;

use Illuminate\Support\ServiceProvider as Provider;

class ServiceProvider extends Provider
{
    // this function run on package install
    public function boot()
    {
        // Load gateway migrations to main project
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load gateway seeds to main project
        $this->publishes([
            __DIR__.'/database/seeders' => public_path('vendor/multboy/desafio/src/database/seeds')
        ]);
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Scrabble;

class ScrabbleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Scrabble::class, function ($app) {
            return new Scrabble();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}

<?php

namespace PouyaParsaei\LaravelToDo;

use Illuminate\Support\ServiceProvider;

class LaravelToDoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->loadFactoriesFrom(__DIR__.'/database/factories');
    }

    public function register()
    {

    }
}

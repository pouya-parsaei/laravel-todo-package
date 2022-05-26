<?php

namespace PouyaParsaei\LaravelToDo;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use PouyaParsaei\LaravelToDo\Http\Middleware\CheckUserToken;

class LaravelToDoServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->loadFactoriesFrom(__DIR__.'/database/factories');

        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'todo');

        $this->loadRoutesFrom(__DIR__ . '/routes/api/v1.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/resources/views','todo');

        $this->publishes([
            __DIR__.'/resources/lang' => resource_path('lang/vendor/todo'),
            __DIR__ . '/resources/views' => resource_path('views/vendor/todo')

        ]);

    }

    public function register()
    {

    }
}

<?php

namespace PouyaParsaei\LaravelToDo\tests\TestingHelpers;

use PouyaParsaei\LaravelToDo\Http\Middleware\CheckUserToken;

trait Middlewares
{
    protected $middlewares = ['api','auth:api'];
}

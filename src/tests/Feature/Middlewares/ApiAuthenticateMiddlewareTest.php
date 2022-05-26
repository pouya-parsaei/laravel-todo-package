<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Middlewares;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Middleware\ApiAuthenticate;
use PouyaParsaei\LaravelToDo\Http\Middleware\CheckUserToken;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\TokenMaker;
use Tests\TestCase;

class ApiAuthenticateMiddlewareTest extends TestCase
{
    use RefreshDatabase, TokenMaker;

    public function testWhenUserAuthenticated()
    {
        $user = factory(User::class)->create();
        auth()->login($user);
        $request = Request::create('/labels', 'GET');

        $middleware = new ApiAuthenticate();

        $response = $middleware->handle($request,function(){});

        $this->assertEquals(null,$response);
    }

    public function testWhenUserIsNotAuthenticated()
    {
        $user = factory(User::class)->create();
        $request = Request::create('/labels', 'GET');

        $middleware = new ApiAuthenticate();

        $response = $middleware->handle($request,function(){});

        $this->assertEquals(401,$response->getStatusCode());
    }

}

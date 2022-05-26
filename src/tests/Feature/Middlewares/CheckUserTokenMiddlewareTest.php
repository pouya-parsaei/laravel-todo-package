<?php

namespace PouyaParsaei\LaravelToDo\tests\Feature\Middlewares;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;
use PouyaParsaei\LaravelToDo\Http\Middleware\CheckUserToken;
use PouyaParsaei\LaravelToDo\Models\Label;
use PouyaParsaei\LaravelToDo\tests\TestingHelpers\TokenMaker;
use Tests\TestCase;

class CheckUserTokenMiddlewareTest extends TestCase
{
    use RefreshDatabase, TokenMaker;

    public function testWhenUserHasToken()
    {
        $user = factory(User::class)->create();
        auth()->login($user);
        $token =  $this->createAuthorizationToken($user->api_token);

        $request = Request::create('/labels', 'GET');
        $request->headers->set('Authorization', $token);


        $middleware = new CheckUserToken();

        $response = $middleware->handle($request,function(){});

        $this->assertEquals(null,$response);
    }

    public function testWhenUserDoesNotHaveToken()
    {
        $user = factory(User::class)->create();
        auth()->login($user);
        $request = Request::create('/labels', 'GET');
        $request->headers->set('Authorization', '');
        $middleware = new CheckUserToken();

        $response = $middleware->handle($request,function(){});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }

    public function testWhenUserHasInvalidToken()
    {
        $user = factory(User::class)->create();
        auth()->login($user);
        $request = Request::create('/labels', 'GET');
        $FakeToken = $this->createFakeToken();
        $request->headers->set('Authorization', $FakeToken);

        $middleware = new CheckUserToken();

        $response = $middleware->handle($request,function(){});

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJson($response->getContent());
    }
}

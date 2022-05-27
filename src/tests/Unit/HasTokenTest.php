<?php

namespace PouyaParsaei\LaravelToDo\tests\Unit;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PouyaParsaei\LaravelToDo\Traits\HasToken;
use Tests\TestCase;

class HasTokenTest extends TestCase
{
    use RefreshDatabase,HasToken;

    public function testHasValidToken()
    {
        $user = factory(User::class)->create();

        $this->assertTrue($user->hasValidToken($user->api_token));
        $this->assertFalse($user->hasValidToken(Str::random(60)));
    }
}

<?php

namespace PouyaParsaei\LaravelToDo\tests\TestingHelpers;

use Illuminate\Support\Str;

trait TokenMaker
{
    public function createAuthorizationToken(string $token)
    {
        return 'Bearer' . ' ' . $token;
    }

    public function createFakeToken()
    {
        return 'Bearer' . ' ' . Str::random(48);
    }
}

<?php

namespace PouyaParsaei\LaravelToDo\Traits;

trait HasToken
{
    public function hasValidToken(string $token)
    {
        return $this->api_token == $token;
    }
}

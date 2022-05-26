<?php

namespace PouyaParsaei\LaravelToDo\Http\Middleware;

use Closure;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;

class ApiAuthenticate
{
    use ResponseHelper;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(!auth()->user())
            return $this->respondNotAuthenticated(trans('todo::messages.errors.not authenticated'));

        return $next($request);
    }
}

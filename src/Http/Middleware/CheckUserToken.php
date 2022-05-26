<?php

namespace PouyaParsaei\LaravelToDo\Http\Middleware;

use Closure;
use PouyaParsaei\LaravelToDo\Helpers\ResponseHelper;

class CheckUserToken
{
    use ResponseHelper;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!$request->hasHeader('authorization') || !auth()->user()->hasValidToken(substr($request->header('authorization'), 7)))
            return $this->respondNotAuthorized(trans('todo::messages.errors.not authorized'));

        return $next($request);

    }
}

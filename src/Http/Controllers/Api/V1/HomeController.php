<?php

namespace PouyaParsaei\LaravelToDo\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('todo::dashboard.home');
    }
}
